<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_sphinx.class.php,v 1.1 2020/12/24 11:01:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_sphinx extends alerts {
	
	protected function get_module() {
		return 'sphinx';
	}
	
	protected function get_section() {
		return 'param_sphinx';
	}
	
	protected function fetch_data() {
		
		$this->data = array();
		
		$sphinx_message = check_sphinx_service();
		if ($sphinx_message) {
			$this->add_data('', $sphinx_message);
// 			$aff_alerte .= "<ul>" . $msg['param_sphinx'] . "<li><a href='#'>$sphinx_message</a></li></ul>";
		}
	}
}