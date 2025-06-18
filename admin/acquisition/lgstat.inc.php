<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: lgstat.inc.php,v 1.7 2021/01/21 08:52:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// gestion des statuts de lignes d'actes
global $class_path, $msg, $charset, $id;
require_once("$class_path/lignes_actes_statuts.class.php");
require_once($class_path."/configuration/configuration_controller.class.php");

$lgstat_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>".htmlentities($msg[103],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='libelle' name='libelle' value=\"!!libelle!!\" class='saisie-60em' />
</div>
<div class='row'>
	<label class='etiquette'>".htmlentities($msg['acquisition_lgstat_arelancer'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	!!sel_relance!!
</div>
";

configuration_controller::set_model_class_name('lgstat');
configuration_controller::set_list_ui_class_name('list_configuration_acquisition_lgstat_ui');
configuration_controller::proceed($id);
