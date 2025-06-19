<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_resa.class.php,v 1.2 2022/02/11 09:39:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_resa extends alerts {
	
	protected function get_module() {
		return 'circ';
	}
	
	protected function get_section() {
		return 'resa_menu_alert';
	}
	
	protected function fetch_data() {
		$this->data = array();
		$this->resa_a_traiter();
		$this->resa_a_ranger();
		$this->resa_depassees_a_traiter();
		$this->resa_planning_a_traiter();
	}
	
	public function resa_a_traiter() {
		global $pmb_transferts_actif,$transferts_choix_lieu_opac,$deflt_docs_location, $pmb_location_reservation,$transferts_site_fixe,$pmb_lecteurs_localises;
		
		$query="SELECT resa_idnotice, resa_idbulletin FROM resa, exemplaires, docs_statut  WHERE (resa_cb is null OR resa_cb='')
		and resa_idnotice=expl_notice and resa_idbulletin=expl_bulletin
		and expl_statut=idstatut AND pret_flag=1
		limit 1";
		
		if($pmb_lecteurs_localises && $deflt_docs_location){
			$query="SELECT resa_idnotice, resa_idbulletin FROM resa, exemplaires, docs_statut  WHERE (resa_cb is null OR resa_cb='')
			and resa_idnotice=expl_notice and resa_idbulletin=expl_bulletin
			and expl_location='".$deflt_docs_location."'
			and expl_statut=idstatut AND pret_flag=1
			limit 1";
		}
		// respecter les droits de réservation du lecteur
		if($pmb_location_reservation) {
			$query="SELECT resa_idnotice, resa_idbulletin FROM resa, empr, resa_loc, exemplaires , docs_statut WHERE
			resa_idnotice=expl_notice and resa_idbulletin=expl_bulletin
			and expl_location='".$deflt_docs_location."'
			and	expl_statut=idstatut AND pret_flag=1
			and	resa_idempr = id_empr AND (resa_cb is null OR resa_cb='')
			and empr_location=resa_emprloc and resa_loc='$deflt_docs_location'
			limit 1";
		}
		
		if ($pmb_transferts_actif=="1") {
			switch ($transferts_choix_lieu_opac) {
				case "1":
					//retrait de la resa sur lieu choisi par le lecteur
					$query="SELECT resa_idnotice, resa_idbulletin FROM resa, empr WHERE resa_idempr = id_empr AND (resa_cb is null OR resa_cb='') AND resa_loc_retrait='".$deflt_docs_location."' limit 1";
					break;
				case "2":
					//retrait de la resa sur lieu fixé
					if ($deflt_docs_location==$transferts_site_fixe)
						$query="SELECT resa_idnotice, resa_idbulletin FROM resa WHERE (resa_cb is null OR resa_cb='') limit 1";
						else return "";
						
						break;
				case "3":
					//retrait de la resa sur lieu exemplaire
					// respecter les droits de réservation du lecteur
					if($pmb_location_reservation)
						$query = "select resa_idnotice, resa_idbulletin from resa, exemplaires,empr, resa_loc where resa_idempr = id_empr AND (resa_cb is null OR resa_cb='') and empr_location=resa_emprloc and resa_loc='$deflt_docs_location' and
						resa_idnotice=expl_notice and resa_idbulletin=expl_bulletin and expl_location=resa_loc limit 1";
						else
							$query = "select resa_idnotice, resa_idbulletin from resa, exemplaires,empr where resa_idempr = id_empr AND (resa_cb is null OR resa_cb='') and
						resa_idnotice=expl_notice and resa_idbulletin=expl_bulletin and expl_location='".$deflt_docs_location."' limit 1";
							break;
				default:
					//retrait de la resa sur lieu lecteur
					$query="SELECT resa_idnotice, resa_idbulletin FROM resa, empr WHERE resa_idempr = id_empr AND (resa_cb is null OR resa_cb='') AND empr_location='".$deflt_docs_location."' limit 1";
					break;
			}
		}
		if($this->is_num_rows_from_query($query)) {
			$this->add_data('listeresa', 'resa_menu_a_traiter', 'encours');
		}
	}
	
	public function resa_a_ranger() {
		global $deflt_docs_location;
		
		$query="SELECT count(1) from resa_ranger,exemplaires where resa_cb=expl_cb and expl_location='$deflt_docs_location' limit 1 ";
		if ($this->is_count_from_query($query)) {
			$this->add_data('listeresa', 'resa_menu_a_ranger', 'docranger');
		}
	}
	
	public function resa_depassees_a_traiter() {
		global $pmb_transferts_actif, $deflt_docs_location,$transferts_choix_lieu_opac;
		
		$query="SELECT 1 FROM resa, empr WHERE resa_idempr = id_empr AND resa_date_fin < CURDATE() and resa_date_fin <>  '0000-00-00' ";
		if ($pmb_transferts_actif=="1") {
			switch ($transferts_choix_lieu_opac) {
				case "1":
					//retrait de la resa sur lieu choisi par le lecteur
					$query .= " AND resa_loc_retrait='".$deflt_docs_location."' ";
					break;
				case "2":
					//retrait de la resa sur lieu fixé
					break;
				case "3":
					//retrait de la resa sur lieu exemplaire
					break;
				default:
					//retrait de la resa sur lieu lecteur
					$query .= " AND empr_location='".$deflt_docs_location."' ";
					break;
			}
			
		}
		
		// comptage des résas dépassées
		//$query="SELECT 1 FROM resa where resa_date_fin < CURDATE() and resa_date_fin <> '0000-00-00' limit 1 ";
		if ($this->is_num_rows_from_query($query)) {
			$this->add_data('listeresa', 'resa_menu_a_depassees', 'depassee');
		}
	}
	
	public function resa_planning_a_traiter() {
		global $pmb_resa_planning, $pmb_resa_planning_toresa, $deflt_resas_location, $deflt_docs_location;
		
		if($pmb_resa_planning) {
			$pmb_resa_planning_toresa = intval($pmb_resa_planning_toresa);
			if ($deflt_resas_location) {
				$expl_loc = $deflt_resas_location;
			} else {
				$expl_loc = $deflt_docs_location;
			}
			$query = "SELECT count(*) ";
			$query.= "FROM resa_planning ";
			$query.= "WHERE resa_remaining_qty!=0 ";
			$query.= "and resa_validee=0 ";
			$query.= "and resa_loc_retrait = $expl_loc ";
			$query.= "and datediff(resa_date_debut, curdate()) <= ".$pmb_resa_planning_toresa;
			
			if ($this->is_count_from_query($query)) {
				$this->add_data('resa_planning', 'resa_planning_to_validate', 'all', '&resa_planning_circ_ui_montrerquoi=invalidees');
			}
			
			$query = "SELECT count(*) ";
			$query.= "FROM resa_planning ";
			$query.= "WHERE resa_remaining_qty!=0 ";
			$query.= "and resa_validee=1 ";
			$query.= "and resa_loc_retrait = $expl_loc ";
			$query.= "and datediff(resa_date_debut, curdate()) <= ".$pmb_resa_planning_toresa;
			
			if ($this->is_count_from_query($query)) {
				$this->add_data('resa_planning', 'resa_planning_todo', 'all');
			}
		}
	}
}