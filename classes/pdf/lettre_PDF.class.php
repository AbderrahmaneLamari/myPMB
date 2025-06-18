<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: lettre_PDF.class.php,v 1.12.2.1 2021/06/30 07:45:10 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/parameters_subst.class.php");

class lettre_PDF {
	
	public $PDF;
	public $orient_page = 'P';			//Orientation page (P=portrait, L=paysage)
	public $largeur_page = 210;			//Largeur de page
	public $hauteur_page = 297;			//Hauteur de page
	public $unit = 'mm';				//Unite 
	public $marge_haut = 10;			//Marge haut
	public $marge_bas = 20;				//Marge bas
	public $marge_droite = 10;			//Marge droite
	public $marge_gauche = 10;			//Marge gauche
	public $w = 190;					//Largeur utile page
	public $font = 'Helvetica';			//Police
	public $fs = 10;					//Taille police
	
	protected static $instances = array();
	protected static $language = '';
	protected static $languages_messages = array();
	
	public function __construct() {
	    $this->_substitution_parameters();
		$this->_init();
		$this->_open();
	}
	
	protected function _substitution_parameters() {
	    global $include_path;
	    global $deflt2docs_location;
	    
	    //Globalisons tout d'abord les paramètres communs à toutes les localisations
	    if (file_exists($include_path."/parameters_subst/pdf_per_localisations_subst.xml")){
	        $parameter_subst = new parameters_subst($include_path."/parameters_subst/pdf_per_localisations_subst.xml", 0);
	    } else {
	        $parameter_subst = new parameters_subst($include_path."/parameters_subst/pdf_per_localisations.xml", 0);
	    }
	    $parameter_subst->extract();
	    
	    if(isset($deflt2docs_location)) {
	        if (file_exists($include_path."/parameters_subst/pdf_per_localisations_subst.xml")){
	            $parameter_subst = new parameters_subst($include_path."/parameters_subst/pdf_per_localisations_subst.xml", $deflt2docs_location);
	        } else {
	            $parameter_subst = new parameters_subst($include_path."/parameters_subst/pdf_per_localisations.xml", $deflt2docs_location);
	        }
	        $parameter_subst->extract();
	    }
	}
	
	protected function _init_default_parameters() {
		
	}
	
	protected function _init_default_positions() {
	
	}
	
	protected function _init() {
		global $pmb_pdf_font;
		
		$this->_init_PDF();
		
		$this->_init_marges();
		
		$this->w = $this->largeur_page-$this->marge_gauche-$this->marge_droite;
		
		$this->font = $pmb_pdf_font;
		if($this->get_parameter_value('text_size')) {
			$this->fs = $this->get_parameter_value('text_size');
		}
		$this->_init_default_parameters();
		$this->_init_default_positions();
	}
	
	protected function _open() {
		$this->PDF->Open();
		$this->PDF->SetMargins($this->marge_gauche, $this->marge_haut, $this->marge_droite);
		$this->PDF->setFont($this->font);
		
		$this->PDF->footer_type=1;
	}
	
	protected function get_parameter_id($type_param, $sstype_param) {
	    $query = "SELECT id_param FROM parametres WHERE type_param='".addslashes($type_param)."' AND sstype_param='".addslashes($sstype_param)."'";
	    return pmb_mysql_result(pmb_mysql_query($query), 0, 'id_param');
	}
	
	protected function get_parameter_value($name) {
	    $parameter_name = static::get_parameter_prefix().'_'.$name;
	    global $$parameter_name;
	    return $$parameter_name;
	}
	
	protected function set_parameter_value($name, $value) {
		$parameter_name = static::get_parameter_prefix().'_'.$name;
		global $$parameter_name;
		$$parameter_name = $value;
	}
	
	protected function set_language($language) {
		global $msg, $lang;
	    
	    if(empty(static::$languages_messages)) {
	    	static::$languages_messages[$lang] = $msg;
	    }
        $msg = static::get_language_messages($language);
        static::$language = $language;
	}
	
	protected function restaure_language() {
		global $msg, $lang;
	    
	    if(!empty(static::$languages_messages[$lang])) {
	    	$msg = static::$languages_messages[$lang];
	    }
	}
	
	protected function _init_marges() {
		$marges_page = $this->get_parameter_value('marges_page');
		if(!empty($marges_page)) {
			$marges_page = explode(',', $marges_page);
			if (!empty($marges_page[0])) $this->marge_haut = $marges_page[0];
			if (!empty($marges_page[1])) $this->marge_bas = $marges_page[1];
			if (!empty($marges_page[2])) $this->marge_droite = $marges_page[2];
			if (!empty($marges_page[3])) $this->marge_gauche = $marges_page[3];
		} else {
			$marge_page_droite = $this->get_parameter_value('marge_page_droite');
			if (!empty($marge_page_droite)) $this->marge_droite = $marge_page_droite;
			$marge_page_gauche = $this->get_parameter_value('marge_page_gauche');
			if (!empty($marge_page_gauche)) $this->marge_gauche = $marge_page_gauche;
		}
	}
	
	protected function _init_position($name, $position=array()) {
		if (isset($position[0]) && $position[0]) $this->{"x_".$name} = $position[0];
		if (isset($position[1]) && $position[1]) $this->{"y_".$name} = $position[1];
		if (isset($position[2]) && $position[2]) $this->{"l_".$name} = $position[2];
		if (isset($position[3]) && $position[3]) $this->{"h_".$name} = $position[3];
		if (isset($position[4]) && $position[4]) $this->{"fs_".$name} = $position[4];
	}
	
	protected function get_position_values($name) {
		$values = array();
		if (isset($this->{"x_".$name})) $values[0] = $this->{"x_".$name};
		if (isset($this->{"y_".$name})) $values[1] = $this->{"y_".$name};
		if (isset($this->{"l_".$name})) $values[2] = $this->{"l_".$name};
		if (isset($this->{"h_".$name})) $values[3] = $this->{"h_".$name};
		if (isset($this->{"fs_".$name})) $values[4] = $this->{"fs_".$name};
		return $values;
	}
	
	public function getLettre($format=0,$name='lettre.pdf') {
		if (!$format) {
			return $this->PDF->OutPut();
		} else {
			return $this->PDF->OutPut($name,'S');
		}
	}
	
	public function getFileName() {
		return $this->filename;
	}
	
	protected static function get_parameter_prefix() {
	    return '';
	}
	
	public function reset_default_positions() {
		$this->_init_default_positions();
	}
	
	public static function get_instance($group='') {
	    global $msg, $charset;
	    global $base_path, $class_path, $include_path;
	    
	    $className = static::class;
	    if(!isset(static::$instances[$className])) {
		    if($group) {
		        $prefix = static::get_parameter_prefix();
		        $print_parameter = $prefix."_print";
		        global ${$print_parameter};
		        if(!empty(${$print_parameter}) && file_exists($class_path."/pdf/".$group."/".${$print_parameter}.".class.php")) {
		            require_once($class_path."/pdf/".$group."/".${$print_parameter}.".class.php");
		            $className = ${$print_parameter};
		        } else {
		            require_once($class_path."/pdf/".$group."/".$className.".class.php");
		        }
		    } else {
		        if(!empty(${$print_parameter}) && file_exists($class_path."/pdf/".${$print_parameter}.".class.php")) {
		            require_once($class_path."/pdf/".${$print_parameter}.".class.php");
		            $className = ${$print_parameter};
		        } else {
		            require_once($class_path."/pdf/".$className.".class.php");
		        }
		    }
		    static::$instances[$className] = new $className();
	    } else {
	    	//Ré-initialisation des positions pour démarrer une nouvelle page
	    	static::$instances[$className]->reset_default_positions();
	    }
	    return static::$instances[$className];
	}
	
	public static function get_language_messages($language) {
	    global $include_path;
	    
	    if(!isset(static::$languages_messages[$language])) {
	        $messages_instance = new XMLlist($include_path."/messages/".$language.".xml");
	        $messages_instance->analyser();
	        static::$languages_messages[$language] = $messages_instance->table;
	    }
	    return static::$languages_messages[$language];
	}
}