<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.11 2021/05/25 07:03:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $moyen, $msg, $idcaddie, $item;

switch ($moyen) {
	case 'import':
		caddie_controller::proceed_import($idcaddie, $item, 'EXPL');
		break;
	case 'selection':
		require_once ($class_path."/caddie_procs.class.php");
		caddie_controller::proceed_selection($idcaddie, 'collecte', '', 'selection');
		break;
	case 'douchette':
		caddie_controller::proceed_barcode($idcaddie, 'collecte', 'add');
		break;
	default:
		print "<br /><br /><b>".$msg["caddie_select_collecte"]."</b>" ;
		break;
	}
