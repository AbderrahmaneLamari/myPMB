<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: stat_opac.tpl.php,v 1.13 2022/03/29 12:35:35 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $stat_view_addview_content_form, $stat_view_addcol_content_form, $stat_view_request_content_form, $stat_view_import_req_form, $current_module;

$stat_view_addview_content_form="
<div class='colonne2'>
	<div class='row'>
		<label class='etiquette'>$msg[stat_view_name] &nbsp;</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-20em' name='view_name' value='!!name_view!!' />
	</div>
</div>
<div class='colonne_suite'>
	<div class='row'>
		<label class='etiquette'>$msg[stat_view_comment] &nbsp;</label>
	</div>
	<div class='row'>
		<textarea name='view_comment' rows='2' cols='50'/>!!view_comment!!</textarea>
	</div>
</div>
!!table_colonne!!

<div class='row'></div>
";

$stat_view_addcol_content_form="
<div class='row'>
	<label class='etiquette'>$msg[stat_col_name] &nbsp;</label>
</div>
<div class='row'>
	<input type='text' class='saisie-20em' name='col_name' value='!!col_name!!' />
</div>
<div class='row'>
	<label class='etiquette'>$msg[stat_col_expr] &nbsp;</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='expr_col' value='!!expr_col!!' />
</div>
<div class='row'>
	<label class='etiquette'>$msg[stat_col_filtre] &nbsp;</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='expr_filtre' value='!!expr_filtre!!' />
</div>
<div class='row'>
	<label class='etiquette'>$msg[stat_col_type] &nbsp;</label>
</div>
<div class='row'>
	!!datatype!!
</div>
";

$stat_view_request_content_form = "
<script>
	function right_to_left() {
		left=document.request_form.f_request_code;
		right=document.request_form.elements['nom_col[]'];
		for (i=0; i<right.length; i++) {
			if (right.options[i].selected) {
				left.value =  left.value +' '+ right.options[i].text;
			}
		}
	}
</script>
<div class='row'>
	<label class='etiquette' for='form_name'>$msg[705]</label>
</div>
<div class='row'>
	<input type='text' name='f_request_name' value='!!name_request!!' maxlength='255' class='saisie-50em' />
</div>
<table height='100%' width='100%'>
	<tbody>
		<tr>
			<td width='40%'><label class='etiquette' for='form_code'>$msg[706]</label></td>
			<td width='20%'></td>
			<td width='40%'><label class='etiquette' for='form_code'>$msg[stat_associate_col]</label></td>
		</tr>
		<tr>
			<td height='100%' width='40%'><textarea cols='55' rows='8' name='f_request_code'>!!code!!</textarea></td>
			<td width='20%' style='text-align:center'><input type='button' class='bouton' value='<<' onClick=\"right_to_left()\" />&nbsp;</td>
			<td height='100%' width='40%'>!!liste_cols!!</td>
		</tr>
	</tbody>
</table>

<div class='row'>
	<label class='etiquette' for='form_comment'>$msg[707]</label>
</div>
<div class='row'>
	<input type='text' name='f_request_comment' value='!!comment!!' maxlength='255' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='autorisations_all'>".$msg["procs_autorisations_all"]."</label>
	<input type='checkbox' id='autorisations_all' name='autorisations_all' value='1' !!autorisations_all!! />
</div>
<div class='row'>
	<label class='etiquette' for='form_type'>".$msg['etagere_autorisations']."</label>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,1);'>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,0);'>
</div>
<div class='row'>
	!!autorisations_users!!
</div>
";

$stat_view_import_req_form="
<form class='form-$current_module' ENCTYPE='multipart/form-data' name='fileform' method='post' action='!!action!!' >
<h3>".$msg['stat_title_form_import']."</h3>
<div class='form-contenu' >
	<div class='row'>
		<label class='etiquette' for='req_file'>".$msg['stat_file_import']."</label>
		</div>
	<div class='row'>
		<INPUT NAME='f_fichier' 'saisie-80em' TYPE='file' size='60'>
		</div>
	</div>
<input type='submit' class='bouton' value=' ".$msg['stat_bt_import']." ' />
</form>
";

?>