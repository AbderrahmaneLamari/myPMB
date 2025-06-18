<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_records_ui.class.php,v 1.9.2.1 2021/09/21 16:43:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/analyse_query.class.php");

class list_records_ui extends list_ui {
		
	protected $aq_members;
	
	protected function _get_query_base() {
		$aq_members = $this->get_aq_members();
		$query = 'SELECT *,'.$aq_members["select"].' as pert FROM notices ';
		return $query;
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('pert', 'desc');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'options', true);
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		global $sub;
		
		$this->filters = array(
				'user_query' => '*',
				'niveau_biblio' => '',
				'niveau_hierar' => ''
		);
		parent::init_filters($filters);
	}
		
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$user_query = $this->objects_type.'_user_query';
		global ${$user_query};
		if(isset(${$user_query}) && ${$user_query} != '') {
			$this->filters['user_query'] = ${$user_query};
		}
		parent::set_filters_from_form();
	}
		
	protected function get_aq_members() {
		global $msg;
	
		if(!isset($this->aq_members)) {
			$aq=new analyse_query($this->filters['user_query']);
			if ($aq->error) {
				error_message($msg["searcher_syntax_error"],sprintf($msg["searcher_syntax_error_desc"],$aq->current_car,$aq->input_html,$aq->error_message));
				exit();
			}
			$this->aq_members=$aq->get_query_members("notices","index_wew","index_sew","notice_id");
		}
		return $this->aq_members;
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['user_query']) {
			$aq_members = $this->get_aq_members();
			$filters[] = $aq_members["where"];
		}
		if($this->filters['niveau_biblio']) {
			$filters [] = 'niveau_biblio = "'.$this->filters['niveau_biblio'].'"';
		}
		if($this->filters['niveau_hierar']) {
			$filters [] = 'niveau_hierar = "'.$this->filters['niveau_hierar'].'"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);		
		}
		return $filter_query;
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
	
		$content = '';
		switch($property) {
		    case 'record_header':
		    case 'record_isbd':
		        $method_name = 'get_'.$property;
		        $cart_click_noti = "onClick=\"openPopUp('./cart.php?object_type=NOTI&item=".$object->notice_id."', 'cart')\"";
		        $url = "./catalog.php?categ=serials&sub=view&serial_id=".$object->notice_id;
		        
		        $content .= "<img src='".get_url_icon('basket_small_20x20.gif')."' class='align_middle' alt='basket' title='".$msg[400]."' ".$cart_click_noti." />";
		        $content .= "<a href='".$url."'>".$object->{$method_name}()."</a>";
		        break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
}