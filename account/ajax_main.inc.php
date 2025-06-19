<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.1 2021/11/09 08:53:30 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $categ, $action, $object_type, $class_path;

//En fonction de $categ, il inclut les fichiers correspondants
switch($categ) {
	case 'lists':
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'lists');
				break;
		}
		break;
	case 'tabs':
		switch($action) {
			case "list":
				require_once "$class_path/tabs/tabs_controller.class.php";
				tabs_controller::proceed_ajax($object_type, 'tabs');
				break;
		}
		break;
	case 'modules':
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'modules');
				break;
		}
		break;
	case 'selectors':
		switch($action) {
			case "list":
				require_once "$class_path/selectors/selectors_controller.class.php";
				selectors_controller::proceed_ajax($object_type, 'selectors');
				break;
		}
		break;
	case 'logs':
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'logs');
				break;
		}
		break;
	default:
		break;
}
