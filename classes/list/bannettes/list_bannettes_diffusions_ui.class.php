<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_bannettes_diffusions_ui.class.php,v 1.1.2.3 2023/10/04 08:12:35 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/bannette_diffusion.class.php");

class list_bannettes_diffusions_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'select id_diffusion FROM bannettes_diffusions 
			LEFT JOIN bannettes ON bannettes_diffusions.diffusion_num_bannette = bannettes.id_bannette';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new bannette_diffusion($row->id_diffusion);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('date', 'desc');
	}
	
	/**
	 * Initialisation du groupement par défaut appliqué
	 */
	protected function init_default_applied_group() {
	    $this->applied_group = array(0 => 'name');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'name' => 'dsi_ban_form_nom',
						'nom_classement' => 'dsi_clas_type_class_BAN',
						'date' => 'selvars_date',
						'number_records' => 'bannette_diffusion_number_records',
						'number_recipients' => 'bannette_diffusion_number_recipients',
						'number_deleted_records' => 'bannette_diffusion_number_deleted_records',
						'number_sent_mail' => 'bannette_diffusion_number_sent_mail'
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		//$this->add_column('name');
		$this->add_column('date');
		$this->add_column('number_records');
		$this->add_column('number_sent_mail');
		$this->add_column('number_deleted_records');
		$this->add_column_view();
	}
	
	protected function add_column_view() {
		global $msg;
		
		$html_properties = array(
				'value' => $msg['show'],
				'link' => static::get_controller_url_base().'&suite=view&id=!!id!!'
		);
		$this->add_column_simple_action('view', $msg['bannette_diffusion_detail'], $html_properties);
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('name', 'align', 'left');
		$this->set_setting_column('date', 'datatype', 'datetime');
		$this->set_setting_column('number_records', 'datatype', 'integer');
		$this->set_setting_column('number_recipients', 'datatype', 'integer');
		$this->set_setting_column('number_deleted_records', 'datatype', 'integer');
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'name' => 'dsi_ban_search_nom',
						'classement' => 'dsi_classement',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$this->filters = array(
				'name' => '',
				'classement' => '',
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('name');
		$this->add_selected_filter('classement');
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('name');
		$this->set_filter_from_form('classement', 'integer');
		parent::set_filters_from_form();
	}
	
	protected function get_selection_query($type) {
		$query = '';
		switch ($type) {
			case 'classements':
				$query = 'select id_classement as id, nom_classement as label from classements where id_classement=1 UNION select id_classement as id, nom_classement as label from classements where type_classement="BAN" order by label';
				break;
		}
		return $query;
	}
	
	protected function get_search_filter_name() {
		return $this->get_search_filter_simple_text('name');
	}
	
	protected function get_search_filter_classement() {
		global $msg;
		
		return $this->get_search_filter_simple_selection($this->get_selection_query('classements'), 'classement', $msg['dsi_all_classements']);
	}
	
	protected function _add_query_filters() {
		$this->_add_query_filter_simple_restriction('name', 'nom_bannette', 'boolean_search');
		$this->_add_query_filter_simple_restriction('classement', 'num_classement', 'integer');
	}
	
	protected function _get_object_property_name($object) {
		return $object->get_bannette()->nom_bannette;
	}
	
	protected function _get_object_property_nom_classement($object) {
		$classement = new classement($object->num_classement);
		return $classement->nom_classement;
	}
	
	protected function _get_query_property_filter($property) {
		switch ($property) {
			case 'classement':
				return "select nom_classement from classements where id_classement=".$this->filters[$property];
		}
		return '';
	}
	
	protected function init_default_selection_actions() {
		global $msg;
		
		parent::init_default_selection_actions();
		$delete_link = array(
				'href' => static::get_controller_url_base()."&action=list_delete",
				'confirm' => $msg['confirm_suppr']
		);
		$this->add_selection_action('delete', $msg['63'], 'interdit.gif', $delete_link);
	}
	
	public static function delete_object($id) {
		$id = intval($id);
		bannette_diffusion::delete($id);
	}
}