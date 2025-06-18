<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: accounting_livraisons_controller.class.php,v 1.3 2020/11/05 09:39:48 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/accounting/accounting_controller.class.php");

class accounting_livraisons_controller extends accounting_controller {
	
	protected static $list_ui_class_name = 'list_accounting_livraisons_ui';
	
	protected static function get_list_ui_instance($filters=array(), $pager=array(), $applied_sort=array()) {
	    global $accounting_livraisons_ui_user_input;
	    global $accounting_livraisons_ui_status;
	    
	    $filters = array();
	    $filters['user_input'] = stripslashes($accounting_livraisons_ui_user_input);
	    $filters['status'] = $accounting_livraisons_ui_status;
	    
	    return new static::$list_ui_class_name($filters);
	}
	
	public static function proceed($id=0) {
        global $action;
        global $id_cde;
        
        switch($action) {
            case 'list' :
                entites::setSessionBibliId(static::$id_bibli);
                show_list_liv(static::$id_bibli);
                break;
            case 'from_cde' :
                show_from_cde(static::$id_bibli, $id_cde);
                break;
            case 'modif' :
                show_form_liv(static::$id_bibli, static::$id_acte);
                break;
            case 'delete' :
                sup_liv(static::$id_acte, $id_cde);
                show_list_liv(static::$id_bibli);
                break;
            default:
            	if(entites::is_selected_biblio('show_list_liv') == false) {
            		print entites::show_list_biblio('show_list_liv');
            	} else {
            		parent::proceed($id);
            	}
                break;
        }
        
	}
}