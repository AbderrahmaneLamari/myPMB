<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: nomenclature_family.tpl.php,v 1.1 2021/01/27 08:36:32 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $nomenclature_family_content_form_tpl;

$nomenclature_family_content_form_tpl="		
<div class='row'>
	<label class='etiquette' for='name'>".$msg['admin_nomenclature_family_form_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='name' id='name' value='!!name!!' />
</div>		
<div class='row'>
	<label class='etiquette'>".$msg['admin_nomenclature_family_pupitres']."</label>
</div>
<div class='row'>
	!!musicstands!!
</div>
";

$nomenclature_family_musicstand_content_form_tpl="
<div class='row'>
	<label class='etiquette' for='name'>".$msg['admin_nomenclature_family_musicstand_form_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='name' id='name' value='!!name!!' />
</div>		
<div class='row'>
	<label class='etiquette' for='division'>".$msg['admin_nomenclature_family_musicstand_form_division']."</label>
</div>
<div class='row'>
	<input type='checkbox' name='division' id='division' value='1' !!checked!!/>
</div>			
<div class='row'>
	<label class='etiquette' for='workshop'>".$msg['admin_nomenclature_family_musicstand_form_workshop']."</label>
</div>
<div class='row'>
	<input type='checkbox' name='workshop' id='workshop' value='1' !!workshop_checked!!/>
</div>					
<div class='row'>
	<label class='etiquette' for='name'>".$msg['admin_nomenclature_family_musicstand_form_instruments']."</label>
</div>
<div class='row'>
	!!instruments!!
</div>
";