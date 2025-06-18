<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_rss_ui.class.php,v 1.12.2.2 2021/09/21 16:43:40 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/rss_flux.class.php');

class list_rss_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'SELECT id_rss_flux FROM rss_flux';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new rss_flux($row->id_rss_flux);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'nom_rss_flux' => 'dsi_flux_search_nom',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'nom_rss_flux' => '',
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('nom_rss_flux');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'nom_rss_flux' => 'dsi_flux_form_nom',
					'nb_paniers' => 'dsi_flux_nb_paniers',
					'nb_bannettes' => 'dsi_flux_nb_bannettes',
					'permalink' => 'dsi_flux_link'
			)
		);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('nom_rss_flux');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
		
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'id':
					$order .= 'id_rss_flux';
					break;
				case 'name' :
					$order .= $sort_by;
					break;
				default :
					$order .= parent::_get_query_order();
					break;
			}
			if($order) {
				return $this->_get_query_order_sql_build($order);
			} else {
				return "";
			}
		}	
	}
	
	protected function get_form_title() {
		global $msg, $charset;
		return htmlentities($msg['dsi_flux_search'], ENT_QUOTES, $charset);
	}
	
	protected function get_button_add() {
		global $msg;
		
		return "<input type='button' class='bouton' value='".$msg['ajouter']."' onClick=\"document.location='".static::get_controller_url_base().'&suite=add'."';\" />";
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('nom_rss_flux');
		parent::set_filters_from_form();
	}
	
	protected function init_default_columns() {
		$this->add_column('nom_rss_flux');
		$this->add_column('nb_paniers');
		$this->add_column('nb_bannettes');
		$this->add_column('permalink');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_column('nom_rss_flux', 'align', 'left');
		$this->set_setting_column('permalink', 'align', 'left');
		$this->set_setting_column('nom_rss_flux', 'text', array('strong' => true));
	}
	
	protected function get_search_filter_nom_rss_flux() {
		global $msg, $charset;
	
		return "<input class='saisie-30em' id='".$this->objects_type."_name' type='text' name='".$this->objects_type."_nom_rss_flux' value=\"".htmlentities($this->filters['nom_rss_flux'], ENT_QUOTES, $charset)."\" title='".$msg['3000']."' />";
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['nom_rss_flux']) {
			$filters [] = 'nom_rss_flux like "%'.str_replace("*", "%", $this->filters['nom_rss_flux']).'%"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function get_cell_content($object, $property) {
		global $opac_url_base;
		
		$content = '';
		switch($property) {
			case 'permalink':
				$content .= "<a href='".$opac_url_base."rss.php?id=".$object->id_rss_flux."' target='_blank'>".$opac_url_base."rss.php?id=".$object->id_rss_flux."</a>";
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array();
		switch($property) {
			case 'permalink':
				break;
			default:
				$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=view&suite=acces&id_rss_flux=".$object->id_rss_flux."\"";
				break;
		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
}