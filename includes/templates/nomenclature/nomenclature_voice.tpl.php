<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: nomenclature_voice.tpl.php,v 1.1 2021/01/27 08:36:32 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $nomenclature_voice_content_form_tpl;

$nomenclature_voice_content_form_tpl="		
<div class='row'>
	<label class='etiquette' for='code'>".$msg['admin_nomenclature_voice_form_code']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='code' id='code' value='!!code!!' />
</div>		
<div class='row'>
	<label class='etiquette' for='name'>".$msg['admin_nomenclature_voice_form_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='name' id='name' value='!!name!!' />
</div>	
";
