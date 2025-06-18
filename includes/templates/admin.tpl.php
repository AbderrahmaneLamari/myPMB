<?php
// +-------------------------------------------------+
// ï¿½ 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: admin.tpl.php,v 1.329.2.1 2021/06/30 07:52:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $pmb_sur_location_activate, $pmb_transferts_actif;
global $opac_websubscribe_show, $opac_serialcirc_active;
global $file_in, $suffix, $mimetype, $output, $admin_menu_new, $msg;
global $acquisition_active, $demandes_active, $pmb_map_activate;
global $charset;
global $admin_layout, $current_module, $admin_layout_end, $admin_user_javascript;
global $admin_npass_form, $admin_user_form, $fiches_active, $thesaurus_concepts_active, $dsi_active, $semantic_active, $pmb_extension_tab, $frbr_active, $modelling_active;
global $user_acquisition_adr_form, $admin_param_form, $password_field, $admin_user_list, $cms_active, $admin_user_alert_row, $admin_user_link1, $admin_codstat_content_form, $location_map_tpl;
global $admin_location_form_sur_loc_part, $admin_location_content_form, $admin_section_content_form, $admin_statut_content_form, $admin_orinot_content_form, $admin_onglet_content_form, $admin_notice_usage_content_form;
global $admin_map_echelle_content_form, $admin_map_projection_content_form, $admin_map_ref_content_form, $admin_typdoc_content_form, $admin_lender_content_form, $admin_support_content_form, $admin_emplacement_content_form, $admin_categlec_content_form;
global $admin_statlec_content_form, $admin_empr_statut_content_form, $admin_proc_content_form, $admin_proc_view_remote, $admin_zbib_content_form, $admin_zattr_form, $admin_convert_end, $noimport, $n_errors, $errors_msg;
global $admin_calendrier_form, $admin_calendrier_form_mois_start, $admin_calendrier_form_mois_commentaire, $admin_calendrier_form_mois_end, $admin_notice_statut_content_form;
global $admin_collstate_statut_content_form, $admin_abonnements_periodicite_content_form, $admin_procs_clas_content_form, $admin_infopages_content_form, $admin_group_content_form;
global $admin_liste_jscript, $admin_docnum_statut_content_form, $admin_authorities_statut_content_form;
global $acquisition_rent_requests_activate;
global $pmb_contribution_area_activate;

if(!isset($file_in)) $file_in = '';
if(!isset($suffix)) $suffix = '';
if(!isset($mimetype)) $mimetype = '';
if(!isset($output)) $output = '';

// ---------------------------------------------------------------------------
//	$admin_menu_new : Menu vertical de l'administration
// ---------------------------------------------------------------------------

global $class_path;
require_once($class_path."/modules/module_admin.class.php");
require_once($class_path."/list/tabs/list_tabs_ui.class.php");
require_once($class_path."/list/tabs/list_tabs_admin_ui.class.php");
$module_admin = module_admin::get_instance();
$admin_menu_new = $module_admin->get_left_menu();
	
//    ----------------------------------
// $admin_layout : layout page administration
$admin_layout = "
<!-- conteneur -->
<div id='conteneur'  class='$current_module'>".
$admin_menu_new."
<!-- contenu -->
<div id='contenu'>
!!menu_contextuel!!
";

// $admin_layout_end : layout page administration (fin)
$admin_layout_end = '
</div>
<!-- /conteneur -->
</div>
';


// $admin_user_Javascript : scripts pour la gestion des utilisateurs
$admin_user_javascript = "
<script type='text/javascript'>
	function test_pwd(form, status)
	{
		if(form.form_pwd.value.length == 0)
		{
				alert(\"$msg[79]\");
				return false;
		}
		if(form.form_pwd.value != form.form_pwd2.value)
		{
				alert(\"$msg[80]\");
				return false;
		}

		return true;
	}

	function test_form_create(form, status)
	{
		if(form.form_login.value.replace(/^\s+|\s+$/g, '').length == 0)
		{
				alert(\"$msg[81]\");
				return false;
		}

		if(!form.form_admin.checked && !form.form_catal.checked && !form.form_circ.checked && !form.form_extensions.checked
			&& !form.form_restrictcirc.checked
			&& !form.form_fiches.checked
			&& !form.form_auth.checked
			&& !form.form_dsi.checked
			&& !form.form_pref.checked
			&& !form.form_thesaurus.checked
			&& !form.form_acquisition.checked
			&& !form.form_cms.checked
			&& !form.form_edition.checked
		){
				alert(\"$msg[84]\");
				return false;
		}

		if(status == 1) {
				if(form.form_pwd.value.length == 0)
				{
					alert(\"$msg[82]\");
					return false;
				}
				if(form.form_pwd.value != form.form_pwd2.value)
				{
					alert(\"$msg[83]\");
					return false;
				}

		}

		return true;
	}
</script>
";

// $admin_npass_form : template form changement password
$admin_npass_form = "
<form class='form-$current_module' id='userform' name='userform' method='post' action='./admin.php?categ=users&sub=users&action=pwd&id=!!id!!'>
<h3><span onclick='menuHide(this,event)'>$msg[86] !!myUser!!</span></h3>
<div class='form-contenu'>
	<div class='row'>
		<label class='etiquette' for='form_pwd'>$msg[87]</label>
		<input class='saisie-20em' id='form_pwd' type='password' name='form_pwd' />
		</div>
	<div class='row'>
		<label class='etiquette' for='form_pwd2'>$msg[88]</label>
		<input class='saisie-20em' id='form_pwd2' type='password' name='form_pwd2' />
		</div>
	</div>
<div class='row'>
	<input class='bouton' type='button' value=' $msg[76] ' onClick=\"document.location='./admin.php?categ=users&sub=users'\" />&nbsp;
	<input class='bouton' type='submit' value=' $msg[77] ' onClick=\"return test_pwd(this.form)\" />
	</div>
</form>
";

// $admin_user_form : template form user
$admin_user_form = "
<script type=\"text/javascript\">
<!--
function setValue(f_element, factor) {
    var maxv = 50;
    var minv = 1;

    var vl = document.forms['account_form'].elements[f_element].value;
    if((vl < maxv) && (factor == 1))
       vl++;
    if((vl > minv) && (factor == -1))
        vl--;
    document.forms['account_form'].elements[f_element].value = vl;
}
function test_pwd(form, status) {
	if(form.passw.value.length != 0) {
		if(form.passw.value != form.passw2.value) {
			alert(\"$msg[80]\");
			return false;
		}
    }
	return true;
}

function account_calcule_section(selectBox) {
	for (i=0; i<selectBox.options.length; i++) {
		id=selectBox.options[i].value;
	    list=document.getElementById(\"docloc_section\"+id);
	    list.style.display=\"none\";
	}

	id=selectBox.options[selectBox.selectedIndex].value;
	list=document.getElementById(\"docloc_section\"+id);
	list.style.display=\"block\";
}
-->
</script>
<form class='form-$current_module' name='userform' method='post' action='./admin.php?categ=users&sub=users&action=update&id=!!id!!'>
<h3><span onclick='menuHide(this,event)'>!!title!!</span></h3>
<div class='form-contenu'>
	<div class='row'>
		<div class='colonne3'>
			<label class='etiquette'>$msg[91] &nbsp;</label><br />
			<input type='text' class='saisie-20em' name='form_login' value='!!login!!' />
		</div>
		<div class='colonne3'>
			<label class='etiquette'>$msg[67] &nbsp;</label><br />
			<input type='text' class='saisie-20em' name='form_nom' value='!!nom!!' />
		</div>
		<div class='colonne3'>
			<label class='etiquette'>$msg[68] &nbsp;</label><br />
			<input type='text' class='saisie-20em' name='form_prenom' value='!!prenom!!' />
		</div>
	</div>

	<div class='row'>
		<div class='colonne3'>
			<label class='etiquette'>$msg[user_langue] &nbsp;</label><br />
			!!select_lang!!
		</div>
		<div class='colonne_suite'>
			<!-- sel_group -->
		</div>
	</div>
	<div class='row'><span class='space-wide-space'>&nbsp;</span><hr /></div>
	<div class='row'>
		<div class='colonne3'>
			<label class='etiquette'>".$msg['email']." &nbsp;</label><br />
			<input type='text' class='saisie-20em' name='form_user_email' value='!!user_email!!' />
		</div>
		<div class='colonne3'>
			<span class='ui-panel-display'>
				<input type='checkbox' class='checkbox' !!alter_resa_mail!! value='1' name='form_user_alert_resamail' />
				<label class='etiquette'>".$msg['alert_resa_user_mail']." &nbsp;</label>
			</span>
			<span class='ui-panel-display'>
				".($pmb_contribution_area_activate ? "<input type='checkbox' class='checkbox' !!alert_contrib_mail!! value='1' name='form_user_alert_contribmail' />
				<label class='etiquette'>".$msg['alert_contrib_user_mail']." &nbsp;</label>" : "")."
			</span>
			<span class='ui-panel-display'>
				".($acquisition_active ? "<input type='checkbox' class='checkbox' !!alert_sugg_mail!! value='1' name='form_user_alert_suggmail' />
				<label class='etiquette'>".$msg['alert_sugg_user_mail']." &nbsp;</label>" : "")."
			</span>
		</div>
		<div class='colonne3'>
			<span class='ui-panel-display'>
				".($demandes_active ? "<input type='checkbox' class='checkbox' !!alert_demandes_mail!! value='1' name='form_user_alert_demandesmail' />
				<label class='etiquette'>".$msg['alert_demandes_user_mail']." &nbsp;</label>" : "")."
			</span>
			<span class='ui-panel-display'>
				".($opac_websubscribe_show ? "<input type='checkbox' class='checkbox' !!alert_subscribe_mail!! value='1' name='form_user_alert_subscribemail' />
				<label class='etiquette'>".$msg['alert_subscribe_user_mail']." &nbsp;</label>" : "")."
			</span>
		</div>
	</div>
	<div class='row'><span class='space-wide-space'>&nbsp;</span><hr /></div>
	<div class='row'>
		<div class='colonne3'></div>
		<div class='colonne3'></div>
		<div class='colonne3'></div>
	</div>
	".($opac_serialcirc_active ? "
	<div class='row'>
		<div class='colonne3'>
			<span class='space-wide-space'>&nbsp;</span>
		</div>
		<div class='colonne3'>
			<input type='checkbox' class='checkbox' !!alert_serialcirc_mail!! value='1' name='form_user_alert_serialcircmail' />
			<label class='etiquette'>".$msg['alert_subscribe_serialcirc_mail']." &nbsp;</label>
		</div>
		<div class='row'><span class='space-wide-space'>&nbsp;</span><hr /></div>
	</div>
	" : "")."
	!!password_field!!

<div class='row'>
	<div class='row'>
		<label class='etiquette' for='form_nb_per_page_search'>$msg[nb_enreg_par_page]</label>
	</div>
	<div class='colonne4'>
	<!--	Nombre d'enregistrements par page en recherche	-->
		<label class='etiquette' for='form_nb_per_page_search'>$msg[900]</label><br />
		<input type='text' class='saisie-10em' name='form_nb_per_page_search' value='!!nb_per_page_search!!' size='4' />
	</div>
	<div class='colonne4'>
	<!--	Nombre d'enregistrements par page en sélection d'autorités	-->
		<label class='etiquette'>${msg[901]}</label><br />
		<input class='saisie-10em' type='text' id='form_nb_per_page_select' name='form_nb_per_page_select' value='!!nb_per_page_select!!' size='4' />
	</div>
	<div class='colonne_suite'>
		<label class='etiquette' for='form_nb_per_page_gestion'>${msg[902]}</label><br />
		<input type='text' class='saisie-10em' id='form_nb_per_page_gestion' name='form_nb_per_page_gestion' value='!!nb_per_page_gestion!!' size='4' />
	</div>
</div>
<div class='row'><hr /></div>

<div class='row'>
	<div class='row'>
        <label class='etiquette'>$msg[92]</label>
    </div>
    <div class='colonne4'>
    	<input type='checkbox' class='checkbox' !!circ_flg!! value='1' id='form_circ' name='form_circ' /><label for='form_circ'>$msg[5]</label><br />\n
    	<input type='checkbox' class='checkbox' !!modif_cb_expl_flg!! value='1' id='form_catal_modif_cb_expl' name='form_catal_modif_cb_expl' /><label for='form_catal_modif_cb_expl'><i>".$msg['catal_modif_cb_expl_droit']."</i></label><br/>\n
    	<input type='checkbox' class='checkbox' !!restrictcirc_flg!! value='1' id='form_restrictcirc' name='form_restrictcirc' /><label for='form_restrictcirc'><i>".$msg["restrictcirc_auth"]."</i></label><br />
    	<input type='checkbox' class='checkbox' !!admin_flg!! value='1' id='form_admin' name='form_admin' /><label for='form_admin'>$msg[7]</label><br />\n
        <input type='checkbox' class='checkbox' !!fiches_flg!! value='1' id='form_fiches' name='form_fiches' /><label for='form_fiches'>".$msg["onglet_fichier"]."</label><br />\n
        <input type='checkbox' class='checkbox' !!concepts_flg!! value='1' id='form_concepts' name='form_concepts' /><label for='form_concepts'>".$msg["ontology_skos_menu"]."</label><br />\n
    </div>
    <div class='colonne4'>
    	<input type='checkbox' class='checkbox' !!catal_flg!! value='1' id='form_catal' name='form_catal' /><label for='form_catal'>$msg[93]</label><br />\n
    	<input type='checkbox' class='checkbox' !!edit_flg!! value='1' id='form_edition' name='form_edition' /><label for='form_edition'>$msg[1100]</label><br />\n
    	<input type='checkbox' class='checkbox' !!edit_forcing_flg!! value='1' id='form_edition_forcing' name='form_edition_forcing' /><label for='form_edition_forcing'>".$msg["edit_droit_forcing"]."</label><br />\n
    	<input type='checkbox' class='checkbox' !!sauv_flg!! value='1' id='form_sauv' name='form_sauv' /><label for='form_sauv'>$msg[28]</label><br />
    	<input type='checkbox' class='checkbox' !!cms_flg!! value='1' id='form_cms' name='form_cms' /><label for='form_cms'>".$msg["cms_onglet_title"]."</label><br />
    	<input type='checkbox' class='checkbox' !!cms_build_flg!! value='1' id='form_cms_build' name='form_cms_build' /><label for='form_cms_build'>".$msg["cms_build_tab"]."</label><br />\n
    </div>
    <div class='colonne4'>
    	<input type='checkbox' class='checkbox' !!auth_flg!! value='1' id='form_auth' name='form_auth' /><label for='form_auth'>$msg[132]</label><br />\n
        <input type='checkbox' class='checkbox' !!dsi_flg!! value='1' id='form_dsi' name='form_dsi' /><label for='form_dsi'>".$msg["dsi_droit"]."</label><br />\n
        <input type='checkbox' class='checkbox' !!pref_flg!! value='1' id='form_pref' name='form_pref' /><label for='form_pref'>$msg[933]</label><br />\n
        <input type='checkbox' class='checkbox' !!acquisition_account_invoice_flg!! value='1' id='form_acquisition_account_invoice_flg' name='form_acquisition_account_invoice_flg' /><label for='form_acquisition_account_invoice_flg'>".$msg['acquisition_account_invoice_flg']."</label><br>\n
        <input type='checkbox' class='checkbox' !!semantic_flg!! value='1' id='form_semantic' name='form_semantic' /><label for='form_semantic'>".$msg['semantic_flg']."</label><br />\n
        <input type='checkbox' class='checkbox' !!animations_flg!! value='1' id='form_animations' name='form_animations' /><label for='form_animations'>".$msg["animations"]."</label><br />\n
    </div>
    <div class='colonne_suite'>
    	<input type='checkbox' class='checkbox' !!thesaurus_flg!! value='1' id='form_thesaurus' name='form_thesaurus' /><label for='form_thesaurus'>$msg[thesaurus_auth]</label><br />\n
        <input type='checkbox' class='checkbox' !!acquisition_flg!! value='1' id='form_acquisition' name='form_acquisition' /><label for='form_acquisition'>".$msg["acquisition_droit"]."</label><br />\n
        <input type='checkbox' class='checkbox' !!transferts_flg!! value='1' id='form_transferts' name='form_transferts' /><label for='form_transferts'>".$msg["transferts_droit"]."</label><br />\n
        <input type='checkbox' class='checkbox' !!extensions_flg!! value='1' id='form_extensions' name='form_extensions' /><label for='form_extensions'>".$msg["extensions_droit"]."</label><br />\n
        <input type='checkbox' class='checkbox' !!demandes_flg!! value='1' id='form_demandes' name='form_demandes' /><label for='form_demandes'>".$msg["demandes_droit"]."</label><br />\n
        <input type='checkbox' class='checkbox' !!frbr_flg!! value='1' id='form_frbr' name='form_frbr' /><label for='form_frbr'>".$msg["frbr"]."</label><br />\n
        <input type='checkbox' class='checkbox' !!modelling_flg!! value='1' id='form_modelling' name='form_modelling' /><label for='form_modelling'>".$msg["modelling"]."</label><br />\n
    </div>
</div>

<div class='row'>
	!!form_param_default!!
</div>
<div class='row'></div>
</div>
<div class='row'>
	<div class='left'>
		<input class='bouton' type='button' value=' $msg[76] ' onClick=\"document.location='./admin.php?categ=users&sub=users'\" />&nbsp;
		<input class='bouton' type='submit' value=' $msg[77] ' onClick=\"return test_form_create(this.form, !!form_type!!)\" />
		!!button_duplicate!!
		<input type='hidden' name='form_actif' value='1'>
		</div>
	<div class='right'>
		!!bouton_suppression!!
		</div>
	</div>
<div class='row'></div>
</form>
";


$user_acquisition_adr_form = "
<div class='row'>
	<div class='child'>
		<div class='colonne2'>".htmlentities($msg['acquisition_adr_liv'], ENT_QUOTES, $charset)."</div>
		<div class='colonne2'>".htmlentities($msg['acquisition_adr_fac'], ENT_QUOTES, $charset)."</div>
	</div>
</div>
<div class='row'>
	<div class='child'>
		<div class='colonne2'>
			<div class='colonne' >
				<input type='hidden' id='id_adr_liv[!!id_bibli!!]' name='id_adr_liv[!!id_bibli!!]' value='!!id_adr_liv!!' />
				<textarea  id='adr_liv[!!id_bibli!!]' name='adr_liv[!!id_bibli!!]' class='saisie-30emr' readonly='readonly' cols='50' rows='6' wrap='virtual'>!!adr_liv!!</textarea>&nbsp;
			</div>
			<div class='colonne_suite' >
				<input type='button' class='bouton_small' tabindex='1' value='".$msg['parcourir']."' onclick=\"openPopUp('./select.php?what=coord&caller=!!form_name!!&param1=id_adr_liv[!!id_bibli!!]&param2=adr_liv[!!id_bibli!!]&id_bibli=!!id_bibli!!', 'selector'); \" />&nbsp;
				<input type='button' class='bouton_small' tabindex='1' value='X' onclick=\"document.getElementById('id_adr_liv[!!id_bibli!!]').value='0';document.getElementById('adr_liv[!!id_bibli!!]').value='';\" />
			</div>
		</div>
		<div class='colonne2'>
			<div class='colonne'>
				<input type='hidden' id='id_adr_fac[!!id_bibli!!]' name='id_adr_fac[!!id_bibli!!]' value='!!id_adr_fac!!' />
				<textarea id='adr_fac[!!id_bibli!!]' name='adr_fac[!!id_bibli!!]'  class='saisie-30emr' readonly='readonly' cols='50' rows='6' wrap='virtual'>!!adr_fac!!</textarea>&nbsp;
			</div>
			<div class='colonne_suite'>
				<input type='button' class='bouton_small' tabindex='1' value='".$msg['parcourir']."' onclick=\"openPopUp('./select.php?what=coord&caller=!!form_name!!&param1=id_adr_fac[!!id_bibli!!]&param2=adr_fac[!!id_bibli!!]&id_bibli=!!id_bibli!!', 'selector'); \" />&nbsp;
				<input type='button' class='bouton_small' tabindex='1' value='X' onclick=\"document.getElementById('id_adr_fac[!!id_bibli!!]').value='0';document.getElementById('adr_fac[!!id_bibli!!]').value='';\" />
			</div>
		</div>
	</div>
</div>
";

$admin_param_form = "
<form class='form-$current_module' id='paramform' name='paramform' method='post' action='./admin.php?categ=param&action=update&id_param=!!id_param!!#justmodified'>
<h3><span onclick='menuHide(this,event)'>!!form_title!!</span></h3>
<div class='form-contenu'>
	<div class='row'>
		<div class='colonne5 align_right'>
				<label class='etiquette'>$msg[1602] &nbsp;</label>
				</div>
		<div class='colonne_suite'>
				!!type_param!! <input type='hidden' name='form_type_param' value='!!type_param!!' />
				</div>
		</div>
	<div class='row'>&nbsp;</div>
	<div class='row'>
		<div class='colonne5 align_right'>
				<label class='etiquette'>$msg[1603] &nbsp;</label>
				</div>
		<div class='colonne_suite'>
				!!sstype_param!! <input type='hidden' name='form_sstype_param' value='!!sstype_param!!' />
				</div>
		</div>
	<div class='row'>&nbsp;</div>
	<div class='row'>
		<div class='colonne5 align_right'>
				<label class='etiquette'>$msg[1604] &nbsp;</label>
				</div>
		<div class='colonne_suite'>
				<textarea id='form_valeur_param' name='form_valeur_param' rows='10' cols='90' wrap='virtual' !!data-translation!!>!!valeur_param!!</textarea>
				</div>
		</div>
	<div class='row'>&nbsp;</div>
	<div class='row'>
		<div class='colonne5 align_right'>
				<label class='etiquette'>".$msg['param_explication']." &nbsp;</label>
				</div>
		<div class='colonne_suite'>
				<textarea name='comment_param' rows='10' cols='90' wrap='virtual'>!!comment_param!!</textarea>
				</div>
		</div>
	<div class='row'> </div>
	</div>
	<div class='row'>
		<input class='bouton' type='button' value=' $msg[76] ' onClick=\"document.location='./admin.php?categ=param'\">
		<input class='bouton' type='submit' value=' $msg[77] ' />
		<input type='hidden' class='text' name='form_id_param' value='!!id_param!!' readonly />
			</div>
</form>
<script type='text/javascript'>document.forms['paramform'].elements['form_valeur_param'].focus();</script>
";


$password_field = "
<div class='row'>
	<div class='colonne3'>
		<label class='etiquette'>$msg[2]</label><br />
		<input type='password' name='form_pwd' class='ui-width-medium saisie-20em'>
		</div>
	<div class='colonne3'>
		<label class='etiquette'>$msg[88]</label><br />
		<input type='password' name='form_pwd2' class='ui-width-medium saisie-20em'>
		</div>
	</div>
<div class='row'>&nbsp;</div>
<hr />
";

// $admin_user_list : template liste utilisateurs
$admin_user_list = "
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4'>
		<label class='etiquette'>!!user_name!! (!!user_login!!)</label>
		</div>
	<div class='colonne_suite'>
		!!user_link!!
		</div>
	<div class='colonne_suite' style='float:right;'>
		!!user_created_date!!
	</div>
	</div>
<div class='row'>
	<table class='brd'>";

// Première ligne
$admin_user_list .= "
		<tr >
			<td class='brd'>!!nusercirc!!$msg[5]</td>
			<td class='brd'>!!nusercatal!!$msg[93]</td>
			<td class='brd'>!!nuserauth!!$msg[132]</td>
			<td class='brd'>!!nuserthesaurus!!".$msg["thesaurus_auth"]."</td>
		</tr>";

// Deuxième ligne
$admin_user_list .= "
		<tr>
			<td class='brd'>!!nusermodifcbexpl!!<i>".$msg['catal_modif_cb_expl_droit']."</i></td>
			<td class='brd'>!!nuseredit!!$msg[1100]</td>
			<td class='brd'>";
					if ($dsi_active) $admin_user_list .= "!!nuserdsi!!$msg[dsi_droit]</td>";
					else $admin_user_list .= "&nbsp;</td>";
$admin_user_list .= "<td class='brd'>";
					if ($acquisition_active) $admin_user_list .= "!!nuseracquisition!!$msg[acquisition_droit]</td>";
					else $admin_user_list .= "&nbsp;</td>";
$admin_user_list .= "
		</tr>";

// Troisième ligne
$admin_user_list .= "
		<tr>
			<td class='brd'>!!nuserrestrictcirc!!<i>".$msg["restrictcirc_auth"]."</i></td>
			<td class='brd'>!!nusereditforcing!!$msg[edit_droit_forcing]</td>
			<td class='brd'>!!nuserpref!!$msg[933]</td>
			<td class='brd'>";
				if ($pmb_transferts_actif)
					$admin_user_list .= "!!nusertransferts!!$msg[transferts_droit]</td>";
				else $admin_user_list .= "&nbsp;</td>";
$admin_user_list .= "
		</tr>";

// Quatrième ligne
$admin_user_list .= "
		<tr>
			<td class='brd'>!!nuseradmin!!$msg[7]</td>";
		$admin_user_list .= "<td class='brd'>!!nusersauv!!$msg[28]</td>";
			$admin_user_list .= "<td class='brd'>";
			if ($cms_active)
				$admin_user_list .= "!!nusercms!!$msg[cms_onglet_title]</td>";
			else $admin_user_list .= "&nbsp;</td>";
$admin_user_list .= "
			<td class='brd'>";
			if ($cms_active)
				$admin_user_list .= "!!nusercms_build!!$msg[cms_build_tab]</td>";
			else $admin_user_list .= "&nbsp;</td>";
$admin_user_list .= "
		</tr>";

// Cinquième ligne
$admin_user_list .= "
		<tr>
		<td class='brd'>";
			if ($pmb_extension_tab) $admin_user_list .="!!nuserextensions!!$msg[extensions_droit]</td>";
			else $admin_user_list .= "&nbsp;</td>";
$admin_user_list .= "<td class='brd'>";
			if ($demandes_active)
				$admin_user_list .= "!!nuserdemandes!!$msg[demandes_droit]</td>";
			else $admin_user_list .= "&nbsp;</td>";
			$admin_user_list .= "<td class='brd'>";
			if ($fiches_active)
				$admin_user_list .= "!!nuserfiches!!$msg[onglet_fichier]</td>";
			else $admin_user_list .= "&nbsp;</td>";
			$admin_user_list .= "<td class='brd'>";
			if ($acquisition_active && $acquisition_rent_requests_activate)
				$admin_user_list .= "!!nuseracquisition_account_invoice!!".$msg['acquisition_account_invoice_flg']."</td>";
			else $admin_user_list .= "&nbsp;</td>";
			$admin_user_list .= "
		</tr>";

// Sixième  ligne
$admin_user_list .= "
		<tr>
			<td class='brd'>";
if($semantic_active){
	$admin_user_list .= "!!nusersemantic!!<i>".$msg["semantic_flg"]."</i>";
}
$admin_user_list .= "</td>
			<td class='brd'>";
if($thesaurus_concepts_active){
	$admin_user_list .= "!!nuserconcepts!!<i>".$msg["ontology_skos_menu"]."</i>";
}
$admin_user_list .= "</td>
			<td class='brd'>";
if($modelling_active){
	$admin_user_list .= "!!nusermodelling!!".$msg["modelling"];
}
$admin_user_list .= "</td>
			<td class='brd'></td>
		</tr>";
// Septième ligne
$admin_user_list .= "
		!!user_alert_resamail!!";

// Septième ligne Bis
if ($pmb_contribution_area_activate) $admin_user_list .= "
		!!user_alert_contribmail!!";

// Huitième ligne
if ($demandes_active) $admin_user_list .= "
		!!user_alert_demandesmail!!";

// Neuvième ligne
if ($opac_websubscribe_show) $admin_user_list .= "
		!!user_alert_subscribemail!!";

// 10eme ligne
if ($acquisition_active) $admin_user_list .= "
		!!user_alert_suggmail!!";

$admin_user_list .= "</table>
</div>
<div class='row'>&nbsp;</div>
<hr />
";

$admin_user_alert_row = "
		<tr>
				<td colspan=4 class='brd'>
				!!user_alert!! &nbsp;
				</td>
		</tr>";

$admin_user_link1 = "
	<input class='bouton' type='button' value=' $msg[62] ' onClick=\"document.location='./admin.php?categ=users&sub=users&action=modif&id=!!nuserid!!'\">&nbsp;
	<input class='bouton' type='button' value=' $msg[mot_de_passe] ' onClick=\"document.location='./admin.php?categ=users&sub=users&action=pwd&id=!!nuserid!!'\">
	";
	
// commented because now use the confirmation_delete function used also from the other submodules
// so we show also the name we want to delete - Marco Vaninetti


// $admin_codstat_content_form : template form code stat
$admin_codstat_content_form = "
<div class='row'>
		<label class='etiquette' for='form_cb'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' data-translation-fieldname='codestat_libelle' />
</div>
<div class='row'>
	<label class='etiquette'>$msg[proprio_codage_interne]</label>
</div>
<div class='row'>
	<input type='text' name='form_statisdoc_codage_import' value='!!statisdoc_codage_import!!' class='saisie-20em' />
</div>
<div class='row'>
	<label class='etiquette'>$msg[proprio_codage_proprio]</label>
</div>
<div class='row'>
	!!lender!!
</div>
";

$admin_location_form_sur_loc_part="";
if($pmb_sur_location_activate)
$admin_location_form_sur_loc_part = "
	<div class='row'>
		<label class='etiquette'>$msg[sur_location_select_surloc]</label>
		</div>
	<div class='row'>
		!!sur_loc_selector!!
		<label class='etiquette' >$msg[sur_location_use_surloc]</label>
		<input type=checkbox name='form_location_use_surloc' value='1' !!checkbox_use_surloc!! class='checkbox' />
	</div>
";

//    ----------------------------------------------------
//    Onglet map
//    ----------------------------------------------------
global $pmb_map_activate;
$location_map_tpl = "";
if ($pmb_map_activate)
	$location_map_tpl = "
<!-- onglet 14 -->
<div id='el14Parent' class='parent'>
	<h3>
    	<img src='".get_url_icon('plus.gif')."' class='img_plus' name='imEx' id='el14Img' onClick=\"expandBase('el14', true); return false;\" title='".$msg["notice_map_onglet_title"]."' border='0' /> ".$msg["notice_map_onglet_title"]."
	</h3>
</div>

<div id='el14Child' class='child' etirable='yes' title='".htmlentities($msg['notice_map_onglet_title'],ENT_QUOTES, $charset)."'>
	<div id='el14Child_0' title='".htmlentities($msg['notice_map'],ENT_QUOTES, $charset)."' movable='yes'>
		<div id='el14Child_0b' class='row'>
			!!location_map!!
		</div>
	</div>
</div>";

// $admin_location_content_form : template form des localisations
$admin_location_content_form = "
<div class='row'>
	<label class='etiquette' for='form_cb'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text name='form_libelle' value=\"!!libelle!!\" class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' >$msg[docs_location_pic]</label>
</div>
<div class='row'>
	<input type=text name='form_location_pic' value=\"!!location_pic!!\" class='saisie-50em' />
</div>
<div class='row'>
	<div class='colonne4'>
		<label class='etiquette' >$msg[opac_object_visible]</label>
		<input type=checkbox name='form_location_visible_opac' value='1' !!checkbox!! class='checkbox' />
	</div>
	<div class='colonne4'>
		<label class='etiquette' >CSS</label>
		<input type=text name='form_css_style' value='!!css_style!!' />
	</div>
	<div class='colonne_suite'>
		<label class='etiquette' >$msg[location_infopage_assoc]</label>
		!!loc_infopage!!
	</div>
</div>
<div class='row'>
	<label class='etiquette'>$msg[proprio_codage_interne]</label>
</div>
<div class='row'>
	<input type='text' name='form_locdoc_codage_import' value='!!locdoc_codage_import!!' class='saisie-20em' />
</div>
<div class='row'>
	<label class='etiquette'>$msg[proprio_codage_proprio]</label>
</div>
<div class='row'>
	!!lender!!
</div>
$admin_location_form_sur_loc_part	
<br />
<hr />".$location_map_tpl."
<br />
<div class='row'></div>
<div class='row'><label class='etiquette'>$msg[location_details_name]</label></div><div class='row'><input type='text' name='form_locdoc_name' value='!!loc_name!!' class='saisie-50em' /></div>
<div class='row'><label class='etiquette'>$msg[location_details_adr1]</label></div><div class='row'><input type='text' name='form_locdoc_adr1' value='!!loc_adr1!!' class='saisie-50em' /></div>
<div class='row'><label class='etiquette'>$msg[location_details_adr2]</label></div><div class='row'><input type='text' name='form_locdoc_adr2' value='!!loc_adr2!!' class='saisie-50em' /></div>
<div class='row'><label class='etiquette'>$msg[location_details_cp] / $msg[location_details_town]</label></div>
<div class='row'>
	<div class='colonne4'>
		<input type='text' name='form_locdoc_cp', ' value='!!loc_cp!!' maxlength='15' class='saisie-10em' />
	</div>
	<div class='colonne_suite'>
		<input type='text' name='form_locdoc_town', ' value='!!loc_town!!'' class='saisie-50em' />
	</div>
</div>
<div class='row'><label class='etiquette'>$msg[location_details_state] / $msg[location_details_country]</label></div>
<div class='row'>
	<div class='colonne3'>
		<input type='text' name='form_locdoc_state',' value='!!loc_state!!' class='saisie-20em' />
	</div>
	<div class='colonne_suite'>
		<input type='text' name='form_locdoc_country' value='!!loc_country!!' class='saisie-20em' />
	</div>
</div>
<div class='row'><label class='etiquette'>$msg[location_details_phone]</label></div><div class='row'><input type='text' name='form_locdoc_phone' value='!!loc_phone!!' maxlength='100' class='saisie-20em' /></div>
<div class='row'><label class='etiquette'>$msg[location_details_email]</label></div><div class='row'><input type='text' name='form_locdoc_email' value='!!loc_email!!' maxlength='100' class='saisie-20em' /></div>
<div class='row'><label class='etiquette'>$msg[location_details_website]</label></div><div class='row'><input type='text' name='form_locdoc_website' value='!!loc_website!!' maxlength='100' class='saisie-50em' /></div>
<div class='row'><label class='etiquette'>$msg[location_details_logo]</label></div><div class='row'><input type='text' name='form_locdoc_logo', ' value='!!loc_logo!!' maxlength='255' class='saisie-50em' /></div>
<div class='row'><label class='etiquette'>$msg[location_details_commentaire]</label></div><div class='row'><textarea class='saisie-50em' name='form_locdoc_commentaire' id='form_locdoc_commentaire' cols='55' rows='5'>!!loc_commentaire!!</textarea></div>
<input type='hidden' name='form_actif' value='1'>
";

// $admin_section_content_form : template form section
$admin_section_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' data-translation-fieldname='section_libelle' />
</div>
<div class='row'>
	<label class='etiquette' for='form_libelle_opac'>".$msg['docs_section_libelle_opac']."</label>
</div>
<div class='row'>
	<input type=text id='form_libelle_opac' name='form_libelle_opac' value='!!libelle_opac!!' class='saisie-50em' data-translation-fieldname='section_libelle_opac' />
</div>
<div class='row'>
	<label class='etiquette' >$msg[docs_section_pic]</label>
</div>
<div class='row'>
	<input type=text name='form_section_pic' value='!!section_pic!!' maxlength='255' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' >$msg[opac_object_visible]</label>
	<input type=checkbox name='form_section_visible_opac' value='1' !!checkbox!! class='checkbox' />
</div>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette'>$msg[proprio_codage_interne]</label>
		</div>
		<div class='row'>
			<input type='text' name='form_sdoc_codage_import' value='!!sdoc_codage_import!!' class='saisie-20em' />
		</div>
		<div class='row'>
			<label class='etiquette'>$msg[proprio_codage_proprio]</label>
		</div>
		<div class='row'>
			!!lender!!
		</div>
	</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette'>$msg[section_visible_loc]</label>
		</div>
		<div class='row'>
			!!num_locations!!
		</div>
	</div>
</div>
<div class='row'>&nbsp;</div>
";

// $admin_statut_form : template form statuts
$admin_statut_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' data-translation-fieldname='statut_libelle' />
</div>
<div class='row'>
	<label class='etiquette' for='form_libelle_opac'>".$msg["docs_statut_form_libelle_opac"]."</label>
</div>
<div class='row'>
	<input type=text id='form_libelle_opac' name='form_libelle_opac' value='!!libelle_opac!!' class='saisie-50em' data-translation-fieldname='statut_libelle_opac' />
</div>
<div class='row'>
	<label class='etiquette' for='form_pret'>$msg[117]</label>
	<input type=checkbox name=form_pret value='!!pret!!' !!checkbox!! class='checkbox' onClick=\"test_check(this.form)\" />
</div>
<div class='row'>
	<label class='etiquette' for='form_allow_resa'>".$msg["statut_allow_resa_title"]."</label>
	<input type=checkbox name=form_allow_resa value='1' !!checkbox_allow_resa!! class='checkbox'  />
</div>";

if ($pmb_transferts_actif=="1")
	$admin_statut_content_form .= "
	<div class='row'>
		<label class='etiquette' for='form_trans'>".$msg["transferts_statut_lib_transferable"]."</label>
		<input type=checkbox name=form_trans value='!!trans!!' !!checkbox_trans!! class='checkbox' onClick=\"test_check_trans(this.form)\" />
	</div>";
$admin_statut_content_form .= "
	<div class='row'>
		<label class='etiquette' for='form_visible_opac'>".$msg["opac_object_visible"]."</label>
		<input type=checkbox name=form_visible_opac value='!!visible_opac!!' !!checkbox_visible_opac!! class='checkbox' onClick=\"test_check_visible_opac(this.form)\" />
		</div>
	<div class='row'>
		<label class='etiquette'>$msg[proprio_codage_interne]</label>
		</div>
	<div class='row'>
		<input type='text' name='form_statusdoc_codage_import' value='!!statusdoc_codage_import!!' class='saisie-20em' />
		</div>
	<div class='row'>
		<label class='etiquette'>$msg[proprio_codage_proprio]</label>
		</div>
	<div class='row'>
		!!lender!!
	</div>
";

// $admin_orinot_content_form : template form origine notice
$admin_orinot_content_form = "
<div class='row'>
	<label class='etiquette' >$msg[orinot_nom]</label>
</div>
<div class='row'>
	<input type=text name='form_nom' value='!!nom!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' >$msg[orinot_pays]</label>
</div>
<div class='row'>
	<input type=text name='form_pays' value='!!pays!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' >$msg[orinot_diffusable]</label>
	<input type=checkbox name=form_diffusion value='1' !!checkbox!! class='checkbox' />
</div>
";

// $admin_onglet_content_form : Onglet personalisé de la notice
$admin_onglet_content_form = "
<div class='row'>
	<label class='etiquette' >$msg[admin_noti_onglet_name]</label>
</div>
<div class='row'>
	<input type=text name='form_nom' value='!!nom!!' class='saisie-50em' />
</div>
";

// $admin_notice_usage_content_form : template form droit d'usage notice
$admin_notice_usage_content_form = "
<div class='row'>
	<label class='etiquette' >".$msg['notice_usage_libelle']."</label>
</div>
<div class='row'>
	<input type=text id='usage_libelle' name='usage_libelle' value='!!usage_libelle!!' class='saisie-50em' data-translation-fieldname='usage_libelle' />
</div>
";

$admin_map_echelle_content_form = "
<div class='row'>
	<label class='etiquette' >$msg[admin_noti_map_echelle_name]</label>
</div>
<div class='row'>
	<input type=text name='form_nom' value='!!nom!!' class='saisie-50em' />
</div>
";

$admin_map_projection_content_form = "
<div class='row'>
	<label class='etiquette' >$msg[admin_noti_map_projection_name]</label>
</div>
<div class='row'>
	<input type=text name='form_nom' value='!!nom!!' class='saisie-50em' />
</div>
";

$admin_map_ref_content_form = "
<div class='row'>
	<label class='etiquette' >$msg[admin_noti_map_ref_name]</label>
</div>
<div class='row'>
	<input type=text name='form_nom' value='!!nom!!' class='saisie-50em' />
</div>
";

// $admin_typdoc_content_form : template form types doc
$admin_typdoc_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>".$msg[103]."</label>
</div>
<div class='row'>
	<input type='text' id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' data-translation-fieldname='tdoc_libelle' />
</div>

<!-- form_pret -->
<!-- form_short_loan_duration -->
<!-- form_resa -->
<!-- tarif_pret -->

<div class='row'>
	<label class='etiquette' for='form_tdoc_codage_import' >".$msg['proprio_codage_interne']."</label>
</div>
<div class='row'>
	<input type='text' id='form_tdoc_codage_import' name='form_tdoc_codage_import' value='!!tdoc_codage_import!!' class='saisie-20em' />
</div>
<div class='row'>
	<label class='etiquette'>".$msg['proprio_codage_proprio']."</label>
</div>
<div class='row'>
	<!-- lender -->
</div>
<script type='text/javascript'>
function test_form(form) {
	if(form.form_libelle.value.length == 0) {
		alert('".$msg[98]."');
		return false;
	}
	if(isNaN(form.form_pret.value) || form.form_pret.value.length == 0) {
		alert('".$msg[119]."');
		return false;
	}
	if(isNaN(form.form_short_loan_duration.value) || form.form_short_loan_duration.value.length == 0) {
		alert('".$msg['short_loan_duration_error']."');
		return false;
	}
	if(isNaN(form.form_resa.value) || form.form_resa.value.length == 0) {
		alert('".$msg['resa_duration_error']."');
		return false;
	}
	return true;
}
</script>
";

// $admin_lender_content_form : template form lenders
$admin_lender_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>$msg[558]</label>
</div>
<div class='row'>
	<input type='text' id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' />
</div>
";
// $admin_support_content_form : template form supports
$admin_support_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>".$msg["admin_collstate_support_nom"]."</label>
</div>
<div class='row'>
	<input type='text' id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' />
</div>
";
// $admin_emplacement_content_form : template form emplacements
$admin_emplacement_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>".$msg["admin_collstate_emplacement_nom"]."</label>
</div>
<div class='row'>
	<input type='text' id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' />
</div>
";

// $admin_categlec_content_form : template form categ lecteurs
$admin_categlec_content_form = "
<div class='row'>
		<label class='etiquette' for='form_cb'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text name='form_libelle' value='!!libelle!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='form_duree_adhesion'>$msg[1400]</label>
</div>
<div class='row'>
	<input type=text name='form_duree_adhesion' value='!!duree_adhesion!!' maxlength='10' class='saisie-5em' />
</div>
!!tarif_adhesion!!
<div class='row'>
	<label class='etiquette' for='form_age_min'>$msg[empr_categ_age_min]</label>
</div>
<div class='row'>
	<input type=text name='form_age_min' value='!!age_min!!' maxlength='3' class='saisie-5em' />
</div>
<div class='row'>
	<label class='etiquette' for='form_age_max'>$msg[empr_categ_age_max]</label>
</div>
<div class='row'>
	<input type=text name='form_age_max' value='!!age_max!!' maxlength='3' class='saisie-5em' />
</div>
";

// $admin_statlec_content_form : template form codestat lecteurs
$admin_statlec_content_form = "
<div class='row'>
	<label class='etiquette' for='form_cb'>$msg[103]</label>
</div>
<div class='row'>
	<input type='text' name='form_libelle' value='!!libelle!!' class='saisie-50em' />
</div>
";

// $admin_empr_statut_content_form : template formulaire statuts emprunteurs
$admin_empr_statut_content_form = "
<div class='row'>
	<label class='etiquette' for='form_cb'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text name='statut_libelle' value='!!libelle!!' class='saisie-50em' />
</div>
<div class='row'>
	<input type=checkbox name=allow_loan value='1' id=allow_loan !!checkbox_loan!! class='checkbox' />
	<label class='etiquette' for='allow_loan'>".$msg['empr_allow_loan']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_loan_hist value='1' id=allow_loan_hist !!checkbox_loan_hist!! class='checkbox'/>
	<label class='etiquette' for='allow_loan_hist'>".$msg['empr_allow_loan_hist']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_book value='1' id=allow_book !!checkbox_book!! class='checkbox' />
	<label class='etiquette' for='allow_book'>".$msg['empr_allow_book']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_opac value='1' id=allow_opac !!checkbox_opac!! class='checkbox' />
	<label class='etiquette' for='allow_opac'>".$msg['empr_allow_opac']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_dsi value='1' id=allow_dsi !!checkbox_dsi!! class='checkbox' />
	<label class='etiquette' for='allow_dsi'>".$msg['empr_allow_dsi']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_dsi_priv value='1' id=allow_dsi_priv !!checkbox_dsi_priv!! class='checkbox' />
	<label class='etiquette' for='allow_dsi_priv'>".$msg['empr_allow_dsi_priv']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_sugg value='1' id=allow_sugg !!checkbox_sugg!! class='checkbox' />
	<label class='etiquette' for='allow_sugg'>".$msg['empr_allow_sugg']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_dema value='1' id=allow_dema !!checkbox_dema!! class='checkbox' />
	<label class='etiquette' for='allow_dema'>".$msg['empr_allow_dema']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_liste_lecture value='1' id=allow_liste_lecture !!checkbox_liste_lecture!! class='checkbox' />
	<label class='etiquette' for='allow_liste_lecture'>".$msg['empr_allow_liste_lecture']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_prol value='1' id=allow_prol !!checkbox_prol!! class='checkbox' />
	<label class='etiquette' for='allow_prol'>".$msg['empr_allow_prol']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_avis value='1' id=allow_avis !!checkbox_avis!! class='checkbox' />
	<label class='etiquette' for='allow_avis'>".$msg['empr_allow_avis']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_tag value='1' id=allow_tag !!checkbox_tag!! class='checkbox' />
	<label class='etiquette' for='allow_tag'>".$msg['empr_allow_tag']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_pwd value='1' id=allow_pwd !!checkbox_pwd!! class='checkbox' />
	<label class='etiquette' for='allow_pwd'>".$msg['empr_allow_pwd']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_self_checkout value='1' id=allow_self_checkout !!allow_self_checkout!! class='checkbox' />
	<label class='etiquette' for='allow_self_checkout'>".$msg['empr_allow_self_checkout']."</label>
</div>
<div class='row'>
	<input type=checkbox name=allow_self_checkin value='1' id=allow_self_checkin !!allow_self_checkin!! class='checkbox' />
	<label class='etiquette' for='allow_self_checkin'>".$msg['empr_allow_self_checkin']."</label>
</div>
<div class='row'>
	<input type='checkbox' name='allow_serialcirc' value='1' id='allow_serialcirc' !!allow_serialcirc!! class='checkbox' />
	<label class='etiquette' for='allow_serialcirc'>".$msg['empr_allow_serialcirc']."</label>
</div>
<div class='row'>
	<input type='checkbox' name='allow_scan_request' value='1' id='allow_scan_request' !!allow_scan_request!! class='checkbox' />
	<label class='etiquette' for='allow_scan_request'>".$msg['empr_allow_scan_request']."</label>
</div>
<div class='row'>
	<input type='checkbox' name='allow_contribution' value='1' id='allow_contribution' !!allow_contribution!! class='checkbox' />
	<label class='etiquette' for='allow_contribution'>".$msg['empr_allow_contribution']."</label>
</div>
<div class='row'>
	<input type='checkbox' name='allow_pnb' value='1' id='allow_pnb' !!allow_pnb!! class='checkbox' />
	<label class='etiquette' for='allow_pnb'>".$msg['empr_allow_pnb']."</label>
</div>
";

// $admin_proc_content_form : template form procédures stockées
$admin_proc_content_form = "
<div class=colonne2>
	<div class='row'>
		<label class='etiquette' for='form_name'>$msg[705]</label>
	</div>
	<div class='row'>
		<input type='text' name='f_proc_name' value='!!name!!' maxlength='255' class='saisie-50em' />
	</div>
</div>
<div class=colonne_suite>
	<div class='row'>
		<label class='etiquette' for='form_classement'>$msg[proc_clas_proc]</label>
	</div>
	<div class='row'>
		!!classement!!
	</div>
</div>
<div class='row'>
	<label class='etiquette' for='form_code'>$msg[706]</label>
</div>
<div class='row'>
	<textarea cols='80' rows='8' name='f_proc_code'>!!code!!</textarea>
</div>
<div class='row'>
	<label class='etiquette' for='form_comment'>$msg[707]</label>
</div>
<div class='row'>
	<input type='text' name='f_proc_comment' value='!!comment!!' maxlength='255' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='form_notice_tpl'>".$msg['notice_tpl_notice_id']."</label>
</div>
<div class='row'>
	!!notice_tpl!!
</div>
<div class='row'>
	<label class='etiquette' for='autorisations_all'>".$msg["procs_autorisations_all"]."</label>
	<input type='checkbox' id='autorisations_all' name='autorisations_all' value='1' !!autorisations_all!! />
</div>
<div class='row'>
	<label class='etiquette' for='form_comment'>$msg[procs_autorisations]</label>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,1);'>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,0);'>
</div>
<div class='row'>
	!!autorisations_users!!
</div>";

// $admin_proc_view_remote : template form procédures stockées
$admin_proc_view_remote = "
<h3><span onclick='menuHide(this,event)'>>!!form_title!!</span></h3>
<!--    Contenu du form    -->
<div class='form-contenu'>
	<div class='row'>
	!!additional_information!!
	</div>
	<div class=colonne2>
		<div class='row'>
		<label class='etiquette' for='form_name'>$msg[remote_procedures_procedure_name]</label>
		</div>
		<div class='row'>
		<input type='text' readonly name='f_proc_name' value='!!name!!' maxlength='255' class='saisie-50em' />
		</div>
	</div>
	<div class='row'>
		<label class='etiquette' for='form_code'>$msg[remote_procedures_procedure_sql]</label>
		</div>
	<div class='row'>
		<textarea cols='80' readonly rows='8' name='f_proc_code'>!!code!!</textarea>
		</div>
	<div class='row'>
		<label class='etiquette' for='form_comment'>$msg[remote_procedures_procedure_comment]</label>
		</div>
	<div class='row'>
		<input type='text' readonly name='f_proc_comment' value='!!comment!!' maxlength='255' class='saisie-50em' />
	</div>
	<div class='row'>
		!!parameters_title!!
	</div>
	<div class='row'>
		!!parameters_content!!
	</div>
</div>
<!-- Boutons -->
<div class='row'>
	<div class='left'>
		<input type='button' class='bouton' value='".$msg["remote_procedures_back"]."' onClick='document.location=\"./admin.php?categ=proc&sub=proc\"' />&nbsp;
		<input class='bouton' type='button' value=\"".$msg["remote_procedures_import"]."\" onClick=\"document.location='./admin.php?categ=proc&sub=proc&action=import_remote&id=!!id!!'\" />
		</div>
</div>
<div class='row'></div>
<script type='text/javascript'>document.forms['maj_proc'].elements['f_proc_name'].focus();</script>";

// $admin_zbib_content_form : template form zbib
$admin_zbib_content_form = "
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_Nom] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_nom value='!!nom!!' size=50 />
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_Utilisation] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_search_type value='!!search_type!!' size=50/>
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_Base] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_base value='!!base!!' size=50 />
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_URL] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_url value='!!url!!' size=50>
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_NumPort] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_port value='!!port!!' size='10' />
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_Format] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_format value='!!format!!' size='50' />
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[z3950_sutrs] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_sutrs value='!!sutrs!!' size=50>
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_user] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_user value='!!user!!' size='50' />
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[admin_password] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_password value='!!password!!' size=50>
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne4 align_right'>
		<label class='etiquette'>$msg[zbib_zfunc] &nbsp;</label>
	</div>
	<div class='colonne_suite'>
		<input type=text name=form_zfunc value='!!zfunc!!' size=50>
	</div>
</div>";

// $admin_zattr_form : template form attributs zbib - changed by martizva
$admin_zattr_form = "
<form class='form-$current_module' name=zattrform method=post action=\"./admin.php?categ=z3950&sub=zattr&action=update&bib_id=!!bib_id!!\">
<h3><span onclick='menuHide(this,event)'>!!form_title!!</span></h3>
<div class='form-contenu'>
!!code!!

	<div class='row'>&nbsp;</div>
	<div class='row'>
		<div class='colonne4 align_right'>
				<label class='etiquette'>$msg[admin_Attributs] &nbsp;</label>
				</div>
		<div class='colonne_suite'>
				<input type=text name=form_attr_attr value='!!attr_attr!!' size=25>
				<input type=hidden name=form_attr_bib_id value='!!attr_bib_id!!'>
				</div>
		</div>
	<div class='row'> </div>


</div>
	<div class='row'>
		<div class='left'>
			<input class='bouton' type='button' value=' $msg[76] ' onClick=\"document.location='./admin.php?categ=z3950&sub=zattr&bib_id=!!attr_bib_id!!'\" />&nbsp;
			<input class='bouton' type='submit' value=' $msg[77] ' onClick=\"return test_form(this.form)\" />&nbsp;
			</div>
		<div class='right'>
			<input class='bouton' type='button' value=' $msg[supprimer] ' onClick=\"javascript:confirmation_delete('bib_id=!!attr_bib_id!!&attr_libelle=!!attr_libelle!!','!!local_attr_libelle!!')\" />
		</div>
	</div>
<div class='row'></div>
</form><script type='text/javascript'>document.forms['zattrform'].elements['form_attr_libelle'].focus();</script>
";

// $admin_convert_end form - FIX MaxMan
$admin_convert_end = "
<br /><br />
<form class='form-$current_module' action=\"folow_import.php\" method=\"post\" name=\"destfic\">
<h3><span onclick='menuHide(this,event)'>".$msg["admin_conversion_end11"]."</span></h3>
<div class='form-contenu'>
	<div class='row'>";

if (($output=="yes")&&(!$noimport)) {
	$admin_convert_end .= "
		<input id=\"admin_conversion_end5\" type=\"radio\" name=\"deliver\" value=\"1\" checked><label for=\"admin_conversion_end5\">&nbsp;".$msg["admin_conversion_end5"]."</label><br />
		<input id=\"admin_conversion_end6\" type=\"radio\" name=\"deliver\" value=\"2\" checked><label for=\"admin_conversion_end6\">&nbsp;".$msg["admin_conversion_end6"]."</label><br />";
}
$admin_convert_end .= "
		<input id=\"admin_conversion_end7\" type=\"radio\" name=\"deliver\" value=\"3\" checked><label for=\"admin_conversion_end7\">&nbsp;".$msg["admin_conversion_end7"]."</label><br />
		<input type=\"hidden\" name=\"file_in\" value=\"$file_in\">
		<input type=\"hidden\" name=\"suffix\" value=\"$suffix\">
		<input type=\"hidden\" name=\"mimetype\" value=\"$mimetype\">
	</div>
	";
if (($output=="yes")&&(!$noimport)) {
	$admin_convert_end .= "<!--select_func_import-->";
}
$admin_convert_end .= "</div><div class='row'>
	<input type=\"submit\" class='bouton' value=\"".$msg["admin_conversion_end8"]."\"/>
</div>
</form>
<br />
<div class='row'>
	<span class='center'><b>".$msg["admin_conversion_end9"]."</b></span>
</div>
<div class='row'>";
if(!isset($n_errors)) $n_errors = 0;
if ($n_errors==0) {
	$admin_convert_end .= "<span class='center'><b>".$msg["admin_conversion_end10"]."</b></span>";
} else {
	$admin_convert_end .= "  $errors_msg  ";
}
$admin_convert_end .= "</div>";

// $admin_calendrier_form : template form calendrier des jours d'ouverture
$admin_calendrier_form = "
<form class='form-$current_module' id='calendrier' name='calendrier' method='post' action='./admin.php?categ=calendrier'>
<h3><span onclick='menuHide(this,event)'>$msg[calendrier_titre_form]";
$admin_calendrier_form .= " - !!biblio_name!!<br />!!localisation!!";
$admin_calendrier_form .= "</span></h3>
<div class='form-contenu'>
	<div class='row'>
		<label class='etiquette' for='date_deb'>$msg[calendrier_date_debut] :</label>";
$admin_calendrier_form .= get_input_date("date_deb", "date_deb");
$admin_calendrier_form .= "&nbsp;
		<label class='etiquette' for='date_fin'>$msg[calendrier_date_fin] :</label>";
$admin_calendrier_form .= get_input_date("date_fin", "date_fin");
$admin_calendrier_form .= "</div>
	<div class='row'>
		<label class='etiquette' >$msg[calendrier_jours_concernes] :</label>
		<label class='etiquette' for='j2'>$msg[1018]</label><input id='j2' type='checkbox' name='j2' value=1 />&nbsp;
		<label class='etiquette' for='j3'>$msg[1019]</label><input id='j3' type='checkbox' name='j3' value=1 />&nbsp;
		<label class='etiquette' for='j4'>$msg[1020]</label><input id='j4' type='checkbox' name='j4' value=1 />&nbsp;
		<label class='etiquette' for='j5'>$msg[1021]</label><input id='j5' type='checkbox' name='j5' value=1 />&nbsp;
		<label class='etiquette' for='j6'>$msg[1022]</label><input id='j6' type='checkbox' name='j6' value=1 />&nbsp;
		<label class='etiquette' for='j7'>$msg[1023]</label><input id='j7' type='checkbox' name='j7' value=1 />&nbsp;
		<label class='etiquette' for='j1'>$msg[1024]</label><input id='j1' type='checkbox' name='j1' value=1 />&nbsp;
        <input type='button' class='bouton_small align_middle' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(\"j1|j2|j3|j4|j5|j6|j7\",1);'>
		<input type='button' class='bouton_small align_middle' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(\"j1|j2|j3|j4|j5|j6|j7\",0);'>
		</div>
	<div class='row'>
		<label class='etiquette' for='commentaire'>$msg[calendrier_commentaire] :</label>
		<input class='saisie-30em' id='commentaire' type='text' name='commentaire' />
		</div>
	<div class='row'>
		<label class='etiquette' for='duplicate'>$msg[calendrier_duplicate] :</label>
		!!duplicate_location!!
		</div>
	</div>
<div class='row'>
	<input type='hidden' name='loc' value='!!book_location_id!!'  />
	<input class='bouton' type='submit' value=' $msg[calendrier_ouvrir] ' onClick=\"this.form.faire.value='ouvrir'\" />&nbsp;
	<input class='bouton' type='submit' value=' $msg[calendrier_fermer] ' onClick=\"this.form.faire.value='fermer'\" />&nbsp;
	<input class='bouton' type='submit' value=' $msg[calendrier_initialization] ' onClick=\"this.form.faire.value='initialization'\" />&nbsp;
	<input type='hidden' name='faire' value='' />
	</div>
</form>
";

// $admin_calendrier_form : template form calendrier pour un mois pour les commentaires par jour
$admin_calendrier_form_mois_start = "
<form class='form-$current_module' id='calendrier' name='calendrier' method='post' action='./admin.php?categ=calendrier'>
<h3><span onclick='menuHide(this,event)'>$msg[calendrier_titre_form_commentaire]</span></h3>
<div class='form-contenu'>";

$admin_calendrier_form_mois_commentaire = " <input class='saisie-5em' id='commentaire' type='text' name='!!name!!' value='!!commentaire!!' />" ;
$admin_calendrier_form_mois_commentaire = " <textarea name='!!name!!' class='saisie-5em' rows='4' wrap='virtual'>!!commentaire!!</textarea>";
		
$admin_calendrier_form_mois_end = "	</div>
<div class='row'>
	<input class='bouton' type='button' value='$msg[76]' onClick=\"document.location='./admin.php?categ=calendrier'\">&nbsp;
	<input class='bouton' type='submit' value='$msg[77]' onClick=\"this.form.faire.value='commentaire'\">
	<input type='hidden' name='faire' value='' />
	<input type='hidden' name='loc' value='!!book_location_id!!'  />
	<input type='hidden' name='annee_mois' value='!!annee_mois!!' />
	</div>
</form>
";

// $admin_notice_statut_content_form : template form statuts de notices
$admin_notice_statut_content_form = "
<div class='row'>
	<label class='etiquette' ><strong>$msg[noti_statut_gestion]</strong></label>
</div>
<div class='row'>
	<label class='etiquette' for='form_libelle'>$msg[noti_statut_libelle]</label>
</div>
<div class='row'>
	<input type=text name='form_gestion_libelle' value='!!gestion_libelle!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='form_visible_gestion'>$msg[noti_statut_visu_gestion]</label>
	<input type=checkbox name=form_visible_gestion value='1' !!checkbox_visible_gestion!! class='checkbox' />&nbsp;
</div>
<div class='row'>
	<div class='colonne5'>
		<label class='etiquette' for='form_class_html'>$msg[noti_statut_class_html]</label>
	</div>
	<div class='colonne_suite'>
		!!class_html!!
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<label class='etiquette' ><strong>$msg[noti_statut_opac]</strong></label>
</div>
<div class='row'>
	<label class='etiquette' for='form_libelle'>$msg[noti_statut_libelle]</label>
</div>
<div class='row'>
	<input type=text name='form_opac_libelle' value='!!opac_libelle!!' class='saisie-50em' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette'>$msg[notice_statut_visibilite_generale]</label>
</div>
<div class='colonne_suite'>
	<label class='etiquette'>$msg[notice_statut_visibilite_restrict]</label>
</div>
<div class='colonne2'>
	<label class='etiquette' for='form_visible_opac'>$msg[noti_statut_visu_opac_form]</label>
	<input type=checkbox name=form_visible_opac value='1' !!checkbox_visible_opac!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_visu_abon'>$msg[noti_statut_visible_opac_abon]</label>
	<input type=checkbox name=form_visu_abon value='1' !!checkbox_visu_abon!! class='checkbox' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette' for='form_expl_visu_expl'>$msg[noti_statut_visu_expl]</label>
	<input type=checkbox name=form_visu_expl value='1' !!checkbox_visu_expl!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_expl_visu_abon'>$msg[noti_statut_expl_visible_opac_abon]</label>
	<input type=checkbox name=form_expl_visu_abon value='1' !!checkbox_expl_visu_abon!! class='checkbox' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette' for='form_explnum_visu_expl'>$msg[noti_statut_visu_explnum]</label>
	<input type=checkbox name=form_explnum_visu value='1' !!checkbox_explnum_visu!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_expl_visu_abon'>$msg[noti_statut_explnum_visible_opac_abon]</label>
	<input type=checkbox name=form_explnum_visu_abon value='1' !!checkbox_explnum_visu_abon!! class='checkbox' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette' for='form_scan_request_opac'>".$msg['noti_statut_scan_request_opac']."</label>
	<input type='checkbox' name='form_scan_request_opac' value='1' !!checkbox_scan_request_opac!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_scan_request_opac_abon'>".$msg['noti_statut_scan_request_opac_abon']."</label>
	<input type='checkbox' name='form_scan_request_opac_abon' value='1' !!checkbox_scan_request_opac_abon!! class='checkbox' />
</div>
<div class='row'></div>
";

// $admin_notice_statut_content_form : template form statuts des etats de collections
$admin_collstate_statut_content_form = "
<div class='row'>
	<label class='etiquette' ><strong>".$msg["collstate_statut_gestion"]."</strong></label>
</div>
<div class='row'>
	<label class='etiquette' for='form_gestion_libelle'>".$msg["collstate_statut_libelle"]."</label>
</div>
<div class='row'>
	<input type=text name='form_gestion_libelle' value='!!gestion_libelle!!' class='saisie-50em' />
</div>
<div class='row'>
	<div class='colonne5'>
		<label class='etiquette' for='form_class_html'>".$msg["collstate_statut_class_html"]."</label>
	</div>
	<div class='colonne_suite'>
		!!class_html!!
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<label class='etiquette' ><strong>".$msg["collstate_statut_opac"]."</strong></label>
</div>
<div class='row'>
	<label class='etiquette' for='form_opac_libelle'>".$msg["collstate_statut_libelle"]."</label>
</div>
<div class='row'>
	<input type=text name='form_opac_libelle' value='!!opac_libelle!!' class='saisie-50em' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette'>".$msg["collstate_statut_visibilite_generale"]."</label>
</div>
<div class='colonne_suite'>
	<label class='etiquette'>".$msg["collstate_statut_visibilite_restrict"]."</label>
</div>
<div class='colonne2'>
	<label class='etiquette' for='form_visible_opac'>".$msg["collstate_statut_visu_opac_form"]."</label>
	<input type=checkbox name=form_visible_opac value='1' !!checkbox_visible_opac!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_visu_abon'>".$msg["collstate_statut_visible_opac_abon"]."</label>
	<input type=checkbox name=form_visu_abon value='1' !!checkbox_visu_abon!! class='checkbox' />
</div>
<div class='row'></div>
";

$admin_abonnements_periodicite_content_form = "
<div class='row'>
	<label class='etiquette' for='libelle'>$msg[abonnements_periodicite_libelle]</label>
</div>
<div class='row'>
	<input type=text name='libelle' value='!!libelle!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='duree'>$msg[abonnements_periodicite_duree]</label>
</div>
<div class='row'>
	<input type=text name='duree' value='!!duree!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='unite'>$msg[abonnements_periodicite_unite]</label>
</div>
<div class='row'>
	!!unite!!
</div>
<div class='row'>
	<label class='etiquette' for='seuil_periodicite'>$msg[seuil_periodicite]</label>
</div>
<div class='row'>
	<input type=text name='seuil_periodicite' value='!!seuil_periodicite!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='retard_periodicite'>$msg[retard_periodicite]</label>
</div>
<div class='row'>
	<input type=text name='retard_periodicite' value='!!retard_periodicite!!' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='consultation_duration'>".$msg["serialcirc_consultation_duration"]."</label>
</div>
<div class='row'>
	<input type=text name='consultation_duration' value='!!consultation_duration!!' class='saisie-50em' />
</div>
";

// $admin_procs_clas_content_form : template form classements de procédures
$admin_procs_clas_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libproc_classement'>$msg[proc_clas_lib]</label>
</div>
<div class='row'>
	<input type=text name=form_libproc_classement value='!!libelle!!' class='saisie-50em' />
</div>
";

// $admin_infopages_content_form : template form des pages d'info
$admin_infopages_content_form = "
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='form_title_infopage'>".$msg['infopage_title_infopage']."</label>
		</div>
		<div class='row'>
			<input type=text name='form_title_infopage' value=\"!!title_infopage!!\" class='saisie-50em' />
		</div>
	</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='form_valid_infopage'>".$msg['infopage_valid_infopage']."</label>
			<input type=checkbox name='form_valid_infopage' value='1' !!checkbox!! class='checkbox' /><br />
			<label class='etiquette' for='form_restrict_infopage'>".$msg['infopage_restrict_infopage']."</label>
			<input type=checkbox name='form_restrict_infopage' value='1' !!restrict_checkbox!! class='checkbox' />
		</div>
	</div>
	<div class='row'>
		<label class='etiquette' for='form_content_infopage'>".$msg['infopages_content_infopage']."</label>
	</div>
	<div class='row'>
		<textarea id='form_content_infopage' name='form_content_infopage' cols='120' rows='40'>!!content_infopage!!</textarea>
	</div>
</div>
<div class='row'>
	<label class='etiquette' for='form_content_infopage'>".$msg['infopages_classement_list']."</label>
</div>
<div class='row'>
	<select data-dojo-type='dijit/form/ComboBox' id='classementGen_!!object_type!!' name='classementGen_!!object_type!!'>
		!!classements_liste!!
	</select>
</div>";


// $admin_group_content_form : template groupe
$admin_group_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>".$msg['admin_usr_grp_lib']."</label>
</div>
<div class='row'>
	<input type=text id='form_libelle' name='form_libelle' value='!!libelle!!' class='saisie-50em' />
</div>
";

$admin_liste_jscript = "
	<script type='text/javascript' src='./javascript/ajax.js'></script>
	<script type='text/javascript'>
		function showListItems(obj) {
		

			kill_frame_items();

			var pos=findPos(obj);
			var what = 	obj.getAttribute('what');
			var item = 		obj.getAttribute('item');
			var total = 		obj.getAttribute('total');
		
			var url='./admin/docs/frame_liste_items.php?what='+what+'&item='+item+'&total='+total;
			var list_view=document.createElement('iframe');
			list_view.setAttribute('id','frame_list_items');
			list_view.setAttribute('name','list_items');
			list_view.src=url;
		
			var att=document.getElementById('att');
			list_view.style.visibility='hidden';
			list_view.style.display='block';
			list_view=att.appendChild(list_view);

			list_view.style.left=(pos[0])+'px';
			list_view.style.top=(pos[1])+'px';

			list_view.style.visibility='visible';
		}

		function kill_frame_items() {
			var list_view=document.getElementById('frame_list_items');
			if (list_view)
				list_view.parentNode.removeChild(list_view);
		}
		</script>
";

// $admin_docnum_statut_content_form : template form statuts des documents numériques
$admin_docnum_statut_content_form = "
<div class='row'>
	<label class='etiquette' ><strong>".$msg["docnum_statut_gestion"]."</strong></label>
</div>
<div class='row'>
	<label class='etiquette' for='form_libelle'>".$msg["docnum_statut_libelle"]."</label>
</div>
<div class='row'>
	<input type=text id='form_gestion_libelle' name='form_gestion_libelle' value='!!gestion_libelle!!' class='saisie-50em' />
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne5'>
		<label class='etiquette' for='form_class_html'>".$msg["docnum_statut_class_html"]."</label>
	</div>
	<div class='colonne_suite'>
		!!class_html!!
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<label class='etiquette' ><strong>".$msg["docnum_statut_opac"]."</strong></label>
</div>
<div class='row'>
	<label class='etiquette' for='form_libelle'>".$msg["docnum_statut_libelle"]."</label>
</div>
<div class='row'>
	<input type=text id='form_opac_libelle' name='form_opac_libelle' value='!!opac_libelle!!' class='saisie-50em' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette'>".$msg["docnum_statut_visibilite_generale"]."</label>
</div>
<div class='colonne_suite'>
	<label class='etiquette'>".$msg["docnum_statut_visibilite_restrict"]."</label>
</div>
<div class='colonne2'>
	<label class='etiquette' for='form_visible_opac'>".$msg["docnum_statut_visu_opac_form"]."</label>
	<input type=checkbox name=form_visible_opac value='1' !!checkbox_visible_opac!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_visible_opac_abon'>".$msg["docnum_statut_visu_opac_abon"]."</label>
	<input type=checkbox name=form_visible_opac_abon value='1' !!checkbox_visible_opac_abon!! class='checkbox' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette' for='form_consult_opac'>".$msg["docnum_statut_cons_opac_form"]."</label>
	<input type=checkbox name=form_consult_opac value='1' !!checkbox_consult_opac!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_consult_opac_abon'>".$msg["docnum_statut_cons_opac_abon"]."</label>
	<input type=checkbox name=form_consult_opac_abon value='1' !!checkbox_consult_opac_abon!! class='checkbox' />
</div>
<div class='row'>&nbsp;</div>
<div class='colonne2'>
	<label class='etiquette' for='form_visible_opac'>".$msg["docnum_statut_down_opac_form"]."</label>
	<input type=checkbox name=form_download_opac value='1' !!checkbox_download_opac!! class='checkbox' />
</div>
<div class='colonne_suite'>
	<label class='etiquette' for='form_download_opac_abon'>".$msg["docnum_statut_down_opac_abon"]."</label>
	<input type=checkbox name=form_download_opac_abon value='1' !!checkbox_download_opac_abon!! class='checkbox' />
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<label class='etiquette' for='form_thumbnail_visible_opac_override'>".$msg["docnum_statut_thumbnail_visible_opac_override"]."</label>
	<input type=checkbox name='form_thumbnail_visible_opac_override' value='1' !!checkbox_thumbnail_visible_opac_override!! class='checkbox' />
</div>
<div class='row'>&nbsp;</div>
";

$admin_authorities_statut_content_form = "
<div class='row'>
	<label class='etiquette' for='form_libelle'>".$msg["docnum_statut_libelle"]."</label>
</div>
<div class='row'>
	<input type=text name='form_gestion_libelle' value='!!gestion_libelle!!' class='saisie-50em' />
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne5'>
		<label class='etiquette' for='form_class_html'>".$msg["docnum_statut_class_html"]."</label>
	</div>
	<div class='colonne_suite'>
		!!class_html!!
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne5'>
		<label class='etiquette' for='form_used_for'>".$msg["authorities_used_for"]."</label>
	</div>
	<div class='colonne_suite'>
		!!list_authorities!!
	</div>
</div>
<div class='row'>&nbsp;</div>
";

