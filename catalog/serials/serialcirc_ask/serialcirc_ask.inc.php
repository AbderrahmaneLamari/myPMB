<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_ask.inc.php,v 1.5 2020/11/05 12:56:21 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if(!isset($location_id)) $location_id = 0;
if(!isset($type_filter)) $type_filter = 0;
if(!isset($statut_filter)) $statut_filter = 0;

require_once("$class_path/serialcirc_ask.class.php");

switch($sub){		
	case 'circ_ask':		
		switch($action){	
			case 'accept':		
				foreach($asklist_id as $id){
					$ask= new serialcirc_ask($id);
					$ask->accept();
				}				
			break;		
			case 'refus':		
				foreach($asklist_id as $id){
					$ask= new serialcirc_ask($id);
					$ask->refus();
				}				
			break;		
			case 'delete':		
				foreach($asklist_id as $id){
					$ask= new serialcirc_ask($id);
					$ask->delete();
				}				
			break;				
		}
		$list_serialcirc_ask_ui = new list_serialcirc_ask_ui();
		print $list_serialcirc_ask_ui->get_display_list();
	break;		
	default :
		$list_serialcirc_ask_ui = new list_serialcirc_ask_ui();
		print $list_serialcirc_ask_ui->get_display_list();
	break;		
	
}



