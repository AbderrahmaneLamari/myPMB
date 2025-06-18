<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frais.inc.php,v 1.27 2021/01/21 08:52:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// gestion des frais annexes
global $class_path, $msg, $charset, $id;
require_once "{$class_path}/frais.class.php";
require_once "{$class_path}/tva_achats.class.php";
require_once "{$class_path}/configuration/configuration_controller.class.php";

$frais_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>".htmlentities($msg[103],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='libelle' name='libelle' value=\"!!libelle!!\" class='saisie-30em' />
</div>

<div class='row'>
	<label class='etiquette' for='condition'>".htmlentities($msg['acquisition_frais_cond'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<textarea id='condition' name='condition' class='saisie-80em' cols='62' rows='6' wrap='virtual'>!!condition!!</textarea>
</div>

<div class='row'>
	<label class='etiquette' for='montant'>".htmlentities($msg['acquisition_frais_montant'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='montant' name='montant' value=\"!!montant!!\" class='saisie-10em' />
	<label class='etiquette'>&nbsp;".$pmb_gestion_devise."</label>
</div>

<div class='row'>
	<label class='etiquette' for='cp_compta'>".htmlentities($msg['acquisition_num_cp_compta'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type='text' id='cp_compta' name='cp_compta' value=\"!!cp_compta!!\" class='saisie-20em' />
</div>	
";

if ($acquisition_gestion_tva) {
	$frais_content_form.="	
	<div class='row'>
		<label class='etiquette'>".htmlentities($msg['acquisition_num_tva_achat'],ENT_QUOTES,$charset)."</label>
	</div>
	<div class='row'>
		!!tva_achat!!
	</div>
";
}

$frais_content_form.="
	<div class='row'>
		<label class='etiquette'>".htmlentities($msg['acquisition_frais_add_to_new_order'], ENT_QUOTES, $charset)."</label>
	</div>
	<div class='row'>
		!!add_to_new_order!!
	</div>
";

configuration_controller::set_model_class_name('frais');
configuration_controller::set_list_ui_class_name('list_configuration_acquisition_frais_ui');
configuration_controller::proceed($id);
