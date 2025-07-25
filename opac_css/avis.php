<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// � 2006 mental works / www.mental-works.com contact@mental-works.com
// 	compl�tement repris et corrig� par PMB Services
// +-------------------------------------------------+
// $Id: avis.php,v 1.55.6.1 2023/04/18 13:10:16 qvarin Exp $

use Pmb\Common\Library\CSRF\ParserCSRF;

$base_path=".";
require_once($base_path."/includes/init.inc.php");

//fichiers n�cessaires au bon fonctionnement de l'environnement
require_once($base_path."/includes/common_includes.inc.php");

require_once($base_path.'/includes/templates/common.tpl.php');

// classe de gestion des cat�gories
require_once($base_path.'/classes/categorie.class.php');
require_once($base_path.'/classes/notice.class.php');
require_once($base_path.'/classes/notice_display.class.php');

// classe indexation interne
require_once($base_path.'/classes/indexint.class.php');

// classe de gestion des r�servations
require_once($base_path.'/classes/resa.class.php');

require_once($base_path.'/classes/cms/cms_article.class.php');

// pour l'affichage correct des notices
require_once($base_path."/includes/templates/notice.tpl.php");
require_once($base_path."/includes/navbar.inc.php");
require_once($base_path."/includes/explnum.inc.php");
require_once($base_path."/includes/notice_affichage.inc.php");
require_once($base_path."/includes/bulletin_affichage.inc.php");

require_once($base_path."/includes/connexion_empr.inc.php");

// autenticazione LDAP - by MaxMan
require_once($base_path."/includes/ldap_auth.inc.php");

// RSS
require_once($base_path."/includes/includes_rss.inc.php");

// pour fonction de formulaire de connexion
require_once($base_path."/includes/empr.inc.php");
// pour fonction de v�rification de connexion
require_once($base_path.'/includes/empr_func.inc.php');
require_once ($include_path."/interpreter/bbcode.inc.php");

if ($opac_avis_allow==0) die("");
// par d�faut, on suppose que le droit donn� par le statut est Ok
$allow_avis = 1 ;
$allow_tag = 1 ;

if (($todo=='liste' || !$todo) && ($opac_avis_allow==3)) {
	//consultation possible sans authentification
	$log_ok = 1;
} else {
	//V�rification de la session
	$empty_pwd=true;
	$ext_auth=false;
	// si param�trage authentification particuli�re et pour le re-authentification ntlm
	if (file_exists($base_path.'/includes/ext_auth.inc.php')) require_once($base_path.'/includes/ext_auth.inc.php');
	$log_ok=connexion_empr();
}

$allow_avis_ajout=true;
// on a tout v�rifi� mais si tout est libre alors on force le log_ok � 1
if ($opac_avis_allow==3) {
	$log_ok=1;
	$allow_avis=1;
}
if ($opac_avis_allow==1 && !$log_ok) {
	$allow_avis_ajout=false ;
}
// La consultation d'avis est autoris� mais son statut bloque...
if ($opac_avis_allow>0 && $allow_avis==0) {
	$log_ok=1;
	$allow_avis=1;
	$allow_avis_ajout=false ;
}

// pour template des avis
require_once($base_path.'/includes/templates/avis.tpl.php');

ob_start();

print $popup_header;

if ($opac_avis_allow && !$allow_avis) die($popup_footer);

print $avis_tpl_header ;

if (!isset($noticeid)) $noticeid = 0;
if (!isset($articleid)) $articleid = 0;
if (!isset($sectionid)) $sectionid = 0;

switch($todo) {
	case 'liste' :
	default:
		if($noticeid) {
			if ($opac_notice_affichage_class) $notice_affichage=$opac_notice_affichage_class; else $notice_affichage="notice_affichage";
			$notice=new $notice_affichage($noticeid);
			print $notice->avis_detail();
		}
		if($articleid) {
			$cms_article = new cms_article($articleid);
			print $cms_article->get_display_avis_detail();
		}
		if($sectionid) {
			$cms_section = new cms_section($sectionid);
			print $cms_section->get_display_avis_detail();
		}
		break;
	}

if (!$log_ok && $opac_avis_allow==2) {
	$lvl='avis_'.$todo;
	print do_formulaire_connexion();
}

//Enregistrement du log
global $pmb_logs_activate;
if($pmb_logs_activate){
	global $log;
	$log->add_log('num_session',session_id());
	$log->save();
}

print $popup_footer;

$htmltoparse = ob_get_contents();
ob_end_clean();

$parserCSRF = new ParserCSRF();
print $parserCSRF->parseHTML($htmltoparse);

/* Fermeture de la connexion */
pmb_mysql_close($dbh);
