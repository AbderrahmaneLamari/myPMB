<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: transaction_payment_method.tpl.php,v 1.4 2021/02/03 08:27:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $transaction_payment_method_form, $msg, $current_module;

$transaction_payment_method_content_form="
<div class='row'>
	<label class='etiquette' for='f_name'>".$msg["transaction_payment_method_form_name"]."</label>
	<div class='row'>
		<input type='text' class='saisie-50em' id=\"f_name\" value='!!name!!' name='f_name'  />				
	</div>
</div>
";



