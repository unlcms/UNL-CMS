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
    
    private function __construct($baseUrl)
    {
        header('Content-type: text/plain');
        $baseUrl = trim($baseUrl);
        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }
        $this->_siteMap = array();
        $this->_processedPages = array();
        
        $this->_baseUrl = $baseUrl;
        $this->_addSitePath('');
    }
    
    private function _migrate()
    {
        do {
            $pagesToProcess = $this->_getPagesToProcess();
            foreach ($pagesToProcess as $pageToProcess) {
                $this->_processPage($pageToProcess);
            }
            if ($i++ == 1) exit;
        } while (count($pagesToProcess) > 0);
        exit;
    }
    
    private function _addSitePath($path)
    {
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
    
    private function _processPage($path)
    {
        $url = $this->_baseUrl . $path;
        $startToken = '<!-- InstanceBeginEditable name="maincontentarea" -->';
        $endToken = '<!-- InstanceEndEditable -->';
    
        $html = file_get_contents($url);
        $contentStart = strpos($html, $startToken);
        $contentEnd = strpos($html, $endToken, $contentStart);
        $maincontentarea = substr($html,
                                  $contentStart + strlen($startToken),
                                  $contentEnd - $contentStart - strlen($startToken));
        $maincontentarea = trim($maincontentarea);
        
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $maincontentNode = $dom->getElementById('maincontent');
        $linkNodes = $maincontentNode->getElementsByTagName('a');
        echo PHP_EOL . 'Path: ' . $path . PHP_EOL; ob_flush(); flush();
        foreach ($linkNodes as $linkNode) {
            $href = $linkNode->getAttribute('href');
            echo $href  . ' => ';
            $href = $this->_makeLinkAbsolute($href, dirname($path));
            echo $href . PHP_EOL;
            if (substr($href, 0, strlen($this->_baseUrl)) == $this->_baseUrl) {
                $newPath = substr($href, strlen($this->_baseUrl));
                $this->_addSitePath($newPath);
            }
        }
        //$this->_createPage('Test', $maincontentarea);
        $this->_addProcessedPage($path);
    }
    
    private function _makeLinkAbsolute($href, $intermediatePath)
    {
        if (strlen($intermediatePath) > 0 && substr($intermediatePath, -1) != '/') {
            $intermediatePath .= '/';
        }
        $parts = parse_url($href);
        if ($parts['scheme']) {
            return $href;
        }
        if (substr($parts['path'], 0, 1) == '/') {
            $baseParts = parse_url($this->_baseUrl);
            return $baseParts['scheme'] . '://' . $baseParts['host'] . $parts['path'];
        }
        return $this->_baseUrl . $intermediatePath . $href;
    }
    
    private function _createPage($title, $content)
    {
        $node = new StdClass();
        $node->type = 'page';
        $node->title = $title;
        $node->body = array(
            'und' => array(
                array(
                    'value' => $content,
                    'format' => filter_default_format()
                )
            )
        );
//        print_r($node); exit;
        node_submit($node);
//        print_r($node); exit;
        node_save($node);
    }
}

