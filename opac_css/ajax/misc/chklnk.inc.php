<?php
// +-------------------------------------------------+
// © 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: chklnk.inc.php,v 1.2.6.1 2023/07/13 12:47:04 rtigero Exp $
global $class_path, $link, $timeout, $charset;

require_once ("$class_path/curl.class.php");
$link = urldecode($link);

if ($link != "") {
    $curl = new Curl();
    $curl->limit = 1024; // Limite à 1Ko
    
    if (isset($timeout) && is_numeric($timeout)) {
        $curl->timeout = $timeout;
    }
    
    $response = $curl->get($link);
    
    if ($response) {
        $msg = $response->headers['Status-Code'];
    } else {
        $msg = "can't resolve $link";
    }
    
} else {
    $msg = "empty link";
}
print htmlentities($msg, ENT_QUOTES, $charset);
?>