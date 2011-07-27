<?php

function unl_migration($form, &$form_state)
{
    if ($form_state['rebuild']) {
        $form['root'] = array(
            '#type' => 'fieldset',
            '#title' => 'This is taking a while.  Click continue.'
        );
        $form['root']['submit'] = array(
            '#type' => 'submit',
            '#value' => 'Continue',
        );
        return $form;
    } 
    
    
    $form['root'] = array(
        '#type' => 'fieldset',
        '#title' => 'Migration Tool',
    );
    
    $form['root']['site_url'] = array(
        '#type' => 'textfield',
        '#title' => t('Site URL'),
        '#description' => t('Full URL to the existing site you wish to migrate'),
        '#required' => TRUE
    );
    
    $form['root']['frontier_path'] = array(
        '#type' => 'textfield',
        '#title' => t('Frontier FTP Path'),
        '#description' => t('Full path to the root of your site on frontier (if applicable).'),
        '#required' => FALSE
    );
    $form['root']['frontier_user'] = array(
        '#type' => 'textfield',
        '#title' => t('Frontier FTP Username'),
        '#required' => FALSE
    );
    $form['root']['frontier_pass'] = array(
        '#type' => 'password',
        '#title' => t('Frontier FTP Password'),
        '#required' => FALSE
    );
    $form['root']['ignore_duplicates'] = array(
        '#type' => 'checkbox',
        '#title' => t('Ignore Duplicate Pages/Files'),
        '#description' => t("This may be needed if your site has an unlimited number of dynamicly generated paths."),
    );
    
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Migrate'
    );
    
    return $form;
}

function unl_migration_submit($form, &$form_state) {
  
  $migration = new Unl_Migration_Tool(
    $form_state['values']['site_url'],
    $form_state['values']['frontier_path'],
    $form_state['values']['frontier_user'],
    $form_state['values']['frontier_pass'],
    $form_state['values']['ignore_duplicates']
  );
  
  $operations = array(
    array(
      'unl_migration_step',
      array(
        $migration,
      )
    )
  );
  
  $batch = array(
  	'operations' => $operations,
  	'file' => substr(__FILE__, strlen(DRUPAL_ROOT) + 1),
  );
  batch_set($batch);
}

function unl_migration_step($migration, &$context)
{
  $finished = 0;
  if (isset($context['sandbox']['file']) && file_exists($context['sandbox']['file'])) {
    $migration = Unl_Migration_Tool::load_from_disk($context['sandbox']['file']);
    $finished = $context['sandbox']['finished'];
  }
  
  if ($migration->migrate()) {
    $context['finished'] = 1;
    return;
  }
  
  $finished += 0.01;
  if ($finished > 0.99) {
    $finished = 0.99;
  }
  $context['finished'] = $finished;
  $context['sandbox']['finished'] = $finished;
  $context['sandbox']['file'] = Unl_Migration_Tool::save_to_disk($migration);
}

function unl_migration_queue_step($migration_storage_file) {
  $migration = Unl_Migration_Tool::load_from_disk($migration_storage_file);
  if ($migration->migrate(30)) {
    return TRUE;
  }
  DrupalQueue::get('unl_migration', TRUE)
    ->createItem(Unl_Migration_Tool::save_to_disk($migration));
  return FALSE;
}


class Unl_Migration_Tool
{
    /**
     * base url to the site to migrate, eg: http://www.unl.edu/band/
     *
     * @var string
     */
    private $_baseUrl;

    /**
     * base path to frontier dir, eg: /cwis/data/band
     *
     * @var string
     */
    private $_frontierPath;
    private $_frontierUser;
    private $_frontierPass;
    private $_frontier;

    private $_curl;

    private $_siteMap             = array();
    private $_processedPages      = array();
    private $_processedPageHashes = array();
    private $_content             = array();
    private $_createdContent      = array();
    private $_lastModifications   = array();
    private $_redirects           = array();
    private $_hrefTransform       = array();
    private $_hrefTransformFiles  = array();
    private $_menu                = array();
    private $_nodeMap             = array();
    private $_pageTitles          = array();
    private $_log                 = array();
    private $_blocks              = array();
    private $_isFrontier          = FALSE;
    private $_frontierIndexFiles  = array('low_bandwidth.shtml', 'index.shtml', 'index.html', 'index.htm', 'default.shtml');
    private $_frontierFilesScanned = array();
    private $_ignoreDuplicates    = FALSE;
    
    /**
     * Keep track of the state of the migration progress so that we can resume later
     * @var int
     */
    public $_state = self::STATE_NONE;
    const STATE_NONE              = 1;
    const STATE_PROCESSING_BLOCKS = 2;
    const STATE_PROCESSING_PAGES  = 3;
    const STATE_CREATING_NODES    = 4;
    const STATE_DONE              = 5;
    
    private $_start_time;
    
    public function __construct($baseUrl, $frontierPath, $frontierUser, $frontierPass, $ignoreDuplicates)
    {
        header('Content-type: text/plain');

        // Check to see if we're migrating from frontier so we can make some extra assumptions.
        $baseUrlParts = parse_url($baseUrl);
        $remoteHostname = @gethostbyaddr(gethostbyname($baseUrlParts['host']));
        if ($remoteHostname == 'frontier.unl.edu') {
            $this->_isFrontier = TRUE;
        }
        
        // Add trailing slash if necessary
        $baseUrl = trim($baseUrl);
        if (substr($baseUrl, -1) != '/') {
            //$baseUrl .= '/';
        }

        $this->_frontierPath = $frontierPath;
        $this->_frontierUser = $frontierUser;
        $this->_frontierPass = $frontierPass;
        
        $this->_ignoreDuplicates = (bool) $ignoreDuplicates;
        
        $this->_baseUrl = $baseUrl;
        $this->_addSitePath('');
    }
    
    public function migrate($time_limit = 5)
    {
        if (!$this->_sanity_check()) {
            return TRUE;
        }
      
        $this->_start_time = time();
        ini_set('memory_limit', -1);
        
        if ($this->_state == self::STATE_NONE) {
            if (!$this->_frontierScan('', $time_limit)) {
                return FALSE;
            }
            
            $this->_state = self::STATE_PROCESSING_BLOCKS;
            if (time() - $this->_start_time > $time_limit) {
                return FALSE;
            }
        }
        
        if ($this->_state == self::STATE_PROCESSING_BLOCKS) {
            // Parse the menu
            $this->_processMenu();
            $this->_process_blocks();
            $this->_state = self::STATE_PROCESSING_PAGES;
        }
        
        if ($this->_state == self::STATE_PROCESSING_PAGES) {
            // Process all of the pages on the site (Takes a while)
            do {
                set_time_limit(30);
                
                $pagesToProcess = $this->_getPagesToProcess();
                foreach ($pagesToProcess as $pageToProcess) {
                    if (time() - $this->_start_time > $time_limit) {
                        return FALSE;
                    }
                    $this->_processPage($pageToProcess);
                }
            } while (count($pagesToProcess) > 0);
         
        
            // Fix any links to files that got moved to sites/<site>/files
            foreach ($this->_hrefTransform as $path => &$transforms) {
                if (array_key_exists('', $transforms)) {
                    unset($transforms['']);
                }
                foreach ($transforms as $oldPath => &$newPath) {
                    if (array_key_exists($newPath, $this->_redirects)) {
                        $newPath = $this->_redirects[$newPath];
                    }
                    if (array_key_exists($newPath, $this->_hrefTransformFiles)) {
                        $newPath = $this->_hrefTransformFiles[$newPath];
                    }
                }
            }
           
            $this->_state = self::STATE_CREATING_NODES;
        }
        
        if ($this->_state == self::STATE_CREATING_NODES) {
            // Update links and then create new page nodes. (Takes a while)
            foreach ($this->_content as $path => $content) {
                if (in_array($path, $this->_createdContent, TRUE)) {
                    continue;
                }
                if (time() - $this->_start_time > $time_limit) {
                    return FALSE;
                }
                set_time_limit(30);
                
                $hrefTransform = isset($this->_hrefTransform[$path]) ? $this->_hrefTransform[$path] : array();
                $content = strtr($content, $hrefTransform);
                
                $pageTitle = $this->_pageTitles[$path];
                $this->_createPage($pageTitle, $content, $path, '' == $path);
                $this->_createdContent[] = $path;
            }
            
            $this->_createMenu();
            $this->_create_blocks();
            
            $this->_state = self::STATE_DONE;
        }
        
        return TRUE;
    }

    private function _sanity_check() {
      if (!$this->_getUrl($this->_baseUrl)) {
        form_set_error('unl', 'The specified site does not exist!');
        return FALSE;
      }
      return TRUE;
    }
    
    private function _addSitePath($path)
    {
        if (($fragmentStart = strrpos($path, '#')) !== FALSE) {
            $path = substr($path, 0, $fragmentStart);
        }
        $this->_siteMap[hash('SHA256', $path)] = $path;
    }
    
    private function _getPagesToProcess()
    {
        $pagesToProcess = array();
        foreach ($this->_siteMap as $path) {
            if (in_array($path, $this->_processedPages)) {
                continue;
            }
            $pagesToProcess[] = $path;
        }
        return $pagesToProcess;
    }
    
    private function _addProcessedPage($path)
    {
        $this->_processedPages[hash('SHA256', $path)] = $path;
    }
    
    private function _processMenu()
    {
        $content = $this->_getUrl($this->_baseUrl);
        $html = $content['content'];
        
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $navlinksNode = $dom->getElementById('navigation');
        if (!$navlinksNode) {
          return;
        }
    
        // Check to see if there's a base tag on this page.
        $base_tags = $dom->getElementsByTagName('base');
        $page_base = NULL;
        if ($base_tags->length > 0) {
          $page_base = $base_tags->item(0)->getAttribute('href');
        }
        
        $linkNodes = $navlinksNode->getElementsByTagName('a');
        foreach ($linkNodes as $linkNode) {
            $this->_processLinks($linkNode->getAttribute('href'), '', $page_base, '<menu>');
        }
        
        $navlinksUlNode = $navlinksNode->getElementsByTagName('ul')->item(0);
        foreach ($navlinksUlNode->childNodes as $primaryLinkLiNode) {
            if (strtolower($primaryLinkLiNode->nodeName) != 'li') {
                continue;
            }
            $primaryLinkNode = $primaryLinkLiNode->getElementsByTagName('a')->item(0);
            $menuItem = array('text' => trim($primaryLinkNode->textContent),
                              'href' => $this->_makeLinkAbsolute($primaryLinkNode->getAttribute('href'), ''));
            
            $childLinksUlNode = $primaryLinkLiNode->getElementsByTagName('ul')->item(0);
            if (!$childLinksUlNode) {
                $this->_menu[] = $menuItem;
                continue;
            }
            $childMenu = array();
            foreach ($childLinksUlNode->childNodes as $childLinkLiNode) {
                if (strtolower($childLinkLiNode->nodeName) != 'li') {
                    continue;
                }
                $childLinkNode = $childLinkLiNode->getElementsByTagName('a')->item(0);
                // If somebody left this menu item empty, skip it.  Liferay, I'm looking at you!
                if (!$childLinkNode || !$childLinkNode->hasAttribute('href')) {
                    continue;
                }
                $childMenu[] = array('text' => trim($childLinkNode->textContent),
                                     'href' => $this->_makeLinkAbsolute($childLinkNode->getAttribute('href'), ''));
            }
            $menuItem['children'] = $childMenu;
            $this->_menu[] = $menuItem;
        }
        
        if (count($this->_menu) == 0) {
            $this->_log('Could not find the navigation menu for your site!', WATCHDOG_ERROR);
        }
    }

    private function _createMenu()
    {
        // Start off by removing the "Home" menu link if it exists.
        $menu_links = menu_load_links('main-menu');
        foreach ($menu_links as $menu_link) {
          if ($menu_link['plid'] == 0 &&
              $menu_link['link_title'] == 'Home' &&
              $menu_link['link_path'] == '<front>') {
            menu_link_delete($menu_link['mlid']);
          }
        }
        
        // Now recursively create each menu.
        $primaryWeights = 1;
        foreach ($this->_menu as $primaryMenu) {
            $item = array(
                'expanded' => TRUE,
                'menu_name' => 'main-menu',
                'link_title' => $primaryMenu['text'],
                'link_path' => '',
                'weight' => $primaryWeights++
            );
            $href = $primaryMenu['href'];
            if (substr($href, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
                $path = substr($href, strlen($this->_baseUrl));
                if (!$path) {
                    $path = '';
                }
                if ($fragmentPos = strrpos($path, '#') !== FALSE) {
                    $item['options']['fragment'] = substr($path, $fragmentPos + 1);
                    $path = substr($path, 0, $fragmentPos);
                }
                if (substr($path, -1) == '/') {
                    $path = substr($path, 0, -1);
                }
                $nodeId = array_search($path, $this->_nodeMap, TRUE);
                if ($nodeId) {
                    $item['link_path'] = 'node/' . $nodeId;
                }  
            } else {
                $item['link_path'] = $href;
            }
            
            if ($item['link_path']) {
                menu_link_save($item);
                $this->_log('Created menu item "' . $item['link_title'] . '" linked to ' . $item['link_path'] . '.');
            } else {
                $this->_log('Could not find a node to link to the ' . $item['link_title'] . ' menu item.', WATCHDOG_ERROR);
                continue;
            }
            
            if (!array_key_exists('children', $primaryMenu)) {
                continue;
            }
            
            $plid = $item['mlid'];
            $parentTitle = $item['link_title'];
            $childWeights = 1;
            foreach ($primaryMenu['children'] as $childMenu) {
                $item = array(
                    'menu_name' => 'main-menu',
                    'link_title' => $childMenu['text'],
                    'link_path' => '',
                    'plid' => $plid,
                    'weight' => $childWeights++
                );
                $href = $childMenu['href'];
                if (substr($href, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
                    $path = substr($href, strlen($this->_baseUrl));
                    if (!$path) {
                        $path = '';
                    }
                    if (($fragmentPos = strrpos($path, '#')) !== FALSE) {
                        $item['options']['fragment'] = substr($path, $fragmentPos + 1);
                        $path = substr($path, 0, $fragmentPos);
                    }
                    if (substr($path, -1) == '/') {
                        $path = substr($path, 0, -1);
                    }
                    $nodeId = array_search($path, $this->_nodeMap, TRUE);
                    if ($nodeId) {
                        $item['link_path'] = 'node/' . $nodeId;
                    }
                } else {
                    $item['link_path'] = $href;
                }
                
                if ($item['link_path']) {
                    menu_link_save($item);
                    $this->_log('Created menu item "' . $parentTitle . ' / ' . $item['link_title'] . '" linked to ' . $item['link_path'] . '.');
                } else {
                    $this->_log('Could not find a node to link to the "' . $parentTitle . ' / ' . $item['link_title'] . '" menu.', WATCHDOG_ERROR);
                }
            }
        }
    }
    
    private function _process_blocks() {
      $content = $this->_getUrl($this->_baseUrl);
      $html = $content['content'];
      
      $this->_blocks['related_links'] = $this->_get_instance_editable_content($html, 'leftcollinks');
      $this->_blocks['contact_info'] = $this->_get_instance_editable_content($html, 'contactinfo');
      $this->_blocks['optional_footer'] = $this->_get_instance_editable_content($html, 'optionalfooter');
      $this->_blocks['footer_content'] = $this->_get_instance_editable_content($html, 'footercontent');
      // @TODO replace these with str_replace calls
      $this->_blocks['related_links'] = trim(strtr($this->_blocks['related_links'], array('<h3>Related Links</h3>' => '')));
      $this->_blocks['contact_info'] = trim(strtr($this->_blocks['contact_info'], array('<h3>Contacting Us</h3>' => '')));
      $this->_blocks['contact_info'] = trim(strtr($this->_blocks['contact_info'], array('<h3>Contact Us</h3>' => '')));
    }
    
    private function _create_blocks() {
      db_update('block_custom')
        ->fields(array(
          'body'   => $this->_blocks['contact_info'],
        ))
        ->condition('bid', 101)
        ->execute();
      db_update('block_custom')
        ->fields(array(
          'body'   => $this->_blocks['related_links'],
        ))
        ->condition('bid', 102)
        ->execute();
      db_update('block_custom')
        ->fields(array(
          'body'   => $this->_blocks['optional_footer'],
        ))
        ->condition('bid', 103)
        ->execute();
      db_update('block_custom')
        ->fields(array(
          'body'   => $this->_blocks['footer_content'],
        ))
        ->condition('bid', 104)
        ->execute();
    }
    
    private function _processPage($path)
    {
        $this->_addProcessedPage($path);
        $fullPath = $this->_baseUrl . $path;
        
        $url = $this->_baseUrl . $path;
    
        $data = $this->_getUrl($url);
        if (!$data['content']) {
            $this->_log('The file at ' . $fullPath . ' was empty! Ignoring.', WATCHDOG_ERROR);
            return;
        }
        
        $pageHash = hash('md5', $data['content']);
        if (($matchingPath = array_search($pageHash, $this->_processedPageHashes)) !== FALSE) {
            $logMessage = "The file found at $fullPath was a duplicate of the file at {$this->_baseUrl}$matchingPath !";
            if ($this->_ignoreDuplicates) {
                $this->_log($logMessage . ' Ignoring.', WATCHDOG_WARNING);
                return;
            } else {
                $this->_log($logMessage, WATCHDOG_WARNING);
            }
        }
        $this->_processedPageHashes[$path] = $pageHash; 
        
        if (isset($data['lastModified'])) {
            $this->_lastModifications[$path] = $data['lastModified'];
        }
        if (strpos($data['contentType'], 'html') === FALSE) {
            if (!$data['contentType']) {
                $this->_log('The file type at ' . $fullPath . ' was not specified. Ignoring.', WATCHDOG_ERROR);
                return;
            }
            @drupal_mkdir('public://' . urldecode(dirname($path)), NULL, TRUE);
            if (!mb_check_encoding($path, 'UTF-8')) {
                $path = iconv('ISO-8859-1', 'UTF-8', $path); 
            }
            try {
              $file = file_save_data($data['content'], 'public://' . urldecode($path), FILE_EXISTS_REPLACE);
            } catch (Exception $e) {
              $this->_log('Could not migrate file "' . $path . '"! File name too long?', WATCHDOG_ERROR);
            }
            $this->_hrefTransformFiles[$path] = file_stream_wrapper_get_instance_by_scheme('public')->getDirectoryPath() . '/' . $path;
            return;
        }
        $html = $data['content'];
        
        if (preg_match('/charset=(.*);?/', $data['contentType'], $matches)) {
            $charset = $matches[1];
            $html = iconv($charset, 'UTF-8', $html);
        }
        
        $maincontentarea = $this->_get_instance_editable_content($html, 'maincontentarea');
        if (!$maincontentarea) {
            $maincontentarea = $this->_get_old_main_content_area($html);
        }
    
        if (!$maincontentarea) {
            $this->_log('The file at ' . $fullPath . ' has no valid maincontentarea. Using entire body.', WATCHDOG_WARNING);
            $maincontentarea = $this->_get_text_between_tokens($html, '<body>', '</body>');
        }
    
        if (!$maincontentarea) {
            // its possible the body tag has attributes.  Check for this and filter them out.
            $maincontentarea = $this->_get_text_between_tokens($html, '<body', '</body>');
            // As long as we find a closing bracket before the next opening bracket, its probably safe to assume the body tag is intact. 
            if (strpos($maincontentarea, '>') < strpos($maincontentarea, '<')) {
              $maincontentarea = trim(substr($maincontentarea, strpos($maincontentarea, '>') + 1));
            // Otherwise, ignore it all. (Will be an issue if the body has no other tags, but how likely is this?)
            } else {
              $maincontentarea = '';
            }
        }
        
        if (!$maincontentarea) {
            $this->_log('The file at ' . $fullPath . ' has no valid body. Ignoring.', WATCHDOG_ERROR);
            return;
        }
        
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        // Check to see if there's a base tag on this page.
        $base_tags = $dom->getElementsByTagName('base');
        $page_base = NULL;
        if ($base_tags->length > 0) {
          $page_base = $base_tags->item(0)->getAttribute('href');
        }
        
        $pageTitle = '';
        $pageTitleNode = $dom->getElementById('pagetitle');
        if ($pageTitleNode) {
            $pageTitleH2Nodes = $pageTitleNode->getElementsByTagName('h2');
            if ($pageTitleH2Nodes->length > 0) {
                $pageTitle = $pageTitleH2Nodes->item(0)->textContent;
            }
        }
        
        if (!$pageTitle) {
            $titleText = '';
            $titleNodes = $dom->getElementsByTagName('title');
            if ($titleNodes->length > 0) {
                $titleText = $titleNodes->item(0)->textContent; 
            }
            $titleParts = explode('|', $titleText);
            if (count($titleParts) > 2) {
                $pageTitle = trim(array_pop($titleParts));
            }
            else {
                $pageTitle = $titleText;
            }
        }
        
        if (!$pageTitle) {
            $this->_log('No page title was found at ' . $fullPath . '.', WATCHDOG_ERROR);
            $pageTitle = 'Untitled';
        }
        
        $maincontentNode = $dom->getElementById('maincontent');
        if (!$maincontentNode) {
            $this->_log('The file at ' . $fullPath . ' has no valid maincontentarea. Using entire body.', WATCHDOG_WARNING);
            $bodyNodes = $dom->getElementsByTagName('body');
            if ($bodyNodes->length == 0) {
                $this->_log('The file at ' . $fullPath . ' has no valid body. Ignoring.', WATCHDOG_ERROR);
                return;
            }
            $maincontentNode = $bodyNodes->item(0);
        }
        
        $linkNodes = $maincontentNode->getElementsByTagName('a');
        foreach ($linkNodes as $linkNode) {
            $this->_processLinks($linkNode->getAttribute('href'), $path, $page_base);
        }
    
        $linkNodes = $maincontentNode->getElementsByTagName('img');
        foreach ($linkNodes as $linkNode) {
            $this->_processLinks($linkNode->getAttribute('src'), $path, $page_base);
        }
        
        $this->_content[$path] = $maincontentarea;
        $this->_pageTitles[$path] = $pageTitle;
    }
    
    private function _processLinks($originalHref, $path, $page_base = NULL, $tag = NULL)
    {
        if (substr($originalHref, 0, 1) == '#') {
            return;
        }
        
        if (!$page_base) {
          $page_base = $path;
        }
        
        $href = $this->_makeLinkAbsolute($originalHref, $page_base);
        
        if (substr($href, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
            $newPath = substr($href, strlen($this->_baseUrl));
            if ($newPath === FALSE) {
                $newPath = '';
            }
            if ($tag) {
                $this->_hrefTransform[$tag][$originalHref] = $newPath;
            } else {
                $this->_hrefTransform[$path][$originalHref] = $newPath;
            }
            $this->_addSitePath($newPath);
        }
    }
    
    private function _makeLinkAbsolute($href, $path)
    {
        $path_parts = parse_url($path);
        
        if (isset($path_parts['scheme'])) {
            $base_url = $path;
            $path = '';
        } else {
            $base_url = $this->_baseUrl;
        }
        
        if (substr($path, -1) == '/') {
            $intermediatePath = $path;
        } else {
            $intermediatePath = dirname($path);
        }
        if ($intermediatePath == '.') {
            $intermediatePath = '';
        }
        if (strlen($intermediatePath) > 0 && substr($intermediatePath, -1) != '/') {
            $intermediatePath .= '/';
        }
        
        $parts = parse_url($href);
        if (isset($parts['scheme']) && !in_array($parts['scheme'], array('http', 'https'))) {
            return $href;
        }
        if (isset($parts['scheme'])) {
            $absoluteUrl = $href;
        } else if (isset($parts['path']) && substr($parts['path'], 0, 1) == '/') {
            $baseParts = parse_url($this->_baseUrl);
            $absoluteUrl = $baseParts['scheme'] . '://' . $baseParts['host'] . $parts['path'];
            if (isset($parts['fragment'])) {
                $absoluteUrl .= '#' . $parts['fragment'];
            }
        } else if (substr($href, 0, 1) == '#') {
            $absoluteUrl = $this->_baseUrl . $path . $href;
        } else {
            $absoluteUrl = $this->_baseUrl . $intermediatePath . $href;
        }
        $parts = parse_url($absoluteUrl);
        
     /*   $this->_log('Absolute URL ' . $absoluteUrl . ' converted to parts:'
            .' scheme:' . $parts['scheme']
            .' host:' . $parts['host']
            .' port:' . $parts['port']
            .' user:' . $parts['user']
            .' pass:' . $parts['pass']
            .' path:' . $parts['path']
            .' query:' . $parts['query']
            .' fragment:' . $parts['fragment']); */
        
        if (isset($parts['path'])) {
            while (strpos($parts['path'], '/./') !== FALSE) {
                $parts['path'] = strtr($parts['path'], array('/./' => '/'));
            }
            $i = 0;
            while (strpos($parts['path'], '/../') !== FALSE) {
                $parts['path'] = preg_replace('/\\/[^\\/]*\\/\\.\\.\\//', '/', $parts['path']);
                $parts['path'] = preg_replace('/^\\/\\.\\.\\//', '/', $parts['path']);
                // Prevent infinite loops if we get some crazy url.
                if ($i++ > 100) exit;
            }
        }
        
        $absoluteUrl = $parts['scheme'] . '://' . $parts['host'];
        $absoluteUrl .= isset($parts['path']) ? $parts['path'] : '';
        $absoluteUrl .= isset($parts['query']) ? '?' . $parts['query'] : '';
        $absoluteUrl .= isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
        
        if (
          $this->_isFrontier &&
          substr($absoluteUrl, 0, strlen($this->_baseUrl)) == $this->_baseUrl &&
          in_array(basename($parts['path']), $this->_frontierIndexFiles)
        ) {
            $absoluteUrl = $parts['scheme'] . '://' . $parts['host'];
            $absoluteUrl .= isset($parts['path']) ? dirname($parts['path']) . '/' : '';
            $absoluteUrl .= isset($parts['query']) ? '?' . $parts['query'] : '';
            $absoluteUrl .= isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
        }
        
        return $absoluteUrl;
    }
    
    private function _createPage($title, $content, $alias = '', $makeFrontPage = FALSE)
    {
        
        if (substr($alias, -1) == '/') {
            $alias = substr($alias, 0, -1);
        }
        
        $node = new StdClass();
        $node->uid = $GLOBALS['user']->uid;
        $node->type = 'page';
        $node->title = $title;
        $node->language = 'und';
        $node->path['alias'] = $alias;
        if (module_exists('pathauto')) {
          $node->path['pathauto'] = FALSE;
        }
        
        $filter_format_keys = array_keys(filter_formats());
        $node->body = array(
          'und' => array(
            array(
              'value' => $content,
              'format' => array_shift($filter_format_keys),
            ),
          ),
        );
        
        node_submit($node);
        try {
            node_save($node);
        } catch (Exception $e) {
            $this->_log('Could not save page at ' . $alias . '. This is probably a case sensitivity conflict.', WATCHDOG_ERROR);
            return;
        }
        
        if (isset($this->_lastModifications[$alias])) {
            $mtime = $this->_lastModifications[$alias];
            $mtimes = array(
                'created' => $mtime,
                'changed' => $mtime
            );
            $result = db_update('node')
                ->fields($mtimes)
                ->condition('nid', $node->nid)
                ->execute();
        }
        
        $this->_nodeMap[$node->nid] = $alias;
        
        if ($makeFrontPage) {
            variable_set('site_frontpage', 'node/' . $node->nid);
            variable_set('site_name', $title);
        }
        
        $this->_log('Created page "' . $title . '" with node id ' . $node->nid . ' at ' . $alias . '.');
    }
    
    private function _getUrl($url)
    {
        if (!$this->_curl) {
          $this->_curl = curl_init();
        }
        $url = strtr($url, array(' ' => '%20'));
        curl_setopt($this->_curl, CURLOPT_URL, $url);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->_curl, CURLOPT_HEADER, TRUE);
        curl_setopt($this->_curl, CURLOPT_NOBODY, TRUE);
        
        $data = curl_exec($this->_curl);
        $meta = curl_getinfo($this->_curl);
        
        $rawHeaders = substr($data, 0, $meta['header_size']);
        $rawHeaders = trim($rawHeaders);
        $rawHeaders = explode("\n", $rawHeaders);
        array_shift($rawHeaders);
        $headers = array();
        foreach ($rawHeaders as $rawHeader) {
            $splitPos = strpos($rawHeader, ':');
            $headerKey = substr($rawHeader, 0, $splitPos);
            $headerValue = substr($rawHeader, $splitPos+1);
            $headers[$headerKey] = trim($headerValue);
        }
        
        // don't copy files greater than 10MB in size
        if (isset($headers['Content-Length']) && $headers['Content-Length'] > (10 * 1024 * 1024)) {
            $size = floor($headers['Content-Length'] / (1024 * 1024)); 
            $this->_log("The file at $url is $size MB!  Ignoring.", WATCHDOG_ERROR);
            $content = '';
        } else {
            curl_setopt($this->_curl, CURLOPT_NOBODY, FALSE);
            $data = curl_exec($this->_curl);
            $meta = curl_getinfo($this->_curl);
            $content = substr($data, $meta['header_size']);
        }
        
        
        if (in_array($meta['http_code'], array(301, 302))) {
            $location = $headers['Location'];
            $path = substr($location, strlen($this->_baseUrl));
            $this->_addSitePath($path);
            
            if (substr($location, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
                $this->_redirects[substr($url, strlen($this->_baseUrl))] = substr($location, strlen($this->_baseUrl));
            } else {
                $this->_redirects[substr($url, strlen($this->_baseUrl))] = $location;
            }
            
            $this->_log('Found a redirect from ' . $url . ' to ' . $location . '. Some links may need to be updated.', WATCHDOG_WARNING);
            return FALSE;
        } else if ($meta['http_code'] != 200) {
            $this->_log('HTTP ' . $meta['http_code'] . ' while fetching ' . $url . '. Possible dead link.', WATCHDOG_ERROR);
            return FALSE;
        }
        
        $data = array(
            'content' => $content,
            'contentType' => $meta['content_type'],
        );
        
        if ($this->_frontierPath) {
            $mtime = $this->_getModifiedDate($url);
            if ($mtime) {
                $data['lastModified'] = $mtime;
            } else if (isset($headers['Last-Modified'])) {
                $data['lastModified'] = strtotime($headers['Last-Modified']);
            }
        }
        
        return $data;
    }
    
    private function _getModifiedDate($url)
    {
        if (!$this->_frontierConnect()) {
            return NULL;
        }
        
        //Don't want url encoded chars like %20 in ftp file path 
        $url = urldecode($url);
        
        $path = substr($url, strlen($this->_baseUrl));
        if ($path[0] != '/') {
            $path = '/'.$path;
        }
        
        $ftpPath = $this->_frontierPath . $path;
        $ftpPaths = array();
        if (substr($ftpPath, -1) == '/') {
            foreach ($this->_frontierIndexFiles as $frontierIndexFile) {
                $ftpPaths[] = $ftpPath . $frontierIndexFile;
            }
        } else {
            $ftpPaths[] = $ftpPath;
        }
        
        foreach ($ftpPaths as $ftpPath) {
            $files = ftp_rawlist($this->_frontier, $ftpPath);
            if (isset($files[0])) {
                break;
            }
        }
        if (!isset($files[0])) {
            return NULL;
        }
        $mtime = substr($files[0], 43, 12);
        $mtime = strtotime($mtime);
        return $mtime;
    }
    
    private function _frontierConnect()
    {
        if (!$this->_isFrontier || !$this->_frontierPath) {
            return NULL;
        }
        
        if (!$this->_frontier) {
            $this->_frontier = ftp_ssl_connect('frontier.unl.edu');
            //TODO: make this a login that only has read access to everything.
            $login = ftp_login($this->_frontier, $this->_frontierUser, $this->_frontierPass);
            if (!$login) {
                $this->_frontier = NULL;
                $this->_log('Could not connect to frontier with user ' . $this->_frontierUser . '.', WATCHDOG_ERROR);
            }
            ftp_pasv($this->_frontier, TRUE);
        }
        return $this->_frontier;
    }
    
    private function _frontierScan($path, $time_limit)
    {
        if (!$this->_frontierConnect()) {
            return TRUE;
        }
        
        $ftpPath = $this->_frontierPath . $path;
        $rawFileList = ftp_rawlist($this->_frontier, $ftpPath);
        $fileList = ftp_nlist($this->_frontier, $ftpPath);
        $files = array();
        foreach ($rawFileList as $index => $rawListing) {
            $file = substr($fileList[$index], strlen($ftpPath));
            
            if (time() - $this->_start_time > $time_limit) {
                return FALSE;
            }
            
            if (in_array($path . $file, $this->_frontierFilesScanned)) {
                continue;
            }
            
            if (in_array($file, array('_notes', '_baks'))) {
                continue;
            }
            
            if (substr($rawListing, 0, 1) == 'd') {
                //file is a directory
                if (!$this->_frontierScan($path . $file . '/',  $time_limit)) {
                    return FALSE;
                };
            } else {
                if (substr($path, 0, 1) == '/') {
                    $path = substr($path, 1);
                }
                $files[] = $file;
                if (in_array($file, $this->_frontierIndexFiles)) {
                    $this->_addSitePath($path);
                } else {
                    $this->_addSitePath($path . $file);
                }
            }
            $this->_frontierFilesScanned[] = $path . $file;
        }
        return TRUE;
    }
    
    private function _log($message, $severity = WATCHDOG_INFO)
    {
      $this->_log[] = $message;
      
      if ($severity == WATCHDOG_INFO) {
        $type = 'status';
      }
      else if ($severity == WATCHDOG_WARNING) {
        $type = 'warning';
      }
      else {
        $type = 'error';
      }
      drupal_set_message($message, $type, FALSE);
      
      watchdog('unl migration', $message, NULL, $severity);
    }

  private function _get_instance_editable_content($html, $name) {
    $start_token = '<!-- InstanceBeginEditable name="' . $name . '" -->';
    $end_token = '<!-- InstanceEndEditable -->';
    
    if ($content = $this->_get_text_between_tokens($html, $start_token, $end_token)) {
      return $content;
    }
    
    
    $start_token = '<!-- TemplateBeginEditable name="' . $name . '" -->';
    $end_token = '<!-- TemplateEndEditable -->';
    
    if ($content = $this->_get_text_between_tokens($html, $start_token, $end_token)) {
      return $content;
    }
    
    return FALSE;
  }
  
  private function _get_old_main_content_area(&$html) {
    $start_token = '<!--THIS IS THE MAIN CONTENT AREA -->';
    $end_token = '<!--THIS IS THE END OF THE MAIN CONTENT AREA.-->';
    
    $content = $this->_get_text_between_tokens($html, $start_token, $end_token);
    
    $html = strtr($html, array(
      $start_token => $start_token . '<div id="maincontent">',
      $end_token   => $end_token . '</div>'
    ));
    
    return $content;
  }
  
  private function _get_text_between_tokens($text, $start_token, $end_token) {
    $content_start = strpos($text, $start_token);
    $content_end = strpos($text, $end_token, $content_start);
    $content = substr($text,
                      $content_start + strlen($start_token),
                      $content_end - $content_start - strlen($start_token));
    $content = trim($content);
  
    if ($content && $content_start !== FALSE && $content_end !== FALSE) {
      return $content;
    }
    
    return FALSE;
  }
  
  static public function save_to_disk(Unl_Migration_Tool $instance)
  {
    $migration_storage_file = drupal_tempnam(file_directory_temp(), 'unl_migration_');
    file_put_contents($migration_storage_file, serialize($instance));
    if (PHP_SAPI == 'cli') {
      chmod($migration_storage_file, 0666);
    }
    return $migration_storage_file;
  }
  
  static public function load_from_disk($migration_storage_file) {
    $instance = unserialize(file_get_contents($migration_storage_file));
    unlink($migration_storage_file);
    return $instance;
  }
}

