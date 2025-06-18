<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: contact_form_object.class.php,v 1.3 2021/04/26 12:53:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/translation.class.php");
require_once($class_path."/contact_forms/contact_form_recipients.class.php");
require_once($include_path."/templates/contact_forms/contact_form.tpl.php");

class contact_form_object {
	
	/**
	 * identifiant de l'objet
	 */
	protected $id;
	
	/**
	 * Libellé de l'objet
	 * @var string
	 */
	protected $label;
	
	/**
	 * Votre message
	 * @var string
	 */
	protected $message;
	
	protected $num_contact_form;
	
	public function __construct($id=0) {
	    $this->id = intval($id);
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		
		if($this->id) {
			$query = 'select object_label, object_message, num_contact_form from contact_form_objects where id_object ='.$this->id;
			$result = pmb_mysql_query($query);
			$row = pmb_mysql_fetch_object($result);
			$this->label = $row->object_label;
			$this->message = $row->object_message;
			$this->num_contact_form = $row->num_contact_form;
		}
	}
	
	public function get_form() {
		global $msg, $charset;
		global $contact_form_object_content_form_tpl;
		
		$content_form = $contact_form_object_content_form_tpl;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_form('contact_form_object_form');
		if($this->id){
			$interface_form->set_label($msg['admin_opac_contact_form_object_form_edit']);
		}else{
			$interface_form->set_label($msg['admin_opac_contact_form_object_form_add']);
		}
		$interface_form->add_url_base("&num_contact_form=".$this->num_contact_form);

		$content_form = str_replace('!!label!!', htmlentities($this->label, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!message!!', htmlentities($this->message, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!num_contact_form!!', $this->num_contact_form, $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['admin_opac_contact_form_object_confirm_delete'])
		->set_content_form($content_form)
		->set_table_name('contact_form_objects')
		->set_field_focus('object_label');
		return $interface_form->get_display();
	}
	
	/**
	 * Données provenant d'un formulaire
	 */
	public function set_properties_from_form() {
		global $object_label;
		global $object_message;
		
		$this->label = stripslashes($object_label);
		$this->message = stripslashes($object_message);
	}
	
	/**
	 * Sauvegarde
	 */
	public function save(){
	
		if($this->id) {
			$query = 'update contact_form_objects set ';
			$where = 'where id_object= '.$this->id;
		} else {
			$query = 'insert into contact_form_objects set ';
			$where = '';
		}
		$query .= '
				object_label = "'.addslashes($this->label).'",
                object_message = "'.addslashes($this->message).'",
				num_contact_form = "'.addslashes($this->num_contact_form).'"
				'.$where;
		$result = pmb_mysql_query($query);
		if($result) {
			if(!$this->id) {
				$this->id = pmb_mysql_insert_id();
			}
			//Traductions
			$translation = new translation($this->id, 'contact_form_objects');
			$translation->update_small_text('object_label');
			$translation->update_text('object_message');
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Suppression
	 */
	public function delete(){
		if($this->id) {
			$contact_form_recipients = new contact_form_recipients($this->num_contact_form, 'by_objects');
			$contact_form_recipients->unset_recipient($this->id);
			$contact_form_recipients->save();
			translation::delete($this->id, 'contact_form_objects');
			$query = "delete from contact_form_objects where id_object = ".$this->id;
			pmb_mysql_query($query);
			return true;
		}
		return false;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_label() {
		return $this->label;
	}
	
	public function set_label($label) {
		$this->label = $label;
	}
	
	public function get_message() {
	    return $this->message;
	}
	
	public function set_message($message) {
	    $this->message = $message;
	}
	
	public function get_num_contact_form() {
		return $this->num_contact_form;
	}
	
	public function set_num_contact_form($num_contact_form) {
		$this->num_contact_form = intval($num_contact_form);
	}
}