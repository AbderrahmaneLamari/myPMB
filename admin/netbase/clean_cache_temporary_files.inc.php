<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: clean_cache_temporary_files.inc.php,v 1.2.4.1 2023/06/15 11:57:48 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $class_path, $msg, $charset;
global $v_state, $spec;

require_once ($class_path."/netbase/netbase_cache.class.php");

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["cleaning_cache_temporary_files"], ENT_QUOTES, $charset)."</h2>";
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["cleaning_cache_temporary_files"], ENT_QUOTES, $charset)." : ";

$cleaned = netbase_cache::clean_files($base_path."/temp");
if($cleaned) {
	//Correctement r�alis� en gestion, on nettoye � l'OPAC
	$cleaned = netbase_cache::clean_files($base_path."/opac_css/temp");
}
if($cleaned) {
	$v_state.= "OK";
} else {
	$v_state.= "KO";
}
$spec = $spec - CLEAN_CACHE_TEMPORARY_FILES;

// mise � jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec);