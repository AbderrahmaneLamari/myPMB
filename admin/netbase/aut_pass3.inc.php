<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: aut_pass3.inc.php,v 1.13 2021/12/13 15:23:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $msg, $charset;
global $v_state, $spec, $start;

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_responsabilites"], ENT_QUOTES, $charset)." : 1</h2>";

pmb_mysql_query("delete responsability from responsability left join notices on responsability_notice=notice_id where notice_id is null ");
$affected = pmb_mysql_affected_rows();

// mise � jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec, $affected, '3');
