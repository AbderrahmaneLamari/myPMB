<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_bulletins_ui.class.php,v 1.1.2.8 2022/01/20 15:28:21 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_bulletins_ui extends list_ui {
		
	protected function _get_query_base() {
		$query = 'SELECT bulletin_id FROM bulletins';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new bulletinage($row->bulletin_id,0,'',$this->filters['docs_location_id'],false);
	}
	
	protected function is_visible_object($row) {
		if($this->filters['docs_location_id']) {
			$query_expl = "SELECT count(*) FROM exemplaires WHERE expl_bulletin = ".$row->bulletin_id." AND expl_location = ".$this->filters['docs_location_id'];
			$count_expl = pmb_mysql_result(pmb_mysql_query($query_expl), 0);
			$query_explnum = "SELECT count(*) FROM explnum LEFT JOIN explnum_location ON explnum_location.num_explnum = explnum.explnum_id  WHERE explnum_bulletin = ".$row->bulletin_id." AND explnum_location.num_location = ".$this->filters['docs_location_id'];
			$count_explnum = pmb_mysql_result(pmb_mysql_query($query_explnum), 0);
			if(!$count_expl && !$count_explnum) {
				return false;
			}
		}
		return true;
	}
	
	protected function add_object($row) {
		if($this->is_visible_object($row)) {
			parent::add_object($row);
		}
	}
	
	protected function fetch_data() {
		parent::fetch_data();
		$this->pager['nb_results'] = count($this->objects);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'location' => '',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		global $deflt_bulletinage_location;
		
		$this->filters = array(
				'serial_id' => 0,
				'docs_location_id' => $deflt_bulletinage_location,
				'bulletin_numero' => '',
				'date_date_start' => '',
				'date_date_end' => '',
				'mention_date' => ''
		);
		parent::init_filters($filters);
	}
	
	protected function init_override_filters() {
		global $deflt_bulletinage_location;
		
		$this->filters['docs_location_id'] = $deflt_bulletinage_location;
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('location');
	}
	
	/**
	 * Initialisation de la pagination par défaut
	 */
	protected function init_default_pager() {
		global $nb_per_page_a_search;
		parent::init_default_pager();
		$this->pager['nb_per_page'] = ($nb_per_page_a_search ? $nb_per_page_a_search : 10);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('date_date', 'desc');
	    $this->add_applied_sort('bulletin_numero');
	    $this->add_applied_sort('bulletin_id', 'desc');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		global $pmb_collstate_advanced;
		
		$this->available_columns =
		array('main_fields' =>
				array(
						'bulletin_numero' => '4025',
						'date_date' => '4026',
						'mention_date' => 'bulletin_mention_periode',
						'bulletin_titre' => 'bulletin_mention_titre_court',
						'nb_analysis' => 'bul_articles',
						'nb_explnum' => 'bul_docnum',
						'nb_expl' => 'bul_exemplaires'
				)
		);
		if ($pmb_collstate_advanced) {
			$this->available_columns['main_fields']['collstates'] = 'bul_collstate';
		}
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function get_display_html_content_selection() {
		return "<div class='center'>
					<input type='checkbox' id='".$this->objects_type."_selection_!!bulletin_id!!' name='".$this->objects_type."_selection[!!bulletin_id!!]' class='".$this->objects_type."_selection' value='!!bulletin_id!!'>
					<span id=\"BULL_drag_!!bulletin_id!!\" dragicon='".get_url_icon('icone_drag_notice.png')."' dragtext=\"!!bulletin_numero!!\" draggable=\"yes\" dragtype=\"notice\" callback_before=\"show_carts\" callback_after=\"\" style=\"padding-left:7px;cursor: pointer;\">
						<img src=\"".get_url_icon('notice_drag.png')."\"/>
					</span>
				</div>";
	}
	
	protected function init_default_columns() {
		global $pmb_collstate_advanced;
		
		$this->add_column_selection();
		$this->add_column('bulletin_numero');
		$this->add_column('date_date');
		$this->add_column('mention_date');
		$this->add_column('bulletin_titre');
		$this->add_column('nb_analysis');
		$this->add_column('nb_explnum');
		$this->add_column('nb_expl');
		if ($pmb_collstate_advanced) {
			$this->add_column('collstates');
		}
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'unfoldable_filters', false);
		$this->set_setting_display('search_form', 'sorts', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('objects_list', 'fast_filters', true);
		$this->set_setting_column('default', 'align', 'left');
		$this->set_setting_column('nb_analysis', 'align', 'center');
		$this->set_setting_column('nb_explnum', 'align', 'center');
		$this->set_setting_column('nb_expl', 'align', 'center');
		$this->set_setting_column('date_date', 'datatype', 'date');
		$this->set_setting_column('nb_analysis', 'datatype', 'integer');
		$this->set_setting_column('nb_explnum', 'datatype', 'integer');
		$this->set_setting_column('nb_expl', 'datatype', 'integer');
		$this->set_setting_column('bulletin_numero', 'fast_filter', 1);
		$this->set_setting_column('date_date', 'fast_filter', 1);
		$this->set_setting_column('mention_date', 'fast_filter', 1);
	}
	
	protected function get_form_title() {
		global $msg, $charset;
		
		$form_title = htmlentities($msg['4001'], ENT_QUOTES, $charset);
		$form_title .= " (<a href='./catalog.php?categ=serials&sub=pointage&serial_id=".$this->filters['serial_id']."&location_view=".$this->filters['docs_location_id']."'>".$msg["link_notice_to_bulletinage"]."</a>)";
		return $form_title;
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		global $docs_location_id;
		if(isset($docs_location_id)) {
			$this->filters['docs_location_id'] = intval($docs_location_id);
		}
		$this->set_filter_from_form('bulletin_numero');
		$this->set_filter_from_form('date_date_start');
		$this->set_filter_from_form('date_date_end');
		$this->set_filter_from_form('mention_date');
		parent::set_filters_from_form();
	}
		
	protected function get_search_filter_location() {
		return docs_location::gen_combo_box_docs($this->filters['docs_location_id'], 1, "this.form.submit();");
	}
	
	protected function init_default_selection_actions() {
		global $msg;
		global $base_path;
		
		parent::init_default_selection_actions();
		$link = array();
		$link['openPopUp'] = $base_path."/cart.php?object_type=BULL";
		$link['openPopUpTitle'] = 'cart';
		$this->add_selection_action('caddie', $msg['400'], 'basket_small_20x20.gif', $link);
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['serial_id']) {
			$filters [] = 'bulletin_notice = "'.$this->filters['serial_id'].'"';
		}
		if($this->filters['bulletin_numero']) {
			$filters [] = 'bulletin_numero like "%'.str_replace('*','%', $this->filters['bulletin_numero']).'%"';
		}
		if($this->filters['date_date_start']) {
			$filters [] = 'date_date >= "'.$this->filters['date_date_start'].'"';
		}
		if($this->filters['date_date_end']) {
			$filters [] = 'date_date < "'.$this->filters['date_date_end'].'"';
		}
		if($this->filters['mention_date']) {
			$filters [] = 'mention_date like "%'.str_replace('*','%', $this->filters['mention_date']).'%"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);		
		}
		return $filter_query;
	}
	
	protected function _get_object_property_nb_expl($object) {
		if (!empty($object->expl) && is_array($object->expl)) {
			return count($object->expl);
		}
		return '';
	}
	
	protected function _get_object_property_nb_explnum($object) {
		if($object->nbexplnum) {
			return $object->nbexplnum;
		}
		return '';
	}
	
	protected function get_collstates($object) {
		global $pmb_collstate_advanced;
		
		$collstates = array();
		if ($pmb_collstate_advanced) {
			$query = "SELECT collstate_bulletins_num_collstate, state_collections FROM collstate_bulletins JOIN collections_state ON collections_state.collstate_id = collstate_bulletins.collstate_bulletins_num_collstate WHERE collstate_bulletins_num_bulletin = '".$object->bulletin_id."'";
			$result = pmb_mysql_query($query);
			if (pmb_mysql_num_rows($result)) {
				while ($row = pmb_mysql_fetch_object($result)) {
					$collstates[$row->collstate_bulletins_num_collstate] = $row->state_collections;
				}
			}
		}
		return $collstates;
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
	
		$content = '';
		switch($property) {
			case 'nb_analysis':
				if ($object->nb_analysis) {
					$content .= $object->nb_analysis."&nbsp;<img src='".get_url_icon('basket_small_20x20.gif')."' class='align_middle' alt='basket' title='".$msg[400]."' onClick=\"openPopUp('./cart.php?object_type=BULL&item=".$object->bulletin_id."&what=DEP', 'cart')\" style='cursor:pointer;'>";
				}
				break;
			case 'collstates':
				$collstates = $this->get_collstates($object);
				foreach($collstates as $id => $collstate) {
					if($content) {
						$content.= "<br/>";
					}
					$content .="<a href='./catalog.php?categ=serials&sub=collstate_bulletins_list&id=".$id."&serial_id=".$this->filters['serial_id']."&bulletin_id=0'>".$collstate."</a>";
				}
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
			case 'nb_analysis':
				break;
			default:
				$attributes['onclick'] = "window.location=\"".bulletinage::get_permalink($object->bulletin_id)."\"";
				break;
		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function get_display_cell_html_value($object, $value) {
		$value = str_replace('!!bulletin_id!!', $object->bulletin_id, $value);
		$value = str_replace('!!bulletin_numero!!', $object->bulletin_numero, $value);
		return parent::get_display_cell_html_value($object, $value);
	}
	
	protected function get_name_selected_objects() {
		return "checkbox_bulletin";
	}
	
	public function get_error_message_empty_list() {
		global $msg;
		
		if (!empty($this->filters['mention_date']) || !empty($this->filters['date_date_start']) || !empty($this->filters['date_date_end']) || !empty($this->filters['bulletin_numero'])) {
			return $msg['perio_restrict_no_bulletin'];
		} else {
			return $msg[4024] ;
		}
	}
	
	protected function get_error_message_empty_selection($action=array()) {
		global $msg;
		return $msg['bulletin_have_select'];
	}
	
	protected function _get_query_human_location() {
		if($this->filters['docs_location_id']) {
			$docs_location = new docs_location($this->filters['docs_location_id']);
			return $docs_location->libelle;
		}
		return '';
	}
	
	public function get_bulletins_pagination() {
		return $this->pager();
	}
	
	public static function get_controller_url_base() {
		global $serial_id;
		return parent::get_controller_url_base()."&serial_id=".$serial_id;
	}
	
	public static function get_ajax_controller_url_base() {
		global $base_path, $current_module, $sub, $serial_id;
		return $base_path.'/ajax.php?module='.$current_module.'&categ=bulletins&sub='.$sub.'&serial_id='.$serial_id;
	}
}