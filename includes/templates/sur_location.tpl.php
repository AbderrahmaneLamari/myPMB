<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sur_location.tpl.php,v 1.10 2022/10/10 11:47:02 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $sur_location_map_tpl, $charset, $tpl_sur_location_content_form, $tpl_docs_loc_table_line, $pmb_map_activate;

//    ----------------------------------------------------
//    Onglet map
//    ----------------------------------------------------
$sur_location_map_tpl = "";
if ($pmb_map_activate)
	$sur_location_map_tpl = "
<!-- onglet 14 -->
<div id='el14Parent' class='parent'>
	<h3>
    	<img src='".get_url_icon('plus.gif')."' class='img_plus' name='imEx' id='el14Img' onClick=\"expandBase('el14', true); return false;\" title='".$msg["notice_map_onglet_title"]."' border='0' /> ".$msg["notice_map_onglet_title"]."
	</h3>
</div>

<div id='el14Child' class='child' etirable='yes' title='".htmlentities($msg['notice_map_onglet_title'],ENT_QUOTES, $charset)."'>
	<div id='el14Child_0' title='".htmlentities($msg['notice_map'],ENT_QUOTES, $charset)."' movable='yes'>		
		<div id='el14Child_0b' class='row'>
			!!sur_location_map!!
		</div>
	</div>
</div>";


$tpl_sur_location_content_form = "
<script type='text/javascript' src='./javascript/tablist.js'></script>
<div class='row'>
	<label class='etiquette' for='form_cb'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text name='form_libelle' value=\"!!libelle!!\" class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' >$msg[docs_sur_location_pic]</label>
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
		<label class='etiquette' >$msg[sur_location_infopage_assoc]</label>
		!!loc_infopage!!
	</div>
</div>
<div class='row'>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<table class='sortable'>
		<tr>
			<th>".$msg["sur_location_loc_sel"]."</th>
			<th>".$msg[103]."</th>
			<th>".$msg['opac_object_visible_short']."</th>
			<th>".$msg['sur_location_comment']."</th>
		</tr>
		!!docs_loc_lines!!
	</table>
</div>
<div class='row'></div>
".$sur_location_map_tpl."
<div class='row'></div>
<div id='sur_location_detail' class='notice-parent'>
	<img src='".get_url_icon('plus.gif')."' class='img_plus' name='imEx' id='sur_location_detailImg' title='".addslashes($msg['plus_detail'])."' onclick=\"expandBase('sur_location_detail', true); return false;\" hspace='3' border='0'>
	<span class='notice-heada'>
		".$msg['sur_location_coordonnee']."
	</span>
</div>
<div id='sur_location_detailChild' class='notice-child' style='margin-bottom: 6px; display: none; width: 94%;'>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_name']."</label></div><div class='row'><input type='text' name='form_locdoc_name', ' value='!!loc_name!!' class='saisie-50em' /></div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_adr1']."</label></div><div class='row'><input type='text' name='form_locdoc_adr1', ' value='!!loc_adr1!!' class='saisie-50em' /></div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_adr2']."</label></div><div class='row'><input type='text' name='form_locdoc_adr2', ' value='!!loc_adr2!!' class='saisie-50em' /></div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_cp']." / ".$msg['sur_location_details_town']."</label></div>
	<div class='row'>
		<div class='colonne4'>
			<input type='text' name='form_locdoc_cp', ' value='!!loc_cp!!' maxlength='15' class='saisie-10em' />
		</div>
		<div class='colonne_suite'>
			<input type='text' name='form_locdoc_town', ' value='!!loc_town!!'' class='saisie-50em' />
		</div>
	</div>
	
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_state']." / ".$msg['sur_location_details_country']."</label></div>
	<div class='row'>
		<div class='colonne3'>
			<input type='text' name='form_locdoc_state',' value='!!loc_state!!' class='saisie-20em' />
		</div>
		<div class='colonne_suite'>
			<input type='text' name='form_locdoc_country' value='!!loc_country!!' class='saisie-20em' />
		</div>
	</div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_phone']."</label></div><div class='row'><input type='text' name='form_locdoc_phone' value='!!loc_phone!!' maxlength='100' class='saisie-20em' /></div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_email']."</label></div><div class='row'><input type='text' name='form_locdoc_email' value='!!loc_email!!' maxlength='255' class='saisie-50em' /></div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_website']."</label></div><div class='row'><input type='text' name='form_locdoc_website' value='!!loc_website!!' maxlength='100' class='saisie-50em' /></div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_details_logo']."</label></div><div class='row'><input type='text' name='form_locdoc_logo', ' value='!!loc_logo!!' maxlength='255' class='saisie-50em' /></div>
	<div class='row'><label class='etiquette'>".$msg['sur_location_comment']."</label></div><div class='row'><textarea class='saisie-50em' name='form_locdoc_commentaire' id='form_locdoc_commentaire' cols='55' rows='5'>!!loc_commentaire!!</textarea></div>
</div> <!--   Fin du + d�pliable    -->
";

$tpl_docs_loc_table_line = "
	<tr class='!!odd_even!!' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='!!odd_even!!'\"  style=\"!!style!!\">
		<td><input type=checkbox name='form_location_selected_!!docs_loc_id!!' value='1' !!checkbox!! class='checkbox' /></td>
		<td>!!docs_loc_libelle!!</td>
		<td>!!docs_loc_visible_opac!!</td>
		<td>!!docs_loc_comment!!</td>
	</tr>	
";