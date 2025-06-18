<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_acquisition_pricing_systems_ui.class.php,v 1.2 2021/04/19 07:10:22 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/rent/rent_pricing_system.class.php');

class list_configuration_acquisition_pricing_systems_ui extends list_configuration_acquisition_ui {
	
	protected function get_title() {
		$entity = new entites($this->filters['num_entity']);
		return "<div class='row'><label>".$entity->raison_sociale."</label></div>";
	}
	
	protected function _get_query_base() {
		return 'SELECT * FROM rent_pricing_systems
			JOIN exercices ON rent_pricing_systems.pricing_system_num_exercice = exercices.id_exercice';
	}
	
	protected function get_object_instance($row) {
		return new rent_pricing_system($row->id_pricing_system);
	}
	
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'num_entity' => 0,
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_applied_sort() {
		$this->add_applied_sort('id');
	}
	
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['num_entity']) {
			$filters[] = 'num_entite = "'.$this->filters['num_entity'].'"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function get_main_fields_from_sub() {
		return array(
				'label' => 'pricing_system_label',
				'associated_exercice' => 'pricing_system_associated_exercice',
		);
	}
	
	protected function init_default_columns() {
		$this->add_column_expand();
		parent::init_default_columns();
		$this->add_column_edit_grid();
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'expand', 'label', 'associated_exercice', 'edit_grid',
		);
	}
	
	protected function add_column_expand() {
		$this->columns[] = array(
				'property' => 'expand',
				'label' => '',
				'html' => "<img src='".get_url_icon('plus.gif')."' id='pricing_system_img_!!id!!' class='img_plus' onClick='if (event) e=event; else e=window.event; e.cancelBubble=true; if (e.stopPropagation) e.stopPropagation(); display_grid(\"!!id!!\"); ' style='cursor:pointer;'/>",
				'exportable' => false
		);
	}
	
	protected function add_column_edit_grid() {
		global $msg, $charset;
		$this->columns[] = array(
				'property' => 'edit_grid',
				'label' => '',
				'html' => "<center><input type='button' class='bouton' onclick=\"document.location='".static::get_controller_url_base()."&id_entity=".$this->filters['num_entity']."&action=grid_edit&id=!!id!!'\" value=\"".htmlentities($msg['pricing_system_edit_grid'], ENT_QUOTES, $charset)."\" /></center>",
				'exportable' => false
		);
	}
	
	protected function get_display_content_grid_object_list($object, $indice) {
		$rent_pricing_system_grid = new rent_pricing_system_grid($object->get_id());
		$display = "
		<tr id='pricing_system_grid_".$object->get_id()."' class='".($indice % 2 ? 'odd' : 'even')."' style='display : none;'>
			<td></td>
			<td colspan='3'>".$rent_pricing_system_grid->get_display()."</td>
		</tr>
		";
		return $display;
	}
	
	protected function get_display_content_object_list($object, $indice) {
		$this->is_editable_object_list = false;
		$display = parent::get_display_content_object_list($object, $indice);
		$display .= $this->get_display_content_grid_object_list($object, $indice);
		return $display;
	}
	
	protected function _get_object_property_associated_exercice($object) {
		return $object->get_exercice()->libelle;
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array(
				'onclick' => "document.location=\"".$this->get_edition_link($object)."\""
		);
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function get_edition_link($object) {
		return static::get_controller_url_base().'&action=edit&id_entity='.$this->filters['num_entity'].'&id='.$object->get_id();
	}
	
	protected function get_label_button_add() {
		global $msg;
		
		return $msg['pricing_system_add'];
	}
	
	protected function get_button_add() {
		global $charset;
		
		return "<input class='bouton' type='button' value='".htmlentities($this->get_label_button_add(), ENT_QUOTES, $charset)."' onClick=\"document.location='".static::get_controller_url_base()."&action=edit&id_entity=".$this->filters['num_entity']."';\" />";
	}
	
	public function get_display_list() {
		$display = "<script src='javascript/pricing_systems.js'></script>";
		$display .= parent::get_display_list();
		return $display;
	}
}