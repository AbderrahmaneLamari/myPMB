<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_collstate_statut_ui.class.php,v 1.4.6.1 2023/03/24 08:11:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_configuration_collstate_statut_ui extends list_configuration_collstate_ui {
	
	protected function _get_query_base() {
		return 'SELECT * FROM arch_statut';
	}
	
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('archstatut_gestion_libelle');
	}
	
	protected function get_main_fields_from_sub() {
		return array(
				'archstatut_gestion_libelle' => 'collstate_statut_libelle',
				'archstatut_opac_libelle' => 'collstate_statut_libelle',
				'archstatut_visible_opac' => 'collstate_statut_visu_opac',
		);
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('archstatut_visible_opac', 'align', 'center');
		$this->set_setting_column('archstatut_visible_opac', 'datatype', 'boolean');
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch($property) {
			case 'archstatut_gestion_libelle':
				$content .= "<span class='".$object->archstatut_class_html."'  style='margin-right: 3px;'><img src='".get_url_icon('spacer.gif')."' width='10' height='10' /></span>";
				break;
		}
		$content .= parent::get_cell_content($object, $property);
		return $content;
	}
	
	public function get_display_header_list() {
		global $msg;
	
		$display = "
		<tr>
			<th>".$msg["collstate_statut_gestion"]."</th>
			<th colspan=2>".$msg["collstate_statut_opac"]."</th>
		</tr>";
		$display .= parent::get_display_header_list();
		return $display;
	}
	
	protected function get_edition_link($object) {
		return static::get_controller_url_base().'&action=modif&id='.$object->archstatut_id;
	}
	
	protected function get_label_button_add() {
		global $msg;
		
		return $msg['115'];
	}
}