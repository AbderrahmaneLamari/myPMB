<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_bulletinage.class.php,v 1.1 2020/12/24 11:01:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_bulletinage extends alerts {
	
	protected function get_module() {
		return 'catalog';
	}
	
	protected function get_section() {
		return 'pointage_menu_pointage';
	}
	
	protected function fetch_data() {
		global $deflt_bulletinage_location;
		
		$this->data = array();
		
		// comptage des abonnements à renouveler
		$query = "SELECT count(*) as total FROM abts_abts WHERE date_fin BETWEEN CURDATE() AND  DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
		if($deflt_bulletinage_location) {
			$query .= " AND location_id='".$deflt_bulletinage_location."'";
		}
		if ($this->is_count_from_query($query)) {
			$this->add_data('serials', 'abonnements_to_renew', 'pointage');
		}
		// comptage des abonnements dépassés
		$query = "SELECT count(*) as total FROM abts_abts WHERE date_fin < CURDATE()";
		if($deflt_bulletinage_location) {
			$query .= " AND location_id='".$deflt_bulletinage_location."'";
		}
		if ($this->is_count_from_query($query)) {
			$this->add_data('serials', 'abonnements_outdated', 'pointage');
		}
	}
}