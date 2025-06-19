<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: messages.inc.php,v 1.5 2022/02/07 11:17:30 rtigero Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");
global $class_path, $include_path, $action, $group, $messages;
require_once($class_path."/encoding_normalize.class.php");
require_once("$include_path/apache_functions.inc.php");

//Mise en cache des messages
//on ajoute des entêtes qui autorisent le navigateur à faire du cache...
$headers = getallheaders();
//une journée
$offset = 60 * 60 * 24 ;
if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) <= time())) {
    header('Last-Modified: '.$headers['If-Modified-Since'], true, 304);
    return;
}else{
    header('Expired: '.gmdate("D, d M Y H:i:s", time() + $offset).' GMT', true);
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT', true, 200);
}

switch($action){
	case 'get_messages':
		if($group){
			if($messages->table_js[$group]){
				$array_message_retourne = array();
				foreach($messages->table_js[$group] as $key => $value){
					$array_message_retourne[] = array("code"=>$key, "message"=>$value, "group"=>$group);
				}
				print encoding_normalize::json_encode($array_message_retourne);
			}else{
				print encoding_normalize::json_encode(array());
			}
		}
		break;
}