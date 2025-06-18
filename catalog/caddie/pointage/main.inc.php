<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.14 2021/05/25 07:03:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $moyen, $msg, $idcaddie;

switch ($moyen) {
	case 'raz':
		caddie_controller::proceed_raz($idcaddie);
		break;
	case 'selection':
		require_once ($class_path."/caddie_procs.class.php");
		caddie_controller::proceed_selection($idcaddie, 'pointage', '', 'selection');
		break;
	case 'douchette':
		caddie_controller::proceed_barcode($idcaddie, 'pointage', 'pointe');
		break;
	case 'panier':
		include ("./catalog/caddie/pointage/panier.inc.php");
		break;
	case 'search_history':
		include ("./catalog/caddie/pointage/search_history.inc.php");
		break;
	default:
		print "<br /><br /><b>".$msg["caddie_select_pointage"]."</b>" ;
		break;
	}
