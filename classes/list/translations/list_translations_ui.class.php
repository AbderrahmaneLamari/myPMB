<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_translations_ui.class.php,v 1.12 2022/10/06 11:57:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_translations_ui extends list_ui {
	
	protected $folder_path;
	
	protected $folder_files;
	
	protected $languages_messages;
	
	protected $classifications;
	
	protected function get_files_parsed_folder($folder_path, $recursive=1){
		$folder_files = array();
		if(file_exists($folder_path)){
			$dh = opendir($folder_path);
			while(($file = readdir($dh)) !== false){
				if($file != "." && $file != ".." && $file != "CVS"){
					if(is_dir($folder_path.'/'.$file) && $recursive){
						$folder_files[$file] = $this->get_files_parsed_folder($folder_path.'/'.$file, 1);
						if(!count($folder_files[$file])) {
							unset($folder_files[$file]);
						}
						ksort($folder_files);
					} elseif(!strpos($file, '_subst.xml') && strpos($file, '.xml') && $file != 'manifest.xml') {
						$folder_files['files'][] = $file;
					}
				}
			}
		}
		return $folder_files;
	}
	
	protected function _init_folder_path() {
		global $base_path;
		
		$this->folder_path = '';
		switch ($this->filters['root']) {
			case 'pmb':
				switch ($this->filters['module']) {
					case 'cms' :
						$this->folder_path = $base_path.'/cms';
						break;
					case 'frbr' :
						$this->folder_path = $base_path.'/classes/frbr';
					default :
						$this->folder_path = $base_path.'/includes/messages';
						break;
				}
				break;
			case 'opac':
				switch ($this->filters['module']) {
					case 'cms' :
						$this->folder_path = $base_path.'/opac_css/cms';
						break;
					case 'frbr' :
						$this->folder_path = $base_path.'/opac_css/classes/frbr';
						break;
					default :
						$this->folder_path = $base_path.'/opac_css/includes/messages';
						break;
				}
				break;
		}
	}
	
	protected function _init_languages_messages($language) {
		if(file_exists($this->folder_path."/".$language.".xml")){
			$messages = new XMLlist($this->folder_path."/".$language.".xml", 0);
			$messages->analyser();
			$this->languages_messages[$language] = $messages->table;
		}
	}
	
	protected function _init_folder_messages() {
		if(is_array($this->folder_files) && count($this->folder_files)) {
			foreach ($this->folder_files as $files) {
				foreach ($files as $filename) {
					if(!is_array($filename)) {
						if(strpos($filename, $this->filters['translation_from']) !== false) {
							$this->_init_languages_messages($this->filters['translation_from']);
						}
						if(strpos($filename, $this->filters['translation_to']) !== false) {
							$this->_init_languages_messages($this->filters['translation_to']);
						}
					}
				}
			}
		}
	}
	
	protected function fetch_data() {
		$this->set_filters_from_form();
		$this->set_applied_sort_from_form();
		$this->_init_folder_path();
		$this->folder_files = $this->get_files_parsed_folder($this->folder_path);
		$this->_init_folder_messages();
		if(!empty($this->languages_messages[$this->filters['translation_from']])) {
			foreach ($this->languages_messages[$this->filters['translation_from']] as $code=>$label) {
				$this->add_message($code, $label);
			}
			if($this->applied_sort_type != "SQL"){
				$this->pager['nb_results'] = count($this->objects);
			}
		}
		$this->messages = "";
	}
	
	public function add_message($code, $label) {
		$label_to = '';
		if(!empty($this->languages_messages[$this->filters['translation_to']][$code])) {
			$label_to = $this->languages_messages[$this->filters['translation_to']][$code];
		}
		
		//On considère que le message est traduit s'il est différent
		if(empty($this->filters['is_translated']) || ($this->filters['is_translated'] == 1 && $label == $label_to && $this->filters['translation_from'] != $this->filters['translation_to'])  || ($this->filters['is_translated'] == 2 && $label != $label_to)) {
			$module = array(
					'id' => $code,
					'code' => $code,
					'translation_from' => $label,
					'translation_to' => $label_to,
			);
			if(empty($this->filters['classifications']) || (in_array(substr($code, 0, strpos($code, '_')), $this->filters['classifications']))) {
				$this->add_object((object) $module);
			}
			$this->add_classification(substr($code, 0, strpos($code, '_')));
		}
	}
		
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters['main_fields'] = array(
				'root' => 'root',
				'module' => 'module',
				'translation_from' => 'translation_from',
				'translation_to' => 'translation_to',
				'is_translated' => 'is_translated',
				'classifications' => 'classifications'
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		global $lang;
		$this->filters = array(
				'root' => 'pmb',
				'module' => 'common',
				'translation_from' => $lang,
				'translation_to' => $lang,
				'is_translated' => 0,
				'classifications' => array(),
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('root');
		$this->add_selected_filter('module');
		$this->add_empty_selected_filter();
		$this->add_selected_filter('translation_from');
		$this->add_selected_filter('translation_to');
		$this->add_empty_selected_filter();
		$this->add_selected_filter('is_translated');
		$this->add_selected_filter('classifications');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'code' => '663',
						'translation_from' => 'translation_from',
						'translation_to' => 'translation_to',
						'classification' => 'classification',
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('code');
		$this->add_column('translation_from');
		$this->add_column('translation_to');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_column('default', 'align', 'left');
		$this->set_setting_column('translation_to', 'display_mode', 'edition');
	}
	
	protected function init_default_applied_sort() {
		$this->add_applied_sort('translation_from');
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('root');
		$this->set_filter_from_form('module');
		$this->set_filter_from_form('translation_from');
		$this->set_filter_from_form('translation_to');
		$this->set_filter_from_form('is_translated', 'integer');
		$this->set_filter_from_form('classifications');
	}
	
	protected function get_search_filter_root() {
		$options = array(
				'pmb' => 'PMB',
				'opac' => 'OPAC'
		);
		return $this->get_search_filter_simple_selection('', 'root', '', $options);
	}
	
	protected function get_search_filter_module() {
		global $msg;
		
		$options = array(
				'common' => $msg['common'],
				'cms' => $msg['param_cms'],
				'frbr' => $msg['frbr']
		);
		return $this->get_search_filter_simple_selection('', 'module', '', $options);
	}
	
	protected function get_languages_selector($name='', $lang='') {
		global $charset;
		global $include_path;
		
		// langue par defaut
		if(!$lang) $lang="fr_FR";
		$langues = new XMLlist("$include_path/messages/languages.xml");
		$langues->analyser();
		$clang = $langues->table;
		reset($clang);
		$selector = "<select name='".$this->objects_type."_".$name."'>";
		foreach ($clang as $cle => $value) {
			// arabe seulement si on est en utf-8
			if (($charset != 'utf-8' and $cle != 'ar') or ($charset == 'utf-8')) {
				if(strcmp($cle, $lang) != 0) $selector .= "<option value='$cle'>$value ($cle)</option>";
				else $selector .= "<option value='$cle' selected>$value ($cle)</option>";
			}
		}
		$selector .= "</select>";
		return $selector;
	}
	
	protected function get_search_filter_translation_from() {
		return $this->get_languages_selector('translation_from', $this->filters['translation_from']);
	}
	
	protected function get_search_filter_translation_to() {
		return $this->get_languages_selector('translation_to', $this->filters['translation_to']);
	}
	
	protected function get_search_filter_is_translated() {
		global $msg;
		return "
			<input type='radio' id='".$this->objects_type."_is_translated_all' name='".$this->objects_type."_is_translated' value='0' ".(!$this->filters['is_translated'] ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_is_translated_all'>".$msg['all']."</label>
			<input type='radio' id='".$this->objects_type."_is_translated_no' name='".$this->objects_type."_is_translated' value='1' ".($this->filters['is_translated'] == 1 ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_is_translated_no'>".$msg['39']."</label>
			<input type='radio' id='".$this->objects_type."_is_translated_yes' name='".$this->objects_type."_is_translated' value='2' ".($this->filters['is_translated'] == 2 ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_is_translated_yes'>".$msg['40']."</label>";
	}
	
	protected function get_search_filter_classifications() {
		global $msg;
		$options = array();
		if(!empty($this->classifications)) {
			foreach ($this->classifications as $value=>$counter) {
				if($counter > 20) {
					$options[$value] = $value;
				}
			}
		}
		return $this->get_search_filter_multiple_selection('', 'classifications', $msg['all'], $options);
	}
	
	protected function _add_query_filters() {
		$this->_add_query_filter_simple_restriction('translation_to', 'trans_lang');
	}
	
	protected function _get_query_human_is_translated() {
		global $msg;
		
		if($this->filters['is_translated']) {
			return $msg['40'];
		} else {
			return $msg['39'];
		}
	}
	
	protected function init_default_selection_actions() {
		global $msg;
		
		parent::init_default_selection_actions();
		$save_link = array(
				'href' => static::get_controller_url_base()."&action=list_save",
				'confirm' => ''
		);
		$this->add_selection_action('save', $msg['77'], 'save.gif', $save_link);
	}
	
	public function add_classification($code) {
		if(empty($code)) return;
		if(empty($this->classifications[$code])) {
			$this->classifications[$code] = 0;
		}
		$this->classifications[$code]++;
	}
}