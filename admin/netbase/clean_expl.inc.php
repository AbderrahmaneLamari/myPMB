<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: clean_expl.inc.php,v 1.22 2021/12/13 15:23:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec;

require_once($class_path."/parametres_perso.class.php");
require_once($class_path."/audit.class.php");

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_suppr_notices"], ENT_QUOTES, $charset)."</h2>";

// La routine ne nettoie pour l'instant que les monographies
pmb_mysql_query("delete notices  
	FROM notices left join exemplaires on expl_notice=notice_id  
		left join explnum on explnum_notice=notice_id 
		left join notices_relations NRN on NRN.num_notice=notice_id  
		left join notices_relations NRL on NRL.linked_notice=notice_id 
	WHERE niveau_biblio='m' AND niveau_hierar='0' and explnum_notice is null and expl_notice is null and NRN.num_notice is null and NRL.linked_notice is null");
$affected = pmb_mysql_affected_rows();
 
$spec = $spec - CLEAN_NOTICES;
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg['nettoyage_suppr_notices'], ENT_QUOTES, $charset);
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_suppr_notices"], ENT_QUOTES, $charset);
pmb_mysql_query('OPTIMIZE TABLE notices');
// mise � jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec);
