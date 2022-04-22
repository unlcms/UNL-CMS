<?php

/**
 * LDAP Class
 *
 * LICENSE: Some license information
 *
 * @copyright  2006 New Media Center - University of Nebraska-Lincoln
 * @license    http://www.gnu.org/licenses/gpl.txt GPL
 * @version    0.0.1
 * @link       http://nmc.unl.edu/
 * @since      File available since Release 0.0.1
*/

class Unl_Ldap {

    protected $_conn;
    protected $_baseDN;

    /**
     * Constructor, pass the URI to an LDAP Database
     *
     * @param string $uri
     * @return Unl_Ldap
     * @throws Exception
     *
     */
    public function __construct($uri)
    {
        if (!function_exists('ldap_connect')) {
            throw new Exception('The PHP LDAP extension is required and is not enabled.');
        }

    	$this->_conn = ldap_connect($uri);
    	if($this->_conn === FALSE) {
    	    throw new Exception('Could not connect to LDAP Server');
    	}

    	if(ldap_set_option($this->_conn, LDAP_OPT_PROTOCOL_VERSION, 3) === FALSE) {
    	    throw new Exception('Could not connect to LDAP Server: ' . ldap_error($this->_conn), ldap_errno($this->_conn));
    	}

    	// check for an actual connection
    	@ldap_start_tls($this->_conn);
    	if(ldap_errno($this->_conn) == -1) {
    	    throw new Exception('Could not connect to LDAP Server', -1);
    	}

    }

    /**
     * Bind: Attempts to bind to the LDAP Serever with the provided credentials
     *
     * @param string[optional] $rdn
     * @param string[optional] $password
     * @return void
     * @throws Exception
     *
     */
    public function bind($rdn = null, $password = null)
    {
        if(@ldap_bind($this->_conn, $rdn, $password) === FALSE) {
            throw new Exception('Unable to bind: ' . ldap_error($this->_conn), ldap_errno($this->_conn));
        }
    }

    /**
     * Sets the baseDN for searches
     *
     * @param string $baseDN
     */
    public function setSearchBase($baseDN)
    {
        $this->_baseDN = $baseDN;
    }

    /**
     * Search: Attempts to do a search with the given filter string
     *
     * @param string[optional] $base_dn
     * @param string $filter
     * @return array
     * @throws Exception
     *
     */
    public function search($base_dn, $filter=null)
    {
        if($filter === null) {
            $filter = $base_dn;
            $base_dn = $this->_baseDN;
        }

        $result = @ldap_search($this->_conn, $base_dn, $filter);
        if($result === FALSE) {
            throw new Exception('LDAP search failed: ' . ldap_error($this->_conn));
        }

        $referrals = null;
        $matcheddn = null;
        $errmsg = null;
        $errcode = null;
        if(ldap_parse_result($this->_conn, $result, $errcode, $matcheddn, $errmsg, $referrals)) {
            if($errcode !== 0) {
                throw new Exception('Error retrieving results: ' . ldap_err2str($errcode));
            }
        } else {
            throw new Exception('Error retrieving results: ' . ldap_error($this->_conn));
        }

        $entries = ldap_get_entries($this->_conn, $result);
        if($result === FALSE) {
            throw new Exception('Error retrieving results: ' . ldap_error($this->_conn));
        }

        // strip off redundant data
        for($i = 0; $i < $entries['count']; $i++) {
            for($j = 0; $j < $entries[$i]['count']; $j++) {
                unset($entries[$i][$entries[$i][$j]]['count']);
                unset($entries[$i][$j]);
            }
            unset($entries[$i]['count']);
        }
        unset($entries['count']);

        return $entries;
    }

}
