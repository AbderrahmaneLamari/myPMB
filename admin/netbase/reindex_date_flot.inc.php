<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reindex_date_flot.inc.php,v 1.3 2021/12/15 08:47:16 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec, $count;

require_once ($class_path."/netbase/netbase_entities.class.php");

// initialisation de la borne de départ
if (empty($start)) {
	$start=0;
	//remise a zero de la table au début
}

$v_state=urldecode($v_state);
$fields_date_flot = array();
if (empty($count)) {
    $count = 0;
    $fields_date_flot = netbase_entities::get_custom_fields_date_flot();
    if(!empty($fields_date_flot)) {
    	foreach ($fields_date_flot as $prefix=>$fields) {
    		$count += count($fields);
    	}
    }
}

print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_reindex_date_flot"], ENT_QUOTES, $charset)."</h2>";

$counter = 0;
foreach ($fields_date_flot as $prefix=>$fields_id) {
    foreach ($fields_id as $field_id) {
		netbase_entities::index_custom_field_date_flot($prefix, $field_id);
        $counter++;
        print netbase::get_display_progress($counter, $count);
    }
}

$spec = $spec - INDEX_DATE_FLOT;
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_reindex_date_flot"], ENT_QUOTES, $charset)." :";
$v_state .= $count." ".htmlentities($msg["nettoyage_res_reindex_date_flot"], ENT_QUOTES, $charset);

print netbase::get_process_state_form($v_state, $spec);
