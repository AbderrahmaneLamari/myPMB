<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: transaction.tpl.php,v 1.5 2021/02/03 08:27:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $transactype_content_form, $msg;

$transactype_content_form="
<div class='row'>
	<label class='etiquette' for='f_name'>".$msg["transactype_form_name"]."</label>
	<div class='row'>
		<input type='text' class='saisie-50em' id=\"f_name\" value='!!name!!' name='f_name'  />				
	</div>
</div>
<div class='row'>
	<label class='etiquette' for='f_unit_price'>".$msg["transactype_form_unit_price"]."</label>
	<div class='row'>
		<input type='text' class='saisie-50em' id=\"f_unit_price\" value='!!unit_price!!' name='f_unit_price'  />				
	</div>
</div>
<div class='row'>
	<label class='etiquette' for='f_quick_allowed'>".$msg["transactype_form_quick_allowed"]."</label>
	<div class='row'>
		<input type='checkbox' !!quick_allowed_checked!! class='checkbox' id=\"f_quick_allowed\" value='1' name='f_quick_allowed'  />				
	</div>
</div>
";



