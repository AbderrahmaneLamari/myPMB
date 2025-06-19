<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_mails_configuration.class.php,v 1.1 2023/01/19 14:57:47 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_mails_configuration extends alerts {
	
	protected function get_module() {
		return 'admin';
	}
	
	protected function get_section() {
		return 'mails_configuration';
	}
	
	protected function fetch_data() {
		$this->data = array();
		
		//pour les mails non configurés
		$cpt_mails = $this->cpt_mails("mail_configuration_validated = 0");
		if ($cpt_mails != 0) {
			$this->add_data('mails', 'mail_configuration_unvalidated', 'configuration');
		}
	}
	
	//fonction pour compter les transferts
	protected function cpt_mails($clause_where) {
		$query = 	"SELECT 1 " .
				"FROM mails_configuration " .
				"WHERE " . $clause_where . " " .
				"LIMIT 1";
		return $this->is_num_rows_from_query($query);
	}
}