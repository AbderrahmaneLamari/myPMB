<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_parameters_mail_ui.class.php,v 1.3.2.1 2021/06/30 07:45:10 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_parameters_mail_ui extends list_parameters_ui {
	
	public function init_filters($filters=array()) {
		$filters['types_param'] = static::get_types_param();
		parent::init_filters($filters);
	}
	
	public static function get_types_param() {
	    return array(
	        'acquisition_pdfcde',
	        'mailretard',
	        'pdflettreresa',
	        'mailrelanceadhesion',
	    );
	}
	
	public static function get_sstypes_param_is_translated() {
	    return array(
	        'objet',
	        'before_list',
	        'after_list',
	        'fdp',
	        'madame_monsieur',
	        'texte',
	        'objet_group',
	        'before_list_group',
	        'after_list_group',
	        'fdp_group',
	    );
	}
}