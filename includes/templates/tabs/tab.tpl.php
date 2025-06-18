<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: tab.tpl.php,v 1.2 2021/05/19 08:50:52 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $tab_content_form;

$tab_content_form = "
<div class='row'>
	<label class='etiquette' for='tab_visible'>".$msg["tab_visible"]."</label>
	<input type='checkbox' id='tab_visible' name='tab_visible' value='1' !!visible!! />
</div>
<div class='row'>
	<label class='etiquette' for='tab_autorisations_all'>".$msg["tab_autorisations_all"]."</label>
	<input type='checkbox' id='tab_autorisations_all' name='tab_autorisations_all' value='1' !!autorisations_all!! />
</div>
<div class='row'>
	<label class='etiquette' for='tab_autorisations'>".$msg['tab_autorisations']."</label>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,1);'>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,0);'>
</div>
<div class='row'>
	!!autorisations_users!!
</div>
<div class='row'>
	<label class='etiquette' for='tab_shortcut'>".$msg['tab_shortcut']."</label>
</div>
<div class='row'>
	<input type='text' id='tab_shortcut' name='tab_shortcut' value='!!shortcut!!' class='saisie-5em' />
</div>
<input type='hidden' id='tab_module' name='tab_module' value='!!module!!' />
<input type='hidden' id='tab_categ' name='tab_categ' value='!!categ!!' />
<input type='hidden' id='tab_sub' name='tab_sub' value='!!sub!!' />";

?>