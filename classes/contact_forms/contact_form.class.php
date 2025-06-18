<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: contact_form.class.php,v 1.2 2020/08/12 01:06:43 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/contact_forms/contact_form_parameters.class.php");
require_once($class_path."/contact_forms/contact_form_objects.class.php");
require_once($class_path."/contact_forms/contact_form_object.class.php");
require_once($class_path."/contact_forms/contact_form_recipients.class.php");

require_once($include_path."/templates/contact_forms/contact_form.tpl.php");
require_once($include_path."/h2o/h2o.php");
require_once($include_path."/mail.inc.php");

class contact_form {
	
	protected $id;
	
	protected $label;
	
	protected $desc;
	
	/**
	 * Tableau des paramètres (administration > Formulaire de contact > Paramètres)
	 * @var contact_form_parameters
	 */
	protected $parameters;
	
	/**
	 * Elements du formulaire suite à la validation
	 * @var Object
	 */
	protected $form_fields;
	
	/**
	 * Tableau de messages à afficher
	 */
	protected $messages;
	
	/**
	 * Envoyé (Oui / Non)
	 * @var Boolean
	 */
	protected $sended;
	
	/**
	 * Constructeur
	 */
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		$this->label = '';
		$this->desc = '';
		$query = "SELECT * FROM contact_forms WHERE id_contact_form = ".$this->id;
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			$row = pmb_mysql_fetch_object($result);
			$this->label = $row->contact_form_label;
			$this->desc = $row->contact_form_desc;
		}
		$contact_form_parameters = new contact_form_parameters($this->id);
		$this->parameters = $contact_form_parameters->get_parameters();
		$this->form_fields = new stdClass();
		$this->messages = array();
		$this->sended = false;
	}
	
	/**
	 * Pré-remplissage du formulaire (avec la globale associée) 
	 */
	protected function _get_global_field($name) {
		
		$value = '';
		switch ($name) {
			case 'name':
				$value = 'empr_nom';
				break;
			case 'firstname':
				$value = 'empr_prenom';
				break;
			case 'email':
				$value = 'empr_mail';
				break;
		}
		if($value) {
			global ${$value};
			return ${$value};
		} else {
			return '';
		}
	}

	/**
	 * Parcours des champs à afficher
	 */
	protected function _get_display_fields() {
		global $msg, $charset;
		
		$display_fields = "";
		if(is_array($this->parameters['fields'])) {
			foreach($this->parameters['fields'] as $name=>$field) {
				if($field['display']) {
					$display_fields .= "
					<div class='contact_form_parameter_".$name."'>
						<div class='colonne2'>
							<label for='contact_form_parameter_".$name."'>".htmlentities($msg['contact_form_parameter_'.$name], ENT_QUOTES, $charset)."</label>";
					if($field['mandatory']) {
						$display_fields .= htmlentities($msg['contact_form_parameter_mandatory_field'], ENT_QUOTES, $charset);
					}
					$display_fields .= "
						</div>
						<div class='colonne2'>";
						switch ($field['type']) {
							case 'email':
								$display_fields .= "<input type='email' id='contact_form_parameter_".$name."' name='contact_form_parameter_".$name."' value='".$this->_get_global_field($name)."' ".($field['mandatory'] ? "required='true'" : "")." />";
								break;
							case 'file':
							    $display_fields .=  static::get_attachments_field();
							    break;
							case 'text':
							default:
								$display_fields .= "<input type='text' id='contact_form_parameter_".$name."' name='contact_form_parameter_".$name."' data-dojo-type='dijit/form/TextBox' value='".$this->_get_global_field($name)."' ".($field['mandatory'] ? "required='true'" : "")." />";
								break;
						}
							
					$display_fields .= "</div>
					</div>
					<div class='contact_form_separator'>&nbsp;</div>";
				}
			}
		}
		return $display_fields;
	}
	
	public function get_form() {
		global $contact_form_content_form, $msg, $charset;
		
		$content_form = $contact_form_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_form('contact_form_form');
		if(!$this->id){
			$interface_form->set_label($msg['admin_opac_contact_form_add']);
		}else{
			$interface_form->set_label($msg['admin_opac_contact_form_edit']);
		}
		$content_form = str_replace('!!label!!', htmlentities($this->label, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!desc!!', htmlentities($this->desc, ENT_QUOTES, $charset), $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->label." ?")
		->set_content_form($content_form)
		->set_table_name('contact_forms')
		->set_field_focus('contact_form_label');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $contact_form_label, $contact_form_desc;
		
		$this->label = stripslashes($contact_form_label);
		$this->desc = stripslashes($contact_form_desc);
	}
	
	public function save() {
		// O.K.  if item already exists UPDATE else INSERT
		if($this->id) {
			$query = "UPDATE contact_forms SET contact_form_label='".addslashes($this->label)."', contact_form_desc='".addslashes($this->desc)."' WHERE id_contact_form=".$this->id;
			pmb_mysql_query($query);
		} else {
			$query = "INSERT INTO contact_forms (contact_form_label,contact_form_desc) VALUES ('".addslashes($this->label)."','".addslashes($this->desc)."') ";
			pmb_mysql_query($query);
			$this->id = pmb_mysql_insert_id();
		}
		return true;
	}
	
	public static function delete($id=0) {
		$id = intval($id);
		if (!isset($id)) {
			return;
		}
		if($id == 1) {
			return;
		}
		$query = "delete from contact_forms where id_contact_form = ".$id;
		pmb_mysql_query($query);
		contact_form_objects::delete($id);
		return true;
	}
	
	/**
	 * Vérification des données soumises
	 */
	public function check_form() {
	    global $msg;
		
		//captcha
		$securimage = new Securimage();
		if (!$securimage->check($this->form_fields->contact_form_verifcode)) {
			$this->messages[] = $msg['contact_form_verifcode_mandatory'];
		}
		//Remove random value
		$_SESSION['image_random_value'] = '';
		//spécifique au mode par objets 
		if(empty($this->form_fields->contact_form_recipients) && ($this->parameters['recipients_mode'] == 'by_objects')) {
			if($this->form_fields->contact_form_objects) {
				$this->form_fields->contact_form_recipients = $this->form_fields->contact_form_objects; 
			} elseif(!empty($this->form_fields->contact_form_object_free_entry)) {
			    $this->form_fields->contact_form_recipients = 0;
			}
		}
		if(!isset($this->form_fields->contact_form_recipients) || ($this->form_fields->contact_form_recipients === '')) {
			$this->messages[] = $msg['contact_form_recipient_mandatory'];
		}
		if(is_array($this->parameters['fields'])) {
			foreach ($this->parameters['fields'] as $name=>$field) {
				$property = 'contact_form_parameter_'.$name;
				if($field['mandatory'] && (empty($this->form_fields->{$property}) || (trim($this->form_fields->{$property}) == ''))) {
					$this->messages[] = $msg[$property.'_mandatory'];
				}
			}
		}
		if(!$this->form_fields->contact_form_objects && empty($this->form_fields->contact_form_object_free_entry)) {
		    $this->messages[] = $msg['contact_form_object_mandatory'];
		}
		if(!trim($this->form_fields->contact_form_text)) {
			$this->messages[] = $msg['contact_form_text_mandatory'];
		}
		if(count($this->messages)) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Envoi de mail
	 */
	public function send_mail() {
		global $msg, $charset;
		
		$contact_form_recipients = new contact_form_recipients($this->id, $this->parameters['recipients_mode']);
		$recipients = $contact_form_recipients->get_recipients();
		$recipient_info = $recipients[$this->parameters['recipients_mode']][$this->form_fields->contact_form_recipients];
		$transmitter_info = array();
		if(!empty($this->form_fields->contact_form_parameter_name)) {
			$transmitter_info['name'] = $this->form_fields->contact_form_parameter_name." ".$this->form_fields->contact_form_parameter_firstname;
		} else {
			$transmitter_info['name'] = $this->form_fields->contact_form_parameter_email;
		}
		$transmitter_info['email'] = $this->form_fields->contact_form_parameter_email;
		if(!is_valid_mail($transmitter_info['email'])){
		    $this->messages[] = $msg['contact_form_error_email_adress'];
		}
		if($this->form_fields->contact_form_objects) {
		    $contact_form_object = new contact_form_object($this->form_fields->contact_form_objects);
		    $object_label = $contact_form_object->get_translated_label();
		} else {
		    $object_label = $this->form_fields->contact_form_object_free_entry;
		}
		$attachments = array();
		if(!empty($this->form_fields->contact_form_parameter_attachments)) {
		    foreach ($this->form_fields->contact_form_parameter_attachments as $parameter_attachment) {
		        if(empty($parameter_attachment->has_error)) {
		            $attachments[] = array(
		                'contenu' => file_get_contents($parameter_attachment->location),
		                'nomfichier' => $parameter_attachment->name
		            );
		        }
		    }
		}
		$content = h2o($this->parameters['email_content'])->render(array('contact_form' => $this->form_fields));
		$headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=".$charset."\n";
		$this->sended = mailpmb($recipient_info['name'], $recipient_info['email'], $object_label, $content, $recipient_info['name'], $recipient_info['email'], $headers, $recipient_info['copy_email'],"",0,$attachments,$transmitter_info['name'], $transmitter_info['email']);
		if($this->sended) {
			$this->messages[] = $msg['contact_form_send_success_msg'];
			if($this->parameters['confirm_email']) {
			    $sended_copy = mailpmb($transmitter_info['name'], $transmitter_info['email'], $object_label." ".$msg['contact_form_send_copy_suffix'], $content, $recipient_info['name'], ($recipient_info['transmitter_email'] ? $recipient_info['transmitter_email'] : $recipient_info['email']), $headers, "", "", 0, $attachments);
				if($sended_copy) {
					$this->messages[] = $msg['contact_form_send_copy_success_msg'];
				} else {
					$this->messages[] = $msg['contact_form_send_copy_error_msg'];
				}
			}
		} else {
			$this->messages[] = $msg['contact_form_send_error_msg'];
		}
		//Mails envoyés - Suppression des pièces jointes dans le répertoire temporaire
		if(!empty($this->form_fields->contact_form_parameter_attachments)) {
		    foreach ($this->form_fields->contact_form_parameter_attachments as $parameter_attachment) {
		        if(empty($parameter_attachment->has_error)) {
		            if(file_exists($parameter_attachment->location)) {
		                unlink($parameter_attachment->location);
		            }
		        }
		    }
		}
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_label() {
		return $this->label;
	}
	
	public function get_desc() {
		return $this->desc;
	}
	
	public function get_parameters() {
		return $this->parameters;
	}
	
	public function get_form_fields() {
		return $this->form_fields;
	}
	
	public function set_form_fields($form_fields) {
		$this->form_fields = $form_fields;
	}
	
	public function get_messages() {
		return $this->messages;
	}
	
	public function set_messages($messages) {
		$this->messages = $messages;
	}
	
	public function is_sended() {
		return $this->sended;
	}
	
	public static function get_attachments_field() {
	    global $contact_form_attachments_field_tpl;
	    
	    $form = $contact_form_attachments_field_tpl;
	    return $form;
	}
}