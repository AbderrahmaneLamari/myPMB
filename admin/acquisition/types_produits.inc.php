<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: types_produits.inc.php,v 1.19 2021/01/21 08:52:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset, $id;
global $acquisition_gestion_tva;

// gestion des types de produits achetés
require_once("$class_path/types_produits.class.php");
require_once("$class_path/tva_achats.class.php");
require_once($class_path."/configuration/configuration_controller.class.php");

$type_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>".htmlentities($msg[103],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='libelle' name='libelle' value=\"!!libelle!!\" class='saisie-30em' />
</div>

<div class='row'>
	<label class='etiquette' for='cp_compta'>".htmlentities($msg['acquisition_num_cp_compta'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type='text' id='cp_compta' name='cp_compta' value=\"!!cp_compta!!\" class='saisie-20em' />
</div>
";

if ($acquisition_gestion_tva) {
	$type_content_form.="	
	<div class='row'>
		<label class='etiquette'>".htmlentities($msg['acquisition_num_tva_achat'],ENT_QUOTES,$charset)."</label>
	</div>
	<div class='row'>
		!!tva_achat!!
	</div>
";
}

//Gestion de la tva
if ($acquisition_gestion_tva) {
	$nbr = tva_achats::countTva();
	
	//Gestion de TVA et pas de taux de tva définis
	if (!$nbr) {
		$error_msg.= htmlentities($msg["acquisition_err_tva"],ENT_QUOTES, $charset)."<div class='row'></div>";	
		error_message($msg[321], $error_msg.htmlentities($msg["acquisition_err_par"],ENT_QUOTES, $charset), '1', './admin.php?categ=acquisition');
		die;
	}
}

configuration_controller::set_model_class_name('types_produits');
configuration_controller::set_list_ui_class_name('list_configuration_acquisition_type_ui');
configuration_controller::proceed($id);
