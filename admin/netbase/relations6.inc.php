<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: relations6.inc.php,v 1.17 2021/12/15 08:47:16 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec;

require_once($class_path."/notice_relations.class.php");

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_clean_relations_dep1"], ENT_QUOTES, $charset)."</h2>";

pmb_mysql_query("delete analysis from analysis left join notices on analysis_notice=notice_id where notice_id is null");
$affected = pmb_mysql_affected_rows();

pmb_mysql_query("delete notices from notices left join analysis on analysis_notice=notice_id where analysis_notice is null and niveau_hierar='2' and niveau_biblio='a'");
$affected += pmb_mysql_affected_rows();

pmb_mysql_query("delete analysis from analysis left join bulletins on analysis_bulletin=bulletin_id where bulletin_id is null");
$affected += pmb_mysql_affected_rows();

pmb_mysql_query("delete bulletins from bulletins left join notices on bulletin_notice=notice_id where notice_id is null");
$affected += pmb_mysql_affected_rows();

$affected += notice_relations::clean_lost_links();

$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_suppr_relations"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_suppr_relations_dep1"], ENT_QUOTES, $charset);
pmb_mysql_query('OPTIMIZE TABLE notices');

// mise � jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec, '', '7');
