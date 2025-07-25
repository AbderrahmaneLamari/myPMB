<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: gen_date_tri.inc.php,v 1.8 2021/12/13 15:23:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec, $count;

require_once($class_path.'/notice.class.php');

// la taille d'un paquet de notices
$lot = REINDEX_PAQUET_SIZE; // defini dans ./params.inc.php

// initialisation de la borne de d�part
if (!isset($start)) {
	$start=0;
}

$v_state=urldecode($v_state);

if (!$count) {
	$notices = pmb_mysql_query("SELECT count(1) FROM notices");
	$count = pmb_mysql_result($notices, 0, 0);
}
	
print "<br /><br /><h2 class='center'>".htmlentities($msg["gen_date_tri_msg"], ENT_QUOTES, $charset)."</h2>";


$query = pmb_mysql_query("select notice_id, year, niveau_biblio, niveau_hierar from notices order by notice_id LIMIT $start, $lot");
if(pmb_mysql_num_rows($query)) {
	print netbase::get_display_progress($start, $count);
	
	while($mesNotices = pmb_mysql_fetch_assoc($query)) {
		
		switch($mesNotices['niveau_biblio'].$mesNotices['niveau_hierar']){
			case 'a2': 
				//Si c'est un article, on r�cup�re la date du bulletin associ�
				$reqAnneeArticle = "SELECT date_date FROM bulletins, analysis WHERE analysis_bulletin=bulletin_id AND analysis_notice='".$mesNotices['notice_id']."'";
				$queryArt=pmb_mysql_query($reqAnneeArticle);
				
				if(!pmb_mysql_num_rows($queryArt)) $dateArt = "";
				else $dateArt=pmb_mysql_result($queryArt,0,0);
							
				if($dateArt == '0000-00-00' || !isset($dateArt) || $dateArt == "") $annee_art_tmp = "";
					else $annee_art_tmp = substr($dateArt,0,4);

				//On met � jour, les notices avec la date de parution et l'ann�e
				$reqMajArt = "UPDATE notices SET date_parution='".$dateArt."', year='".$annee_art_tmp."', update_date=update_date
							WHERE notice_id='".$mesNotices['notice_id']."'";
		        pmb_mysql_query($reqMajArt);
			    break;	
				
			case 'b2': 
				//Si c'est une notice de bulletin, on r�cup�re la date pour connaitre l'ann�e						
				$reqAnneeBulletin = "SELECT date_date FROM bulletins WHERE num_notice='".$mesNotices['notice_id']."'";
				$queryAnnee=pmb_mysql_query($reqAnneeBulletin);
				
				if(!pmb_mysql_num_rows($queryAnnee)) $dateBulletin="";
				else $dateBulletin = pmb_mysql_result($queryAnnee,0,0);
				
				if($dateBulletin == '0000-00-00' || !isset($dateBulletin) || $dateBulletin == "") $annee_tmp = "";
				else $annee_tmp = substr($dateBulletin,0,4);
				
				//On met � jour date de parution et ann�e
				$reqMajBull = "UPDATE notices SET date_parution='".$dateBulletin."', year='".$annee_tmp."', update_date=update_date
						WHERE notice_id='".$mesNotices['notice_id']."'";
	    		pmb_mysql_query($reqMajBull);
				
				break;
				
			default:
				// Mise � jour du champ date_parution des notices (monographie et p�rio)
				$date_parution = notice::get_date_parution($mesNotices['year']);
		    	$reqMaj = "UPDATE notices SET date_parution='".$date_parution."', update_date=update_date WHERE notice_id='".$mesNotices['notice_id']."'";
		    	pmb_mysql_query($reqMaj);
		    	break;
		}    	           		   	
	}
	pmb_mysql_free_result($query);

	$next = $start + $lot;
	print netbase::get_current_state_form($v_state, $spec, '', $next, $count);
} else {
	$spec = $spec - GEN_DATE_TRI;
	$not = pmb_mysql_query("SELECT count(1) FROM notices");
	$compte = pmb_mysql_result($not, 0, 0);
	$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg['gen_date_tri_msg'], ENT_QUOTES, $charset)." : ";
	$v_state .= $compte." ".htmlentities($msg['gen_date_tri_msg'], ENT_QUOTES, $charset);
	print netbase::get_process_state_form($v_state, $spec);
}