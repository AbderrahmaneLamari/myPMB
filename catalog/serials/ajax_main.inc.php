<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.2.2.1 2021/07/09 07:01:57 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $sub, $action, $class_path;
global $object_type;

switch($sub) {
	case "circ_ask" :
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'serialcirc');
				break;
		}
		break;
	case "pointage" :
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type);
				break;
		}
		break;
	default:
		break;
}