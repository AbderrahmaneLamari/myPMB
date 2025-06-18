<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: suggestions_categ.inc.php,v 1.11 2021/01/21 08:52:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// gestion des listes de suggestions
global $class_path, $msg, $charset, $id;
require_once("$class_path/suggestions_categ.class.php");
require_once($class_path."/configuration/configuration_controller.class.php");

$categ_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>".htmlentities($msg[103],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='libelle' name='libelle' value=\"!!libelle!!\" class='saisie-60em' />
</div>
";

configuration_controller::set_model_class_name('suggestions_categ');
configuration_controller::set_list_ui_class_name('list_configuration_acquisition_categ_ui');
configuration_controller::proceed($id);