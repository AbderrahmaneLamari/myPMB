<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.22 2022/06/27 14:04:39 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $sub, $page, $msg, $pmb_map_activate, $pmb_extended_search_dnd_interface;

require_once($class_path."/search.class.php");
require_once($class_path."/search_perso.class.php");

$sc=new search(true);
$sc->init_links();

switch ($sub) {
	case "launch":
		if ((string)$page=="") {
		    if(!isset($_SESSION["session_history"])) $_SESSION["session_history"] = array();
			$_SESSION["CURRENT"]=count($_SESSION["session_history"]);
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["URI"]="./catalog.php?categ=search&mode=6";
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["POST"]=$_POST;
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["GET"]=$_GET;
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["GET"]["sub"]="";
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["POST"]["sub"]="";
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["HUMAN_QUERY"]=$sc->make_human_query();
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["HUMAN_TITLE"]= "[".$msg["130"]."] ".$msg["search_extended"];
			$_POST["page"]=0;
			$page=0;
		}
		$sc->show_results("./catalog.php?categ=search&mode=6&sub=launch","./catalog.php?categ=search&mode=6", true, '', true );
		if ($_SESSION["CURRENT"]!==false) {
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["URI"]="./catalog.php?categ=search&mode=6&sub=launch";
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["POST"]=$_POST;
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["GET"]=$_GET;
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["PAGE"]=$page+1;
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["HUMAN_QUERY"]=$sc->make_human_query();
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["SEARCH_TYPE"]="extended";
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]['TEXT_LIST_QUERY']='';
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["TEXT_QUERY"]="";
			$_SESSION["session_history"][$_SESSION["CURRENT"]]["NOTI"]["EXTENDED_SEARCH"]=$sc->json_encode_search();
		}
		if($pmb_map_activate){
			$sc->check_emprises();
		}		
		break;
	default:
		print $sc->show_form("./catalog.php?categ=search&mode=6","./catalog.php?categ=search&mode=6&sub=launch");
		if ($pmb_extended_search_dnd_interface){
			$search_perso= new search_perso();
			print '<div id="search_perso" style="display:none">'.$search_perso->get_forms_list().'</div>';
		}	
		break;		
}
