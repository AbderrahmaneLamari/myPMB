<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_transferts_statutsdef_ui.class.php,v 1.1 2021/02/04 08:13:12 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_configuration_transferts_statutsdef_ui extends list_configuration_transferts_ui {
	
	protected function _get_query_base() {
		return "SELECT * FROM docs_location LEFT JOIN docs_statut ON idstatut=transfert_statut_defaut";
	}
	
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('location_libelle');
	}
	
	protected function get_main_fields_from_sub() {
		return array(
				'location_libelle' => 'admin_transferts_statutsDef_site',
				'statut_libelle' => 'admin_transferts_statutsDef_statuts',
		);
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		
		$content = '';
		switch($property) {
			case 'statut_libelle':
				if ($object->statut_libelle) {
					$content .= $object->statut_libelle;
				} else {
					$content .= $msg["admin_transferts_statut_transfert_non_defini"];
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_edition_link($object) {
		return static::get_controller_url_base().'&action=modif&id='.$object->idlocation;
	}
	
	protected function get_button_add() {
		return '';
	}
}