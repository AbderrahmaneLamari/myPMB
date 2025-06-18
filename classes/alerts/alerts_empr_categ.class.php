<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_empr_categ.class.php,v 1.1 2020/12/24 11:01:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_empr_categ extends alerts {
	
	protected function get_module() {
		return 'edit';
	}
	
	protected function get_section() {
		return 'empr_categ_alert';
	}
	
	protected function fetch_data() {
		global $deflt2docs_location,$pmb_lecteurs_localises;
		
		$this->data = array();
		
		// comptage des emprunteurs qui n'ont pas le droit d'être dans la catégorie
		$query = "select 1 from empr left join empr_categ on empr_categ = id_categ_empr ";
		$query .=" where ((((age_min<> 0) || (age_max <> 0)) && (age_max >= age_min)) && (((DATE_FORMAT( curdate() , '%Y' )-empr_year) < age_min) || ((DATE_FORMAT( curdate() , '%Y' )-empr_year) > age_max)))";
		// restriction localisation le cas échéant
		if ($pmb_lecteurs_localises) {
			$query .= " AND empr_location='$deflt2docs_location' ";
		}
		if($this->is_num_rows_from_query($query)) {
			$this->add_data('empr', 'empr_change_categ_todo', 'categ_change');
		}
	}
	
}