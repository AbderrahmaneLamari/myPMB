<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mail_reader_loans_late_relance.class.php,v 1.3 2020/11/26 13:56:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/mail/reader/loans/mail_reader_loans_late.class.php");

class mail_reader_loans_late_relance extends mail_reader_loans_late {
	
	protected function get_mail_expl_content($expl_cb) {
	    global $msg;
	    
	    $mail_expl_content = '';
	    
	    $expl = $this->get_expl_informations($expl_cb);
	    
	    $header_aut = "" ;
	    $responsabilites = get_notice_authors(($expl->m_id+$expl->s_id)) ;
	    $header_aut = gen_authors_header($responsabilites);
	    $header_aut ? $auteur=" / ".$header_aut : $auteur="";
	    
	    // récupération du titre de série
	    $tit_serie="";
	    if ($expl->tparent_id && $expl->m_id) {
	        $parent = new serie($expl->tparent_id);
	        $tit_serie = $parent->name;
	        if ($expl->tnvol)
	            $tit_serie .= ', '.$expl->tnvol;
	    }
	    if ($tit_serie) {
	        $expl->tit = $tit_serie.'. '.$expl->tit;
	    }
	    
	    $mail_expl_content.=$expl->tit.$auteur."\r\n";
	    $mail_expl_content.="    -".sprintf($msg["relance_mail_retard_dates"],$expl->aff_pret_date,$expl->aff_pret_retour);
	    return $mail_expl_content;
	}
	
	protected function get_fine_from_expl_data($data) {
		$fine=0;
		$amende=new amende($data["pret_idempr"]);
		$amd=$amende->get_amende($data["expl_id"]);
		if ($amd["valeur"]) {
			$fine=$amd["valeur"];
		}
		return $fine;
	}
	
	protected function get_data_from_cb_expl($cb_expl, $id_empr) {
		$query = "select expl_id from exemplaires where expl_cb='".$cb_expl."'";
		$result = pmb_mysql_query($query);
		return array(
				'expl_cb' => $cb_expl,
				'pret_idempr' => $id_empr,
				'expl_id' => pmb_mysql_result($result, '0', 'expl_id')
		);
	}
	
	protected function get_mail_content($id_empr=0, $id_groupe=0) {
		global $msg;
		global $mailretard_hide_fine;
		global $niveau;
		
		$mail_content = '';
		if($this->get_parameter_value('madame_monsieur')) {
			$mail_content .= $this->get_parameter_value('madame_monsieur')."\r\n\r\n";
		}
		if($this->get_parameter_value('before_list')) {
			$mail_content .= $this->get_parameter_value('before_list')."\r\n\r\n";
		}
		
		//Récupération des exemplaires
		$expl_list = $this->get_expl_list($id_empr);
		
		$total_amendes=0;
		
		//Calcul des frais de relance
		$id_compte=comptes::get_compte_id_from_empr($id_empr,2);
		if ($id_compte) {
		    $cpte=new comptes($id_compte);
		    $frais_relance=$cpte->summarize_transactions("","",0,$realisee=-1);
		    if ($frais_relance<0) $frais_relance=-$frais_relance; else $frais_relance=0;
		}
		
		if($niveau!=3) {
			foreach ($expl_list as $data) {
				//Calcul des amendes
				$valeur=$this->get_fine_from_expl_data($data);
				$total_amendes+=$valeur; 
				$mail_content .= $this->get_mail_expl_content($data['expl_cb']);
				if ($valeur && !$mailretard_hide_fine) $mail_content.=" ".sprintf($msg["relance_mail_retard_amende"],comptes::format_simple($valeur));
				$mail_content.="\r\n\r\n";
			}
		} else {
			$liste_r = $expl_list['r'];
			$liste_r3 = $expl_list['r3'];
			if(is_array($liste_r) && count($liste_r)) {
				// Il y a des retard simple: on affiche d'abord les retards simples
				foreach($liste_r as $cb_expl) {
					//Calcul des amendes
					$data = $this->get_data_from_cb_expl($cb_expl, $id_empr);
					$valeur=$this->get_fine_from_expl_data($data);
					$total_amendes+=$valeur;
					$mail_content .= $this->get_mail_expl_content($cb_expl);
					if ($valeur && !$mailretard_hide_fine) $mail_content.=" ".sprintf($msg["relance_mail_retard_amende"],comptes::format_simple($valeur));
					$mail_content.="\r\n\r\n";
				}
				if($this->get_parameter_value('before_recouvrement')) {
					$mail_content .= $this->get_parameter_value('before_recouvrement')."\r\n\r\n";
				}
				// affiche retards niveau 3
				if(isset($liste_r3)){
					foreach($liste_r3 as $cb_expl) {
						//Calcul des amendes
						$data = $this->get_data_from_cb_expl($cb_expl, $id_empr);
						$valeur=$this->get_fine_from_expl_data($data);
						$total_amendes+=$valeur;
						$mail_content .= $this->get_mail_expl_content($cb_expl);
						if ($valeur && !$mailretard_hide_fine) $mail_content.=" ".sprintf($msg["relance_mail_retard_amende"],comptes::format_simple($valeur));
						$mail_content.="\r\n\r\n";
					}
				}
			} else {
				// il n'y a que des retards niveau 3
				if(isset($liste_r3)){
					foreach($liste_r3 as $cb_expl) {
						//Calcul des amendes
						$data = $this->get_data_from_cb_expl($cb_expl, $id_empr);
						$valeur=$this->get_fine_from_expl_data($data);
						$total_amendes+=$valeur;
						$mail_content .= $this->get_mail_expl_content($cb_expl);
						if ($valeur && !$mailretard_hide_fine) $mail_content.=" ".sprintf($msg["relance_mail_retard_amende"],comptes::format_simple($valeur));
						$mail_content.="\r\n\r\n";
					}
				}
				if($this->get_parameter_value('after_recouvrement')) {
					$mail_content .= $this->get_parameter_value('after_recouvrement')."\r\n\r\n";
				}
			}
		}
		
		if (!$mailretard_hide_fine) {
			if ($total_amendes) $mail_content.= sprintf($msg["relance_mail_retard_total_amendes"],comptes::format_simple($total_amendes))."\r\n";
		    
			if ($frais_relance) $mail_content.= $msg["relance_lettre_retard_frais_relance"].comptes::format_simple($frais_relance)."\r\n";
		    
			if (($frais_relance)&&($total_amendes)) $mail_content.= $msg["relance_lettre_retard_total_du"].comptes::format_simple($total_amendes+$frais_relance)."\r\n";
			if (($frais_relance)||($total_amendes)) $mail_content.= "\r\n";
		}
		
		if($this->get_parameter_value('after_list')) {
			$mail_content .= $this->get_parameter_value('after_list')."\r\n\r\n";
		}
		if($this->get_parameter_value('fdp')) {
			$mail_content .= $this->get_parameter_value('fdp')."\r\n\r\n";
		}
		$mail_content .= $this->get_mail_bloc_adresse() ;
		return $mail_content;
	}
	
	public function send_mail($id_empr=0, $id_groupe=0) {
	    global $charset;
	    global $biblio_name, $biblio_email, $PMBuseremailbcc;
	    
	    //Tableau contenant le destinataire (emprunteur) ou les destinataires (tous les responsables de groupe dont l'emprunteur est membre)
	    $to_nom = array();
	    $to_mail = array();
	    
	    /* Récupération du nom, prénom et mail du lecteur concerné */
	    $requete="select id_empr, empr_mail, empr_nom, empr_prenom, empr_cb from empr where id_empr=$id_empr";
	    $res=pmb_mysql_query($requete);
	    $coords=pmb_mysql_fetch_object($res);
	    $to_nom[0] = $coords->empr_prenom." ".$coords->empr_nom;
	    $to_mail[0] = $coords->empr_mail;
	    
	    //Si mail de rappel affecté au responsable du groupe : on envoie à tous les responsables des groupes (concernés par l'emprunteur)
	    $requete="select id_groupe,resp_groupe from groupe,empr_groupe where id_groupe=groupe_id and empr_id=$id_empr and resp_groupe and mail_rappel";
	    $res=pmb_mysql_query($requete);
	    if(pmb_mysql_num_rows($res) > 0) {
	        $qt_to = 0;
	        while ($row = pmb_mysql_fetch_object($res)) {
	            $requete="select id_empr, empr_mail, empr_nom, empr_prenom from empr where id_empr='".$row->resp_groupe."'";
	            $result=pmb_mysql_query($requete);
	            $coords_dest=pmb_mysql_fetch_object($result);
	            $to_nom[$qt_to] = $coords_dest->empr_prenom." ".$coords_dest->empr_nom;
	            $to_mail[$qt_to] = $coords_dest->empr_mail;
	            $qt_to++;
	        }
	    }
	    
	    $headers = "Content-type: text/plain; charset=".$charset."\n";
	    $mail_content = $this->get_mail_content($id_empr, $id_groupe);
	    
	    //remplacement nom et prenom
	    $mail_content=str_replace("!!empr_name!!", $coords->empr_nom,$mail_content);
	    $mail_content=str_replace("!!empr_first_name!!", $coords->empr_prenom,$mail_content);
	    
	    // function mailpmb($to_nom="", $to_mail, $obj="", $corps="", $from_name="", $from_mail, $headers, $copie_CC="", $copie_BCC="", $faire_nl2br=0, $pieces_jointes=array()) {
	    $flag_res = false;
	    //On boucle si plusieurs destinataires
	    foreach ($to_nom as $key=>$dummy_value) {
	        if(mailpmb($dummy_value, $to_mail[$key], $this->get_mail_object()." : ".$coords->empr_prenom." ".mb_strtoupper($coords->empr_nom,$charset)." (".$coords->empr_cb.")",$mail_content,$biblio_name, $biblio_email,$headers, "", $PMBuseremailbcc, 1)){
	            $flag_res = true;
	        }
	    }
	    //Il faut au moins un email bien envoyé pour retourner true.
	    return $flag_res;
	}
}