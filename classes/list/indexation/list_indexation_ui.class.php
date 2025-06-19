<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_indexation_ui.class.php,v 1.1.2.3 2023/09/29 06:47:59 dgoron Exp $

use Pmb\Common\Helper\HelperEntities;

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// global $class_path;
// require_once($class_path.'/indexation_stack.class.php');

class list_indexation_ui extends list_ui {
	
	protected $indexation;
	
	protected $fields;
	
	protected $sub_fields;
	
	protected function _get_query_base() {
		switch ($this->filters['type']) {
			case 'fields':
				switch ($this->filters['entity_type']) {
					case TYPE_NOTICE:
						$query = "SELECT id_notice as id, notices_fields_global_index.* FROM notices_fields_global_index";
						break;
					default:
						$query = "SELECT id_authority as id, authorities_fields_global_index.* FROM authorities_fields_global_index";
						break;
				}
				break;
			case 'words':
				
				break;
		}
		
		return $query;
	}
	
	/**
	 * Champ(s) du tri SQL
	 */
	protected function _get_query_field_order($sort_by) {
	    switch($sort_by) {
	        case 'id' :
	            return 'id_notice';
	        case 'i_value' :
	            return 'value';
	        case 'pond' :
	            return 'pond';
	        default :
	            return parent::_get_query_field_order($sort_by);
	    }
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'entity_type' => 'indexation_entity_type',
						'field' => 'indexation_field',
						'sub_field' => 'indexation_sub_field',
						'id' => 'indexation_id',
						'i_value' => 'indexation_i_value',
// 						'indexed' => 'indexation_indexed'
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'type' => 'fields',
				'entity_type' => TYPE_NOTICE,
				'field' => '',
				'sub_field' => '',
				'id' => '',
				'i_value' => ''
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('entity_type');
		$this->add_selected_filter('field');
		$this->add_selected_filter('sub_field');
		$this->add_selected_filter('id');
		$this->add_selected_filter('i_value');
	}
	
	protected function init_default_applied_group() {
		$this->applied_group = array(0 => 'id');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'id' => 'indexation_id',
					'field' => 'indexation_field',
					'sub_field' => 'indexation_sub_field',
					'i_value' => 'indexation_i_value',
					'pond' => 'indexation_pond',
			)
		);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('id');
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'field', 'sub_field'
		);
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('entity_type');
		$this->set_filter_from_form('field');
		$this->set_filter_from_form('sub_field');
		$this->set_filter_from_form('id');
		$this->set_filter_from_form('i_value');
		parent::set_filters_from_form();
	}
	
	protected function init_default_columns() {
		$this->add_column('field');
		$this->add_column('sub_field');
		$this->add_column('i_value');
		$this->add_column('pond');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_column('default', 'align', 'left');
		$this->set_setting_column('id', 'datatype', 'integer');
		$this->set_setting_column('pond', 'datatype', 'integer');
	}
	
	protected function get_search_filter_entity_type() {
		$options = HelperEntities::get_entities_labels();
		return $this->get_search_filter_simple_selection('', 'entity_type', '', $options);
	}
	
	protected function get_search_filter_field() {
		global $msg;
		
		$options = array();
		$fields = $this->get_fields();
		foreach ($fields as $code=>$field) {
			$options[$code] = $field['label'];
		}
		asort($options);
		return $this->get_search_filter_simple_selection('', 'field', $msg['all'], $options);
	}
	
	protected function get_search_filter_sub_field() {
		$options = array();
		$fields = $this->get_fields();
		if(!empty($fields[$this->filters['field']]['sub_fields'])) {
			foreach ($fields[$this->filters['field']]['sub_fields'] as $code=>$label) {
				$options[$code] = $label;
			}
		}
		asort($options);
		return $this->get_search_filter_simple_selection('', 'sub_field', '', $options);
	}
	
	protected function get_search_filter_id() {
		return $this->get_search_filter_simple_text('id');
	}
	
	protected function get_search_filter_i_value() {
		return $this->get_search_filter_simple_text('i_value');
	}
	
	protected function _add_query_filters() {
		if($this->filters['entity_type'] != TYPE_NOTICE) {
// 			$this->_add_query_filter_simple_restriction('entity_type', 'type');
		}
		$this->_add_query_filter_simple_restriction('field', 'code_champ');
		$this->_add_query_filter_simple_restriction('sub_field', 'code_ss_champ');
		switch ($this->filters['entity_type']) {
			case TYPE_NOTICE:
				$this->_add_query_filter_simple_restriction('id', 'id_notice');
				break;
			default:
				$this->_add_query_filter_simple_restriction('id', 'id_authority');
				break;
		}
		$this->_add_query_filter_simple_restriction('i_value', 'value');
	}
	
	protected function _get_object_property_entity_type($object) {
// 		return indexation_stack::get_label_from_type($object->entity_type);
	}
	
	protected function _get_object_property_field($object) {
		$fields = $this->get_fields();
		return $fields[$object->code_champ]['label'];
	}
	
	protected function _get_object_property_sub_field($object) {
		$fields = $this->get_fields();
		return $fields[$object->code_champ]['sub_fields'][$object->code_ss_champ] ?? '';
	}
	
	protected function _get_object_property_i_value($object) {
		return $object->value;
	}
	
	protected function _get_query_human_entity_type() {
		if(!empty($this->filters['entity_type'])) {
// 			return indexation_stack::get_label_from_type($this->filters['entity_type']);
		}
		return '';
	}
	
	protected function _get_query_human_field() {
		$fields = $this->get_fields();
		return $fields[$this->filters['field']]['label'];
	}
	
	protected function get_fields() {
		global $msg;
		
		if(!isset($this->fields)) {
			$this->fields = array();
			$this->get_indexation();
			$xml_fields = $this->indexation::$xml_indexation[$this->indexation->get_type()]['FIELD'];
			foreach ($xml_fields as $field) {
				$sub_fields = array();
				if(!empty($field['TABLE'][0]['TABLEFIELD']) && count($field['TABLE'][0]['TABLEFIELD']) > 1) {
					foreach ($field['TABLE'][0]['TABLEFIELD'] as $tablefield) {
						$sub_fields[ltrim($tablefield['ID'], '0')] = $msg[$tablefield['NAME']];
					}
				}
				$code = intval(ltrim($field['ID'], '0'));
				if($code == 1000) {
					$query = "select id_authperso, authperso_name from authperso order by authperso_name";
					$result = pmb_mysql_query($query);
					if (pmb_mysql_num_rows($result)) {
						while($row = pmb_mysql_fetch_object($result)) {
							$this->fields[(1000+$row->id_authperso)] = array(
									'label' => $row->authperso_name,
									'sub_fields' => $sub_fields
							);
						}
					}
				} else {
					$this->fields[$code] = array(
							'label' => $msg[$field['NAME']],
							'sub_fields' => $sub_fields
					);
				}
			}
		}
		return $this->fields;
	}
	
	public function get_indexation() {
		global $include_path;
		
		if(!isset($this->indexation)) {
			switch ($this->filters['entity_type']) {
				case TYPE_NOTICE:
					$this->indexation = new indexation_record($include_path."/indexation/notices/champs_base.xml", 'notices');;
					break;
				default:
					$this->indexation = indexations_collection::get_indexation(authority::$type_table[$this->filters['entity_type']]);
					break;
			}
			$this->indexation->initialization();
		}
		return $this->indexation;
	}
	
}