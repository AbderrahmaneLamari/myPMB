<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: tva_achats.inc.php,v 1.17 2021/01/21 08:52:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset, $id;

// gestion des comptes de tva achats
require_once("$class_path/tva_achats.class.php");
require_once($class_path."/configuration/configuration_controller.class.php");

$tva_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>".htmlentities($msg[103],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='libelle' name='libelle' value=\"!!libelle!!\" class='saisie-30em' />
</div>

<div class='row'>
	<label class='etiquette' for='taux_tva'>".htmlentities($msg['acquisition_tva_taux'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type='text' id='taux_tva' name='taux_tva' value=\"!!taux_tva!!\" class='saisie-10em' />&nbsp;
	<label class='etiquette'>%</label>
</div>

<div class='row'>
	<label class='etiquette' for='cp_compta'>".htmlentities($msg['acquisition_num_cp_compta'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type='text' id='cp_compta' name='cp_compta' value=\"!!cp_compta!!\" class='saisie-20em' />
</div>
";

configuration_controller::set_model_class_name('tva_achats');
configuration_controller::set_list_ui_class_name('list_configuration_acquisition_tva_ui');
configuration_controller::proceed($id);
