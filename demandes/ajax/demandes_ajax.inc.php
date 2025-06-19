<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes_ajax.inc.php,v 1.11 2021/08/06 11:50:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $include_path, $sub, $action, $quoifaire;
global $id_demande, $idnotice, $object_type;

require_once("$class_path/mono_display.class.php");
require_once("$class_path/demandes.class.php");
require_once("$include_path/templates/demandes_actions.tpl.php");

switch($quoifaire){
	
	case 'show_list_action':
		$demande=new demandes($id_demande,false);
		ajax_http_send_response(demandes_actions::show_list_actions($demande->actions,$id_demande,0,false,true));
		break;
	case 'show_notice':
		show_notice($idnotice);	
		break;
	case 'change_read_dmde':
		$demande=new demandes($id_demande,false);
		demandes::change_read($demande);
		ajax_http_send_response(demandes::dmde_propageLu($id_demande));
		break;

}

switch ($sub) {
	case 'faq_questions':
		switch($action) {
			case "list":
				require_once($class_path.'/demandes/faq_questions_controller.class.php');
				faq_questions_controller::proceed_ajax($object_type, 'demandes');
				break;
		}
		break;
	default:
		switch($action) {
			case "list":
				require_once($class_path.'/demandes/demandes_controller.class.php');
				demandes_controller::proceed_ajax($object_type, 'demandes');
				break;
		}
		break;
}
/*
 * Affichage de la notice
 */
function show_notice($idnotice){
	
	
	$isbd = new mono_display($idnotice, 6, '', 1, '', '', '',1);	
	$html = "<div class='row' style='padding-top: 8px;'>".$isbd->aff_statut."<h1 style='display: inline;'>".$isbd->header."</h1></div>";
	$html .= "<div class='row'>".$isbd->isbd."</div>";
	
	
	print ajax_http_send_response($html);
}
?>