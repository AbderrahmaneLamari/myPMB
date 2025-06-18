<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_scheduler_dashboard_ui.class.php,v 1.21.2.3 2021/11/17 13:33:29 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($include_path.'/templates/list/list_scheduler_dashboard_ui.tpl.php');
require_once($class_path.'/scheduler/scheduler_tasks.class.php');
require_once($class_path.'/scheduler/scheduler_task.class.php');

class list_scheduler_dashboard_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'SELECT id_tache as id, num_type_tache, libelle_tache as label, start_at as date_start, end_at as date_end, status as state, msg_statut, calc_next_date_deb, calc_next_heure_deb, commande, indicat_progress as progress
				from taches
				join planificateur ON taches.num_planificateur = planificateur.id_planificateur';
		return $query;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'types' => 'scheduler_types',
						'labels' => 'scheduler_labels',
						'states' => 'scheduler_states',
						'date' => 'scheduler_dates',
						
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'types' => array(),
				'labels' => array(),
				'date_start' => '',
				'date_end' => '',
				'states' => array()
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('types');
		$this->add_selected_filter('labels');
		$this->add_selected_filter('states');
		$this->add_selected_filter('date');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'label' => 'planificateur_task',
					'date_start' => 'planificateur_start_exec',
					'date_end' => 'planificateur_end_exec',
					'date_next' => 'planificateur_next_exec',
					'progress' => 'planificateur_progress_task',
					'state' => 'planificateur_etat_exec',
					'command' => 'planificateur_commande_exec',
			)
		);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('date_next', 'desc');
	}
	
	/**
	 * Fonction de callback
	 * @param $a
	 * @param $b
	 */
	protected function _compare_objects($a, $b) {
	    $sort_by = $this->applied_sort[0]['by'];
		switch ($sort_by) {
			case 'date_start':
			case 'date_end':
				if($a->{$sort_by} == '0000-00-00 00:00:00') {
					return -1;
				} elseif($b->{$sort_by} == '0000-00-00 00:00:00') {
					return 1;
				} else {
					return strcmp($a->{$sort_by}, $b->{$sort_by});
				}
				break;
			case 'date_next':
				$scheduler_dashboard = new scheduler_dashboard();
				$a_date_next = strip_tags($scheduler_dashboard->command_waiting($a->id));
				$b_date_next = strip_tags($scheduler_dashboard->command_waiting($b->id));
				if($a_date_next == '' && $b_date_next == '') {
					return strcmp($a->date_start, $b->date_start);
				} elseif($a_date_next == '') {
					return -1;
				} elseif($b_date_next == '') {
					return 1;
				} else {
					return strcmp($a_date_next, $b_date_next);
				}
				break;
			default:
				return parent::_compare_objects($a, $b);
		}
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('types', 'integer');
		$this->set_filter_from_form('labels');
		$this->set_filter_from_form('states');
		$this->set_filter_from_form('date_start');
		$this->set_filter_from_form('date_end');
		parent::set_filters_from_form();
	}
	
	protected function init_default_selection_actions() {
		global $msg;
		
		parent::init_default_selection_actions();
		$delete_link = array(
				'href' => static::get_controller_url_base()."&action=list_delete",
				'confirm' => $msg['scheduler_delete_confirm']
		);
		$this->add_selection_action('delete', $msg['63'], 'interdit.gif', $delete_link);
	}
	
	protected function get_display_cell_html_value($object, $value) {
		if($object->state <= 2) {
			$value = "";
		}
		return parent::get_display_cell_html_value($object, $value);
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('label');
		$this->add_column('date_start');
		$this->add_column('date_end');
		$this->add_column('date_next');
		$this->add_column('progress');
		$this->add_column('state');
		$this->add_column('command');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'options', true);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_column('date_start', 'datatype', 'datetime');
		$this->set_setting_column('date_end', 'datatype', 'datetime');
		$this->set_setting_column('date_next', 'datatype', 'datetime');
	}
	
	protected function get_search_filter_types() {
		global $msg;
	
		$options = array();
		scheduler_tasks::parse_catalog();
		foreach (scheduler_tasks::$xml_catalog['ACTION'] as $element) {
			$options[$element['ID']] = get_msg_to_display($element['COMMENT']);
		}
		return $this->get_search_filter_multiple_selection('', 'types', $msg['scheduler_all'], $options);
	}
	
	protected function get_search_filter_labels() {
		global $msg;
	
		$query = "SELECT distinct libelle_tache as id, libelle_tache as label FROM planificateur ORDER BY libelle_tache";
		return $this->get_search_filter_multiple_selection($query, 'labels', $msg['scheduler_all']);
	}
	
	protected function get_search_filter_states() {
		global $msg;
	
		$options = array();
		$query = "SELECT distinct status FROM taches";
		$result = pmb_mysql_query($query);
		while ($row = pmb_mysql_fetch_object($result)) {
			$options[$row->status] = $msg['planificateur_state_'.$row->status];
		}
		return $this->get_search_filter_multiple_selection('', 'states', $msg['scheduler_all'], $options);
	}
	
	protected function get_search_filter_date() {
		return $this->get_search_filter_interval_date('date');
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if(is_array($this->filters['types']) && count($this->filters['types'])) {
			$filters [] = 'num_type_tache IN ("'.implode('","', $this->filters['types']).'")';
		}
		if(is_array($this->filters['labels']) && count($this->filters['labels'])) {
			$filters [] = 'libelle_tache IN ("'.implode('","', addslashes_array($this->filters['labels'])).'")';
		}
		if(is_array($this->filters['states']) && count($this->filters['states'])) {
			$filters [] = 'status IN ("'.implode('","', $this->filters['states']).'")';
		}
		if($this->filters['date_start']) {
			$filters [] = 'start_at >= "'.$this->filters['date_start'].'"';
		}
		if($this->filters['date_end']) {
			$filters [] = 'end_at <= "'.$this->filters['date_end'].' 23:59:59"';
		}
		if($this->filters['ids']) {
			$filters [] = 'id_tache IN ('.$this->filters['ids'].')';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function _get_query_human_types() {
		$types_labels = array();
		scheduler_tasks::parse_catalog();
		foreach (scheduler_tasks::$xml_catalog['ACTION'] as $element) {
			if(in_array($element['ID'], $this->filters['types'])) {
				$types_labels[] = get_msg_to_display($element['COMMENT']);
			}
		}
		return $types_labels;
	}
	
	protected function _get_query_human_states() {
		global $msg;
		$states_labels = array();
		foreach ($this->filters['states'] as $state) {
			$states_labels[] = $msg['planificateur_state_'.$state];
		}
		return $states_labels;
	}
	
	protected function _get_query_human_date() {
		return $this->_get_query_human_interval_date('date');
	}
	
	protected function get_commands($object) {
		global $charset;
		
		$scheduler_tasks = new scheduler_tasks();
		foreach ($scheduler_tasks->tasks as $name=>$tasks_type) {
			if ($tasks_type->get_id() == $object->num_type_tache) {
				//présence de commandes .. selecteurs ??
				$show_commands = "";
				$states = $tasks_type->get_states();
				foreach ($states as $aelement) {
					if ($object->state == $aelement["id"]) {
						foreach ($aelement["nextState"] as $state) {
							if ($state["command"] != "") {
								//récupère le label de la commande
								$commands = $tasks_type->get_commands();
								foreach($commands as $command) {
									if (($state["command"] == $command["name"]) && ($state["dontsend"] != "yes")) {
										$show_commands .= "<option id='".$object->id."' value='".$command["id"]."'>".htmlentities($command["label"], ENT_QUOTES, $charset)."</option>";
									}
								}
							}
						}
					}
				}
				return $show_commands;
			}
		}
		return '';
	}
	
	protected function _get_object_property_state($object) {
		global $msg;
		return $msg['planificateur_state_'.$object->state];
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		
		$content = '';
		switch($property) {
			case 'date_next':
				$scheduler_dashboard = new scheduler_dashboard();
				$content .= $scheduler_dashboard->command_waiting($object->id);
				break;
			case 'progress':
				$scheduler_progress_bar = new scheduler_progress_bar($object->progress);
				$content .= $scheduler_progress_bar->get_display();
				break;
			case 'command':
				$show_commands = $this->get_commands($object);
				if ($show_commands != "") {
					$content .= "<select id='form_commandes' name='form_commandes' class='saisie-15em' onchange='commande(this.options[this.selectedIndex].id, this.options[this.selectedIndex].value)' onClick='if (event) e=event; else e=window.event; e.cancelBubble=true; if (e.stopPropagation) e.stopPropagation();'>
					<option value='0' selected>".$msg['planificateur_commande_default']."</option>";
					$content .= $show_commands;
					$content .= "</select>";
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_display_cell($object, $property) {
		if($property == 'date_next') {
			return $this->get_cell_content($object, $property);
		} else {
			//lien du rapport
			$line="onmousedown=\"if (event) e=event; else e=window.event; \" onClick='show_layer(); get_report_content(".$object->id.",".$object->num_type_tache.");' style='cursor: pointer'";
			return "<td class='center' ".$line.">".$this->get_cell_content($object, $property)."</td>";
		}
	}
	
	/**
	 * Affiche la recherche + la liste
	 */
	public function get_display_list() {
		global $base_path;
		
		$display = "<script>
			function show_docsnum(id) {
				if (document.getElementById(id).style.display=='none') {
					document.getElementById(id).style.display='';
		
				} else {
					document.getElementById(id).style.display='none';
				}
			}
		</script>
		<script type=\"text/javascript\" src='".$base_path."/javascript/select.js'></script>
		<script>
			var ajax_get_report=new http_request();
		
			function get_report_content(task_id,type_task_id) {
				var url = './ajax.php?module=ajax&categ=planificateur&sub=get_report&task_id='+task_id+'&type_task_id='+type_task_id;
				  ajax_get_report.request(url,0,'',1,show_report_content,0,0);
			}
		
			function show_report_content(response) {
				document.getElementById('frame_notice_preview').innerHTML=ajax_get_report.get_text();
			}
		
			function refresh() {
				var url = './ajax.php?module=ajax&categ=planificateur&sub=reporting';
				if(document.getElementById('".$this->objects_type."_pager_0')) {
					var pager = document.getElementById('".$this->objects_type."_pager_0').value;
				} else if(document.getElementById('".$this->objects_type."_pager')) {
					var pager = document.getElementById('".$this->objects_type."_pager').value;
				} else {
					var pager = '';
				}
				ajax_get_report.request(url,1,'pager='+pager,1,refresh_div,0,0);
		
			}
			function refresh_div() {
				document.getElementById('scheduler_dashboard_ui_list', true).innerHTML=ajax_get_report.get_text();
				var timer=setTimeout('refresh()',20000);
			}
		
			var ajax_command=new http_request();
			var tache_id='';
			function commande(id_tache, cmd) {
				tache_id=id_tache;
				var url_cmd = './ajax.php?module=ajax&categ=planificateur&sub=command&task_id='+tache_id+'&cmd='+cmd;
				ajax_command.request(url_cmd,0,'',1,commande_td,0,0);
			}
			function commande_td() {
				document.getElementById('commande_tache_'+tache_id, true).innerHTML=ajax_command.get_text();
			}
		</script>
		<script type='text/javascript'>var timer=setTimeout('refresh()',20000);</script>";
		$display .= parent::get_display_list();
		return $display;
	}
	
	protected function get_grouped_label($object, $property) {
		$grouped_label = '';
		switch($property) {
			case 'progress':
				$grouped_label = $object->progress.'%';
				break;
			default:
				$grouped_label = parent::get_grouped_label($object, $property);
				break;
		}
		return $grouped_label;
	}
	
	public static function delete_object($id) {
		scheduler_task::delete($id);
	}
}