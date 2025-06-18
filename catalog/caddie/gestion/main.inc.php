<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.12 2021/05/25 07:03:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $quoi, $baseLink, $categ, $action, $idcaddie, $item;

require_once("$class_path/classementGen.class.php") ;
require_once($class_path."/caddie_procs.class.php");

// inclusions principales
switch ($quoi) {
	case 'procs':
		caddie_procs::proceed();
		break;
	case 'remote_procs':
		caddie_procs::proceed_remote();
		break;
	case "classementGen" :
		$baseLink="./catalog.php?categ=caddie&sub=gestion&quoi=classementGen";
		$classementGen = new classementGen($categ,0);
		$classementGen->proceed($action);
		break;
	case 'panier':
	default:
		if(!isset($item)) $item = '';
		caddie_controller::proceed($idcaddie, $item);
		break;
	}
