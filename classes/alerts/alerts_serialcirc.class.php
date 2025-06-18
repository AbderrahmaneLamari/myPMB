<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_serialcirc.class.php,v 1.1 2020/12/24 11:01:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_serialcirc extends alerts {
	
	protected function get_module() {
		return 'catalog';
	}
	
	protected function get_section() {
		return 'menu_alert_demande_abo';
	}
	
	protected function fetch_data() {
		$this->data = array();
		
		$query="SELECT * FROM serialcirc_ask WHERE serialcirc_ask_statut=0";
		if($this->is_num_rows_from_query($query)) {
			$this->add_data('serials', 'alert_demande_abo', 'circ_ask');
		}
	}
}