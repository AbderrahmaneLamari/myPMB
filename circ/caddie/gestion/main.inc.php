<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.13 2021/05/25 07:04:10 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $include_path, $quoi, $action, $idemprcaddie, $item;

require_once("$class_path/classementGen.class.php") ;
require_once($class_path."/empr_caddie_procs.class.php");
require_once ($include_path."/templates/empr_cart.tpl.php");

switch ($quoi) {
	case 'razpointage':
		empr_caddie_controller::proceed_raz($idemprcaddie);
		break;
	case 'pointage':
		empr_caddie_controller::proceed_selection($idemprcaddie, 'gestion', 'pointage', 'selection');
		break;
	case 'pointagebarcode':
		empr_caddie_controller::proceed_barcode($idemprcaddie, 'gestion', 'pointe');
		break;
	case 'selection':
		empr_caddie_controller::proceed_selection($idemprcaddie, 'gestion', 'selection', 'selection');
		break;
	case 'barcode':
		empr_caddie_controller::proceed_barcode($idemprcaddie, 'gestion', 'add');
		break;
	case 'procs':
		empr_caddie_procs::proceed();
		break;
	case 'remote_procs':
		empr_caddie_procs::proceed_remote();
		break;
	case "classementGen" :
		$baseLink="./circ.php?categ=caddie&sub=gestion&quoi=classementGen";
		$classementGen = new classementGen("empr_caddie",0);
		$classementGen->proceed($action);
		break;
	case 'pointagepanier':
		empr_caddie_controller::proceed_by_caddie($idemprcaddie);
		break;
	case 'panier':
	default:
		$item = intval($item);
		empr_caddie_controller::proceed($idemprcaddie, $item);
		break;
	}
