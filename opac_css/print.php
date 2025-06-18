<?php
// +--------------------------------------------------------------------------+
// | PMB est sous licence GPL, la réutilisation du code est cadrée            |
// +--------------------------------------------------------------------------+
// $Id: print.php,v 1.129.2.4 2021/11/03 10:27:26 dgoron Exp $

use Spipu\Html2Pdf\Html2Pdf;

$base_path=".";
$use_opac_url_base=1;
require_once($base_path."/includes/init.inc.php");

global $include_path, $msg, $charset, $lvl, $css;
global $opac_print_cart_header_footer, $opac_print_email, $opac_print_email_autocomplete, $opac_print_email_recipients, $opac_print_email_sender, $opac_print_email_sender_mandatory;
global $opac_print_template_default, $opac_print_expl_default, $opac_print_explnum;
global $number, $select_noti, $type;
global $id_liste, $id_etagere, $action, $output, $current_search;
global $emailexp, $emailobj, $emailcontent, $emaildest, $emaildest_id, $notice_tpl, $captcha_code, $empr_mail;
global $opac_notice_affichage_class, $opac_notices_format, $opac_notices_format_django_directory;
global $short, $header, $vignette, $ex;
global $icon_doc, $biblio_doc;
global $opac_biblio_name, $opac_biblio_email, $opac_parse_html, $opac_url_base, $opac_nb_aut_rec_per_page;

//fichiers nécessaires au bon fonctionnement de l'environnement
require_once($base_path."/includes/common_includes.inc.php");

// fonctions de gestion de formulaire
require_once('./includes/templates/common.tpl.php');
require_once('./includes/notice_categories.inc.php');

// classe de gestion des catégories
require_once($base_path.'/classes/categorie.class.php');
require_once($base_path.'/classes/notice.class.php');
require_once($base_path.'/classes/notice_display.class.php');

// classe indexation interne
require_once($base_path.'/classes/indexint.class.php');

// classe d'affichage des tags
require_once($base_path.'/classes/tags.class.php');

// pour l'affichage correct des notices
require_once($base_path."/includes/templates/notice.tpl.php");
require_once($base_path."/includes/navbar.inc.php");
require_once($base_path."/includes/explnum.inc.php");

require_once('./classes/notice_affichage.class.php');
require_once('./classes/notice_affichage_unimarc.class.php');
require_once('./classes/notice_affichage.ext.class.php');
require_once($base_path.'/classes/XMLlist.class.php');
require_once("./classes/notice_tpl_gen.class.php");
require_once("./classes/etagere_caddies.class.php");
require_once("./classes/docnum_merge.class.php");
require_once($include_path."/mail.inc.php") ;

require_once $base_path."/classes/html_helper.class.php";

require_once($base_path."/classes/emprunteur_display.class.php");

// si paramétrage authentification particulière et pour la re-authentification ntlm
if (file_exists($base_path.'/includes/ext_auth.inc.php')) require_once($base_path.'/includes/ext_auth.inc.php');

// SECURITE
$id_liste = intval($id_liste);
$id_etagere = intval($id_etagere);

//on log au début pour l'avoir même sur les impressions pdf ou non abouties
global $pmb_logs_activate;
global $mode;
if($pmb_logs_activate){
	global $log, $infos_notice, $infos_expl;

	if($_SESSION['user_code']) {
		$res=pmb_mysql_query($log->get_empr_query());
		if($res){
			$empr_carac = pmb_mysql_fetch_array($res);
			$log->add_log('empr',$empr_carac);
		}
	}
	$log->add_log('num_session',session_id());
	$log->add_log('expl',$infos_expl);
	$log->add_log('docs',$infos_notice);
	$log->save();
}

if (file_exists($include_path.'/print/print_options_subst.xml')){
	$xml_print = new XMLlist($include_path.'/print/print_options_subst.xml');
} else {
	$xml_print = new XMLlist($include_path.'/print/print_options.xml');
}
$xml_print->analyser();
$print_options = $xml_print->table;

if (($action=="print_$lvl")&&($output=="tt")) {
	header("Content-Type: application/word");
	header('Content-Disposition: attachement; filename="liste.doc"');
}

$header_print = '';
$footer_print = '';
if($opac_print_cart_header_footer) {
	$req = "select * from print_cart_tpl where id_print_cart_tpl='".$opac_print_cart_header_footer."'";
	$resultat = pmb_mysql_query($req);
	if (pmb_mysql_num_rows($resultat)) {
		$r = pmb_mysql_fetch_object($resultat);
		$header_print = $r->print_cart_tpl_header;
		$footer_print = $r->print_cart_tpl_footer;
	}
}
$output_final = '<!DOCTYPE html><html lang="'.get_iso_lang_code().'">
		<head>
			<meta charset="'.$charset.'" />
        	<meta name=viewport content="width=device-width, initial-scale=1">
			<title>'.$msg['print_title'].'</title>';
if ( $action != "print_$lvl" ) {
	$output_final.= HtmlHelper::getInstance()->getStyle($css);
}
$output_final.= '</head><body class="popup">';
if($action) {
	$output_final.= $header_print;
}

if ($output!='email') {
	$output_final.= "<script type='text/javascript' src='./includes/javascript/http_request.js'></script>
				<script type='text/javascript' >
					function setCheckboxes(the_form, the_objet, do_check) {
						 var elts = document.forms[the_form].elements[the_objet+'[]'] ;
						 var elts_cnt = (typeof(elts.length) != 'undefined') ? elts.length : 0;
						 if (elts_cnt) {
							for (var i = 0; i < elts_cnt; i++) {
						 		elts[i].checked = do_check;
						 	} 
						 } else {
						 	elts.checked = do_check;
						 } 
						 return true;
					} 
				</script>";
} else {
	if($opac_print_email_sender) {
		$emailexp = trim(stripslashes($emailexp));
		if($emailexp) {
			$output_final.= $msg['print_emailexp'].' '.$emailexp.'<br />';
		}
	}
	$emailcontent = trim(stripslashes($emailcontent));
	if($emailcontent) {
		$output_final.= $msg['print_emailcontent'].' '.$emailcontent.'<br />';
	}
}


if ($action!="print_$lvl") {
	$output_final.="<script type='text/javascript'>
						// Fonction a utilisier pour l'encodage des URLs en javascript
						function encode_URL(data){
							var docCharSet = document.characterSet ? document.characterSet : document.charset;
							if(docCharSet == \"UTF-8\"){
								return encodeURIComponent(data);
							}else{
								return escape(data);
							}
						}
					</script>";
	$output_final.="<div id='att'></div>";
	if($lvl) $output_final.="<h2 class='print_title'>".$msg["print_title_".$lvl]."</h2>";
	else $output_final .="<h2 class='print_title'>".$msg["print_title"]."</h2>";
	$output_final.= "<h3 class='print_options' >".$msg["print_options"]."</h3>";
	$output_final.= "<form onsubmit='return checkForSubmit()' name='print_options' id='print_options' action='print.php?lvl=" . urlencode($lvl) . "&action=print_" . urlencode($lvl) . ($mode ? "&mode=".$mode : '') . "' method='post'>";
	if($id_liste) $output_final .= "<input type='hidden' name='id_liste' value='$id_liste'>";
	if($id_etagere) $output_final .= "<input type='hidden' name='id_etagere' value='$id_etagere'>";
	if($current_search) $output_final .= "<input type='hidden' name='current_search' value='" . htmlentities($current_search,ENT_QUOTES,$charset) . "'/>";
	
	$script_selnoti = "<script type='text/javascript' src='./includes/javascript/misc.js'></script>";
 	if(!$id_liste && !$id_etagere && !$current_search){
		 $script_selnoti .= "
			 <script type='text/javascript'>
				function getSelectedNotice(){	
					 if(document.getElementById('selected').checked){
						var notices = opener.document.forms['cart_form'].elements;
						var hasSelected = false;
						var items='';
						for (var i = 0; i < notices.length; i++) { 
		 					if (notices[i].name=='notice[]') {
						 		if(notices[i].checked) {
						 			if(hasSelected) 
						 				items += ','+notices[i].value;
						 			else items += notices[i].value;
									hasSelected = true;	
								}
		 					}
						}
						if(!hasSelected) {
							alert('".$msg["list_lecture_no_ck"]."');
							return false;	
						} else {
							document.getElementById('select_noti').value = items;
							return true;
						}
					}
					return true;
				}
			";
	 } elseif(!$id_liste && !$id_etagere) {
	 	$script_selnoti .= "
			 <script type='text/javascript'>
	 			function getSelectedNotice(){
	 				if(document.getElementById('select_noti').value != '') {
	 					return true;
	 				}
	 				return false;
	 			}";
	 } elseif(!$id_etagere) {
	 	 $script_selnoti .= "
			 <script type='text/javascript'>
				function getSelectedNotice(){	
					 if(document.getElementById('selected').checked){
						var notices = opener.document.getElementsByName('notice[]');
						var hasSelected = false;
						var items='';
						for (var i = 0; i < notices.length; i++) {
	 	 					if (notices[i].name=='notice[]') { 
						 		if(notices[i].checked) {
						 			if(hasSelected) 
						 				items += ','+notices[i].value;
						 			else items += notices[i].value;
									hasSelected = true;	
								}
	 	 					}
						}
						if(!hasSelected) {
							alert('".$msg["list_lecture_no_ck"]."');
							return false;	
						} else {
							document.getElementById('select_noti').value = items;
							return true;
						}
					}
					return true;
				}
			";
	 } else {
	 	$script_selnoti .= "
			 <script type='text/javascript'>
	 			function getSelectedNotice(){
	 				return true;
	 			}";
	 }
	$script_selnoti .= "
			function hasSelectedExplnum(){
				var hasSelected = false;
				var explnum = document.getElementsByName('doc_num_list[]');

				for (var i = 0; i < explnum.length; i++) { 
				 	if(explnum[i].checked) {
						hasSelected = true;	
					}
				}
				if (hasSelected) {
					return true;
				} else {
					alert('".$msg["opac_print_no_expl_checked"]."');
					return false;
				}
			}
				
			function checkForSubmit(){
				var selnotices = getSelectedNotice();
				if (selnotices) {
					if (document.getElementById('oute') && document.getElementById('oute').checked) {
						if(document.getElementById('emailexp') && !is_valid_mail(document.getElementById('emailexp').value) && parseInt(".$opac_print_email_sender_mandatory.")) {
							alert('".addslashes($msg["print_email_sender_mandatory"])."');
							return false;
						}
                        //vérification du remplissage du captcha
                        if(!document.getElementById('captcha_code').value){
                            document.getElementById('captcha_code').focus();
                            return false;
                        }
					}
					if (document.getElementById('docnum').checked) {
						return hasSelectedExplnum();
					} else {
						return true;
					}
				} else {
					return false;
				}
			}
			</script>";
 	$onchange="
		var div_sel=document.getElementById('sel_notice_tpl');
		var div_sel2=document.getElementById('sel_notice_tpl2');
		var notice_tpl=document.getElementById('notice_tpl');
		var sel=notice_tpl.options[notice_tpl.selectedIndex].value;
	    if(sel>0){
	    	div_sel.style.display='none';
	    	div_sel2.style.display='none';
	    }else { 
	    	div_sel.style.display='block';
	    	div_sel2.style.display='block';
	    }		    
	";
 	if ($opac_print_template_default) $selected = $opac_print_template_default;
 	else $selected = 0;
	$sel_notice_tpl=notice_tpl_gen::gen_tpl_select("notice_tpl",$selected,$onchange);
	$output_final .="
		<script type='text/javascript'>
			function sel_part_gestion(){
				var other_docnum_part=document.getElementById('other_docnum_part');	
				if(document.getElementById('outp').checked){	    	
			    	document.getElementById('other_docnum_part').style.display='block';		    	
			    	document.getElementById('docnum_part').style.display='none';	
			    	document.getElementById('mail_part').style.display='none';				    	
			    	document.getElementById('pdf_part').style.display='none';		    						    		    
				}
				if(document.getElementById('outt').checked){	    	
			    	document.getElementById('other_docnum_part').style.display='block';		    	
			    	document.getElementById('docnum_part').style.display='none';	
			    	document.getElementById('mail_part').style.display='none';				    	
			    	document.getElementById('pdf_part').style.display='none';		    						    		    
				}
				if(document.getElementById('oute')) {
					if(document.getElementById('oute').checked){	    	
				    	document.getElementById('other_docnum_part').style.display='block';		    	
				    	document.getElementById('docnum_part').style.display='none';	
				    	document.getElementById('mail_part').style.display='block';				    	
				    	document.getElementById('pdf_part').style.display='none';		    		
				 		if(typeof ajax_resize_elements == 'function'){
							ajax_resize_elements();
						}
					}
				}
				if(document.getElementById('docnum').checked){	    	
			    	document.getElementById('other_docnum_part').style.display='none';			    	
			    	document.getElementById('docnum_part').style.display='block';		 	
			    	document.getElementById('mail_part').style.display='none';				    	
			    	document.getElementById('pdf_part').style.display='none';		 
			    	get_doc_num_list();		    		    
				}
				if(document.getElementById('pdf').checked){	    
			    	document.getElementById('other_docnum_part').style.display='block';		    	
			    	document.getElementById('docnum_part').style.display='none';	
			    	document.getElementById('mail_part').style.display='none';				    	
			    	document.getElementById('pdf_part').style.display='block';		
				}
			}
			function get_doc_num_list(){
				var docnum_part=document.getElementById('docnum_part');	
				var wait = document.createElement('img');			
				docnum_part.innerHTML = '';
				wait.setAttribute('src','".get_url_icon('patience.gif')."');
				wait.setAttribute('align','top');
				docnum_part.appendChild(wait);
				getSelectedNotice();
				
				var number=0;
				if(document.getElementById('selected')) {
					if(document.getElementById('selected').checked)number=1;
				} else {
					number=1;
				}
				
				var req = new http_request();				
				var url='./ajax.php?module=ajax&categ=print_docnum&sub=get_list&select_noti='+document.getElementById('select_noti').value+
				'&number='+ number+'".($id_etagere ? "&id_etagere=".$id_etagere : '').($id_liste ? "&id_liste=".$id_liste : '')."';
				req.request(url);
				docnum_part.innerHTML = req.get_text();						
			}
		</script>
	";
	$output_final .= $script_selnoti."
	<b>".$msg["print_output_title"]."</b>
	<blockquote>
		<input type='radio' name='output' id='outp' onClick =\"sel_part_gestion();\" value='printer' ".($print_options['outp'] ? ' checked ' : '')."/><label for='outp'>&nbsp;".$msg["print_output_printer"]."</label><br />
		<input type='radio' name='output' id='pdf' onClick =\"sel_part_gestion();\" value='pdf' ".($print_options['pdf'] ? ' checked ' : '')." /><label for='pdf'>&nbsp;".$msg["print_output_pdf"]."</label><br />
		<input type='radio' name='output' id='outt' onClick =\"sel_part_gestion();\" value='tt' ".($print_options['outt'] ? ' checked ' : '')." /><label for='outt'>&nbsp;".$msg["print_output_writer"]."</label><br />";
	if (($opac_print_email == 1) || ($opac_print_email == 2 && !empty($empr_mail))) {
		//désactivation de l'envoi si le paramètre email_recipients est à 0 en étant déconnecté
		if($opac_print_email_recipients || (!$opac_print_email_recipients && $_SESSION['id_empr_session'])) {
			$output_final .= "<input type='radio' name='output' id='oute' onClick =\"sel_part_gestion();\" value='email' ".($print_options['oute'] ? ' checked ' : '')."/><label for='oute'>&nbsp;".$msg["print_email"]."</label><br />";
		}
	}
	if ($opac_print_explnum) {
		$output_final .="	<input type='radio' name='output' id='docnum' onClick =\"sel_part_gestion();\" value='docnum' ".($print_options['docnum'] ? ' checked ' : '')."/><label for='docnum'>&nbsp;".$msg["print_output_docnum"]."</label>";
	} else {
		//On conserve un champ caché pour éviter les erreurs javascript
		$output_final .="	<input type='hidden' id='docnum' value='0'/>";
	}
	$output_final.="<hr>";
	$output_final .="	&nbsp;&nbsp;
	</blockquote>
	<input type='hidden' name='select_noti' id='select_noti' value='".(($lvl=="search") ? $_SESSION["tab_result_current_page"] : "")."'/>";
	
	if ($lvl!="search" && $lvl!="etagere") {
		$output_final .= "<b>".$msg["print_select_record"]."</b>
			<blockquote>
				<input type='radio' name='number' onClick =\"sel_part_gestion();\" value='0' id='all' ".($print_options['all'] ? ' checked ' : '')."/><label for='all'>&nbsp;".$msg["print_all_records"]."</label><br />
				<input type='radio' name='number' onClick =\"sel_part_gestion();\" value='1' id='selected' ".($print_options['selected'] ? ' checked ' : '')."/><label for='selected'>&nbsp;".$msg["print_selected_records"]."</label>
			</blockquote>";
	}
	$output_final .= "
	<div id='mail_part'>
		<div class='row'>
			<div>".$msg["print_emaildest"]."</div>";
	
	if($opac_print_email_recipients == 1 || ($opac_print_email_recipients == 2 && $_SESSION['id_empr_session'])) {
		$output_final.= "
			<div class='row'>
				<input type='text' id='emaildest_0' class='saisie-20emr' completion='empr_mail' name='emaildest[]' autfield='emaildest_id_0' value='' autocomplete='off'/>
				<input type='button' class='bouton' value='X' onclick=\"document.getElementById('emaildest_0').value=''; document.getElementById('emaildest_id_0').value='';\">
				<input class='bouton' value='+' onclick='add_dest_field(this);' counter='0' type='button'>
				<input type='hidden' name='emaildest_id[]' id='emaildest_id_0'/>
			</div>
			<script type='text/javascript' src='./includes/javascript/http_request.js'></script>"; 
		if(($opac_print_email_autocomplete == 1 && $_SESSION['id_empr_session']) || ($opac_print_email_autocomplete == 2)) {
			$output_final.="<script type='text/javascript' src='./includes/javascript/ajax.js'></script>";
			$output_final.= "<script type='text/javascript'>
								ajax_parse_dom();
							</script>";
		}
	} else {
		if(!empty($empr_mail)) {
			$output_final.= "
			<div class='row'>
				<input type='text' id='emaildest_0' class='saisie-20emr' name='emaildest[]' value='".$empr_mail."' readonly />
			</div>";
		} else {
			$output_final.= "
			<div class='row'>
				<strong>".htmlentities($msg['print_emaildest_not_found'], ENT_QUOTES, $charset)."</strong>
			</div>";
		}
	}
				
	$output_final.="</div>";
	if($opac_print_email_sender) {
		$output_final.="
		<div class='row'>
			<div>".$msg["print_emailexp"]."</div>
			<input type='text' size='30' id='emailexp' name='emailexp' value='".(!empty($empr_mail) ? $empr_mail : '')."' />
		</div>";
	}
	$output_final.="<div class='row'>
			<div>".$msg["print_emailobj_label"]."</div>
			<input type='text' size='30' name='emailobj' value='".htmlentities(trim($msg["print_emailobjet"]." ".$opac_biblio_name." - ".formatdate(today())), ENT_QUOTES, $charset)."' />
		</div>
		<div class='row'>
			<div>".$msg["print_emailcontent"]."</div>
			<textarea rows='4' cols='40' id='emailcontent' name='emailcontent' value=''></textarea>
		</div>
        <div class='row' id='captcha'>
            <div>".$msg["print_verifcode"]."</div>";
            $output_final.=emprunteur_display::get_captcha();
            $output_final.="<input type='text' name='captcha_code' id='captcha_code' value=''/>
        </div>
	</div>
	
	
	<div id='pdf_part'>
	</div>
	<div id='other_docnum_part'>
		<b>".$msg["print_type_title"]."</b>
		<blockquote>
			$sel_notice_tpl
			<div id='sel_notice_tpl' ".($selected > 0 ? "style='display:none;'" : "style='display:block;'").">
				<input type='radio' name='type' value='ISBD' id='isbd' ".($print_options['isbd'] ? ' checked ' : '')."/><label for='isbd'>&nbsp;".$msg["print_type_isbd"]."</label><br />
				<input type='radio' name='type' value='PUBLIC' id='public' ".($print_options['public'] ? ' checked ' : '')."/><label for='public'>&nbsp;".$msg["print_type_public"]."</label>
			</div>
		</blockquote>
		<div id='sel_notice_tpl2' ".($selected > 0 ? "style='display:none;'" : "style='display:block;'").">
			<div id='print_format'>
				<b>".$msg["print_format_title"]."</b>
				<blockquote>
					<input type='radio' name='short' id='s1' value='1' ".($print_options['s1'] ? ' checked ' : '')."/><label for='s1'>&nbsp;".$msg["print_short_format"]."</label><br />
					<input type='radio' name='short' id='s0' value='0'".($print_options['s0'] ? ' checked ' : '')."/><label for='s0'>&nbsp;".$msg["print_long_format"]."</label><br />
					<input type='checkbox' name='header' id='header' value='1' ".($print_options['header'] ? ' checked ' : '')."/>&nbsp;<label for='header'>".$msg["print_header"]."</label><br />
					<input type='checkbox' name='vignette' id='vignette' value='1' ".($print_options['vignette'] ? ' checked ' : '')."/>&nbsp;<label for='vignette'>".$msg["print_vignette"]."</label>
				</blockquote>
			</div>
			<b>".$msg["print_ex_title"]."</b>
			<blockquote>";
			if ($opac_print_expl_default) {
				$checkprintexpl="checked";
				$checknoprintexpl="";
			} else {
				$checkprintexpl="";
				$checknoprintexpl="checked";
			}
			$output_final .= "
				<input type='radio' name='ex' id='ex1' value='1' $checkprintexpl /><label for='ex1'>&nbsp;".$msg["print_ex"]."</label><br />
				<input type='radio' name='ex' id='ex0' value='0' $checknoprintexpl /><label for='ex0'>&nbsp;".$msg["print_no_ex"]."</label>
			</blockquote>
		</div>
	</div> 
	<div id='docnum_part'>	
	</div> 
	<input type='submit' value='".$msg["print_validate"]."' class='bouton'/>&nbsp;
	<input type='button' value='".$msg["print_cancel"]."' class='bouton' onClick='self.close();'/>
	";
	$output_final .= "</form>
		<script type='text/javascript'>
			sel_part_gestion();
			function add_dest_field(buttonClicked){
				var currentCounter = buttonClicked.getAttribute('counter');
				currentCounter++;
				
				var newLine = document.createElement('div');
				newLine.setAttribute('class', 'row');
				
				var newInput = document.createElement('input');
				newInput.setAttribute('class','saisie-20emr');
				newInput.setAttribute('id', 'emaildest_'+currentCounter); 
				newInput.setAttribute('completion','empr_mail');
				newInput.setAttribute('name','emaildest[]');
				newInput.setAttribute('autfield', 'emaildest_id_'+currentCounter);
				newInput.setAttribute('value', '');
				newInput.setAttribute('autocomplete', 'off');
				newInput.setAttribute('type', 'text');
				
				var newInputId = document.createElement('input');
				newInputId.setAttribute('id','emaildest_id_'+currentCounter);
				newInputId.setAttribute('type','hidden');
				newInputId.setAttribute('name','emaildest_id[]');
				
				
				var newPurge = document.createElement('input');
				newPurge.setAttribute('value','X');
				newPurge.setAttribute('type','button');
				newPurge.setAttribute('class','bouton');
				newPurge.addEventListener('click', function(){
					newInput.value=''; 
					newInputId.value=''; 
				});
				
				newLine.appendChild(newInput);
				newLine.appendChild(newInputId);
				newLine.appendChild(newPurge);
				
				buttonClicked.setAttribute('counter', currentCounter);
				buttonClicked.parentElement.appendChild(newLine);
				if(typeof ajax_pack_element == 'function'){
					ajax_pack_element(newInput);
				}
			}
		</script>"; 
} elseif($output=="docnum"){
	$docnum=new docnum_merge(0,$doc_num_list);
	$docnum->merge();
	exit;	 
} else {
	$opac_visionneuse_allow=0;
	if(!empty($notice_tpl))$noti_tpl=new notice_tpl_gen($notice_tpl);
	else $noti_tpl = '';
	
	if(is_readable("./styles/".$css."/print/print.css") ) {
		$output_final.= "<link rel=\"stylesheet\" href=\"".$opac_url_base."styles/".$css."/print/print.css\" />";
	}
	$output_final .= "<style type='text/css'>
		BODY { 	
			font-size: 10pt;
			font-family: verdana, geneva, helvetica, arial;
			color:#000000;
			}
		td {
			font-size: 10pt;
			font-family: verdana, geneva, helvetica, arial;
			color:#000000;
		}
		th {
			font-size: 10pt;
			font-family: verdana, geneva, helvetica, arial;
			font-weight:bold;
			color:#000000;
			background:#DDDDDD;
			text-align:left;
		}
		hr {
			border:none;
			border-bottom:1px solid #000000;
		}
		h3 {
			font-size: 12pt;
		}
		.vignetteimg {
		    max-width: 140px;
		    max-height: 200px;
		    -moz-box-shadow: 1px 1px 5px #666666;
		    -webkit-box-shadow: 1px 1px 5px #666666;
		    box-shadow: 1px 1px 5px #666666;
		}
		.img_notice {
			max-width: 140px;
			max-height: 200px;
		}
		.vignette_doc_num {
			display:none;
		}
		</style>";
	if($noti_tpl) {
		$output_final.=$noti_tpl->get_print_css_style();
	}
	
	$notices = array();
	switch ($action) {
		case 'print_cart':
			if($number && $select_noti){
				$notices = explode(",",$select_noti);
			} else $notices =$_SESSION["cart"];
			break;
		case 'print_list':
			if($number && $select_noti){
				$notices = explode(",",$select_noti);
			} else {
				$liste = new liste_lecture($id_liste);
				$nom_liste = $liste->nom_liste;
				$description = $liste->description;
				$notices = $liste->sort_notices($liste->notices);
			}
			break;
		case 'print_search':
			if($select_noti){
				$notices = explode(",",$select_noti);
			} else {
				$notices = explode(",",$_SESSION["tab_result_current_page"]);
			}
			//on ajoute 'es' dans le cas des notices externes
		    if (!empty($mode) && $mode == TYPE_EXTERNAL){
		        foreach ($notices as $key=>$noti){
		            $notices[$key] = 'es'.$noti;
		        }
		    }
			break;
		case 'print_etagere':
			$etagere_caddies = new etagere_caddies($id_etagere);
			$notices = $etagere_caddies->get_notices();
			break;
	}
	
	$show_what = array(
			'short' => $short,
			'header' => $header,
			'vignette' => $vignette,
			'expl' => $ex,
	);
	$notices_aff="";
	if (count($notices)) {
		$date_today = formatdate(today()) ;
		if ($output=="email") {
			//on rajoute une mention spécifiant l'origine du mail...
			$rqt = "select empr_nom, empr_prenom from empr where id_empr ='".$_SESSION['id_empr_session']."'";
			$res = pmb_mysql_query($rqt);
			if(pmb_mysql_num_rows($res)){
				$info = pmb_mysql_fetch_object($res);
				$output_final .= "<h3>".$msg['biblio_send_by']." ".$info->empr_nom." ".$info->empr_prenom."</h3>" ;
			}
		}
		$output_final .= "<h3>".$date_today."&nbsp;".sprintf($msg["show_cart_n_notices"],count($notices))."</h3><hr style='border:none; border-bottom:solid #000000 3px;'/>";
		for ($i=0; $i<count($notices); $i++) {
			$notice_aff="";
			if($noti_tpl) {
				$notice_aff.=$noti_tpl->build_notice(substr($notices[$i],0,2)!="es"?$notices[$i]:substr($notices[$i],2));
				$output_final .= $notice_aff."<br /> ";
			} else{
				if (substr($notices[$i],0,2)!="es" && $type=='PUBLIC' && $opac_notices_format==AFF_ETA_NOTICES_TEMPLATE_DJANGO) {
					if($short) {
						switch ($output) {
							case 'pdf' :
								$notice_aff = record_display::get_display_for_pdf_short($notices[$i], '', $show_what);
								break;
							case 'printer' :
							default:
								$notice_aff = record_display::get_display_for_printer_short($notices[$i], '', $show_what);
						}
					}else {
						switch ($output) {
							case 'pdf' :
								$notice_aff = record_display::get_display_for_pdf_extended($notices[$i], '', $show_what);
								break;
							case 'printer' :
							default:
								$notice_aff = record_display::get_display_for_printer_extended($notices[$i], '', $show_what);
						}
					}
					$output_final.= $notice_aff."<hr /> ";
					
					//TRAITEMENT DE LAFFICHAGE DES NOTICES ES (EXTERNAL SEARCH)
				} else if (substr($notices[$i],0,2)=="es" && $opac_notices_format==AFF_ETA_NOTICES_TEMPLATE_DJANGO){
				    if($short) { 
				        switch ($output) {
				            case 'pdf' :
                                $notice = substr($notices[$i],2);
                                $notice_aff = record_display::get_display_for_pdf_short_unimarc($notice, $opac_notices_format_django_directory,$show_what);   
				                break;
				            case 'printer' :
				            default:
                                $notice = substr($notices[$i],2);
                                $notice_aff = record_display::get_display_for_printer_short_unimarc($notice, $opac_notices_format_django_directory, $show_what);
                                break;
				        }
				    }else {
				        switch ($output) {
				            case 'pdf' :
                                $notice = substr($notices[$i],2);
                                $notice_aff = record_display::get_display_for_pdf_extended_unimarc($notice, $opac_notices_format_django_directory, $show_what);
				                break;
				            case 'printer' :
				            default:
                                $notice = substr($notices[$i],2);
                                $notice_aff = record_display::get_display_for_printer_extended_unimarc($notice, $opac_notices_format_django_directory, $show_what);
                                break;
				        }
				    }
				    $output_final.= $notice_aff."<hr /> ";
				    
				} else {
					if (substr($notices[$i],0,2)!="es") {
						if (!$opac_notice_affichage_class) $opac_notice_affichage_class="notice_affichage";
					} else {
						$opac_notice_affichage_class="notice_affichage_unimarc";
					}
					$current = new $opac_notice_affichage_class((substr($notices[$i],0,2)!="es"?$notices[$i]:substr($notices[$i],2)),array(),0,1);
					$notice_aff .= $current->get_print_css_style();
					$current->do_header();
					if ($type=='PUBLIC') {
						$current->do_public($short,$ex);
						if ($vignette) $current->do_image($current->notice_public,false);
					} else {
						$current->do_isbd($short,$ex);
						if ($vignette) {
						    if ($output == 'pdf') $opac_notice_is_pdf = true;
						    $current->do_image($current->notice_isbd,false);
						}
					}
					//Icone type de Document
					if (!isset($icon_doc)) {
						$icon_doc = marc_list_collection::get_instance('icondoc');
						$icon_doc = $icon_doc->table;
					}
					$icon = $icon_doc[$current->notice->niveau_biblio.$current->notice->typdoc];
					$iconDoc = "";
					if ($icon) {
						if(!isset($biblio_doc)) {
							$biblio_doc = marc_list_collection::get_instance('nivbiblio');
							$biblio_doc = $biblio_doc->table;
						}
						$info_bulle_icon=$biblio_doc[$current->notice->niveau_biblio]." : ".$tdoc->table[$current->notice->typdoc];
						$iconDoc="<img src=\"".get_url_icon($icon, 1)."\" alt=\"$info_bulle_icon\" title=\"$info_bulle_icon\" class='align_top'/>";
					}
					if ($header) {
						$notice_aff .= "<h3>&nbsp;".$iconDoc.$current->notice_header."</h3>";
					}
					if ($current->notice->niveau_biblio =='s') {
					    if (!isset($bulletins)) $bulletins = '';
						$perio="<span class='fond-mere'>[".$msg['isbd_type_perio'].$bulletins."]</span>&nbsp;";
					} elseif ($current->notice->niveau_biblio =='a') {
						$perio="<span class='fond-article'>[".$msg['isbd_type_art']."]</span>&nbsp;";
					} else $perio="";
					if ($type=='PUBLIC') {
					    $notice_aff .= $perio.$current->notice_public;
					} else { 
					    $notice_aff .= $perio.$current->notice_isbd;
					}
					if ($ex) $notice_aff .= $current->affichage_expl ;
					$output_final .= $notice_aff."<hr /> ";
				}
			}
			if ($opac_notice_is_pdf) {
			    $notices_aff.= "<nobreak>" . $notice_aff . "</nobreak>";
			} else {
			    $notices_aff.= $notice_aff;
			}
			if($noti_tpl) {
				$notices_aff .= "<br /> ";
			} else {
				$notices_aff .= "<hr /> ";
			}
		}

		$notices_aff = $header_print.$notices_aff.$footer_print;
		$output_final .= $footer_print;
		if ($charset!='utf-8') $output_final=cp1252Toiso88591($output_final);
	}
	
	if ($output=="printer") $output_final .= "<script>self.print();</script>";
	
}
if($opac_parse_html){
	$output_final=parseHTML($output_final);
}

if ($output=="pdf"){
	if($charset != 'utf-8'){
		if(function_exists("mb_convert_encoding")){
			$notices_aff = mb_convert_encoding($notices_aff,"UTF-8","Windows-1252");
		}else{
			$notices_aff = utf8_encode($notices_aff);
		}
	}
	$html2pdf = new Html2Pdf();
	$css = "<style>".file_get_contents("./styles/$css/print/print.css")."</style>";
	$html2pdf->writeHTML("<html><body>".$css.$notices_aff."</body></html>");
	$html2pdf->output('diffusion.pdf','I');
	exit;
}

if ($output!='email') {
	print pmb_bidi($output_final.'</body></html>') ;
} else {
    //Gestion du captcha
    $securimage = new Securimage();
    if (!$securimage->check($captcha_code)) {
        //En cas d'erreur de captcha on stoppe et on affiche un bouton pour retourner sur le formulaire
        print(
            "<div class='error'>
                <span>".$msg['print_wrongcode']."</span>
                <button class='bouton' type='button' onclick='history.back()'>".$msg['print_wrongcode_backbutton']."</button>
            </div>"
        );
        pmb_mysql_close();
        exit;
    }
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=".$charset."\n";
	$emailobj = trim(stripslashes($emailobj));
	if (!$emailobj) {
		$emailobj=$msg['print_emailobjet'].' '.$opac_biblio_name.' - '.$date_today;
	}
	
	$mail_addresses = array();
	foreach($emaildest as $i => $email){
		if(isset($emaildest_id[$i]) && $emaildest_id[$i]){
			$emaildest_id[$i]+= 0;
			$query = "select empr_mail from empr where id_empr = ".$emaildest_id[$i];
			$result = pmb_mysql_result(pmb_mysql_query($query), 0,0);
			$mail_addresses[] = $result;
		}else{
			if($email){
				$mail_addresses[] = $email;
			}
		}
	}
	$reply_name = "";
	$reply_mail = "";
	if($opac_print_email_sender) {
		$reply_name = $emailexp;
		$reply_mail = $emailexp;
	}
	$res_envoi=mailpmb('', implode(';',$mail_addresses), $emailobj, $output_final.'<br /><br />'.mail_bloc_adresse().'</body></html>', $opac_biblio_name, $opac_biblio_email, $headers, '', '', 0, array(), $reply_name, $reply_mail);
	$vide_cache=filemtime("./styles/".$css."/".$css.".css");
	if ($res_envoi) { 
		print "<!DOCTYPE html><html lang='".get_iso_lang_code()."'><head><meta charset=\"".$charset."\" /><title>".$msg["print_title"]."</title></head><body class='popup'><link rel=\"stylesheet\" href=\"./styles/".$css."/$css.css?".$vide_cache."\" />\n<br /><br /><h3>".sprintf($msg["print_emailsucceed"],implode(', ',$mail_addresses))."</h3><br />
		<a href=\"\" onClick=\"self.close(); return false;\">".$msg["print_emailclose"]."</a></body></html>" ;
	} else { 
		echo "<!DOCTYPE html><html lang='".get_iso_lang_code()."'><head><meta charset=\"".$charset."\" /><title>".$msg["print_title"]."</title></head><body class='popup'><link rel=\"stylesheet\" href=\"./styles/".$css."/$css.css?".$vide_cache."\" />\n<br /><br /><h3>".sprintf($msg["print_emailfailed"],implode(', ',$mail_addresses))."</h3><br />
		<a href=\"\" onClick=\"self.close(); return false;\">".$msg["print_emailclose"]."</a></body></html>" ;
	}
}