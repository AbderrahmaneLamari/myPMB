<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: gen_signature_notice.inc.php,v 1.8 2021/12/13 15:23:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec, $count;

require_once($class_path."/notice_doublon.class.php");

$sign= new notice_doublon();

// la taille d'un paquet de notices
$lot = NOEXPL_PAQUET_SIZE*10; // defini dans ./params.inc.php

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

if(!$count) {
	$notices = pmb_mysql_query("SELECT count(1) FROM notices");
	$count = pmb_mysql_result($notices, 0, 0);
}

print "<br /><br /><h2 class='center'>".htmlentities($msg["gen_signature_notice"], ENT_QUOTES, $charset)."</h2>";

$query = pmb_mysql_query("SELECT notice_id FROM notices LIMIT $start, $lot");
if(pmb_mysql_num_rows($query)) {
	print netbase::get_display_progress($start, $count);
   	while ($row = pmb_mysql_fetch_row($query) )  { 		
   		$val= $sign->gen_signature($row[0]);
		pmb_mysql_query("update notices set signature='$val', update_date=update_date where notice_id=".$row[0]);		
   	}
   	pmb_mysql_free_result($query);
	$next = $start + $lot;
 	print netbase::get_current_state_form($v_state, $spec, '', $next, $count);
} else {
	$spec = $spec - GEN_SIGNATURE_NOTICE;
	$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["gen_signature_notice_status"], ENT_QUOTES, $charset);
	$v_state .= $count." ".htmlentities($msg["gen_signature_notice_status_end"], ENT_QUOTES, $charset);
	pmb_mysql_query('OPTIMIZE TABLE notices');
	// mise � jour de l'affichage de la jauge
	print netbase::get_display_final_progress();

	print netbase::get_process_state_form($v_state, $spec);
}	