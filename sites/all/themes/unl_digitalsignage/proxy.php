<?php
/**
 * Twitter api proxy
 */

/**
 * Determines if a network in the form of 192.168.17.1/16 or
 * 127.0.0.1/255.255.255.255 or 10.0.0.1 matches a given ip
 * @param $network The network and mask
 * @param $ip The ip to check
 * @return bool true or false
 */
function net_match($network, $ip) {
    $ip_arr = explode('/', $network);
    $network_long = ip2long($ip_arr[0]);
    $x = ip2long($ip_arr[1]);
    $mask =  long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
    $ip_long = ip2long($ip);
    return ($ip_long & $mask) == ($network_long & $mask);
}

if (isset($_SERVER['REMOTE_ADDR'])) {
    $validIPs = array('129.93.0.0/16','65.123.32.0/19','64.39.240.0/20','216.128.208.0/20','67.208.34.248/249');
    foreach ($validIPs as $range) {
        if (net_match($range, $_SERVER['REMOTE_ADDR'])) {
            $result = file_get_contents($_GET['u']);
            header('Content-Type:·application/json;·charset=utf-8');
            echo $result;
            exit();
        }
    }
}

exit();
