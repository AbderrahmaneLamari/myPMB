<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: suggestion_source.tpl.php,v 1.4 2021/01/18 12:58:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $src_content_form, $charset, $msg;

$src_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>".htmlentities($msg[103],ENT_QUOTES,$charset)."</label>
</div>
<div class='row'>
	<input type=text id='libelle' name='libelle' value=\"!!libelle!!\" class='saisie-60em' />
</div>
";
?>