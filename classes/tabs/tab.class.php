<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: tab.class.php,v 1.4.2.1 2021/10/26 09:33:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once($include_path."/templates/tabs/tab.tpl.php");

/**
 * class tab
 * Un menu
 */
class tab {
	
	protected $id;
	
	protected $module;
	
	protected $section;
	
	protected $label_code;
	
	protected $categ;
	
	protected $label;
	
	protected $sub;
	
	protected $url_extra;
	
	protected $number;
	
	protected $destination_link;
	
	protected $visible;
		
	protected $autorisations;
	
	protected $autorisations_all;
	
	protected $shortcut;
	
	protected $order;
	
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->fetch_data();
	}
	
	protected function fetch_data(){
		$this->visible = 1;
		$this->autorisations = '';
		$this->autorisations_all = 1;
		$this->get_shortcut();
		if(!$this->id) return;
		
		$query = 'SELECT * FROM tabs WHERE id_tab = '.$this->id;
		$result = pmb_mysql_query($query);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		$data = pmb_mysql_fetch_object($result);
		$this->module = $data->tab_module;
		$this->categ = $data->tab_categ;
		$this->sub = $data->tab_sub;
		$this->visible = $data->tab_visible;
		$this->autorisations = $data->tab_autorisations;
		$this->autorisations_all = $data->tab_autorisations_all;
		$this->shortcut = $data->tab_shortcut;
		$this->order = $data->tab_order;
	}
	
	public function get_form() {
		global $msg, $charset;
		global $tab_content_form;
		
		$content_form = $tab_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_account_form('tab_form');
		$interface_form->set_label($msg['tab_form_edit']." : ".$this->label);
		
		$content_form = str_replace('!!label!!', htmlentities($this->label, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!visible!!', ($this->visible ? "checked='checked'" : ""), $content_form);
		$content_form = str_replace('!!autorisations_users!!', users::get_form_autorisations($this->autorisations,0), $content_form);
		$content_form = str_replace('!!autorisations_all!!', ($this->autorisations_all ? "checked='checked'" : ""), $content_form);
		$content_form = str_replace('!!shortcut!!', htmlentities($this->get_shortcut(), ENT_QUOTES, $charset), $content_form);
		
		$content_form = str_replace('!!module!!', $this->module, $content_form);
		$content_form = str_replace('!!categ!!', $this->categ, $content_form);
		$content_form = str_replace('!!sub!!', $this->sub, $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_content_form($content_form)
		->set_table_name('tabs');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $tab_module, $tab_categ, $tab_sub;
		global $tab_visible, $autorisations, $tab_autorisations_all, $tab_shortcut, $tab_order;
		
		$this->module = stripslashes($tab_module);
		$this->categ = stripslashes($tab_categ);
		$this->sub = stripslashes($tab_sub);
		$this->visible = intval($tab_visible);
		if (is_array($autorisations)) {
			$this->autorisations=implode(" ",$autorisations);
		} else {
			$this->autorisations="";
		}
		$this->autorisations_all = intval($tab_autorisations_all);
		$this->shortcut = stripslashes($tab_shortcut);
		$this->order = intval($tab_order);
	}
	
	public function save() {
		if($this->id) {
			$query = 'update tabs set ';
			$where = 'where id_tab= '.$this->id;
		} else {
			$query = 'insert into tabs set ';
			$where = '';
		}
		$query .= '
				tab_module = "'.addslashes($this->module).'",
                tab_categ = "'.addslashes($this->categ).'",
				tab_sub = "'.addslashes($this->sub).'",
				tab_visible = "'.$this->visible.'",
				tab_autorisations = "'.addslashes($this->autorisations).'",
				tab_autorisations_all = "'.$this->autorisations_all.'",
				tab_shortcut = "'.addslashes($this->shortcut).'"
				'.$where;
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
		$id = intval($id);
		$query = 'DELETE FROM tabs WHERE id_tab = '.$id;
		pmb_mysql_query($query);
		return true;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_module() {
		return $this->module;
	}
	
	public function set_module($module) {
		$this->module = $module;
		return $this;
	}
	
	public function get_section() {
		return $this->section;
	}
	
	public function set_section($section) {
		$this->section = $section;
		return $this;
	}
	
	public function get_label_code() {
		return $this->label_code;
	}
	
	public function set_label_code($label_code) {
		$this->label_code = $label_code;
		return $this;
	}
	
	public function get_categ() {
		return $this->categ;
	}
	
	public function set_categ($categ) {
		$this->categ = $categ;
		return $this;
	}
	
	public function get_label() {
		return $this->label;
	}
	
	public function set_label($label) {
		$this->label = $label;
		return $this;
	}
	
	public function get_sub() {
		return $this->sub;
	}
	
	public function set_sub($sub) {
		$this->sub = $sub;
		return $this;
	}
	
	public function get_url_extra() {
		return $this->url_extra;
	}
	
	public function set_url_extra($url_extra) {
		$this->url_extra = $url_extra;
		return $this;
	}
	
	public function get_number() {
		return $this->number;
	}
	
	public function set_number($number) {
		$this->number = $number;
		return $this;
	}
	
	public function get_destination_link() {
		return $this->destination_link;
	}
	
	public function set_destination_link($destination_link) {
		$this->destination_link = $destination_link;
		return $this;
	}
	
	public function get_visible() {
		return $this->visible;
	}
	
	public function set_visible($visible) {
		$this->visible = $visible;
		return $this;
	}
	
	public function get_autorisations() {
		return $this->autorisations;
	}
	
	public function set_autorisations($autorisations) {
		$this->autorisations = $autorisations;
		return $this;
	}
	
	public function get_autorisations_all() {
		return $this->autorisations_all;
	}
	
	public function set_autorisations_all($autorisations_all) {
		$this->autorisations_all = $autorisations_all;
		return $this;
	}
	
	public function get_shortcut() {
		global $raclavier;
		
		if(!isset($this->shortcut)) {
			if(!empty($raclavier)) {
				foreach ($raclavier as $rac) {
					if($rac[1] == $this->destination_link) {
						$this->shortcut = $rac[0];
					}
				}
			}
		}
		return $this->shortcut;
	}
	
	public function set_shortcut($shortcut) {
		$this->shortcut = $shortcut;
		return $this;
	}
	
	public function get_order() {
		return $this->order;
	}
	
	public function set_order($order) {
		$this->order = $order;
		return $this;
	}
	
	public function is_in_database() {
		if($this->id) {
			return true;
		}
		$query = 'SELECT * FROM tabs
			WHERE tab_module = "'.addslashes($this->module).'"
			AND tab_categ = "'.addslashes($this->categ).'"
			AND tab_sub="'.addslashes($this->sub).'"';
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			$data = pmb_mysql_fetch_object($result);
			$this->id = $data->id_tab;
			$this->fetch_data();
			return true;
		}
		return false;
	}
	
	public function is_substituted() {
		if($this->id) {
			return true;
		}
		return false;
	}
} // end of tab