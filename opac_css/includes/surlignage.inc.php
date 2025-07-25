<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: surlignage.inc.php,v 1.7.12.1 2023/10/10 06:37:55 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function activation_surlignage() {
	global $msg;
	global $surligne;
	global $opac_surlignage;
	global $lvl;
	global $action;
	global $user_query;
	global $footer;
	global $inclus_footer;
	
	$activation_surlignage="";
	
	switch ($lvl) {
		case "more_results":
			$form_name="form_values";
			break;
		case "search_result":
			$form_name="search_input";
			break;
		case "search_segment":
			switch ($action) {
				case "more_results":
					$form_name="form_values";
					break;
				case "search_result":
					$form_name="search_input";
					break;
			}
			break;
	}
	
	if (($opac_surlignage==2)||($opac_surlignage==3)) {
		if ((!isset($surligne))||((string)$surligne=="")) {	
			if ((!isset($_SESSION['surlignez']))||((string)$_SESSION['surlignez']=="")) {
				if ($opac_surlignage==2) {
					$_SESSION['surlignez']=0;
				} else {
					$_SESSION['surlignez']=1;
				}
			}
			$surligne=$_SESSION['surlignez'];
		} else $_SESSION['surlignez']=$surligne;
		$activation_surlignage.= "&nbsp;&nbsp;&nbsp;<img style='border:0px' class='center' src=\"".get_url_icon('text_horizontalrule.png')."\" onMouseOver=\"this.style.cursor='pointer'\" ";
		if ($_SESSION['surlignez']==0) {
			$activation_surlignage.= "alt='".$msg['surligner']."' title='".$msg['surligner']."' onClick='";
			$activation_surlignage.= "document.$form_name.surligne.value=1;";
		} else {
			$activation_surlignage.= "alt='".$msg['no_surligner']."' title='".$msg['no_surligner']."' onClick='";
			$activation_surlignage.= "document.$form_name.surligne.value=0;";
		}
		$activation_surlignage.= "document.$form_name.submit();";
		$activation_surlignage.= "'/>";	
	} else if (($opac_surlignage==1)||($opac_surlignage==0)) {
		$surligne=$opac_surlignage;
		unset($_SESSION['surlignez']);
	}
	$footer=str_replace("//rechercher!!",($surligne?"addLoadEvent(function() {rechercher(1);});":""),$footer);
	$inclus_footer=str_replace("//rechercher!!",($surligne?"addLoadEvent(function() {rechercher(1);});":""),$inclus_footer);
	return $activation_surlignage;
}

?>
