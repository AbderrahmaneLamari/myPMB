<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_model.class.php,v 1.12.2.1 2021/07/19 14:23:38 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_model {
	
	/**
	 * Identifiant de la liste
	 * @var int
	 */
	protected $id;
	
	/**
	 * Identifiant de l'utilisateur
	 */
	protected $num_user;
	
	/**
	 * Type d'objet
	 * @var string
	 */
	protected $objects_type;
	
	/**
	 * Libellé de la liste
	 * @var string
	 */
	protected $label;
	
	/**
	 * Colonnes sélectionnées
	 */
	protected $selected_columns;
	
	/**
	 * Filtres
	 * @var array
	 */
	protected $filters;
	
	/**
	 * Groupement appliqué
	 */
	protected $applied_group;
	
	/**
	 * Tri appliqué
	 */
	protected $applied_sort;
	
	/**
	 * Pagination
	 * @var array
	 */
	protected $pager;
	
	/**
	 * Filtres sélectionnés
	 * @var array
	 */
	protected $selected_filters;
	
	/**
	 * Paramétrages
	 * @var array
	 */
	protected $settings;
	
	/**
	 * Liste des autorisations
	 * @var array
	 */
	protected $autorisations;
	
	/**
	 * Sélectionné par défaut
	 * @var int
	 */
	protected $default_selected;
	
	/**
	 * Ordre
	 * @var int $order
	 */
	protected $order;
	
	/**
	 * Instance de list_ui dérivée
	 * @var list_ui
	 */
	protected $list_ui;
	
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		global $PMBuserid;
		$this->num_user = $PMBuserid;
		$this->objects_type = '';
		$this->label = '';
		$this->selected_columns = array();
		$this->filters = array();
		$this->applied_group = array();
		$this->applied_sort = array();
		$this->pager = array();
		$this->selected_filters = array();
		$this->settings = array();
		$this->autorisations = array($PMBuserid);
		$this->default_selected = 0;
		$this->order = 0;
		if($this->id) {
			$query = "select * from lists where id_list = ".$this->id;
			$result = pmb_mysql_query($query);
			$row = pmb_mysql_fetch_object($result);
			$this->num_user = $row->list_num_user;
			$this->objects_type = $row->list_objects_type;
			$this->label = $row->list_label;
			$this->selected_columns = encoding_normalize::json_decode($row->list_selected_columns, true);
			$this->filters = encoding_normalize::json_decode($row->list_filters, true);
			$this->applied_group = encoding_normalize::json_decode($row->list_applied_group, true);
			$this->applied_sort = encoding_normalize::json_decode($row->list_applied_sort, true);
			$this->pager = encoding_normalize::json_decode($row->list_pager, true);
			$this->selected_filters = encoding_normalize::json_decode($row->list_selected_filters, true);
			$this->settings = encoding_normalize::json_decode($row->list_settings, true);
			$this->autorisations = explode(' ', $row->list_autorisations);
			$this->default_selected = $row->list_default_selected;
			$this->order = $row->list_order;
		}
	}
	
	public function set_properties_from_form() {
		global $list_label;
		global $autorisations;
		global $list_default_selected;
		
		$this->label = stripslashes($list_label);
		$this->list_ui->set_selected_columns_from_form();
		$this->selected_columns = $this->list_ui->get_selected_columns();
		$this->list_ui->set_filters_from_form();
		$this->filters = $this->list_ui->get_filters();
		$this->list_ui->set_applied_group_from_form();
		$this->applied_group = $this->list_ui->get_applied_group();
		$this->list_ui->set_applied_sort_from_form();
		$this->applied_sort = $this->list_ui->get_applied_sort();
		$this->list_ui->set_pager_from_form();
		$this->pager = $this->list_ui->get_pager();
		$this->list_ui->set_selected_filters_from_form();
		$this->selected_filters = $this->list_ui->get_selected_filters();
		$this->list_ui->set_settings_from_form();
		$this->settings = $this->list_ui->get_settings();
		if (is_array($autorisations)) {
			$this->autorisations = $autorisations;
		} else {
			$this->autorisations = array(1);
		}
		$this->default_selected = $list_default_selected+0;
	}
	
	public function get_query_if_exists() {
		return " SELECT count(1) FROM lists WHERE list_label = '".addslashes($this->label)."' AND list_num_user = '".$this->num_user."' AND list_objects_type = '".$this->objects_type."' AND id_list <> '".$this->id."'";
	}
	
	public function save() {
		if($this->id) {
			$query = "UPDATE lists set ";
			$where = "where id_list = ".$this->id;
		} else {
			$query = "insert into lists set
				list_num_user = '".$this->num_user."',
				list_objects_type = '".$this->objects_type."',";
			$where = "";
		}
		$query .= "
			list_label = '".addslashes($this->label)."',
			list_selected_columns = '".addslashes(json_encode($this->selected_columns))."',
			list_filters = '".addslashes(json_encode($this->filters))."',
			list_applied_group = '".addslashes(json_encode($this->applied_group))."',
			list_applied_sort = '".addslashes(json_encode($this->applied_sort))."',
			list_pager = '".addslashes(json_encode($this->pager))."',
			list_selected_filters = '".addslashes(json_encode($this->selected_filters))."',
			list_settings = '".addslashes(json_encode($this->settings))."',
			list_autorisations = '".implode(' ', $this->autorisations)."',
			list_default_selected = ".$this->default_selected.",
			list_order = ".$this->order."
			".$where."
		";
		$result = pmb_mysql_query($query);
		if($result) {
			if(!$this->id) {
				$this->id = pmb_mysql_insert_id();
			}
			return true;
		} else {
			return false;
		}
	}
	
	public static function delete($id) {
		global $PMBuserid;
		
		$id = intval($id);
		$query = "delete from lists where id_list = ".$id." and list_num_user = ".$PMBuserid;
		pmb_mysql_query($query);
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_num_user() {
		return $this->num_user;
	}
	
	public function get_objects_type() {
		return $this->objects_type;
	}
	
	public function get_label() {
		return $this->label;
	}
	
	public function get_selected_columns() {
		return $this->selected_columns;
	}
	
	public function get_filters() {
		return $this->filters;
	}

	public function get_applied_group() {
		return $this->applied_group;
	}
	
	public function get_applied_sort() {
		return $this->applied_sort;
	}
	
	public function get_pager() {
		return $this->pager;
	}
	
	public function get_selected_filters() {
		return $this->selected_filters;
	}
	
	public function get_settings() {
		return $this->settings;
	}
	
	public function get_autorisations() {
		return $this->autorisations;
	}
	
	public function get_default_selected() {
		return $this->default_selected;
	}
	
	public function set_num_user($num_user) {
		$this->num_user = $num_user;
	}
	
	public function set_objects_type($objects_type) {
		$this->objects_type = $objects_type;
	}
	
	public function set_objects($objects) {
		$this->objects = $objects;
	}
	
	public function set_applied_sort($applied_sort) {
		$this->applied_sort = $applied_sort;
	}

	public function set_filters($filters) {
		$this->filters = $filters;
	}
	
	public function set_applied_group($applied_group) {
		$this->applied_group = $applied_group;
	}
	
	public function set_selected_filters($selected_filters) {
		$this->selected_filters = $selected_filters;
	}
	
	public function set_settings($settings) {
		$this->settings = $settings;
	}
	
	public function set_list_ui($list_ui) {
		$this->list_ui = $list_ui;
	}
	
	public static function get_num_dataset_common_list($objects_type) {
		$query = "SELECT id_list FROM lists WHERE list_objects_type = '".$objects_type."' AND list_num_user = 0 order by id_list DESC limit 1";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			return pmb_mysql_result($result, 0, 'id_list');
		}
		return 0;
	}
	
	public static function delete_common_list($id, $objects_type) {
		$id = intval($id);
		$query = "DELETE FROM lists WHERE id_list = ".$id." AND list_objects_type = '".$objects_type."' AND list_num_user = 0";
		pmb_mysql_query($query);
	}
}