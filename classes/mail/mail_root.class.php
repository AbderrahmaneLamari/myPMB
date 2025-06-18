<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mail_root.class.php,v 1.7.2.1 2021/06/30 07:45:10 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once($include_path."/h2o/pmb_h2o.inc.php");

class mail_root {
	
    protected $formatted_data;
    
    protected static $language = '';
    protected static $languages_messages = array();
    
    public function __construct() {
        $this->_substitution_parameters();
        $this->_init();
    }
    
    protected function _substitution_parameters() {
        global $include_path;
        global $deflt2docs_location;
        
        //Globalisons tout d'abord les paramètres communs à toutes les localisations
        if (file_exists($include_path."/parameters_subst/mail_per_localisations_subst.xml")){
            $parameter_subst = new parameters_subst($include_path."/parameters_subst/mail_per_localisations_subst.xml", 0);
        } else {
            $parameter_subst = new parameters_subst($include_path."/parameters_subst/mail_per_localisations.xml", 0);
        }
        $parameter_subst->extract();
        
        if(isset($deflt2docs_location)) {
            if (file_exists($include_path."/parameters_subst/mail_per_localisations_subst.xml")){
                $parameter_subst = new parameters_subst($include_path."/parameters_subst/mail_per_localisations_subst.xml", $deflt2docs_location);
            } else {
                $parameter_subst = new parameters_subst($include_path."/parameters_subst/mail_per_localisations.xml", $deflt2docs_location);
            }
            $parameter_subst->extract();
        }
    }
    
    protected function _init_default_parameters() {
        
    }
    
    protected function _init() {
        $this->_init_default_parameters();
    }
    
    protected static function get_parameter_prefix() {
		return '';
	}
	
	protected function get_evaluated_parameter($parameter_name) {
	    global $biblio_name, $biblio_email, $biblio_phone, $biblio_commentaire, ${$parameter_name};
	    eval ("\$evaluated=\"".addslashes(${$parameter_name})."\";");
	    return stripslashes($evaluated);
	}
	
	protected function get_parameter_id($type_param, $sstype_param) {
	    $query = "SELECT id_param FROM parametres WHERE type_param='".addslashes($type_param)."' AND sstype_param='".addslashes($sstype_param)."'";
	    return pmb_mysql_result(pmb_mysql_query($query), 0, 'id_param');
	}
	
	protected function get_parameter_value($name) {
	    $id_param = $this->get_parameter_id(static::get_parameter_prefix(), $name);
	    $parameter_value = translation::get_translated_text($id_param, 'parametres', 'valeur_param', '', static::$language);
	    if($parameter_value) {
	        return $parameter_value;
	    } else {
    		$parameter_name = static::get_parameter_prefix().'_'.$name;
    		return $this->get_evaluated_parameter($parameter_name);
	    }
	}
	
	protected function _init_parameter_value($name, $value) {
		$parameter_name = static::get_parameter_prefix().'_'.$name;
		global ${$parameter_name};
		if(empty(${$parameter_name})) {
			${$parameter_name} = $value;
		}
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
	
	public static function render($tpl, $data) {
	    global $charset;
        $data=encoding_normalize::utf8_normalize($data);
        $tpl=encoding_normalize::utf8_normalize($tpl);
        $data_to_return = H2o::parseString($tpl)->render($data);
        if ($charset !="utf-8") {
            $data_to_return = utf8_decode($data_to_return);
        }
        return $data_to_return;
	}
	
	public static function get_instance($group='') {
	    global $msg, $charset;
	    global $base_path, $class_path, $include_path;
	    
	    $className = static::class;
	    if($group) {
	        $prefix = static::get_parameter_prefix();
	        $print_parameter = $prefix."_print";
	        global ${$print_parameter};
	        if(!empty(${$print_parameter}) && file_exists($class_path."/mail/".$group."/".${$print_parameter}.".class.php")) {
	            require_once($class_path."/mail/".$group."/".${$print_parameter}.".class.php");
	            $className = ${$print_parameter};
	        } else {
	            require_once($class_path."/mail/".$group."/".$className.".class.php");
	        }
	    } else {
	        if(!empty(${$print_parameter}) && file_exists($class_path."/mail/".${$print_parameter}.".class.php")) {
	            require_once($class_path."/mail/".${$print_parameter}.".class.php");
	            $className = ${$print_parameter};
	        } else {
	            require_once($class_path."/mail/".$className.".class.php");
	        }
	    }
	    return new $className();
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