<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reindex_global.inc.php,v 1.22.4.2 2023/06/20 07:21:19 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $msg, $charset;
global $start, $v_state, $spec, $count, $pass2;

require_once($base_path.'/classes/notice.class.php');
require_once($base_path.'/classes/noeuds.class.php');

// la taille d'un paquet de notices
$lot = REINDEX_PAQUET_SIZE; // defini dans ./params.inc.php


$v_state=urldecode($v_state);

if (!isset($count) || !$count) {
	$notices = pmb_mysql_query("SELECT count(1) FROM notices");
	$count = pmb_mysql_result($notices, 0, 0);
}

//On traite d'abord la table notice_global_index
if(!isset($pass2) || !$pass2) {
	// initialisation de la borne de départ
	if (empty($start)) {
		$start=0;
		//remise a zero de la table au début
		pmb_mysql_query("TRUNCATE notices_global_index");
		pmb_mysql_query("ALTER TABLE notices_global_index DISABLE KEYS");
	}
	print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_reindex_global"], ENT_QUOTES, $charset)." (Part. 1)</h2>";
	print netbase::get_display_progress($start, $count);
	
	$nb_indexed = netbase_records::global_index_from_query("select notice_id as id from notices order by notice_id LIMIT $start, $lot");
	if($nb_indexed) {
		$next = $start + $lot;
		print netbase::get_current_state_form($v_state, $spec, '', $next, $count);
	} else {
		print netbase::get_process_state_form($v_state, $spec, '', '1');
		pmb_mysql_query("ALTER TABLE notices_global_index ENABLE KEYS");
	}
} elseif ($pass2==1) {
	// initialisation de la borne de départ
	if (empty($start)) {
		$start=0;
		//remise a zero de la table au début
		pmb_mysql_query("TRUNCATE notices_mots_global_index");
		pmb_mysql_query("ALTER TABLE notices_mots_global_index DISABLE KEYS");
		
		pmb_mysql_query("TRUNCATE notices_fields_global_index");
		pmb_mysql_query("ALTER TABLE notices_fields_global_index DISABLE KEYS");
	}
	print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_reindex_global"], ENT_QUOTES, $charset)." (Part. 2)</h2>";
	print netbase::get_display_progress($start, $count);
	
	$nb_indexed = netbase_records::index_from_query("select notice_id as id from notices order by notice_id LIMIT $start, $lot");
	if($nb_indexed) {
		$next = $start + $lot;
		print netbase::get_current_state_form($v_state, $spec, '', $next, $count, $pass2);
	} else {
		$spec = $spec - INDEX_GLOBAL;
		$not = pmb_mysql_query("SELECT COUNT(DISTINCT id_notice) FROM notices_fields_global_index");
		$compte = pmb_mysql_result($not, 0, 0);
		$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_reindex_global"], ENT_QUOTES, $charset)." :";
		$v_state .= $compte." ".htmlentities($msg["nettoyage_res_reindex_global"], ENT_QUOTES, $charset);
		print netbase::get_process_state_form($v_state, $spec);
		pmb_mysql_query("ALTER TABLE notices_mots_global_index ENABLE KEYS");
		pmb_mysql_query("ALTER TABLE notices_fields_global_index ENABLE KEYS");
	}
}