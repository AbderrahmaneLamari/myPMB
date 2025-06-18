<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_demandes.class.php,v 1.1 2020/12/24 11:01:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_demandes extends alerts {
	
	protected function get_module() {
		return 'demandes';
	}
	
	protected function get_section() {
		return 'alerte_demandes';
	}
	
	protected function fetch_data() {
		$this->data = array();
		
		// comptage des demandes à valider
		$query = " SELECT 1 FROM demandes where etat_demande=1 limit 1";
		if($this->is_num_rows_from_query($query)) {
			$this->add_data('list', 'alerte_demandes_traiter', '', '&idetat=1');
		}
	}
	
}