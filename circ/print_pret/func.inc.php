<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func.inc.php,v 1.6.8.1 2021/12/09 09:04:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path;
require_once("$class_path/mono_display.class.php");
require_once("$class_path/serial_display.class.php");
require_once("$class_path/emprunteur.class.php");


function get_info_empr($id_empr) {
	$info_empr=new emprunteur ($id_empr);
	
	return $info_empr;
}
function get_info_expl_old($cb_expl) {
	global $msg;
	
	$info_expl = new stdClass();
	if ($cb_expl) {
		$query = "select * from exemplaires  left join docs_type on exemplaires.expl_typdoc=docs_type.idtyp_doc where expl_cb='$cb_expl' ";		
		$result = pmb_mysql_query($query);
		if (($r= pmb_mysql_fetch_array($result))) {
			$info_expl->error_message="";	
			// empr ok	
			$info_expl->id_expl = $r['expl_id'];
			$info_expl->cb_expl = $r['expl_cb'];
			$info_expl->tdoc_libelle = $r['tdoc_libelle'];
			$info_expl->expl_notice = $r['expl_notice'];
			if ($info_expl->expl_notice) {
				$notice = new mono_display($info_expl->expl_notice, 0);
				$info_expl->libelle = $notice->header;
			} else {
				$bulletin = new bulletinage_display( $r['expl_bulletin']);
				$info_expl->libelle = $bulletin->display ;
				$info_expl->expl_notice = $r['expl_bulletin'];
			}
			$pos=strpos($info_expl->libelle,'<a');
			if($pos) $info_expl->libelle = substr($info_expl->libelle,0,strpos($info_expl->libelle,'<a'));		
							
		} else {
			$info_expl->error_message=$msg[367];
		}
	} else {
		$info_expl->error_message=$msg[367];
	}
	return $info_expl;
}

function get_info_expl($cb_doc) {
	global $msg;
	
	$query = "SELECT notices_m.notice_id as m_id, notices_s.notice_id as s_id, expl_cb, expl_cote, expl_location,pret_date, pret_retour, tdoc_libelle, section_libelle, location_libelle, trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if (mention_date, concat(' (',mention_date,')') ,''))) as tit, ";
	$query.= " date_format(pret_date, '".$msg["format_date"]."') as aff_pret_date, ";
	$query.= " date_format(pret_retour, '".$msg["format_date"]."') as aff_pret_retour, "; 
	$query.= " IF(pret_retour>sysdate(),0,1) as retard, notices_m.tparent_id, notices_m.tnvol " ; 
	$query.= " FROM (((exemplaires LEFT JOIN notices AS notices_m ON expl_notice = notices_m.notice_id ) LEFT JOIN bulletins ON expl_bulletin = bulletins.bulletin_id) LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), docs_type, docs_section, docs_location, pret ";
	$query.= " WHERE expl_cb='".$cb_doc."' and expl_typdoc = idtyp_doc and expl_section = idsection and expl_location = idlocation and pret_idexpl = expl_id  ";

	$result = pmb_mysql_query($query);
	$expl = pmb_mysql_fetch_object($result);
	
	$responsabilites = get_notice_authors(($expl->m_id+$expl->s_id)) ;
	$header_aut= gen_authors_header($responsabilites);
	$expl->header_aut=$header_aut;
	// récupération du titre de série
	if ($expl->tparent_id && $expl->m_id) {
		$parent = new serie($expl->tparent_id);
		$tit_serie = $parent->name;
		if($expl->tnvol)
			$tit_serie .= ', '.$expl->tnvol;
	}
	if($tit_serie) {
		$expl->tit = $tit_serie.'. '.$expl->tit;
	}
	return $expl;

} /* fin get_info_expl */
?>