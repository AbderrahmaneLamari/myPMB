<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: clean_entities_data.inc.php,v 1.3 2021/12/13 15:23:45 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $v_state, $spec;

require_once ($class_path."/netbase/netbase_records.class.php");

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["cleaning_entities_data"], ENT_QUOTES, $charset)."</h2>";
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["cleaning_entities_data"], ENT_QUOTES, $charset)." : ";

$affected=0;
$cleaned = netbase_records::clean_data();
if($cleaned) {
	$affected = count(netbase_records::get_cleaned_records());
	$v_state .= $affected." ".htmlentities($msg["cleaning_res_entities_records_data"], ENT_QUOTES, $charset);
} else {
	$v_state.= "KO";
}
$spec = $spec - CLEAN_ENTITIES_DATA;

// mise à jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec);