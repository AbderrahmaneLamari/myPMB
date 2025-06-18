<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: etagere.tpl.php,v 1.23 2021/03/24 08:33:56 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

// templates pour la gestion des paniers

global $etagere_content_form, $msg, $etagere_constitution_content_form;

// template pour le form de création d'une étagère
$etagere_content_form = "
<!--	type	-->
<div class='row'>
	<label class='etiquette' for='form_type'>".$msg['etagere_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' name='form_etagere_name' value='!!name!!' data-translation-fieldname='name' />
</div>
<div class='row'>
	<label class='etiquette' for='form_type'>".$msg['etagere_visible_date']."</label>
</div>
<div class='row'>".
	$msg['etagere_visible_date_all']."&nbsp;<input type=checkbox name=form_visible_all value='1' !!checkbox_all!! class='checkbox' onClick=\"vadidite_check(this.form)\" />&nbsp;&nbsp;".$msg['etagere_visible_date_deb']."<input type='text' class='saisie-10em' name='form_visible_deb' value='!!form_visible_deb!!' />&nbsp;".$msg['etagere_visible_date_fin']."&nbsp;<input type='text' class='saisie-10em' name='form_visible_fin' value='!!form_visible_fin!!' />&nbsp;".$msg['etagere_visible_accueil']."&nbsp;<input type=checkbox name=form_visible_accueil value='1' !!checkbox_accueil!! class='checkbox'  />
</div>
<div class='row'>
	<label class='etiquette' for='form_type'>".$msg['etagere_comment']."</label>
</div>
<div class='row'>
	<textarea id='f_n_contenu' class='saisie-80em' name='form_etagere_comment' cols='62' rows='5' wrap='virtual' data-translation-fieldname='comment'>!!comment!!</textarea>
</div>
<div class='row'>
	<label class='etiquette' for='form_etagere_comment_gestion'>".$msg['etagere_comment_gestion']."</label>
</div>
<div class='row'>
	<textarea id='form_etagere_comment_gestion' class='saisie-80em' name='form_etagere_comment_gestion' cols='62' rows='5' wrap='virtual' data-translation-fieldname='comment_gestion'>!!comment_gestion!!</textarea>
</div>
<div class='row'>
	<div class='row'>
		<label class='etiquette' for='form_type'>".$msg['etagere_thumbnail_url']."</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-80em' id='f_thumbnail_url' name='f_thumbnail_url' value=\"!!thumbnail_url!!\" />
		<input type='button' class='bouton' value='".$msg['raz']."' onClick=\"try{document.getElementById('f_thumbnail_url').value='';document.getElementById('f_img_load').value='';} catch(e) {}; \"/>	
	</div>
";
global $pmb_notice_img_folder_id;
if($pmb_notice_img_folder_id) {
	$etagere_content_form.="
	<div title='".htmlentities($msg['etagere_img_load'],ENT_QUOTES, $charset)."' >
		<!--    Vignette upload    -->
		<div class='row'>
			<label for='f_img_load' class='etiquette'>".$msg['etagere_img_load']."</label>!!message_folder!!
		</div>
		<div class='row'>
			<input type='file' class='saisie-80em' id='f_img_load' name='f_img_load' value='' />
		</div>
	</div>";
}
$etagere_content_form.="
	<div class='row'>
		<label class='etiquette' for='form_type'>".$msg['etagere_autorisations']."</label>
		<input type='button' class='bouton_small align_middle' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,1);'>
		<input type='button' class='bouton_small align_middle' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,0);'>
	</div>
	<div class='row'>
		!!autorisations_users!!
	</div>
	<div class='row'>
		<a href=# onClick=\"document.getElementById('history').src='./sort.php?action=0&caller=etagere'; document.getElementById('history').style.display='';return false;\" alt=\"".$msg['tris_dispos']."\" title=\"".$msg['tris_dispos']."\">
			<img src='".get_url_icon('orderby_az.gif')."' class='align_middle' hspace='3'>
		</a>
		<input type='hidden' value='!!tri!!' name='tri'/>
		<span id='etagere_sort'>
			!!tri_name!!
		</span>
		<script type='text/javascript'>
			function getSort(id,name){
				document.forms.etagere_form.tri.value=id;
				var name = document.createTextNode(name);
				var span = document.getElementById('etagere_sort');
				while(span.firstChild){
					span.removeChild(span.firstChild);
				}
				span.appendChild(name);
				
			}
		</script>
	</div>
	<div class='row'>
		<label class='etiquette' for='form_type'>".$msg['etagere_classement_list']."</label>
	</div>
	<div class='row'>
		<select data-dojo-type='dijit/form/ComboBox' id='classementGen_!!object_type!!' name='classementGen_!!object_type!!'>
			!!classements_liste!!
		</select>
	</div>
</div>
";

// template pour le form de constitution d'une étagère
$etagere_constitution_content_form = "
<div class='row'>
	!!constitution!!
</div>
<input type='hidden' name='idetagere' value='!!idetagere!!' />
";

