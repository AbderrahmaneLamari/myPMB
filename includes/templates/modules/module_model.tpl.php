<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: module_model.tpl.php,v 1.1 2021/06/07 10:19:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $module_content_form;

$module_content_form = "
<div class='row'>
	<label class='etiquette' for='module_destination_link'>".$msg['module_destination_link']."</label>
</div>
<div class='row'>
	<input type='text' id='module_destination_link' name='module_destination_link' value='!!destination_link!!' class='saisie-40em' />
</div>
<input type='hidden' id='module_name' name='module_name' value='!!name!!' />";

?>