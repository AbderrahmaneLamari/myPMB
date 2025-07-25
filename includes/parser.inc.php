<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: parser.inc.php,v 1.30 2023/02/14 15:43:04 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

/*----------------------------------------------------------------------------------------
 Fonctions pour parser un fichier XML
 La fonction � appeler est _parser_ avec comme arguments :
     $nom_fichier : le nom du fichier XML
     $fonction : la lise des fonctions associ�es aux tags de niveau 2
     $rootelement : l'�l�ment root du fichier XML
----------------------------------------------------------------------------------------*/

// Lecture r�cursive de la structure et stockage des param�tres

function _recursive_(&$indice, $niveau, &$param, &$tag_count, &$vals) {
	$nb_vals = count($vals);
	if ($indice > $nb_vals) {
		exit;
	}
	
	while ($indice < $nb_vals) {
	    
		$val = $vals[$indice];
		$indice ++;
	
		if (!isset($tag_count[$val["tag"]])) {
			$tag_count[$val["tag"]] = 0;
		} else {
			$tag_count[$val["tag"]]++;
		}
		
		if (isset($val["attributes"])) {
			$attributs = $val["attributes"];
			foreach ($attributs as $key_att => $val_att) {
				$param[$val["tag"]][$tag_count[$val["tag"]]][$key_att] = $val_att;
			}
		}
		
		if ($val["type"] == "open") {
			$tag_count_next = array();
			_recursive_($indice, $niveau +1, $param[$val["tag"]][$tag_count[$val["tag"]]], $tag_count_next, $vals);
		}
		
		if ($val["type"] == "close" && $niveau > 2) {
			break;
		}
		
		if ($val["type"] == "complete") {
			if(isset($val["value"])) {
				$param[$val["tag"]][$tag_count[$val["tag"]]]["value"] = $val["value"];
			} else {
				$param[$val["tag"]][$tag_count[$val["tag"]]]["value"] = '';
			}
		}
	}
}

//Parse le fichier [nom_fichier] et ex�cute les fonctions li�es aux tags

function _parser_($nom_fichier, $fonction, $rootelement) {
	global $charset;
	$vals = array();
	$index = array();
	if ($file = fopen($nom_fichier, "r")) {
		$simple = fread($file, filesize($nom_fichier));
		fclose($file);
		$rx = "/<?xml.*encoding=[\'\"](.*?)[\'\"].*?>/m";
		if (preg_match($rx, $simple, $m)) $encoding = strtoupper($m[1]);
			else $encoding = "ISO-8859-1";
		//encodages support�s par les fonctions suivantes
		if (($encoding != "ISO-8859-1") && ($encoding != "UTF-8") && ($encoding != "US-ASCII")) $encoding = "ISO-8859-1";
		$p = xml_parser_create($encoding);
		xml_parser_set_option($p, XML_OPTION_TARGET_ENCODING, $charset);		
		xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		if (xml_parse_into_struct($p, $simple, $vals, $index) == 1) {
			xml_parser_free($p);
			$param = array();
			$tag_count = array();
			$indice=0;
			_recursive_($indice, 1, $param, $tag_count, $vals);
		}
		unset($vals, $index);
		if (is_array($param)) {
			if (count($param[$rootelement]) != 1) {
				echo "Erreur, ceci, n'est pas un fichier $rootelement !";
				exit;
			}
			$param_var = $param[$rootelement][0];
			foreach ($param_var as $key => $val) {
				if (isset($fonction[$key])) {
					for ($j = 0; $j < count($val); $j ++) {
						$param_fonction = $val[$j];
						eval($fonction[$key]."(\$param_fonction);");
					}
				}
			}
		}
	}
}

function _parser_text_($xml, $fonction, $rootelement) {
	global $charset;
	$vals = array();
	$index = array();
	if ($xml) {
		$simple = $xml;
		
		$rx = "/<?xml.*encoding=[\'\"](.*?)[\'\"].*?>/m";
		if (preg_match($rx, $simple, $m)) $encoding = strtoupper($m[1]);
			else $encoding = "ISO-8859-1";
		//encodages support�s par les fonctions suivantes
		if (($encoding != "ISO-8859-1") && ($encoding != "UTF-8") && ($encoding != "US-ASCII")) $encoding = "ISO-8859-1";
		$p = xml_parser_create($encoding);
		xml_parser_set_option($p, XML_OPTION_TARGET_ENCODING, $charset);		
		xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		if (xml_parse_into_struct($p, $simple, $vals, $index) == 1) {
			xml_parser_free($p);
			$param = array();
			$tag_count = array();
			$indice=0;
			_recursive_($indice, 1, $param, $tag_count, $vals);
		}
		unset($vals, $index);
		if (!empty($param) && is_array($param)) {
			if (count($param[$rootelement]) != 1) {
				echo "Erreur, ceci, n'est pas un fichier $rootelement !";
				exit;
			}
			$param_var = $param[$rootelement][0];
			foreach ($param_var as $key => $val) {
				if (isset($fonction[$key])) {
					for ($j = 0; $j < count($val); $j ++) {
						$param_fonction = $val[$j];
						eval($fonction[$key]."(\$param_fonction);");
					}
				}
			}
		}
	}
}

function _parser_text_no_function_($xml, $rootelement="", $full_path = '') {
	global $charset;
	global $class_path;
	
	$vals = array();
	$index = array();
	
	if ($xml) {
		$simple = $xml;
		$rx = "/<?xml.*encoding=[\'\"](.*?)[\'\"].*?>/m";
	
		if (preg_match($rx, $simple, $m)) {
			$encoding = strtoupper($m[1]);
		} else {
			$encoding = "ISO-8859-1";
		}
		
		//encodages support�s par les fonctions suivantes
		if (($encoding != "ISO-8859-1") && ($encoding != "UTF-8") && ($encoding != "US-ASCII")) {
			$encoding = "ISO-8859-1";
		}
		
		$p = xml_parser_create($encoding);
		xml_parser_set_option($p, XML_OPTION_TARGET_ENCODING, $charset);		
		xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		if (xml_parse_into_struct($p, $simple, $vals, $index) == 1) {
			
			// Lib�ration de la m�moire
			xml_parser_free($p);
			
			$param = array();
			$tag_count = array();
			$indice = 0;
			
			_recursive_($indice, 1, $param, $tag_count, $vals);
		} else {
			echo xml_error_string(xml_get_error_code($p))." ". xml_get_current_line_number($p);
		}
		
		$p = null;
		unset($vals, $index);
		
		if (isset($param) && is_array($param)) {
			if ($rootelement) {
				if (count($param[$rootelement]) != 1) {
					echo "Erreur, ceci n'est pas un fichier $rootelement !";
					exit;
				}
				$param_var = $param[$rootelement][0];
			} else {
				$param_var = $param;
			}
			
			//Param�trage de substitution par l'interface
			if ($full_path) {
				$path = substr($full_path, 0, strrpos($full_path, '/'));
				$filename = substr($full_path, strrpos($full_path, '/')+1);
				
				switch ($rootelement) {
					case 'CATALOG':
						require_once($class_path.'/misc/files/misc_file_catalog.class.php');
						$misc_file_catalog = new misc_file_catalog($path, $filename);
						if(isset($param_var['ACTION'])) {
							$param_var['ACTION'] = $misc_file_catalog->apply_substitution($param_var['ACTION']);
						} elseif(isset($param_var['ITEM'])) {
							$param_var['ITEM'] = $misc_file_catalog->apply_substitution($param_var['ITEM']);
						}
						break;
					case 'INDEXATION':
						require_once($class_path.'/misc/files/misc_file_indexation.class.php');
						$misc_file_indexation = new misc_file_indexation($path, $filename);
						$param_var['FIELD'] = $misc_file_indexation->apply_substitution($param_var['FIELD']);
						break;
					case 'SORT':
						require_once($class_path.'/misc/files/misc_file_sort.class.php');
						$misc_file_sort = new misc_file_sort($path, $filename);
						$param_var['FIELD'] = $misc_file_sort->apply_substitution($param_var['FIELD']);
						break;
				}
			}
			return $param_var;
		}
	}
}

function recurse_xml($param, $level,$tagname,$lowercase=false) {
	if ($lowercase) $tagname1=strtolower($tagname); else $tagname1=$tagname;
	$ret=str_repeat(" ",$level)."<".$tagname1;
	$ret_sub="";
	$value="";
	if ($param=="") $param=array();
	foreach ($param as $key => $val) {
		if (is_array($val)) {
			for ($i=0; $i<count($val); $i++) {
				if ($lowercase) $key1=strtolower($key); else $key1=$key;
				$ret_sub.=recurse_xml($val[$i],$level+1,$key,$lowercase)."</".$key1.">\n";
			}
		} else {
			if ($key!="value") {	
				if ($lowercase) $key1=strtolower($key); else $key1=$key;
				$ret.=" ".$key1."=\"$val\"";
			}
			else
				$value=$val;	
		}
	}
	$ret.=">".$value;
	if ($ret_sub!="") $ret.="\n".$ret_sub.str_repeat(" ",$level);
	return $ret;
}
	
function array_to_xml($param,$rootelement,$lowercase=false) {
	return recurse_xml($param,0,$rootelement,$lowercase)."</$rootelement>";	
}
?>