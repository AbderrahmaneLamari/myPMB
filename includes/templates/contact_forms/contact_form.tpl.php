<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: contact_form.tpl.php,v 1.4 2021/03/23 08:43:33 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $contact_form_object_content_form_tpl, $contact_form_content_form, $msg;

$contact_form_object_content_form_tpl="
<div class='row'>
	<label class='etiquette' for='object_label'>".$msg['admin_opac_contact_form_object_label']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='object_label' id='object_label' data-translation-fieldname='object_label' value='!!label!!' />
</div>
<div class='row'>
	<label class='etiquette' for='f_message'>".$msg["admin_opac_contact_form_object_message"]."</label>
</div>	
<div class='row'>
	<textarea id='object_message' name='object_message' cols='100' rows='20' data-translation-fieldname='object_message'>!!message!!</textarea>
</div>
";

// $contact_form_content_form : template form contact form
$contact_form_content_form = "
<div class='row'>
	<label class='etiquette' for='contact_form_label'>".$msg['admin_opac_contact_form_label']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='contact_form_label' id='contact_form_label' value='!!label!!' />
</div>
<div class='row'>
	<label class='etiquette' for='contact_form_desc'>".$msg['admin_opac_contact_form_desc']."</label>
</div>
<div class='row'>
	<textarea name='contact_form_desc' id='contact_form_desc'>!!desc!!</textarea>
</div>
";