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
    $form['root']['use_liferay_code'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Liferay Detection'),
      '#description' => t("Normally, this won't interfere with non-liferay sites. If you have a /web directory, you should turn this off."),
      '#default_value' => 1,
    );
    $form['root']['use_liferay_titles'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Liferay Titles'),
      '#description' => t("Liferay doesn't use WDN compliant page titles. This enables their alternate method of finding the page title."),
      '#default_value' => 0,
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
    $form_state['values']['ignore_duplicates'],
    $form_state['values']['use_liferay_code'],
    $form_state['values']['use_liferay_titles']
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
  }
  if (!isset($context['sandbox']['duration'])) {
    $context['sandbox']['duration'] = 1;
  }

  if ($migration->migrate($context['sandbox']['duration'])) {
    $context['finished'] = 1;
    $context['message'] = $migration->getMessage();
    return;
  }

  $context['finished'] = $migration->getFinished();
  $context['message'] = $migration->getMessage();
  $context['sandbox']['file'] = Unl_Migration_Tool::save_to_disk($migration);
  $context['sandbox']['duration'] = min(300, ceil($context['sandbox']['duration'] * 1.5));
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
    private $_breadcrumbs         = array();
    private $_nodeMap             = array();
    private $_pageTitles          = array();
    private $_pageParentLinks     = array();
    private $_log                 = array();
    private $_blocks              = array();
    private $_isFrontier          = FALSE;
    private $_frontierIndexFiles  = array('low_bandwidth.shtml', 'index.shtml', 'index.html', 'index.htm', 'default.shtml');
    private $_frontierFilesScanned = array();
    private $_ignoreDuplicates    = FALSE;
    private $_useLiferayCode      = TRUE;
    private $_useLiferayTitles    = FALSE;
    private $_liferayPageTitles   = array();
    private $_logger;

    private $_liferaySubsites     = array(
      'cropwatch.unl.edu'     => array('corn', 'drybeans', 'forages', 'organic', 'potato', 'sorghum', 'soybeans', 'wheat', 'bioenergy', 'insect', 'economics', 'ssm', 'soils', 'tillage', 'weed', 'varietytest', 'biotechnology', 'farmresearch', 'cropwatch-youth', 'militaryresources', 'gaps', 'sugarbeets'),
      '4h.unl.edu'            => array('extension-4-h-horse', '4hcamps', '4hcurriclum'),
      'animalscience.unl.edu' => array('fernando-lab', 'anscgenomics', 'rprb-lab', 'ruminutrition-lab'),
      'beef.unl.edu'          => array('cattleproduction'),
      'biochem.unl.edu'       => array('barycki', 'bailey', 'becker', 'adamec', 'wilson', 'biochem-fatttlab', 'simpson'),
      'bse.unl.edu'           => array('p2guidelines'),
      'edmedia.unl.edu'       => array('techtraining'),
      'food.unl.edu'          => array('localfoods', 'allergy', 'fnh', 'preservation', 'fpc', 'safety', 'meatproducts', 'youth'),
      'ianrhome.unl.edu'      => array('ianrinternational'),
      'water.unl.edu'         => array('crops', 'cropswater', 'drinkingwater', 'drought', 'wildlife', 'hydrology', 'lakes', 'landscapes', 'landscapewater', 'laweconomics', 'manure', 'propertydesign', 'research', 'sewage', 'students', 'watershed', 'wells', 'wetlands'),
      'westcentral.unl.edu'   => array('wcentomology', 'wcacreage'),
    );

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

    public function __construct($baseUrl, $frontierPath, $frontierUser, $frontierPass, $ignoreDuplicates, $useLiferayCode = FALSE, $useLiferayTitles = FALSE)
    {
        // Check to see if we're migrating from frontier so we can make some extra assumptions.
        $baseUrlParts = parse_url($baseUrl);
        $remoteHostname = @gethostbyaddr(gethostbyname($baseUrlParts['host']));
        if ($remoteHostname == 'frontier.unl.edu') {
            $this->_isFrontier = TRUE;
        }

        // Add trailing slash if necessary
        $baseUrl = trim($baseUrl);
        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }

        $frontierPath = trim ($frontierPath);
        if ($frontierPath && substr($frontierPath, -1) != '/') {
          $frontierPath .= '/';
        }

        $this->_frontierPath = $frontierPath;
        $this->_frontierUser = $frontierUser;
        $this->_frontierPass = $frontierPass;

        $this->_ignoreDuplicates = (bool) $ignoreDuplicates;
        $this->_useLiferayCode = (bool) $useLiferayCode;
        $this->_useLiferayTitles = (bool) $useLiferayTitles;

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
        $this->_process_breadcrumbs();
        $this->_process_liferay_sitemap();
        $this->_state = self::STATE_PROCESSING_PAGES;
      }

      if ($this->_state == self::STATE_PROCESSING_PAGES) {
        // Process all of the pages on the site (Takes a while)
        do {
          set_time_limit(max(30, $time_limit * 1.5));

          $pagesToProcess = $this->_getPagesToProcess();
          foreach ($pagesToProcess as $pageToProcess) {
            if (time() - $this->_start_time > $time_limit) {
              return FALSE;
            }
            try {
              $this->_processPage($pageToProcess);
            }
            catch (Exception $e) {
              $this->_log('An exception occured while processing "' . $pageToProcess . '": "' . $e->getMessage() . '".', WATCHDOG_ERROR);
            }
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
          set_time_limit(max(30, $time_limit * 1.5));

          $hrefTransforms = isset($this->_hrefTransform[$path]) ? $this->_hrefTransform[$path] : array();
          foreach ($hrefTransforms as $hrefTransformFrom => $hrefTransformTo) {
            $content = str_replace(htmlspecialchars($hrefTransformFrom), htmlspecialchars($hrefTransformTo), $content);
          }

          $pageTitle = $this->_pageTitles[$path];
          try {
            $this->_createPage($pageTitle, $content, $path, '' == $path);
          }
          catch (Exception $e) {
            $this->_log('An exception occured while creating "' . $path . '": "' . $e->getMessage() . '".', WATCHDOG_ERROR);
          }
          $this->_createdContent[] = $path;
        }

        $this->_createMenu();
        $this->_create_blocks();
        $this->_create_breadcrumbs();

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
    
    private function _addSitePath($path, $allowTralingSlash = FALSE, $caseSensitive = FALSE)
    {
      // Blacklist any liferay calendars to avoid crawling an infinite number of pages
      if ($this->_useLiferayCode && strpos($path, 'struts_action=%2Fcalendar%2Fview') !== FALSE) {
        return;
      }
      
      if (($fragmentStart = strrpos($path, '#')) !== FALSE) {
          $path = substr($path, 0, $fragmentStart);
      }
      if ($allowTralingSlash) {
        $path = trim($path, ' ');
      }
      else {
        $path = trim($path, '/ ');
      }
      if (array_search(strtolower($path), array_map('strtolower', $this->_siteMap)) !== FALSE && !$caseSensitive) {
        return;
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
                $path = trim($path, '/');

                if ($fragmentPos = strrpos($path, '#') !== FALSE) {
                    $item['options']['fragment'] = substr($path, $fragmentPos + 1);
                    $path = substr($path, 0, $fragmentPos);
                }
                if (substr($path, -1) == '/') {
                    $path = substr($path, 0, -1);
                }
                $nodeId = array_search(strtolower($path), array_map('strtolower', $this->_nodeMap), TRUE);
                if ($nodeId) {
                    $item['link_path'] = 'node/' . $nodeId;
                }
            } else {
                $item['link_path'] = $href;
            }

            if ($item['link_path']) {
              try {
                menu_link_save($item);
                $this->_log('Created menu item "' . $item['link_title'] . '" linked to ' . $item['link_path'] . '.');
              }
              catch (Exception $e) {
                $this->_log('An exception occured creating the menu link for "' . $item['link_title'] . '".', WATCHDOG_ERROR);
                continue;
              }
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
                    $path = trim($path, '/');

                    if (($fragmentPos = strrpos($path, '#')) !== FALSE) {
                        $item['options']['fragment'] = substr($path, $fragmentPos + 1);
                        $path = substr($path, 0, $fragmentPos);
                    }
                    if (substr($path, -1) == '/') {
                        $path = substr($path, 0, -1);
                    }
                    $nodeId = array_search(strtolower($path), array_map('strtolower', $this->_nodeMap), TRUE);
                    if ($nodeId) {
                        $item['link_path'] = 'node/' . $nodeId;
                    }
                } else {
                    $item['link_path'] = $href;
                }

                if ($item['link_path']) {
                  try {
                    menu_link_save($item);
                    $this->_log('Created menu item "' . $parentTitle . ' / ' . $item['link_title'] . '" linked to ' . $item['link_path'] . '.');
                  }
                  catch (Exception $e) {
                    $this->_log('An exception occured creating the menu link for ' . $parentTitle . ' / ' . $item['link_title'] . '.', WATCHDOG_ERROR);
                  }
                } else {
                    $this->_log('Could not find a node to link to the "' . $parentTitle . ' / ' . $item['link_title'] . '" menu.', WATCHDOG_ERROR);
                }
            }
        }


        // Now set up the site hierarchy
        $pageParentLinks = $this->_pageParentLinks;
        foreach ($this->_pageParentLinks as $path => $parentLink) {
          $this->_createParentLink($path, $parentLink);
        }
    }

    private function _createParentLink($childPath, $parentPath) {

      // If the child is the site root, just return the root mlid.
      if (!$childPath) {
        return 0;
      }

      // If the child link already exists, just return its mlid.
      $childLink = menu_link_get_preferred(drupal_get_normal_path(rtrim($childPath, '/')));
      if ($childLink && $childLink['link_path'] != 'node/%') {
        return $childLink['mlid'];
      }

      // Find the parent link, if it doesn't exist, recursively create it.
      $parentNodePath = drupal_get_normal_path(rtrim($parentPath, '/'));
      $parentLink = menu_link_get_preferred($parentNodePath);
      if ($parentLink) {
        $parentLinkId = $parentLink['mlid'];
      } else if (substr($parentNodePath, 0, 5) != 'node/') {
        // This will catch invalid breadcrumb links and change them to point to the site root.
        $parentLink = '';
        $parentLinkId = 0;
      } else {
        $parentLinkId = $this->_createParentLink($parentPath, $this->_pageParentLinks[$parentPath]);
      }

      // Create the menu item.
      $item = array(
        'menu_name' => 'main-menu',
        'link_title' => $this->_pageTitles[$childPath],
        'link_path' => drupal_get_normal_path(rtrim($childPath, '/')),
        'plid' => $parentLinkId,
        'weight' => 50,
        'hidden' => 1,
      );
      menu_link_save($item);

      // Return its mlid.
      return $item['mlid'];
    }

    private function _process_blocks() {
      $content = $this->_getUrl($this->_baseUrl);
      $html = $content['content'];

      $this->_blocks['related_links'] = $this->_get_instance_editable_content($html, 'leftcollinks');
      $this->_blocks['contact_info'] = $this->_get_instance_editable_content($html, 'contactinfo');
      $this->_blocks['optional_footer'] = $this->_get_instance_editable_content($html, 'optionalfooter');
      $this->_blocks['footer_content'] = $this->_get_instance_editable_content($html, 'footercontent');

      foreach ($this->_blocks as $blockName => $block) {
        $dom = new DOMDocument();
        @$dom->loadHTML($block);
        $linkNodes = $dom->getElementsByTagName('a');
        foreach ($linkNodes as $linkNode) {
          $this->_processLinks($linkNode->getAttribute('href'), '', '', '<' . $blockName . '>');
        }
      }

      // Filter out the existing headers.
      $this->_blocks['related_links'] = preg_replace('/\s*<h3>\s*Related Links\s*<\/h3>\s*/', '', $this->_blocks['related_links']);
      $this->_blocks['contact_info'] = preg_replace('/\s*<h3>\sContacting Us*\s*<\/h3>\s*/', '', $this->_blocks['contact_info']);
      $this->_blocks['contact_info'] = preg_replace('/\s*<h3>\s*Contact Us\s*<\/h3>\s*/', '', $this->_blocks['contact_info']);
    }

    private function _create_blocks() {

      foreach ($this->_blocks as $blockName => $block) {
        if (!isset($this->_hrefTransform['<' . $blockName . '>'])) {
          continue;
        }
        foreach ($this->_hrefTransform['<' . $blockName . '>'] as $hrefTransformFrom => $hrefTransformTo) {
          $this->_blocks[$blockName] = str_replace(htmlspecialchars($hrefTransformFrom), htmlspecialchars($hrefTransformTo), $block);
        }
      }

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

    private function _process_breadcrumbs() {
      $content = $this->_getUrl($this->_baseUrl);
      $html = $content['content'];

      $dom = new DOMDocument();
      $dom->loadHTML($html);
      $breadcrumbs_node = $dom->getElementById('breadcrumbs');
      if (!$breadcrumbs_node) {
        return;
      }

      $link_nodes = $breadcrumbs_node->getElementsByTagName('a');
      $list_nodes = $breadcrumbs_node->getElementsByTagName('li');
      $unlinked_node = FALSE;
      if ($list_nodes->length > $link_nodes->length) {
        $unlinked_node = TRUE;
      }

      // Scan each of the breadcrumb links, skipping the first and the last (but only if there's an un-linked "true" last breadcrumb)
      for ($i = 1; $i < $link_nodes->length - ($unlinked_node ? 1 : 0); $i++) {
        $link_node = $link_nodes->item($i);
        $this->_breadcrumbs[] = array(
          'text' => trim($link_node->textContent),
          'href' => $this->_makeLinkAbsolute($link_node->getAttribute('href'), ''),
        );
      }
    }

    private function _create_breadcrumbs() {
      $current_settings = variable_get('theme_unl_wdn_settings', array());
      $current_settings['intermediate_breadcrumbs'] = $this->_breadcrumbs;
      variable_set('theme_unl_wdn_settings', $current_settings);
    }

    private function _process_liferay_sitemap() {
      if (!$this->_useLiferayCode) {
        return;
      }
      
      $urls = array();
      $urls[] = $this->_baseUrl . '?p_p_id=EXT_SITEMAP&p_p_state=exclusive&p_p_mode=view';
      
      $host = parse_url($this->_baseUrl, PHP_URL_HOST);
      if (array_key_exists($host, $this->_liferaySubsites)) {
        foreach ($this->_liferaySubsites[$host] as $subSite) {
          $urls[] = $this->_baseUrl . 'web/' . $subSite . '/?p_p_id=EXT_SITEMAP&p_p_state=exclusive&p_p_mode=view';
        }
      }
      
      foreach ($urls as $url) {
        $data = $this->_getUrl($url);
        
        if (strpos($data['contentType'], 'html') === FALSE) {
          return;
        }
  
        $dom = new DOMDocument();
        @$dom->loadHTML($data['content']);
        
        $linkNodes = $dom->getElementsByTagName('a');
        foreach ($linkNodes as $linkNode) {
          $path = $this->_processLinks($linkNode->getAttribute('href'), '');
          if ($this->_useLiferayTitles) {
            $this->_liferayPageTitles[$path] = trim($linkNode->textContent);
          }
        }
      }
    }

    private function _processPage($path)
    {
        $this->_addProcessedPage($path);
        $fullPath = $this->_baseUrl . $path;
        
        $this->_log('Processing page: ' . $path, WATCHDOG_DEBUG);

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

        $cleanPath = $path;
        $pathParts = parse_url($path);
        // If the path contains a query, we'll have to change it.
        if (array_key_exists('query', $pathParts)) {
          // If a Content-Disposition header exists with a filename, grab it.
          $altFileName = '';
          $matches = array();
          if (array_key_exists('Content-Disposition', $data['headers']) &&
              preg_match('/filename="(.*)"/', $data['headers']['Content-Disposition'], $matches)) {
            $altFileName = $matches[1];
          }

          // Parse the query string
          $query = array();
          parse_str($pathParts['query'], $query);
          
          // If this is a liferay file, just save it as <uuid>.<ext> in the root files directory.
          if ($pathParts['path'] == 'c/document_library/get_file' && $query['uuid']) {
            if (strrpos($pathParts['query'], '.') > strrpos($pathParts['query'], '&') && strrpos($pathParts['query'], '.') !== FALSE) {
              $cleanPath = $query['uuid'] . substr($pathParts['query'], strrpos($pathParts['query'], '.'));
            }
            else if ($altFileName && strpos($altFileName, '.') !== FALSE) {
              $cleanPath = $query['uuid'] . substr($altFileName, strrpos($altFileName, '.'));
            } else {
              $cleanPath = $query['uuid'];
            }
          }
          // Or, if it exists, save it as the content-disposition name.
          else if ($altFileName) {
            $cleanPath = $pathParts['path'] . '/' . $altFileName;
          }
          // Otherwise, just save it with a / instead of a ?.
          else {
            $cleanPath = $pathParts['path'] . '/' . $pathParts['query'];
          }
          $cleanPath = strtr($cleanPath, array('%2f' => '/', '%2F' => '/'));
        }

        if (strpos($data['contentType'], 'html') === FALSE) {
          if (!$data['contentType']) {
            $this->_log('The file type at ' . $fullPath . ' was not specified. Ignoring.', WATCHDOG_ERROR);
            return;
          }

          @drupal_mkdir('public://' . urldecode(dirname($cleanPath)), NULL, TRUE);
          if (!mb_check_encoding($path, 'UTF-8')) {
              $path = iconv('ISO-8859-1', 'UTF-8', $path);
          }

          try {
            $file = file_save_data($data['content'], 'public://' . urldecode($cleanPath), FILE_EXISTS_REPLACE);
          } catch (Exception $e) {
            $this->_log('Could not migrate file "' . $path . '"! File name too long?', WATCHDOG_ERROR);
          }
          $this->_hrefTransformFiles[$path] = $this->_makeRelativeUrl(file_create_url('public://' . $cleanPath));
          return;
        }
        $html = $data['content'];

        $maincontentarea = '';

        if ($path != '') {
          $maincontentarea = $this->_get_liferay_content_area($html);
        }

        if (!$maincontentarea) {
          $maincontentarea = $this->_get_instance_editable_content($html, 'maincontentarea');
        }

        if (!$maincontentarea) {
            $maincontentarea = $this->_get_old_main_content_area($html);
        }

        if (!$maincontentarea) {
            $this->_log('The file at ' . $fullPath . ' has no valid maincontentarea. Using entire body.', WATCHDOG_WARNING);
            $maincontentarea = $this->_get_text_between_tokens($html, '<body>', '</body>');
        }

        if (!$maincontentarea) {
            // its possible the body tag has attributes.  Check for this and filter them out.
            $maincontentarea = $this->_get_text_between_tokens($html, '<body', '</body>', FALSE);
            // As long as we find a closing bracket before the next opening bracket, its probably safe to assume the body tag is intact.
            if (strpos($maincontentarea, '>') < strpos($maincontentarea, '<')) {
              $maincontentarea = trim(substr($maincontentarea, strpos($maincontentarea, '>') + 1));
              // Tidy the output here, otherwise tidy would see HTML starting in the middle of a <body key="val"> tag.
              $maincontentarea = $this->_tidy_html_fragment($maincontentarea);
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
        if ($this->_liferayPageTitles[$path]) {
          $pageTitle = $this->_liferayPageTitles[$path];
        }

        $pageTitleNode = $dom->getElementById('pagetitle');
        if (!$pageTitle && $pageTitleNode) {
          // Search for the WDN 3.1 page title
          $pageTitleH1Nodes = $pageTitleNode->getElementsByTagName('h1');
          if ($pageTitleH1Nodes->length > 0) {
            $pageTitle = $pageTitleH1Nodes->item(0)->textContent;
          }
          if (!$pageTitle) {
            // If not found, search for the earlier version of the WDN page title
            $pageTitleH2Nodes = $pageTitleNode->getElementsByTagName('h2');
            if ($pageTitleH2Nodes->length > 0) {
              $pageTitle = $pageTitleH2Nodes->item(0)->textContent;
            }
          }

          if ($pageTitle && $this->_useLiferayTitles) {
            $pageTitle .= rtrim(' ' . basename($path));
          }
        }

        // If there is no WDN compliant title, search for others
        if (!$pageTitle) {
          // First, check for a WDN compliant <title>
          $titleText = '';
          $titleNodes = $dom->getElementsByTagName('title');
          if ($titleNodes->length > 0) {
            $titleText = $titleNodes->item(0)->textContent;
          }
          $titleParts = explode('|', $titleText);
          if (count($titleParts) > 2) {
            $pageTitle = trim(array_pop($titleParts));
          }
          // Finally, combine what title does exist with the last part of the path
          else {
            $filename = trim($path, '/');
            $filename = explode('/', $filename);
            $filename = array_pop($filename);
            // Strip off a file extension if it exists.
            if (strrpos($filename, '.') !== FALSE) {
              $filename = substr($filename, 0, strrpos($filename, '.'));
            }
            $pageTitle = "$titleText ($filename)";
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

        $this->_content[$cleanPath] = $maincontentarea;
        $this->_pageTitles[$cleanPath] = $pageTitle;

        // Scan the page for the parent breadcrumb
        $breadcrumbs = $dom->getElementById('breadcrumbs');
        if ($breadcrumbs) {
          $breadcrumbs = $breadcrumbs->getElementsByTagName('a');
          $breadcrumb = $breadcrumbs->item($breadcrumbs->length - 1);
          if ($breadcrumb) {
            $breadcrumb = $breadcrumb->getAttribute('href');
            $breadcrumb = $this->_makeLinkAbsolute($breadcrumb, $path);
            if (substr($breadcrumb, 0, strlen($this->_baseUrl)) == $this->_baseUrl && $breadcrumb != $this->_baseUrl) {
              $pageParentLink = substr($breadcrumb, strlen($this->_baseUrl));
            } else {
              $pageParentLink = '';
            }
            if ($pageParentLink == $path) {
              $pageParentLink = '';
            }
            $this->_pageParentLinks[$cleanPath] = $pageParentLink;
          }
        }
        if ($cleanPath != $path) {
          $this->_hrefTransformFiles[$path] = $cleanPath;
        }
    }

    private function _processLinks($originalHref, $path, $page_base = NULL, $tag = NULL) {
      if (substr($originalHref, 0, 1) == '#') {
        return;
      }

      // Tidy will remove any spaces later, so we need to remove them here too.
      $originalHref = trim($originalHref);

      if (!$page_base) {
        $page_base = $path;
      }

      $href = $this->_makeLinkAbsolute($originalHref, $page_base);

      if (substr($href, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
        $newPath = substr($href, strlen($this->_baseUrl));
        if ($newPath === FALSE) {
          $newPath = '';
        }
        $this->_addSitePath($newPath);
      } else {
        $newPath = $href;
      }

      if ($tag) {
        $this->_hrefTransform[$tag][$originalHref] = $newPath;
      } else {
        $this->_hrefTransform[$path][$originalHref] = $newPath;
      }

      return $newPath;
    }

    /**
     * Provided an absolute URL, handles translating Liferay /web/site/some/path
     * paths to http://site.unl.edu/some/path/
     *
     * @param string $url
     * @return string
     */
    private function _translateLiferayWeb($url) {
      if (!$this->_useLiferayCode) {
        return $url;
      }

      if (substr($url, 0, strlen($this->_baseUrl)) != $this->_baseUrl) {
        return $url;
      }

      $urlParts = parse_url($url);
      $pathParts = explode('/', ltrim($urlParts['path'], '/'));

      $siteNameMap = array(
        'extension' => 'www.extension.unl.edu',
        'webster'   => 'www.webster.unl.edu',
      );

      if (
           count($pathParts) >= 2 && $pathParts[0] == 'web'
        && !(in_array($urlParts['host'], array_keys($this->_liferaySubsites)) && in_array($pathParts[1], $this->_liferaySubsites[$urlParts['host']]))
      ) {

        // If the site name is "special" look it up in the map. Otherwise, just add .unl.edu
        if (array_key_exists($pathParts[1], $siteNameMap)) {
          $urlParts['host'] = $siteNameMap[$pathParts[1]];
        }
        else {
          $urlParts['host'] = strtolower($pathParts[1]) . '.unl.edu';
        }

        $pathParts = array_splice($pathParts, 2);
        $urlParts['path'] = '/' . implode('/', $pathParts);

        $url = $urlParts['scheme'] . '://' . $urlParts['host'];
        $url .= isset($urlParts['path']) ? $urlParts['path'] : '';
        $url .= isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
        $url .= isset($urlParts['fragment']) ? '#'.$urlParts['fragment'] : '';
      }

      return $url;
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
            return $this->_translateLiferayWeb($href);
        }
        if (isset($parts['scheme'])) {
            $absoluteUrl = $href;
        } else if (isset($parts['path']) && substr($parts['path'], 0, 1) == '/') {
            $baseParts = parse_url($this->_baseUrl);
            $absoluteUrl = $baseParts['scheme'] . '://' . $baseParts['host'] . $parts['path'];
            if (isset($parts['query'])) {
              $absoluteUrl .= '?' . $parts['query'];
            }
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
            $parts['path'] = isset($parts['path']) ? dirname($parts['path']) . '/' : '';
            while (substr($parts['path'], 0, 2) == '//') {
              $parts['path'] = substr($parts['path'], 1);
            }

            $absoluteUrl = $parts['scheme'] . '://' . $parts['host'];
            $absoluteUrl .= $parts['path'];
            $absoluteUrl .= isset($parts['query']) ? '?' . $parts['query'] : '';
            $absoluteUrl .= isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
        }

        return $this->_translateLiferayWeb($absoluteUrl);
    }

    /**
     * Given an absolute URL $href, returns a URL that is relative to $baseUrl
     * @param string $href
     * @param string[optional] $baseUrl
     */
    private function _makeRelativeUrl($href, $baseUrl = '') {
      if (!$baseUrl) {
        $baseUrl = url('<front>', array('absolute' => TRUE));
      }

      if (substr($href, 0, strlen($baseUrl)) == $baseUrl) {
        if (variable_get('unl_use_base_tag', TRUE)) {
          return substr($href, strlen($baseUrl));
        } else {
          $parts = parse_url($href);
          $relativeUrl = $parts['path'];
          $relativeUrl .= isset($parts['query']) ? '?' . $parts['query'] : '';
          $relativeUrl .= isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
          return $relativeUrl;
        }
      }
      return $href;
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
        curl_setopt($this->_curl, CURLOPT_USERAGENT, 'UNL-CMS Migration Tool');

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
            // Keep trailing slash only if this is a redirect from the non-trailing slash URL
            // and switch to case sensitive site paths if the redirect just changes case.
            $this->_addSitePath($path, $url . '/' == $this->_baseUrl . $path, strtolower($url) == strtolower($this->_baseUrl . $path));

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
            'headers' => $headers,
        );

        if ($this->_frontierPath) {
            $mtime = $this->_getModifiedDate($url);
            if ($mtime) {
                $data['lastModified'] = $mtime;
            } else if (isset($headers['Last-Modified'])) {
                $data['lastModified'] = strtotime($headers['Last-Modified']);
            }
        }

        // Convert non-UTF-8 data to UTF-8.
        if (preg_match('/charset=(.*);?/', $data['contentType'], $matches)) {
          $charset = $matches[1];
          if ($charset != 'UTF-8') {
            $data['content'] = iconv($charset, 'UTF-8', $data['content']);
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
      if (is_callable($this->_logger)) {
        $logger = $this->_logger;
        return $logger($message, $severity);
      }
      
      $this->_log[] = $message;

      if ($severity == WATCHDOG_INFO) {
        $type = 'status';
      }
      else if ($severity == WATCHDOG_WARNING) {
        $type = 'warning';
      }
      else if ($severity == WATCHDOG_DEBUG) {
        return;
      }
      else {
        $type = 'error';
      }
      drupal_set_message(check_plain($message), $type, FALSE);

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

  private function _get_liferay_content_area($html) {
    if (!$this->_useLiferayCode) {
      return FALSE;
    }

    return $this->_get_text_between_tokens(
      $html,
      "<!-- End of shared left start of right -->\n<div class=\"three_col right\">",
      '<form action="" method="post" name="hrefFm">'
    );
  }

  private function _get_text_between_tokens($text, $start_token, $end_token, $tidy_output = TRUE) {
    $content_start = strpos($text, $start_token);
    $content_end = strpos($text, $end_token, $content_start);
    $content = substr($text,
                      $content_start + strlen($start_token),
                      $content_end - $content_start - strlen($start_token));
    $content = trim($content);

    if ($content && $content_start !== FALSE && $content_end !== FALSE) {
      if ($tidy_output) {
        $content = $this->_tidy_html_fragment($content);
      }
      return $content;
    }

    return FALSE;
  }

  private function _tidy_html_fragment($html) {
    $config = array(
      'doctype' => 'transitional',
      'indent' => TRUE,
      'output-xhtml' => TRUE,
      'show-body-only' => TRUE,
      'wrap' => 0,
    );
    $tidy = new Tidy();
    $tidy->parseString($html, $config, 'utf8');
    $tidy->cleanRepair();

    return (string) $tidy;
  }

  public function getMessage() {
    return 'Crawled ' . count($this->_processedPages) . ' of ' . count($this->_siteMap) . ' discovered links.';
  }

  public function getFinished() {
    return min(0.99, count($this->_processedPages) / count($this->_siteMap));
  }
  
  public function setLogger(callable $logger) {
    $this->_logger = $logger;
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
