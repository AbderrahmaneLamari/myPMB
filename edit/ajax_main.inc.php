<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.19.4.1 2023/12/06 07:18:24 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $categ, $sub, $action, $plugin;
global $object_type, $filters, $commands_ids;

require_once($class_path.'/encoding_normalize.class.php');
require_once($class_path.'/pnb/pnb.class.php');
switch($categ){
	case "editions_state" :
		include("./edit/editions_state/ajax_main.inc.php");
		break;
	case 'dashboard' :
		include("./dashboard/ajax_main.inc.php");
		break;
	case 'pnb':
	    switch($action) {
	        case 'mailto':
	            $pnb = new pnb();
	            if(isset($commands_ids)){
	                $commands_ids = explode(',',$commands_ids);
	            }
	            print encoding_normalize::json_encode($pnb->get_mailto_data($commands_ids));
	            break;
	        case "list":
	            lists_controller::proceed_ajax($object_type);
	            break;
	    }
	    break;
	case 'expl':
		switch($action) {
			case "list":
				require_once($class_path."/loans/loans_edition_controller.class.php");
				loans_edition_controller::proceed_ajax($object_type, 'loans');
				break;
		}
		break;
	case 'notices':
		switch($action) {
			case "list":
				if($sub == 'resa_planning') {
					$directory = 'resa_planning';
				} else {
					$directory = 'reservations';
				}
				require_once($class_path."/reservations/reservations_edition_controller.class.php");
				//Les noms de filtres ont changé - on assure la rétro-compatibilité
				if($object_type == 'reservations_edition_treat_ui') {
					list_reservations_edition_treat_ui::set_globals_from_json_filters(stripslashes($filters));
				}
				reservations_edition_controller::proceed_ajax($object_type, $directory);
				break;
		}
		break;
	case 'empr':
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'readers');
				break;
		}
		break;
	case 'serials':
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'records');
				break;
		}
		break;
	case 'transferts':
		switch($action) {
			case "list":
				require_once($class_path."/transferts/transferts_edition_controller.class.php");
				transferts_edition_controller::proceed_ajax($object_type, 'transferts');
				break;
		}
		break;
	case 'transferts_demandes':
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'transferts');
				break;
		}
		break;
	case 'campaigns' :
		require_once($class_path.'/campaigns/campaigns_controller.class.php');
		campaigns_controller::proceed_ajax($object_type);
		break;
	case 'plugin' :
		$plugins = plugins::get_instance();
		$file = $plugins->proceed_ajax("edit",$plugin,$sub);
		if($file){
			include $file;
		}
		break;
	default:
		switch($action) {
			case "list":
				lists_controller::proceed_ajax($object_type, 'configuration/'.$categ);
				break;
		}
		break;
}