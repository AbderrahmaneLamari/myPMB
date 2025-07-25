<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: relations7.inc.php,v 1.16 2021/12/15 08:47:16 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $msg, $charset;
global $start, $v_state, $spec;

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);
print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_clean_relations_pan3"], ENT_QUOTES, $charset)."</h2>";

pmb_mysql_query("delete caddie_content from caddie join caddie_content on (idcaddie=caddie_id and type='BULL') left join bulletins on object_id=bulletin_id where bulletin_id is null");
$affected = pmb_mysql_affected_rows();

pmb_mysql_query("delete notices_langues from notices_langues left join notices on num_notice=notice_id where notice_id is null");
$affected += pmb_mysql_affected_rows();

pmb_mysql_query("delete abo_liste_lecture from abo_liste_lecture left join empr on num_empr=id_empr where id_empr is null");
$affected += pmb_mysql_affected_rows();

pmb_mysql_query("delete abo_liste_lecture from abo_liste_lecture left join opac_liste_lecture on num_liste=id_liste where id_liste is null");
$affected += pmb_mysql_affected_rows();

pmb_mysql_query("delete opac_liste_lecture from opac_liste_lecture left join empr on num_empr=id_empr where id_empr is null");
$affected += pmb_mysql_affected_rows();

$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_suppr_relations"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_suppr_relations_pan3"], ENT_QUOTES, $charset);
pmb_mysql_query('OPTIMIZE TABLE caddie_content');
// mise � jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec, '', '8');
