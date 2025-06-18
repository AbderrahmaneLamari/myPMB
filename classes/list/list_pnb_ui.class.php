<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_pnb_ui.class.php,v 1.24 2021/03/26 10:06:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path.'/templates/list/list_pnb_ui.tpl.php');

class list_pnb_ui extends list_ui {

	protected function _get_query_base() {
		$query = 'select id_pnb_order from pnb_orders';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new pnb_order($row->id_pnb_order);
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$this->filters = array(
		    'alert_end_offers' => '',
		    'alert_staturation_offers' => '',
		);
		parent::init_filters($filters);
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'order_id' => 'edit_pnb_order_id',
					'line_id' => 'edit_pnb_order_line_id',
					'notice' => 'edit_pnb_order_notice',
					'loan_max_duration' => 'edit_pnb_order_loan_max_duration',
					'nb_loans' => 'edit_pnb_order_nb_loans',
					'nb_simultaneous_loans' => 'edit_pnb_order_nb_simultaneous_loans',
					'nb_consult_in_situ' => 'edit_pnb_order_nb_consult_in_situ',
					'nb_consult_ex_situ' => 'edit_pnb_order_nb_consult_ex_situ',
					'offer_date' => 'edit_pnb_order_offer_date',
					'offer_date_end' => 'edit_pnb_order_offer_date_end',
					'offer_duration' => 'edit_pnb_order_offer_duration',
			)
		);
		
	}
	
	protected function init_default_columns() {
		$this->add_column('order_id');
		$this->add_column('line_id');
		$this->add_column('notice');
		$this->add_column('loan_max_duration');
		$this->add_column('nb_loans');
		$this->add_column('nb_simultaneous_loans');
		$this->add_column('nb_consult_in_situ');
		$this->add_column('nb_consult_ex_situ');
		$this->add_column('offer_date');
		$this->add_column('offer_date_end');
		$this->add_column('offer_duration');
		$this->add_column_sel_button();
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('default', 'align', 'left');
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('offer_date', 'desc');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {	
	    if ($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'offer_date':
					$order .= 'pnb_order_offer_date';
					break;
				case 'offer_date_end':
					$order .= 'pnb_order_offer_date_end';
					break;
				default :
					$order .= parent::_get_query_order();
					break;
			}
			if ($order) {
				return $this->_get_query_order_sql_build($order);
			} else {
				return "";
			}
		}	
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
	    global $alert_end_offers, $alert_staturation_offers;
	
		if (isset($alert_end_offers)) {
			$this->filters['alert_end_offers'] = $alert_end_offers;
		} else {			
			$this->filters['alert_end_offers'] = '';
		}
		if (isset($alert_staturation_offers)) {
		    $this->filters['alert_staturation_offers'] = $alert_staturation_offers;
		} else {
		    $this->filters['alert_staturation_offers'] = '';
		}
		parent::set_filters_from_form();
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		global $pmb_pnb_alert_end_offers;
		
		$filter_query = '';		
		$this->set_filters_from_form();
		
		$filters = array();
		if ($this->filters['alert_end_offers']) {
			$filters [] = " DATE_ADD(pnb_order_offer_date_end, INTERVAL - " . $pmb_pnb_alert_end_offers . " DAY) < NOW() ";
		}
		if ($this->filters['alert_staturation_offers']) {
		    $filters [] = " DATE_ADD(pnb_order_offer_date_end, INTERVAL - " . $pmb_pnb_alert_end_offers . " DAY) < NOW() ";
		}

		if (count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}	
		return $filter_query;
	}
	
	protected function fetch_data() {
	    $this->objects = array();
	    $query = $this->_get_query_base();
	    $query .= $this->_get_query_filters();
	    
	    if ($this->filters['alert_staturation_offers']) {
	        global $pmb_pnb_alert_staturation_offers;
	        $query = "select id_pnb_order from (select * from pnb_orders
	        join pnb_loans on pnb_loan_order_line_id = pnb_order_line_id
	        group by pnb_order_line_id having count(id_pnb_loan) >= pnb_order_nb_simultaneous_loans - " . $pmb_pnb_alert_staturation_offers . " ) as t";	        
	    }
	    
	    $query .= $this->_get_query_order();
	    if ($this->applied_sort_type == "SQL"){
	        $this->pager['nb_results'] = pmb_mysql_num_rows(pmb_mysql_query($query));
	        $query .= $this->_get_query_pager();
	    }
	    $result = pmb_mysql_query($query);
	    if (pmb_mysql_num_rows($result)) {
	        while($row = pmb_mysql_fetch_object($result)) {
	            $this->add_object($row);
	        }
	        if ($this->applied_sort_type != "SQL"){
	            $this->pager['nb_results'] = pmb_mysql_num_rows($result);
	        }
	    }
	    $this->messages = "";
	}
	
	/**
	 * Fonction de callback
	 * @param object $a
	 * @param object $b
	 */
	protected function _compare_objects($a, $b) {
	    if ($this->applied_sort[0]['by']) {
	        $sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'nb_loans':
					return strcmp($a->get_loans_completed_number(), $b->get_loans_completed_number());
					break;
				case 'nb_simultaneous_loans':
					return strcmp($a->get_loans_in_progress(), $b->get_loans_in_progress());
					break;
				default :
					return parent::_compare_objects($a, $b);
					break;
			}
		}
	}
	
	/**
	 * Construction dynamique de la fonction JS de tri
	 */
	protected function get_js_sort_script_sort() {
		global $sub;		
		global $list_pnb_ui_script_case_a_cocher;
		
		$display = parent::get_js_sort_script_sort();
		$display.= $list_pnb_ui_script_case_a_cocher;
		$display = str_replace('!!categ!!', 'pnb', $display);
		$display = str_replace('!!sub!!', $sub, $display);
		$display = str_replace('!!action!!', 'list', $display);
		return $display;
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch($property) {
			case 'nb_loans':
			    $content.=  $object->get_loans_completed_number() . " / " . parent::get_cell_content($object, $property);
				break;
			case 'nb_simultaneous_loans':
			    $content.=  $object->get_loans_in_progress() . " / " . parent::get_cell_content($object, $property);
			    break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}		
		return $content;
	}
	
	protected function get_edition_link() {
		return '';
	}
		
	protected function add_column_sel_button() {
		$this->columns[] = array(
				'property' => '',
		    'label' => "<div class='center'><input type='button' id='check_all_command_lines' class='bouton' name='+' value='+'></div>",
		    'html' => "<div class='center'><input type='checkbox' data-pnb name='sel_!!id!!' value='!!id!!'></div>",
		    'exportable' => false
		);
	}
		
	protected function _get_query_human() {
		global $msg, $charset;
	
		$humans = array();
		if ($this->filters['alert_end_offers']) {
			$humans[] = "<b>".htmlentities($msg['pnb_edit_end_offers_filter'], ENT_QUOTES, $charset)."</b> ";
		}
		if ($this->filters['alert_staturation_offers']) {
		    $humans[] = "<b>".htmlentities($msg['pnb_edit_staturation_offers_filter'], ENT_QUOTES, $charset)."</b> ";
		}
		$human_query = "<div class='align_left'><br />".implode(', ', $humans)." => ".sprintf(htmlentities($msg['searcher_results'], ENT_QUOTES, $charset), $this->pager['nb_results'])."<br /><br /></div>";
		return $human_query;
	}
	
	/**
	 * Affichage des filtres du formulaire de recherche
	 */
	public function get_search_filters_form() {
		global $pnb_ui_search_filters_form_tpl;
		
		$search_filters_form = $pnb_ui_search_filters_form_tpl;			
		$search_filters_form = str_replace('!!alert_end_offers_checked!!', ($this->filters['alert_end_offers'] ? 'checked=checked' : '' ), $search_filters_form);
		$search_filters_form = str_replace('!!alert_staturation_offers_checked!!', ($this->filters['alert_staturation_offers'] ? 'checked=checked' : '' ), $search_filters_form);
		
		return $search_filters_form;
	}
}