<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: liste_lecture.inc.php,v 1.12 2021/05/21 08:13:59 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path;
global $id_liste, $lvl, $act, $sub, $msg;

require_once ($class_path."/liste_lecture.class.php");

print "<h3><span>".$msg['list_lecture_private']."</span></h3>";

$id_liste = intval($id_liste);
$listes = new liste_lecture($id_liste, $act);

switch($lvl){
	case 'public_list' :
		$listes->generate_publiclist();
		print $listes->display;
		break;
		
	case 'private_list':
		switch($sub) {
			case 'my_list':
				$listes->generate_mylist();
				if($act != 'add_list') {
				    print $listes->display;
				}
				break;
			case 'shared_list':
				$listes->generate_sharedlist();
				print $listes->display;
				break;
			default:
				$listes->generate_privatelist();
				print $listes->display;
				break;
		}
		break;
	case 'demande_list':
		$listes->generate_demandes();
		print $listes->display;
		break; 
	default:
		break;
}


?>