<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: analytics_service.class.php,v 1.1.2.2 2021/07/21 09:48:58 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once $include_path.'/templates/analytics_services/analytics_service.tpl.php';


class analytics_service{
	
	protected $id = 0;
	
	protected $name = "";
	
	protected $active = 0;
	
	protected $parameters = array();
	
	protected $template = "";
	
	protected $consent_template = "";
	
	public function __construct($id=0){
		$this->id = intval($id);
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		global $name;
		
		$query = "select * from analytics_services where id_analytics_service = '".$this->id."'";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			$row = pmb_mysql_fetch_object($result);
			$this->name = $row->analytics_service_name;
			$this->active = $row->analytics_service_active;
			$this->parameters = json_decode($row->analytics_service_parameters, true);
			$this->template = $row->analytics_service_template;
			$this->consent_template = $row->analytics_service_consent_template;
		} else {
			if(!empty($name)) {
				$this->name = $name;
			}
		}
	}
	
	public function get_label() {
		global $class_path;
		
		$class_name = 'analytics_service_'.$this->name;
		require_once $class_path.'/analytics_services/services/'.$this->name.'/'.$class_name.'.class.php';
		return $class_name::get_label();
	}
	
	protected function get_parameters_content_form() {
		global $class_path;
		
		$class_name = 'analytics_service_'.$this->name;
		require_once $class_path.'/analytics_services/services/'.$this->name.'/'.$class_name.'.class.php';
		return $class_name::get_parameters_content_form($this->parameters);
	}
	
	public function get_form() {
		global $analytics_service_content_form;
		
		$content_form = $analytics_service_content_form;
		$content_form = str_replace('!!name!!', $this->name, $content_form);
		$content_form = str_replace('!!display_label!!', $this->name." (".$this->get_label().")", $content_form);
		$content_form = str_replace('!!active_checked!!', ($this->active ? "checked='checked'" : ""), $content_form);
		$content_form = str_replace('!!parameters!!', $this->get_parameters_content_form(), $content_form);
		$content_form = str_replace('!!template!!', $this->template, $content_form);
		$content_form = str_replace('!!consent_template!!', $this->consent_template, $content_form);
		$interface_form = new interface_form('analytics_service_form');
		
		$interface_form->set_content_form($content_form);
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $analytics_service_name;
		global $analytics_service_active;
		global $analytics_service_parameters;
		global $analytics_service_template;
		global $analytics_service_consent_template;
		
		if(!empty($analytics_service_name)) {
			$this->name = stripslashes($analytics_service_name);
		}
		$this->active = intval($analytics_service_active);
		$this->parameters = stripslashes_array($analytics_service_parameters);
		$this->template = stripslashes($analytics_service_template);
		$this->consent_template = stripslashes($analytics_service_consent_template);
	}
	
	public function save() {
		global $class_path;
	
		if($this->name && file_exists($class_path.'/analytics_services/services/'.$this->name)) {
			$query = "select analytics_service_name from analytics_services where analytics_service_name = '".addslashes($this->name)."'";
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)) {
				$query = "update analytics_services set
					analytics_service_active = '".$this->active."',
					analytics_service_parameters = '".encoding_normalize::json_encode($this->parameters)."',
					analytics_service_template = '".addslashes($this->template)."',
					analytics_service_consent_template = '".addslashes($this->consent_template)."'
					where analytics_service_name = '".addslashes($this->name)."'";
			} else {
				$query = "insert into analytics_services set
					analytics_service_name = '".addslashes($this->name)."',
					analytics_service_active = '".$this->active."',
					analytics_service_parameters = '".json_encode($this->parameters)."',
					analytics_service_template = '".addslashes($this->template)."',
					analytics_service_consent_template = '".addslashes($this->consent_template)."'";
			}
			pmb_mysql_query($query);
			return true;
		}
		return false;
	}
	
	public static function delete($id) {
		$id = intval($id);
		if($id) {
			$query = "DELETE FROM analytics_services WHERE id_analytics_service = ".$id;
			pmb_mysql_query($query);
		}
		return true;
	}
	
	public function get_display_service() {
		$display = $this->template;
		$display .= $this->consent_template;
		return $display; 
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_name() {
		return $this->name;
	}
	
	public function get_active() {
		return $this->active;
	}
	
	public function get_template() {
		return $this->template;
	}
	
	public function get_consent_template() {
		return $this->consent_template;
	}
	
	public function set_name($name) {
		$this->name = $name;
	}
	
	public function set_active($active) {
	    $this->active = intval($active);
	}
	
	public function set_template($template) {
		$this->template = $template;
	}
}