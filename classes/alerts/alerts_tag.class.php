<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_tag.class.php,v 1.1 2020/12/24 11:01:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_tag extends alerts {
	
	protected function get_module() {
		return 'catalog';
	}
	
	protected function get_section() {
		return 'alerte_avis_tag';
	}
	
	protected function fetch_data() {
		global $opac_allow_add_tag;
		global $opac_avis_allow;
		
		$this->data = array();
		
		if ($opac_allow_add_tag) {
			// comptage des tags à valider
			$query = " SELECT 1 FROM tags limit 1";
			if($this->is_num_rows_from_query($query)) {
				$this->add_data('tags', 'alerte_tag_a_valider');
			}
		}
		
		if ($opac_avis_allow) {
			// comptage des avis à valider
			$query = " SELECT 1 FROM avis where valide=0 limit 1";
			if($this->is_num_rows_from_query($query)) {
				$this->add_data('avis', 'alerte_avis_a_valider');
			}
		}
	}
}