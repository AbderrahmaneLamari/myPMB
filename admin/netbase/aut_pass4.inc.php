<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: aut_pass4.inc.php,v 1.15 2021/12/15 08:47:16 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $msg, $charset;
global $v_state, $spec, $start, $count;

// initialisation de la borne de départ
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

if(!$count) {
	$notices = pmb_mysql_query("SELECT count(1) FROM responsability where responsability_author<>0 ");
	$count = pmb_mysql_result($notices, 0, 0) ;
}

print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_responsabilites"], ENT_QUOTES, $charset)." : 2</h2>";

pmb_mysql_query("delete responsability from responsability left join authors on responsability_author=author_id where author_id is null ");
$affected = pmb_mysql_affected_rows();

$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_responsabilites"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_responsabilites"], ENT_QUOTES, $charset);
pmb_mysql_query('OPTIMIZE TABLE authors');
// mise à jour de l'affichage de la jauge
$spec = $spec - CLEAN_AUTHORS;
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec, $affected, '0');
