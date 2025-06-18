<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_parameters_pdf_ui.class.php,v 1.3.2.1 2021/06/30 07:45:10 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_parameters_pdf_ui extends list_parameters_ui {
	
	public function init_filters($filters=array()) {
		$filters['types_param'] = static::get_types_param();
		parent::init_filters($filters);
	}
	
	public static function get_types_param() {
	    return array(
	        'acquisition_pdfliv',
	        'acquisition_pdffac',
	        'pdflettreloansgroup',
	        'pdflettreretard',
	        'pdflettreloans',
	        'pdflettreticket',
	        'pdflettreresa',
	        'pdflettreadhesion',
	        'acquisition_pdfsug',
	    );
	}
	
	public static function get_sstypes_param_is_translated() {
	    return array(
	        'before_list',
	        'after_list',
	        'fdp',
	        'madame_monsieur',
	    );
	}
}