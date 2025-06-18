<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scheduler_tasks.class.php,v 1.8 2021/03/03 07:46:02 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;

require_once($include_path."/parser.inc.php");
require_once($include_path."/templates/taches.tpl.php");
require_once($include_path."/connecteurs_out_common.inc.php");
require_once($class_path."/scheduler/scheduler_task_docnum.class.php");
require_once($class_path."/upload_folder.class.php");
require_once($class_path."/xml_dom.class.php");
require_once($class_path."/scheduler/scheduler_task.class.php");
require_once($class_path."/scheduler/scheduler_tasks_type.class.php");

class scheduler_tasks {
	
	public static $xml_catalog;
	public $tasks=array();								// liste des types de tâches
	
	public function __construct() {
		$this->fetch_data();
	}
	
	public static function parse_catalog() {
		global $base_path;
		
		if(!isset(static::$xml_catalog)) {
			if (file_exists($base_path."/admin/planificateur/catalog_subst.xml")) {
				$filename = $base_path."/admin/planificateur/catalog_subst.xml";
			} else {
				$filename = $base_path."/admin/planificateur/catalog.xml";
			}
			$xml=file_get_contents($filename);
			static::$xml_catalog = _parser_text_no_function_($xml,"CATALOG", $filename);
		}
	}
	
	public static function get_catalog_element($id=0, $attribute='') {
		$id += 0;
		if($id) {
			static::parse_catalog();
			foreach (static::$xml_catalog["ACTION"] as $anitem) {
				if($anitem['ID'] == $id) {
					return get_msg_to_display($anitem[$attribute]);
				}
			}
		}
	}
	
	protected function fetch_data() {
		static::parse_catalog();
		foreach (static::$xml_catalog["ACTION"] as $anitem) {
			$this->tasks[$anitem['NAME']] = new scheduler_tasks_type($anitem['ID']);
			$this->tasks[$anitem['NAME']]->set_name($anitem['NAME']);
			$this->tasks[$anitem['NAME']]->set_path($anitem['PATH']);
			$this->tasks[$anitem['NAME']]->set_comment($anitem['COMMENT']);
		}
	}
	
	protected function get_js_display_list () {
		global $base_path;
		
		$display = "
			<script type='text/javascript'>
				function expand_taches_all() {";
		foreach ($this->tasks as $name=>$tasks_type) {
			$display .= "
					if (document.getElementById('".$name."')) {
						if (document.getElementById('".$name."').style.display=='none') {
							document.getElementById('".$name."').style.display='';
						}
					}";
		}
		$display .= "}
			function collapse_taches_all() {";
		foreach ($this->tasks as $name=>$tasks_type) {
			$display .= "
					if (document.getElementById('".$name."')) {
						if (document.getElementById('".$name."').style.display=='') {
							document.getElementById('".$name."').style.display='none';
						} 
					}";
		}
		$display .= "}
			</script>
			<script type='text/javascript' src='".$base_path."/javascript/tablist.js'></script>";
		return $display;
	}
	
	public function get_display_list () {
		$display = $this->get_js_display_list();
		$display .= "<a href='javascript:expand_taches_all()'><img style='border:0px' id='expandall' src='".get_url_icon('expand_all.gif')."'></a>
		<a href='javascript:collapse_taches_all()'><img style='border:0px' id='collapseall' src='".get_url_icon('collapse_all.gif')."'></a>";
		$display .= list_configuration_planificateur_manager_ui::get_instance()->get_display_list();
		return $display;
	}
	
	public static function get_selector_options($type, $selected) {
		$options = '';
		static::parse_catalog();
		$num_type_tache = 0;
		foreach (static::$xml_catalog['ACTION'] as $catalog) {
			if($catalog['NAME'] == $type) {
				$num_type_tache = $catalog['ID'];
			}
		}
		$query = "select id_planificateur, libelle_tache from planificateur where num_type_tache = ".$num_type_tache;
		$result = pmb_mysql_query($query);
		while($row = pmb_mysql_fetch_object($result)) {
			$options .= "<option value='".$row->id_planificateur."' ".($row->id_planificateur == $selected ? "selected='selected'" : "")."> ".htmlentities($row->libelle_tache, ENT_QUOTES, $charset)."</option>";
		}
		return $options;
	}
}