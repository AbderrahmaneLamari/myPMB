<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_forms_ui.class.php,v 1.2.2.1 2021/07/20 08:22:24 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// global $class_path;
// require_once($class_path."/forms/form.class.php");

class list_forms_ui extends list_ui {
	
	protected $tabs_instances = array();
	
	protected $subtabs_instances = array();
	
	protected function fetch_data() {
		$this->objects = array();
		$this->_init_forms();
		$this->messages = "";
	}
	
	/**
	 * Module Autorités
	 */
	protected function _init_autorites_forms() {
		$this->add_form('author', 'autorites', 'auteurs', 'author_form');
		$this->add_form('editor', 'autorites', 'editeurs', 'editeur_form');
		$this->add_form('collection', 'autorites', 'collections', 'collection_form');
		$this->add_form('subcollection', 'autorites', 'souscollections', 'collection_form');
		$this->add_form('serie', 'autorites', 'series', 'serie_form');
		$this->add_form('indexint', 'autorites', 'indexint', 'indexint_form');
	}
	
	/**
	 * Module Editions
	 */
	protected function _init_edit_forms() {
		//Modèles de planche de codes-barres
		$this->add_form('barcodes_sheet', 'edit', 'barcodes_sheets', 'models');
		//Modèles de planche d'étiquettes
		$this->add_form('sticks_sheet', 'edit', 'sticks_sheet', 'models');
		
		//Templates
		$this->add_form('notice_tpl', 'edit', 'tpl', 'notice');
		$this->add_form('serialcirc_tpl', 'edit', 'tpl', 'serialcirc');
		$this->add_form('bannette_tpl', 'edit', 'tpl', 'bannette');
		$this->add_form('print_cart_tpl', 'edit', 'tpl', 'print_cart_tpl');
	}
	
	/**
	 * Module Administration
	 */
	protected function _init_admin_forms() {
		global $pmb_sur_location_activate, $pmb_map_activate, $pmb_nomenclature_activate;
		global $pmb_gestion_financiere, $pmb_gestion_abonnement, $pmb_gestion_financiere_caisses;
		global $pmb_scan_request_activate, $demandes_active, $faq_active;
		
		//Exemplaires
		$this->add_form('docs_type', 'admin', 'docs', 'typdoc');
		$this->add_form('docs_location', 'admin', 'docs', 'location');
		if($pmb_sur_location_activate) {
			$this->add_form('sur_location', 'admin', 'docs', 'sur_location');
		}
		$this->add_form('docs_section', 'admin', 'docs', 'section');
		$this->add_form('docs_statut', 'admin', 'docs', 'statut');
		$this->add_form('docs_codestat', 'admin', 'docs', 'codstat');
		$this->add_form('lender', 'admin', 'docs', 'lenders');
		
		//Notices
		$this->add_form('origine_notice', 'admin', 'notices', 'orinot');
		$this->add_form('notice_statut', 'admin', 'notices', 'statut');
		if($pmb_map_activate) {
			$this->add_form('map_echelle', 'admin', 'notices', 'map_echelle');
			$this->add_form('map_projection', 'admin', 'notices', 'map_projection');
			$this->add_form('map_ref', 'admin', 'notices', 'map_ref');
		}
		$this->add_form('notice_onglet', 'admin', 'notices', 'onglet');
		$this->add_form('notice_usage', 'admin', 'notices', 'notice_usage');
		
		//Autorités
		$this->add_form('origin', 'admin', 'authorities', 'origins');
		$this->add_form('authorities_statut', 'admin', 'authorities', 'statuts');
		
		//Documents numériques
		$this->add_form('explnum_statut', 'admin', 'docnum', 'statut');
		
		//Etats des collections
		$this->add_form('arch_emplacement', 'admin', 'collstate', 'emplacement');
		$this->add_form('arch_type', 'admin', 'collstate', 'support');
		$this->add_form('arch_statut', 'admin', 'collstate', 'statut');
		
		//Abonnements
		$this->add_form('abts_periodicite', 'admin', 'abonnements', 'periodicite');
		$this->add_form('abts_status', 'admin', 'abonnements', 'status');
		
		//Lecteurs
		$this->add_form('empr_categ', 'admin', 'empr', 'categ');
		$this->add_form('empr_statut', 'admin', 'empr', 'statut');
		$this->add_form('empr_codestat', 'admin', 'empr', 'codestat');
		
		//Utilisateurs
		$this->add_form('users_groups', 'admin', 'users', 'groups');
		
		//Contenu éditorial
		$this->add_form('cms_editorial_publications_state', 'admin', 'cms_editorial', 'publication_state');
		
		//Infopages
		$this->add_form('infopage', 'admin', 'infopages');
		
		//Actions classements
		$this->add_form('procs_classement', 'admin', 'proc', 'clas');
		
		//Nomenclatures
		if($pmb_nomenclature_activate) {
			$this->add_form('nomenclature_family', 'admin', 'family', 'family');
			$this->add_form('nomenclature_musicstand', 'admin', 'family', 'family_musicstand');
			$this->add_form('nomenclature_formation', 'admin', 'formation', 'formation');
			$this->add_form('nomenclature_type', 'admin', 'formation', 'formation_type');
			$this->add_form('nomenclature_voice', 'admin', 'voice', 'voice');
			$this->add_form('nomenclature_instrument', 'admin', 'instrument', 'instrument');
		}
		
		//Gestion financière
		if (($pmb_gestion_financiere)&&($pmb_gestion_abonnement==2)) {
			$this->add_form('type_abt', 'admin', 'finance', 'abts');
		}
		if (($pmb_gestion_financiere)) {
			$this->add_form('transactype', 'admin', 'finance', 'transactype');
			$this->add_form('transaction_payment_method', 'admin', 'finance', 'transaction_payment_method');
		}
		if (($pmb_gestion_financiere)&&($pmb_gestion_financiere_caisses)) {
			$this->add_form('cashdesk', 'admin', 'finance', 'cashdesk');
		}
		
		//Récolteur
		$this->add_form('harvest', 'admin', 'harvest', 'profil');
		$this->add_form('harvest', 'admin', 'harvest', 'profil_import');
		
		//Z39.50
		$this->add_form('z_bib', 'admin', 'z3950', 'zbib');
		
		//Connecteurs
		$this->add_form('connectors_categ', 'admin', 'connecteurs', 'categ');
		$this->add_form('connector_out_setcateg', 'admin', 'connecteurs', 'categout_sets');
		
		//Acquisitions
		$this->add_form('tva_achats', 'admin', 'acquisition', 'tva');
		$this->add_form('types_produits', 'admin', 'acquisition', 'type');
		$this->add_form('frais', 'admin', 'acquisition', 'frais');
		$this->add_form('paiements', 'admin', 'acquisition', 'mode');
		$this->add_form('suggestions_categ', 'admin', 'acquisition', 'categ');
		$this->add_form('suggestion_source', 'admin', 'acquisition', 'src');
		$this->add_form('lgstat', 'admin', 'acquisition', 'lgstat');
		
		//Demandes
		if($demandes_active) {
			$this->add_form('demandes_theme', 'admin', 'demandes', 'theme');
			$this->add_form('demandes_type', 'admin', 'demandes', 'type');
		}
		
		//FAQ
		if($faq_active) {
			$this->add_form('faq_theme', 'admin', 'faq', 'theme');
			$this->add_form('faq_type', 'admin', 'faq', 'type');
		}
		
		//Template de mail
		$this->add_form('mailtpl', 'admin', 'mailtpl', 'build');
		
		//Numérisations
		if($pmb_scan_request_activate) {
			$this->add_form('scan_request_status', 'admin', 'scan_request', 'status');
			$this->add_form('scan_request_priority', 'admin', 'scan_request', 'priorities');
		}
	}
	
	/**
	 * Entités
	 */
	protected function _init_entity_forms() {
		//Autorités
		$this->add_form('auteur', 'autorites', 'auteurs', 'author_form');
		$this->add_form('editeur', 'autorites', 'editeurs', 'editeur_form');
		$this->add_form('collection', 'autorites', 'collections', 'collection_form');
		$this->add_form('subcollection', 'autorites', 'souscollections', 'collection_form');
		$this->add_form('serie', 'autorites', 'series', 'serie_form');
		$this->add_form('indexint', 'autorites', 'indexint', 'indexint_form');
		
		//Notices
		$this->add_form('notice', 'catalog', 'create_form');
		$this->add_form('serial', 'catalog', 'serials', 'serial_form');
		$this->add_form('bulletinage', 'catalog', 'serials', 'bulletinage');
		$this->add_form('analysis', 'catalog', 'serials', 'analysis');
		
	}
	
	protected function _init_forms() {
		$this->_init_admin_forms();
		$this->_init_autorites_forms();
		$this->_init_edit_forms();
		$this->_init_entity_forms();
	}
	
	public function add_form($model_name, $module, $categ, $sub='') {
		$form = new stdClass();
		$form->model_name = $model_name;
		$form->module = $module;
		$form->categ = $categ;
		$form->sub = $sub;
		$this->add_object($form);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters['main_fields'] = array();
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'label' => '103',
						'tab' => 'tabs',
						'subtab' => 'subtabs',
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	public function init_applied_group($applied_group=array()) {
		$this->applied_group = array(0 => 'module', 1 => 'tab');
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'label', 'tab', 'subtab'
		);
	}
	
	protected function init_default_pager() {
		parent::init_default_pager();
		$this->pager['all_on_page'] = true;
	}
	
	protected function init_default_columns() {
		$this->add_column('label');
		$this->add_column('tab');
		$this->add_column('subtab');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->set_setting_column('default', 'align', 'left');
		$this->settings['objects']['default']['display_mode'] = 'expandable_table';
		$this->settings['grouped_objects']['default']['display_mode'] = 'expandable_table';
	}
	
	protected function _get_object_property_module($object) {
		global $msg;
		
		$module = $object->module;
		if(isset($msg[$module])) {
			return $msg[$module];
		} else {
			return $module;
		}
	}
	
	protected function _get_object_property_label($object) {
		$label = '';
		
		
		return $label;
	}
	
	protected function _get_object_property_tab($object) {
		$tabs_instance = $this->get_tabs_instance($object->module);
		$tabs_objects = $tabs_instance->get_objects();
		foreach ($tabs_objects as $tab_object) {
			if($tab_object->get_categ() == $object->categ) {
				return $tab_object->get_label();
			}
		}
		return '';
	}
	
	protected function _get_object_property_subtab($object) {
		$subtabs_instance = $this->get_subtabs_instance($object->module, $object->categ);
		$subtabs_objects = $subtabs_instance->get_objects();
		foreach ($subtabs_objects as $subtab_object) {
			if($subtab_object->get_sub() == $object->sub) {
				return $subtab_object->get_label();
			}
		}
		return '';
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array();
// 		if($object->is_in_database()) {
// 			$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=edit&id=".$object->get_id()."\"";
// 		} else {
// 			$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=edit&form_name=".$object->get_form_name()."&form_table_name=".$object->get_form_table_name();
// 		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	public function get_tabs_instance($module) {
		if(!isset($this->tabs_instances[$module])) {
			$list_tabs_ui_name = "list_tabs_".$module."_ui";
			$list_tabs_ui_name::set_module_name($module);
			$this->tabs_instances[$module] = new $list_tabs_ui_name();
		}
		return $this->tabs_instances[$module];
	}
	
	public function get_subtabs_instance($module, $categ) {
		if(!isset($this->subtabs_instances[$module][$categ])) {
			$list_subtabs_ui_name = "list_subtabs_".$module."_ui";
			$list_subtabs_ui_name::set_module_name($module);
			$list_subtabs_ui_name::set_categ($categ);
			$this->subtabs_instances[$module][$categ] = new $list_subtabs_ui_name();
		}
		return $this->subtabs_instances[$module][$categ];
	}
}