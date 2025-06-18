<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: modes_paiements.inc.php,v 1.17 2021/01/21 08:52:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// gestion des modes de paiement
global $class_path, $msg, $charset, $id;
require_once("$class_path/paiements.class.php");
require_once($class_path."/configuration/configuration_controller.class.php");

$mode_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>".htmlentities($msg[103],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='libelle' name='libelle' value=\"!!libelle!!\" class='saisie-60em' />
</div>

<div class='row'>
	<label class='etiquette' for='comment'>".htmlentities($msg['acquisition_mode_comment'],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<textarea id='comment' name='comment' class='saisie-80em' cols='62' rows='6' wrap='virtual'>!!commentaire!!</textarea>
</div>
";

configuration_controller::set_model_class_name('paiements');
configuration_controller::set_list_ui_class_name('list_configuration_acquisition_mode_ui');
configuration_controller::proceed($id);
