<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: exemplaire.class.php,v 1.1.10.1 2021/07/28 13:33:17 rtigero Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class exemplaire {
	
	// Donne l'id de la notice par son identifiant d'expl
	public static function get_expl_notice_from_id($expl_id=0) {
		$expl_id += 0;
		$query = "select expl_notice, expl_bulletin from exemplaires where expl_id = ".$expl_id;
		$result = pmb_mysql_query($query);
		$row = pmb_mysql_fetch_object($result);
		if($row->expl_notice) {
			return $row->expl_notice;
		} else {
			$query = "select num_notice from bulletins where bulletin_id = ".$row->expl_bulletin;
			$result = pmb_mysql_query($query);
			return pmb_mysql_result($result, 0, 'num_notice');				
		}
	}
	
	// Donne l'id du bulletin par son identifiant d'expl
	public static function get_expl_bulletin_from_id($expl_id=0) {
		$expl_id += 0;
		$query = "select expl_bulletin from exemplaires where expl_id = ".$expl_id;
		$result = pmb_mysql_query($query);
		return pmb_mysql_result($result, 0, 'expl_bulletin');
	}
	
	/**
	 * retourne un ISBD de l'exemplaire fourni en paramètre
	 * @param number $expl_id
	 * @return string
	 */
	public static function get_expl_isbd($expl_id = 0) {
	    global $msg;
	    
	    $query = "SELECT expl_cb, expl_cote, expl_notice FROM exemplaires WHERE expl_id = ".$expl_id;
	    $result = pmb_mysql_query($query);
	    if(pmb_mysql_num_rows($result)){
    	    $expl = pmb_mysql_fetch_object($result);
    	    
    	    $mono_display = new notice_affichage($expl->expl_notice);
    	    $mono_display->do_isbd_simple();
    
    	    return "$expl->expl_cote {$msg['title_separator']}{$msg['number']} $expl->expl_cb {$msg['title_separator']}" . $mono_display->notice_isbd_simple;
	    }
	    return "";
	}
}                             
