<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: nomenclature_formation.tpl.php,v 1.1 2021/01/27 08:36:32 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $nomenclature_formation_content_form_tpl, $nomenclature_formation_type_content_form_tpl;

$nomenclature_formation_content_form_tpl="		
<div class='row'>
	<label class='etiquette' for='name'>".$msg['admin_nomenclature_formation_form_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='name' id='name' value='!!name!!' />
</div>	
<div class='row'>
	<label class='etiquette'>".$msg['admin_nomenclature_formation_form_nature']."</label>
</div>
<div class='row'>		
	<input type='radio' name='nature' value='0' !!nature_checked_0!! />
	".$msg['admin_nomenclature_formation_form_nature_instrument']."
</div>				
<div class='row'>	
	<input type='radio' name='nature' value='1' !!nature_checked_1!! />
	".$msg['admin_nomenclature_formation_form_nature_voice']."
</div>	
<div class='row'>
	<label class='etiquette'>".$msg['admin_nomenclature_formation_types']."</label>
</div>
<div class='row'>
	!!types!!
</div>		
";

$nomenclature_formation_type_content_form_tpl="
<div class='row'>
	<label class='etiquette' for='name'>".$msg['admin_nomenclature_formation_type_form_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='name' id='name' value='!!name!!' />
</div>
";
