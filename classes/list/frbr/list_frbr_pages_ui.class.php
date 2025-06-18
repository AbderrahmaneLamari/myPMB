<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_frbr_pages_ui.class.php,v 1.4.2.2 2021/10/29 08:21:16 rtigero Exp $
if (stristr ( $_SERVER ['REQUEST_URI'], ".class.php" )) die ( "no access" );

class list_frbr_pages_ui extends list_ui {
	
	protected $managed_entities;
	
	protected function _get_query_base() {
		$query = 'select id_page
				from frbr_pages';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new frbr_page($row->id_page);
	}
	
	protected function _get_query_order() {
		if ($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'order':
					$order .= 'page_order, page_name';
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
	
	protected function init_default_applied_sort() {
		$this->add_applied_sort('order');
		$this->add_applied_sort('name');
	}

	public function init_applied_group($applied_group=array()) {
		$this->applied_group = array(0 => 'entity');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = array (
				'main_fields' => array (
						'order' => 'frbr_page_order',
						'name' => 'frbr_page_name',
						'records_list' => 'frbr_page_parameter_records_list',
						'facettes_list' => 'frbr_page_parameter_facettes_list',
						'isbd' => 'frbr_page_parameter_isbd',
						'template_directory' => 'frbr_page_parameter_template_directory',
						'record_template_directory' => 'frbr_page_parameter_record_template_directory'
				)
		);
	}

	/**
	 * Initialisation des colonnes par défaut
	 */
	protected function init_default_columns() {
		$this->add_column('order');
		$this->add_column('name');
		$this->add_column('records_list');
		$this->add_column('facettes_list');
		$this->add_column('isbd');
		$this->add_column('template_directory');
		$this->add_column('record_template_directory');
		$this->add_column_build();
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->settings['objects']['default']['display_mode'] = 'expandable_table';
		$this->settings['grouped_objects']['level_1']['display_mode'] = 'expandable_table';
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array (
				'order', 'name', 'records_list',
				'facettes_list', 'isbd', 'template_directory',
				'record_template_directory'
		);
	}

	protected function add_column_build() {
		global $msg;
		$this->columns[] = array(
			'property' => '',
			'label' => '',
			'html' => '<input type="button" class="bouton" value="'.$msg['frbr_page_tree_build'].'" onclick=\'document.location="'.static::get_controller_url_base().'&sub=build&num_page=!!id!!&num_parent=0"\' />',
			'exportable' => false
		);
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters = array()) {
		$this->filters = array();
		parent::init_filters($filters);
	}

	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters = array (
				'main_fields' => array ()
		);
		$this->available_filters ['custom_fields'] = array ();
	}
	
	protected function _get_object_property_entity($object) {
		$managed_entities = $this->get_managed_entities();
		return $managed_entities[$object->get_entity()]['name'];
	}
	
	protected function get_cell_content($object, $property) {
		global $msg, $charset;
		
		$content = '';
		switch ($property) {
			case 'order':
				$content .= "
					<img src='".get_url_icon('bottom-arrow.png')."' title='".htmlentities($msg['move_bottom_arrow'], ENT_QUOTES, $charset)."' alt='".htmlentities($msg['move_bottom_arrow'], ENT_QUOTES, $charset)."' onClick=\"document.location='".static::get_controller_url_base()."&sub=list&action=down&id=".$object->get_id()."'\" style='cursor:pointer;'/>
					<img src='".get_url_icon('top-arrow.png')."' title='".htmlentities($msg['move_top_arrow'], ENT_QUOTES, $charset)."' alt='".htmlentities($msg['move_top_arrow'], ENT_QUOTES, $charset)."' onClick=\"document.location='".static::get_controller_url_base()."&sub=list&action=up&id=".$object->get_id()."'\" style='cursor:pointer;'/>
				";
				break;
			case 'records_list':
			case 'facettes_list':
			case 'isbd':
				if ($object->get_parameter_value($property)) {
					$content .= "X";
				}
				break;
			case 'template_directory':
			case 'record_template_directory':
				$content .= $object->get_parameter_value($property);
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	public function get_managed_entities() {
		if(!isset($this->managed_entities)) {
			$entities_parser = new frbr_entities_parser();
			$this->managed_entities = $entities_parser->get_managed_entities();
		}
		return $this->managed_entities;
	}
	
	protected function get_button_add() {
		global $msg, $charset;
		
		return "<input type='button' class='bouton' name='frbr_page_add' value='".htmlentities($msg["frbr_page_add"], ENT_QUOTES, $charset)."' onclick=\"document.location='".static::get_controller_url_base()."&sub=edit'\" />";
	}
	
	public function get_js_sort_expandable_list() {
		return "";	
	}
	
	protected function gen_plus($id, $titre, $contenu, $maximise=0) {
		return "
			<div id=\"el" . $id . "Parent\" class='parent' width=\"100%\">
				<span class='heada'>
					<h3>".$titre."</h3>
				</span>
				<br />
			</div>
			<div id=\"el" . $id . "Child\" class=\"child\" style=\"margin-bottom:6px;\">
				".$contenu."
			</div>";
	}
	
	protected function get_display_left_actions() {
		return $this->get_button_add();
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array();
		switch ($property) {
			case 'order':
				break;
			case 'name':
				$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&sub=edit&id=".$object->get_id()."\"";
				$attributes['width'] = "20%";
				break;
			default:
				$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&sub=edit&id=".$object->get_id()."\"";
				break;
		}		
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	public static function get_controller_url_base() {
		global $base_path;
		return $base_path.'/cms.php?categ=frbr_pages';
	}
}