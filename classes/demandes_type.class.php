<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes_type.class.php,v 1.1.8.1 2023/07/07 07:05:15 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($class_path."/workflow.class.php");
require_once($include_path."/templates/demandes_type.tpl.php");

class demandes_type {

	/* ---------------------------------------------------------------
		propriétés de la classe
   --------------------------------------------------------------- */

	public $id=0;
	public $libelle='';
	public $allowed_actions=array();
	
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->getData();
	}

	/* ---------------------------------------------------------------
		getData() : récupération des propriétés
   --------------------------------------------------------------- */
	public function getData() {
		if(!$this->id) {
			$workflow = new workflow('ACTIONS');
			$this->allowed_actions = $workflow->getTypeList();
			return;
		}
	
		$requete = 'SELECT * FROM demandes_type WHERE id_type='.$this->id;
		$result = pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		$data = pmb_mysql_fetch_object($result);
		$this->libelle = $data->libelle_type;
		$this->allowed_actions = unserialize($data->allowed_actions);
		if(!is_array($this->allowed_actions) || !count($this->allowed_actions)){
			$workflow = new workflow('ACTIONS');
			$this->allowed_actions = $workflow->getTypeList();
		}
	}

	public function get_actions_form(){
		global $msg,$charset;
		
		$form = "
		<table class='demandes-actions-types'>
			<tr>
				<th>".$msg['demandes_action_type']."</th>
				<th>".$msg['demandes_action_type_allow']."</th>
				<th>".$msg['demandes_action_type_default']."</th>
			</tr>";
		foreach($this->allowed_actions as $allowed_action){
			$form.="
			<tr>
				<td>".htmlentities($allowed_action['comment'],ENT_QUOTES,$charset)."</td>
				<td>".$msg['connecteurs_yes']."&nbsp;<input type='radio' name='action_".$allowed_action['id']."' value='1'".(isset($allowed_action['active']) && $allowed_action['active'] == 1 ? " checked='checked'": "")."/>&nbsp;&nbsp;
					".$msg['connecteurs_no']."&nbsp;<input type='radio' name='action_".$allowed_action['id']."' value='0'".(empty($allowed_action['active']) ? " checked='checked'": "")."/></td>
				<td><input type='radio' name='default_action' value='".$allowed_action['id']."'".($allowed_action['default']? " checked='checked'": "")."/></td>
			</tr>";
		}
		$form.= "
		</table>";
		return $form;
	}
	
	public function get_content_form() {
		$interface_content_form = new interface_content_form(static::class);
		$interface_content_form->add_element('libelle', '103')
		->add_input_node('text', $this->libelle);
		
		$interface_content_form->add_element('actions')
		->add_html_node($this->get_actions_form());
		
		return $interface_content_form->get_display();
	}
	
	public function get_form() {
		global $msg;
		
		$interface_form = new interface_admin_form('simple_list_form');
		if(!$this->id){
			$interface_form->set_label($msg['demandes_ajout_type']);
		}else{
			$interface_form->set_label($msg['demandes_modif_type']);
		}
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['demandes_del_type'])
		->set_content_form($this->get_content_form())
		->set_table_name('demandes_type')
		->set_field_focus('libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $libelle, $default_action;
		
		$this->libelle = stripslashes($libelle);
		$allowed_actions = array();
		foreach($this->allowed_actions as $allowed_action_form){
			$val = "action_".$allowed_action_form['id'];
			global ${$val};
			$allowed_action_form['active'] = ${$val};
			if($allowed_action_form['id'] == $default_action){
				$allowed_action_form['default'] = 1;
			}else{
				$allowed_action_form['default'] = 0;
			}
			$allowed_actions[] = $allowed_action_form;
			
		}
		$this->allowed_actions = $allowed_actions;
	}
	
	public function save() {
		if($this->id) {
			$requete = "UPDATE demandes_type set libelle_type='".addslashes($this->libelle)."', allowed_actions = \"".addslashes(serialize($this->allowed_actions))."\" where id_type='".$this->id."'";
			pmb_mysql_query($requete);
		} else {
			$requete = "INSERT INTO demandes_type set libelle_type='".addslashes($this->libelle)."', allowed_actions = \"".addslashes(serialize($this->allowed_actions))."\"";
			pmb_mysql_query($requete);
			$this->id = pmb_mysql_insert_id();
		}
	}

	public static function check_data_from_form() {
		global $libelle;
		
		if(empty($libelle)) {
			return false;
		}
		return true;
	}
	
	public static function delete($id) {
		$id = intval($id);
		if ($id) {
			$total = pmb_mysql_num_rows(pmb_mysql_query("select * from demandes where type_demande = '".$id."'"));
			if ($total==0) {
				$requete = "DELETE FROM demandes_type where id_type='".$id."'";
				pmb_mysql_query($requete);
				return true;
			} else {
				pmb_error::get_instance(static::class)->add_message("321", 'demandes_used_type');
				return false;
			}
		}
		return true;
	}
} /* fin de définition de la classe */