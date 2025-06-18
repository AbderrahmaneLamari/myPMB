<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes_notes.inc.php,v 1.13 2021/04/21 16:58:29 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $act, $msg, $idnote, $idaction;
global $redirectto;

require_once($class_path."/demandes.class.php");
require_once($class_path."/demandes_notes.class.php");
require_once($class_path."/demandes_actions.class.php");

$notes = new demandes_notes($idnote,$idaction);
$actions = new demandes_actions($idaction);

switch($act){
	case 'add_note':
		$notes->set_properties_from_form();
		$notes->save();
		demandes_notes::note_majParent($notes->id_note, $notes->num_action, $actions->num_demande,"_gestion");
		demandes_notes::note_majParent($notes->id_note, $notes->num_action, $actions->num_demande,"_opac");
		$actions->fetch_data($notes->num_action,false);
		if($redirectto=="demandes-show_consult_form"){
			$demande=new demandes($actions->num_demande);
			$demande->show_consult_form($notes->num_action);
		}else{
			$actions->show_consultation_form();
		}
		break;
	case 'reponse':
		$notes->show_modif_form(true);
		break;
	case 'save_note':
		$notes->set_properties_from_form();
		$notes->save();
		demandes_notes::note_majParent($notes->id_note, $notes->num_action, $actions->num_demande,"_gestion");
		demandes_notes::note_majParent($notes->id_note, $notes->num_action, $actions->num_demande,"_opac");
		$actions->fetch_data($idaction,false);
		$actions->show_consultation_form();
		break;
	case 'modif_note':
		$notes->show_modif_form();
		break;
	case 'suppr_note':
		demandes_notes::delete($notes);
		$actions->fetch_data($idaction,false);
		$actions->show_consultation_form();
		break;
}

?>