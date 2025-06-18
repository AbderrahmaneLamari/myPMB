<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_registrations.class.php,v 1.2 2021/04/07 14:35:29 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_registrations extends alerts {
	
	protected function get_module() {
		return 'animations';
	}
	
	protected function get_section() {
		return 'alerte_registrations';
	}
	
	protected function fetch_data() {
		$this->data = [];
		
		// Inscriptions à valider
		$query = " SELECT 1 FROM anim_registrations WHERE num_registration_status = 1 LIMIT 1";
		if ($this->is_count_from_query($query)) {
			$this->add_data('registration', 'alerte_registration_waiting', '', '&action=list&num_status=1');
		}
	}
}