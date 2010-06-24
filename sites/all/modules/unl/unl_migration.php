<?php

function unl_migration_page()
{
    return drupal_get_form('unl_migration');
}


function unl_migration($form, &$form_state)
{
    $form['root'] = array(
        '#type' => 'fieldset',
        '#title' => 'Migration Tool'
    );
    
    $form['root']['site_url'] = array(
        '#type' => 'textfield',
        '#title' => t('Site URL'),
        '#description' => t('Full URL to the existing site you wish to migrate'),
        '#required' => TRUE
    );
    
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Migrate'
    );
    
    return $form;
}

function unl_migration_submit($form, &$form_state)
{
    Unl_Migration_Tool::migrate($form_state['values']['site_url']);
}


class Unl_Migration_Tool
{
    static public function migrate($baseUrl)
    {
        $instance = new self($baseUrl);
        return $instance->_migrate();
    }
    
    private $_baseUrl;
    private $_siteMap;
    private $_processedPages;
    private $_curl;
    private $_content;
    private $_hrefTransform;
    private $_hrefTransformFiles;
    private $_menu;
    private $_nodeMap;
    
    private function __construct($baseUrl)
    {
        header('Content-type: text/plain');
        $baseUrl = trim($baseUrl);
        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }
        $this->_siteMap = array();
        $this->_processedPages = array();
        $this->_content = array();
        $this->_hrefTransform = array();
        $this->_hrefTransformFiles = array();
        $this->_menu = array();
        $this->_nodeMap = array();
        
        $this->_baseUrl = $baseUrl;
        $this->_addSitePath('');
        $this->_curl = curl_init();
    }
    
    private function _migrate()
    {
    	ini_set('memory_limit', -1);
    	
    	// Parse the menu
    	$this->_processMenu();
    	
    	// Process all of the pages on the site
        do {
            $pagesToProcess = $this->_getPagesToProcess();
            foreach ($pagesToProcess as $pageToProcess) {
                $this->_processPage($pageToProcess);
            }
            //if ($i++ == 2) break;
            echo PHP_EOL . 'I = ' . $i++ . PHP_EOL;
        } while (count($pagesToProcess) > 0);
        
        // Fix any links to files that got moved to sites/<site>/files
        foreach ($this->_hrefTransform as $path => &$transforms) {
        	if (array_key_exists('', $transforms)) {
        		unset($transforms['']);
        	}
            foreach ($transforms as $oldPath => &$newPath) {
	        	if (array_key_exists($newPath, $this->_hrefTransformFiles)) {
	        		$newPath = $this->_hrefTransformFiles[$newPath];
	        	}
            }
        }
    
        // Update links and then create new page nodes.
        foreach ($this->_content as $path => $content) {
            echo 'PATH: ' . $path . PHP_EOL;
        	$hrefTransform = $this->_hrefTransform[$path];
        	
        	if (is_array($hrefTransform)) {
                $content = strtr($content, $hrefTransform);
        	}
            $this->_createPage('Testing', $content, $path, '' == $path);
        }
        
        var_dump($this->_nodeMap);
        
        $this->_createMenu();
        
        exit;
    }
    
    private function _addSitePath($path)
    {
    	if (($fragmentStart = strrpos($path, '#')) !== FALSE) {
    		echo 'Changing ' . $path;
            $path = substr($path, 0, $fragmentStart);
            echo ' to ' . $path . PHP_EOL;
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
    
        $linkNodes = $navlinksNode->getElementsByTagName('a');
        foreach ($linkNodes as $linkNode) {
            $this->_processLinks($linkNode->getAttribute('href'), $path);
        }
        
        $navlinksUlNode = $navlinksNode->getElementsByTagName('ul')->item(0);
        foreach ($navlinksUlNode->childNodes as $primaryLinkLiNode) {
        	if (strtolower($primaryLinkLiNode->nodeName) != 'li') {
        		continue;
        	}
        	$primaryLinkNode = $primaryLinkLiNode->getElementsByTagName('a')->item(0);
        	$menuItem = array('text' => $primaryLinkNode->textContent,
        	                  'href' => $this->_makeLinkAbsolute($primaryLinkNode->getAttribute('href')), '');
            
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
	            $childMenu[] = array('text' => $childLinkNode->textContent,
	                                 'href' => $this->_makeLinkAbsolute($childLinkNode->getAttribute('href')), '');
        	}
        	$menuItem['children'] = $childMenu;
            $this->_menu[] = $menuItem;
        }
    }

    private function _createMenu()
    {
        $primaryWeights = 1;
        foreach ($this->_menu as $primaryMenu) {
            $item = array(
                'expanded' => TRUE,
                'menu_name' => 'main-menu',
                'link_title' => $primaryMenu['text'],
                'weight' => $primaryWeights++
            );
            $href = $primaryMenu['href'];
        	if (substr($href, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
        		$path = substr($href, strlen($this->_baseUrl));
        		if (!$path) {
        			$path = '';
        		}
                if ($fragmentPos = strrpos($path, '#') !== FALSE) {
                    $path = substr($path, 0, $fragmentPos);
                }
        		$nodeId = array_search($path, $this->_nodeMap, TRUE);
        		$item['link_path'] = 'node/' . $nodeId;
        		echo '[' . $nodeId . '] => ' . $path . PHP_EOL;  
        	} else {
                $item['link_path'] = $href;
        	}
            menu_link_save($item);
            print_r($item);
            
            if (!array_key_exists('children', $primaryMenu)) {
            	continue;
            }
            
            $plid = $item['mlid'];
            $childWeights = 1;
            foreach ($primaryMenu['children'] as $childMenu) {
	            $item = array(
	                'menu_name' => 'main-menu',
	                'link_title' => $childMenu['text'],
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
	                	echo "strrpos() = $fragmentPos\n";
	                	$path = substr($path, 0, $fragmentPos);
	                }
                    $nodeId = array_search($path, $this->_nodeMap, TRUE);
                    $item['link_path'] = 'node/' . $nodeId;
                    echo '[' . $nodeId . '] => ' . $path . PHP_EOL;
	            } else {
	                $item['link_path'] = $href;
	            }
	            menu_link_save($item);
                print_r($item);
            }
        }
    }
    
    private function _processPage($path)
    {
    	$this->_addProcessedPage($path);
    	
        $url = $this->_baseUrl . $path;
        $startToken = '<!-- InstanceBeginEditable name="maincontentarea" -->';
        $endToken = '<!-- InstanceEndEditable -->';
    
        $data = $this->_getUrl($url);
        if (!$data['content']) {
        	return;
        }
        if (strpos($data['contentType'], 'html') === FALSE) {
        	if (!$data['contentType']) {
        		return;
        	}
        	drupal_mkdir('public://' . dirname($path), NULL, TRUE);
        	$file = file_save_data($data['content'], 'public://' . $path, FILE_EXISTS_REPLACE);
        	echo 'Uploaded file: ' . $path. PHP_EOL;
        	$this->_hrefTransformFiles[$path] = file_directory_path() . '/' . $path;
        	return;
        }
        $html = $data['content'];
        
        if (preg_match('/charset=(.*);?/', $data['contentType'], $matches)) {
        	$charset = $matches[1];
        	$html = iconv($charset, 'UTF-8', $html);
        }
        
        $contentStart = strpos($html, $startToken);
        $contentEnd = strpos($html, $endToken, $contentStart);
        $maincontentarea = substr($html,
                                  $contentStart + strlen($startToken),
                                  $contentEnd - $contentStart - strlen($startToken));
        $maincontentarea = trim($maincontentarea);
        if (!$maincontentarea) {
            return;
        }
        
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $maincontentNode = $dom->getElementById('maincontent');
        if (!$maincontentNode) {
        	return;
        }
        
        $linkNodes = $maincontentNode->getElementsByTagName('a');
        foreach ($linkNodes as $linkNode) {
            $this->_processLinks($linkNode->getAttribute('href'), $path);
        }
    
        $linkNodes = $maincontentNode->getElementsByTagName('img');
        foreach ($linkNodes as $linkNode) {
            $this->_processLinks($linkNode->getAttribute('src'), $path);
        }
        
        $this->_content[$path] = $maincontentarea;
    }
    
    private function _processLinks($originalHref, $path)
    {
    	if (substr($originalHref, 0, 1) == '#') {
    		return;
    	}
        $href = $this->_makeLinkAbsolute($originalHref, $path);
        if (substr($href, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
            $newPath = substr($href, strlen($this->_baseUrl));
            if ($newPath === FALSE) {
            	$newPath = '';
            }
            $this->_hrefTransform[$path][$originalHref] = $newPath;
            $this->_addSitePath($newPath);
        }
    }
    
    private function _makeLinkAbsolute($href, $path)
    {
    	echo $href . ' => ';
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
        if ($parts['scheme']) {
            $absoluteUrl = $href;
        } else if (substr($parts['path'], 0, 1) == '/') {
            $baseParts = parse_url($this->_baseUrl);
            $absoluteUrl = $baseParts['scheme'] . '://' . $baseParts['host'] . $parts['path'];
            if ($parts['fragment']) {
            	$absoluteUrl .= '#' . $parts['fragment'];
            }
        } else if (substr($href, 0, 1) == '#') {
        	$absoluteUrl = $this->_baseUrl . $path . $href;
        } else {
            $absoluteUrl = $this->_baseUrl . $intermediatePath . $href;
        }
        $parts = parse_url($absoluteUrl);
        while (strpos($parts['path'], '/./') !== FALSE) {
            $parts['path'] = strtr($parts['path'], array('/./', '/'));
        }
        while (strpos($parts['path'], '/../') !== FALSE) {
            $parts['path'] = preg_replace('/\\/[^\\/]*\\/\\.\\.\\//', '/', $parts['path']);
        }
        
        $absoluteUrl = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
        if ($parts['fragment']) {
            $absoluteUrl .= '#' . $parts['fragment'];
        }            
        echo $absoluteUrl . PHP_EOL;
        return $absoluteUrl;
    }
    
    private function _createPage($title, $content, $alias = '', $makeFrontPage = FALSE)
    {
    	echo 'Alias: ' . PHP_EOL;
        var_dump($alias);
        
    	if (substr($alias, -1) == '/') {
    		$alias = substr($alias, 0, -1);
    	}
    	
        $node = new StdClass();
        $node->type = 'page';
        $node->title = $title;
        $node->language = 'und';
        $node->path['alias'] = $alias;
        $node->body = array(
            'und' => array(
                array(
                    'value' => $content,
                    'format' => filter_default_format()
                )
            )
        );
        
        node_submit($node);
        node_save($node);
        
        var_dump($alias);
        $this->_nodeMap[$node->nid] = $alias;
        
        if ($makeFrontPage) {
        	variable_set('site_frontpage', 'node/' . $node->nid);
        }
    }
    
    private function _getUrl($url)
    {
    	curl_setopt($this->_curl, CURLOPT_URL, $url);
    	curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
    	curl_setopt($this->_curl, CURLOPT_HEADER, TRUE);
    	echo 'Retreiving ' . $url . PHP_EOL;
    	$content = curl_exec($this->_curl);
    	$meta = curl_getinfo($this->_curl);
        $content = substr($content, $meta['header_size']);
        
    	if ($meta['http_code'] == 301) {
    		preg_match('/Location: (.*)/', $content, $matches);
    		$location = $matches[1];
    		$path = substr($location, strlen($this->_baseUrl));
    		$this->_addSitePath($path); 
            return FALSE;
    	}
        
        return array('contentType' => $meta['content_type'], 'content' => $content);
    }
}

