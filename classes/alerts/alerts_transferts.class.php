<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_transferts.class.php,v 1.2 2021/01/05 13:30:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_transferts extends alerts {
	
	protected function get_module() {
		return 'circ';
	}
	
	protected function get_section() {
		return 'alerte_avis_transferts';
	}
	
	protected function fetch_data() {
		global $deflt_docs_location, $transferts_nb_jours_alerte, $transferts_regroupement_depart;
		
		$this->data = array();
		
		//pour les validations
		$cpt_validations = $this->cpt_transferts("etat_transfert=0 AND etat_demande=0 AND num_location_source =" . $deflt_docs_location );
		if ($cpt_validations != 0) {
			$this->add_data('trans', 'alerte_transferts_validation', 'valid');
		}
		//pour les envois
		$cpt_envois = $this->cpt_transferts("etat_transfert=0 AND etat_demande=1 AND num_location_source =" . $deflt_docs_location );
		if ($cpt_envois != 0) {
			$this->add_data('trans', 'alerte_transferts_envoi', 'envoi');
		}
				
		//pour les receptions
		$cpt_receptions = $this->cpt_transferts("etat_transfert=0 AND etat_demande=2 AND num_location_dest =" . $deflt_docs_location );
		if ($cpt_receptions != 0) {
			$this->add_data('trans', 'alerte_transferts_reception', 'recep');
		}
					
		//pour les retours
		$cpt_retours = $this->cpt_transferts("etat_transfert=0 AND type_transfert=1 AND etat_demande=3 AND num_location_dest =" . $deflt_docs_location  . " AND DATE_ADD(date_retour,INTERVAL -" . $transferts_nb_jours_alerte . " DAY)<=CURDATE()");
		if ($cpt_retours != 0) {
			$this->add_data('trans', 'alerte_transferts_retours', 'retour');
		}
						
		//pour les départs ((Validation, envoi, retour)
		if($transferts_regroupement_depart){
			$cpt_departs = $cpt_validations + $cpt_envois + $cpt_retours;
			if ($cpt_departs != 0) {
				$this->add_data('trans', 'alerte_transferts_depart', 'departs');
			}
		}
			
		//pour les refus
		$cpt_refus = $this->cpt_transferts("etat_transfert=0 AND type_transfert=1 AND etat_demande=4 AND num_location_dest =" . $deflt_docs_location);
		if ($cpt_refus != 0) {
			$this->add_data('trans', 'alerte_transferts_refus', 'refus');
		}
	}
	
	//fonction pour compter les transferts
	protected function cpt_transferts($clause_where) {
		$query = 	"SELECT 1 " .
				"FROM transferts " .
				"INNER JOIN transferts_demande ON id_transfert = num_transfert " .
				"WHERE " . $clause_where . " " .
				"LIMIT 1";
		return $this->is_num_rows_from_query($query);
	}
}