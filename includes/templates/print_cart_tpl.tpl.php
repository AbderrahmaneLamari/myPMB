<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: print_cart_tpl.tpl.php,v 1.7 2021/02/02 07:43:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $cart_tpl_content_form, $msg, $pmb_javascript_office_editor;

$cart_tpl_content_form= jscript_unload_question()."
	$pmb_javascript_office_editor
<script type='text/javascript' src='./javascript/tinyMCE_interface.js'></script>
<script type='text/javascript'>
	function test_form(form){
		if((form.f_name.value.length == 0) )		{
			alert('".$msg["admin_mailtpl_name_error"]."');
			return false;
		}
		unload_off();
		return true;
	}
</script>
<div class='row'>
	<label class='etiquette' for='f_name'>".$msg['admin_print_cart_tpl_form_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='f_name' id='f_name' value='!!name!!' />
</div>
<div class='row'>
	<label class='etiquette' for='f_header'>".$msg["admin_print_cart_tpl_form_header"]."</label>
	<div class='row'>
		<textarea id='f_header' name='f_header' cols='100' rows='20'>!!header!!</textarea>
	</div>
</div>
<div class='row'>
	<label class='etiquette' for='f_footer'>".$msg["admin_print_cart_tpl_form_footer"]."</label>
	<div class='row'>
		<textarea id='f_footer' name='f_footer' cols='100' rows='20'>!!footer!!</textarea>
	</div>
</div>
";