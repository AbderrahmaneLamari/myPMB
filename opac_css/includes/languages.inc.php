<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: languages.inc.php,v 1.8 2022/12/20 09:50:20 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function show_select_languages() {
	global $common_tpl_lang_select, $msg, $opac_show_languages, $include_path, $lang ;

	$show_languages = substr($opac_show_languages,0,1) ;
	
	if ($show_languages==1) {
		$languages = explode(",",substr($opac_show_languages,2)) ;
		$langues = new XMLlist("$include_path/messages/languages.xml");
		$langues->analyser();
		$clang = $langues->table;
		$lang_combo = array();
		for ($i=0; $i<sizeof($languages); $i++) {
			$lang_combo[$languages[$i]] = $clang[$languages[$i]] ;
			}

		$common_tpl_lang_select=str_replace("!!msg_lang_select!!",$msg["common_tpl_lang_select"],$common_tpl_lang_select);
		$action = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/')+1);
		$combo = "<form method=\"post\" action=\"".$action."\" >";
		$combo .= get_hidden_global_var('POST');
		$combo .= "<select name=\"lang_sel\" onchange=\"this.form.submit();\">";
		foreach ($lang_combo as $cle => $value) {
			if(strcmp($cle, $lang) != 0) $combo .= "<option value='$cle'>$value</option>";
			else $combo .= "<option value='$cle' selected>$value </option>";
		}
		$combo .= "</select></form>";
		$common_tpl_lang_select=str_replace("!!lang_select!!",$combo,$common_tpl_lang_select);
		// end combo box
		
		return $common_tpl_lang_select ;
		
		} else return "" ; 
	}
