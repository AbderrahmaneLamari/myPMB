<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: make_sugg.inc.php,v 1.25 2021/09/28 08:57:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $msg, $charset, $id_sug, $empr_mail;
global $opac_show_help, $opac_suggestion_search_notice_doublon;
global $opac_sugg_categ, $opac_sugg_categ_default, $opac_sugg_localises, $acquisition_sugg_categ;

require_once($base_path.'/classes/suggestions_categ.class.php');
require_once($base_path.'/classes/docs_location.class.php');
require_once($base_path.'/classes/suggestions.class.php');

$tooltip = str_replace("\\n","<br />",$msg["empr_sugg_ko"]);
$sug_form= "<div id='make_sugg'>
<h3><span>".htmlentities($msg['empr_make_sugg'], ENT_QUOTES, $charset)."</span></h3>";
if($opac_show_help) $sug_form .= "
<div class='row'>
$tooltip</div>
";
if(!isset($id_sug)) $id_sug = 0;
$sugg = new suggestions($id_sug);

if($opac_suggestion_search_notice_doublon){
	$suggestion_search_notice_doublon_script = " onkeyup=\"input_field_change();\" ";
} else {
	$suggestion_search_notice_doublon_script = "";
}

$sug_form.= "
<script >
	var my_timeout;
	function confirm_suppr() {
		phrase = \"".$msg['empr_confirm_suppr_sugg']."\";
		result = confirm(phrase);
		if(result)
			return true;		
		return false;
	}
	
	function input_field_change() {	
		if (my_timeout) clearTimeout(my_timeout);
		my_timeout = setTimeout('get_records_found();', 1000);
	}

	function get_records_found() {
		var tit = document.getElementById('tit').value;
		var code = document.getElementById('code').value;
		
		if((tit.length < 3) && (code.length < 3)) {
			records_found('');
			return;
		}
		var xhr_object = new http_request();
		xhr_object.request('./ajax.php?module=ajax&categ=sugg&sub=get_doublons&tit=' + tit + '&code=' + code, 0, '', 1, records_found);
	}
	
	function records_found(response) {
		dojo.forEach(dijit.findWidgets(dojo.byId('records_found')), function(w) {
			w.destroyRecursive(true);
		});
		
		document.getElementById('records_found').innerHTML = response;
		
		if(typeof(dojo) == 'object'){
	  		dojo.parser.parse(document.getElementById('records_found'));
	  	}	
	}	
</script>

<div id='make_sugg-container'>
<form action=\"empr.php\" method=\"post\" name=\"empr_sugg\" enctype='multipart/form-data'>
	<input type='hidden' name='id_sug' id='id_sug' value='$sugg->id_suggestion' />
	<table style='width:60%' cellpadding='5'>
		<tr>	
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_tit"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type=\"hidden\" name=\"lvl\" />
				<input type=\"text\" id=\"tit\" name=\"tit\" size=\"50\" border=\"0\" value=\"".htmlentities($sugg->titre, ENT_QUOTES, $charset)."\"
				" . $suggestion_search_notice_doublon_script . " />
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_aut"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type=\"text\" id=\"aut\" name=\"aut\" size=\"50\" border=\"0\" value=\"".htmlentities($sugg->auteur, ENT_QUOTES, $charset)."\"/>
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_edi"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type=\"text\" id=\"edi\" name=\"edi\" size=\"50\" border=\"0\" value=\"".htmlentities($sugg->editeur, ENT_QUOTES, $charset)."\"/>
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_code"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type=\"text\" id=\"code\" name=\"code\" size=\"20\" border=\"0\" value=\"".htmlentities($sugg->code, ENT_QUOTES, $charset)."\"
				" . $suggestion_search_notice_doublon_script . " />		
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_prix"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type=\"text\" id=\"prix\" name=\"prix\" size=\"20\" border=\"0\" value=\"".htmlentities($sugg->prix, ENT_QUOTES, $charset)."\"/>
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_url"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type='text' id=\"url_sug\" name=\"url_sug\" size=\"50\" border=\"0\" value=\"".htmlentities($sugg->url_suggestion, ENT_QUOTES, $charset)."\"/>
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right' vertical-align=top>".htmlentities($msg["empr_sugg_comment"], ENT_QUOTES, $charset)."</td>
			<td>
				<textarea id=\"comment\" name=\"comment\" cols=\"50\" rows='4'>".htmlentities($sugg->commentaires, ENT_QUOTES, $charset)."</textarea>
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_datepubli"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type='text' id='date_publi' name='date_publi' value='".($sugg->date_publi != '0000-00-00' ? $sugg->date_publi : '')."' size='50' placeholder='".htmlentities($msg['format_date_input_text_placeholder'], ENT_QUOTES, $charset)."'>
				<input type='button' class='bouton' id='date_publi_sug' name='date_publi_sug' value='...' onClick=\"window.open('./select.php?what=calendrier&caller=empr_sugg&param1=date_publi&param2=date_publi&auto_submit=NO&date_anterieure=YES', 'date_publi', 'toolbar=no, dependent=yes, width=250,height=250, resizable=yes')\"/>
			</td>
		</tr>
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_qte"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type='text' id='nb' name='nb' size='5' value='".htmlentities($sugg->nb, ENT_QUOTES, $charset)."'>
			</td>
		</tr>	";
if(!$_SESSION["id_empr_session"]) {
	
	$sug_form.= "
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_mail"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type=\"text\" id=\"mail\" name=\"mail\" size=\"50\" border=\"0\" value=\"".$empr_mail."\"/>
			</td>
		</tr>";
}
if ($opac_sugg_categ == '1' ) {
	
	if($id_sug){
		$default_categ = $sugg->num_categ;
	} else {
		if (suggestions_categ::exists($opac_sugg_categ_default) ){
			$default_categ = $opac_sugg_categ_default;
		} else {
			$default_categ = '1';
		}
	}
	//Selecteur de categories
	if ($acquisition_sugg_categ != '1') {
		$sel_categ="";
	} else {
		$tab_categ = suggestions_categ::getCategList();
		$sel_categ = "<select class='saisie-25em' id='num_categ' name='num_categ' >";
		foreach($tab_categ as $id_categ=>$lib_categ){
			$sel_categ.= "<option value='".$id_categ."' ";
			if ($id_categ==$default_categ) $sel_categ.= "selected='selected' "; 
			$sel_categ.= "> ";
			$sel_categ.= htmlentities($lib_categ, ENT_QUOTES, $charset)."</option>";
		}
		$sel_categ.= "</select>";
	}
	$sug_form.= "
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg['acquisition_categ'], ENT_QUOTES, $charset)."</td>
			<td>$sel_categ</td>
		</tr>";
	
}

// Localisation de la suggestion
if($_SESSION["id_empr_session"]) {		
	$requete = "SELECT * FROM empr WHERE id_empr=".$_SESSION["id_empr_session"];	
	$res = pmb_mysql_query($requete);
	if($res) {
		$empr = pmb_mysql_fetch_object($res);	
		if (!$empr->empr_location) $empr->empr_location=0 ;	
		$list_locs='';
		$locs=new docs_location();
		$list_locs=$locs->gen_combo_box_sugg($empr->empr_location,1,"");
		if ($opac_sugg_localises==1) {			
			$sug_form.= "
			<tr>
				<td class='cell_header align_right'>".htmlentities($msg['acquisition_location'], ENT_QUOTES, $charset)."</td>
				<td>$list_locs</td>
			</tr>";
		} elseif ($opac_sugg_localises==2) {
			$sug_form.= "<input type=\"hidden\" name=\"sugg_location_id\" value=\"".$empr->empr_location."\"/>";			
		}
	}
}

//Affichage du selecteur de source
$req = "select * from suggestions_source order by libelle_source";
$res= pmb_mysql_query($req);
$option = "<option value='0' selected>".htmlentities($msg['empr_sugg_no_src'],ENT_QUOTES,$charset)."</option>";

while(($src=pmb_mysql_fetch_object($res))){
	if($id_sug){
		$selected = ($sugg->sugg_src == $src->id_source ? 'selected' : '');
	} else {
		$selected = "";
	}
	$option .= "<option value='".$src->id_source."' $selected >".htmlentities($src->libelle_source,ENT_QUOTES,$charset)."</option>";
}
$selecteur = "<select id='sug_src' name='sug_src'>".$option."</select>";
$sug_form .="<tr>
			<td class='cell_header align_right'>".htmlentities($msg['empr_sugg_src'], ENT_QUOTES, $charset)."</td>
			<td>$selecteur</td>
		</tr>"
;

if($sugg){
	
	if($sugg->get_explnum('nom')){
		$file_field = "<label>".htmlentities($sugg->get_explnum('nom'), ENT_QUOTES, $charset)."</label>";
	} else $file_field = "<input type=\"file\" id=\"piece_jointe_sug\" name=\"piece_jointe_sug\" size=\"40\" border=\"0\"/>";
	
	$sug_form.= "
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_piece_jointe"], ENT_QUOTES, $charset)."</td>
			<td>
				$file_field
			</td>
		</tr>";
		
	$btn_del = "<input type='button' class='bouton' name='ok' value='&nbsp;".htmlentities($msg['empr_suppr_sugg'], ENT_QUOTES, $charset)."&nbsp;' onClick=\"if(confirm_suppr()) {this.form.lvl.value='suppr_sugg'; this.form.submit();}\"/>";
} else {
	$sug_form.= "
		<tr>
			<td class='cell_header align_right'>".htmlentities($msg["empr_sugg_piece_jointe"], ENT_QUOTES, $charset)."</td>
			<td>
				<input type=\"file\" id=\"piece_jointe_sug\" name=\"piece_jointe_sug\" size=\"40\" border=\"0\"/>
			</td>
		</tr>";
	$btn_del = "";
}
$sug_form.= "
		<tr>
			<td colspan=2>
				<div id='records_found'></div>
			</td>
		</tr>
		<tr>
			<td colspan=2 class='align_right'>
				<input type='button' class='bouton' name='ok' value='&nbsp;".addslashes($msg['empr_bt_valid_sugg'])."&nbsp;' onClick='this.form.lvl.value=\"valid_sugg\";this.form.submit()'/>
				$btn_del
			</td>
		</tr>
	</table>
</form>
</div></div>
";

print $sug_form;