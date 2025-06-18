<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: threshold.tpl.php,v 1.4 2021/01/22 08:48:42 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $threshold_content_form_tpl, $msg, $charset, $pmb_gestion_devise;

$threshold_content_form_tpl = "
<div class='row'>
	<div class='colonne25'>".htmlentities($msg['threshold_entity'], ENT_QUOTES, $charset)."</div>
	<div class='colonne_suite' >
		<b>!!entity_label!!</b>
		<input type='hidden' id='threshold_num_entity' name='threshold_num_entity' value='!!num_entity!!' />
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne25'>".htmlentities($msg['threshold_label'], ENT_QUOTES, $charset)."</div>
	<div class='colonne_suite' >
		<input type='text' id='threshold_label' name='threshold_label' class='saisie-30em' value='!!label!!' />
	</div>
</div>
<div class='row'>
	<div class='colonne25'>".htmlentities($msg['threshold_amount'], ENT_QUOTES, $charset)."</div>
	<div class='colonne_suite' >
		<input type='text' id='threshold_amount' name='threshold_amount' class='saisie-10em' value='!!amount!!' /> ".$pmb_gestion_devise."
	</div>
</div>
<div class='row'>
	<div class='colonne25'>".htmlentities($msg['threshold_amount_tax_included'], ENT_QUOTES, $charset)."</div>
	<div class='colonne_suite' >
		<input type='checkbox' id='threshold_amount_tax_included' name='threshold_amount_tax_included' value='1' !!amount_tax_included!! />
	</div>
</div>
<div class='row'>
	<div class='colonne25'>".htmlentities($msg['threshold_footer'], ENT_QUOTES, $charset)."</div>
	<div class='colonne_suite' >
		<textarea id='threshold_footer' name='threshold_footer' cols='55' rows='10'>!!footer!!</textarea>
	</div>
</div>";