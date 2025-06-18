<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: analytics_service.tpl.php,v 1.1.2.3 2021/07/21 13:52:16 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $charset, $base_path;
global $analytics_service_js_content_form, $analytics_service_content_form;

$analytics_service_calculate_button = "
<div class='row' id='analytics_service_calculate'>
	<button data-dojo-type='dijit/form/Button'>
	".$msg['analytics_service_calculate_template']."
		<script type='dojo/on' data-dojo-event='click'>
			require(['dojo/request/xhr', 'dojo/dom-form', 'dojo/topic'], function(xhr, domForm, topic){
				xhr.post('./ajax.php?module=admin&categ=opac&sub=analytics_services&action=get_templates',
				 	{
						handleAs: 'json',
						data: domForm.toObject('analytics_service_form')
					}
				).then(function(response){
					if(response) {
						if(document.getElementById('analytics_service_template')) {
							document.getElementById('analytics_service_template').innerHTML = response['template'];
						}
						if(document.getElementById('analytics_service_consent_template')) {
							document.getElementById('analytics_service_consent_template').innerHTML = response['consent_template'];
						}
					}
				});
			});
		</script>
	</button>
</div>";
						
$analytics_service_content_form = '
<div class="row">
	<label class="etiquette" for="analytics_service_name">'.$msg['analytics_service_name'].'</label>
	<input type="hidden" id="analytics_service_name" name="analytics_service_name" value="!!name!!" />
</div>
<div class="row">
	!!display_label!!
</div>
<div class="row">
	<label class="etiquette" for="analytics_service_active">'.$msg['analytics_service_active'].'</label>
</div>
<div class="row">
	<input type="checkbox" id="analytics_service_active" name="analytics_service_active" class="switch" value="1" !!active_checked!!>
	<label for="analytics_service_active">&nbsp;</label>
</div>
!!parameters!!
<div class="row">&nbsp;</div>
<div class="row">
	'.$analytics_service_calculate_button.'
</div>
<hr />
<div class="row">
	'.htmlentities($msg['analytics_service_template_description'], ENT_QUOTES, $charset).'
</div>
<div class="row">&nbsp;</div>
<div class="row">
	<label class="etiquette" for="analytics_service_template">'.$msg['analytics_service_template'].'</label>
</div>
<div class="row">
	<textarea id="analytics_service_template" name="analytics_service_template" cols="120" rows="40">!!template!!</textarea>
</div>
<div class="row">
	<label class="etiquette" for="analytics_service_consent_template">'.$msg['analytics_service_consent_template'].'</label>
</div>
<div class="row">
	<textarea id="analytics_service_consent_template" name="analytics_service_consent_template" cols="120" rows="10">!!consent_template!!</textarea>
</div>';
