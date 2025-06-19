<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_contact_form.inc.php,v 1.13 2022/09/02 08:36:41 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $class_path, $sub, $action, $charset;
global $id, $form_fields, $pmb_logs_activate;

require_once($class_path."/contact_forms/contact_form.class.php");
require_once($class_path."/contact_forms/contact_form_object.class.php");
require_once($class_path."/encoding_normalize.class.php");
switch($sub){
	case 'form':
		switch ($action){
		    case 'send_attachments':
		        $has_errors = 0;
		        $errors_messages = array();
		        $attachments = array();
		        if(!empty($_FILES['contact_form_parameter_attachments']) && is_array($_FILES['contact_form_parameter_attachments']['tmp_name']) && count($_FILES['contact_form_parameter_attachments']['tmp_name'])){
		            foreach ( $_FILES['contact_form_parameter_attachments']['tmp_name'] as $key => $tmp_file ) {
		                $to_file = '';
		                $from_file = '';
		                $has_error = 0;
		                $error_message = '';
		                if(trim($_FILES['contact_form_parameter_attachments']['name'][$key]) && $_FILES['contact_form_parameter_attachments']['size'][$key]){
		                    $to_file = $base_path.'/temp/'.basename($tmp_file);
		                    $from_file = $_FILES['contact_form_parameter_attachments']['name'][$key];
		                    if (!@move_uploaded_file($tmp_file,$to_file)) {
		                        /* Fail to copy %s, Contact your admin... */
		                        $has_error= 1;
		                        $error_message = 'Fail to copy';
		                    }
		                }elseif(trim($_FILES['contact_form_parameter_attachments']['name'][$key])){
		                    $has_error= 2;
		                    $error_message = 'Unknown name';
		                }
		                $attachments[]=array(
		                    "name"=>$from_file,
		                    "location"=>$to_file,
		                    "has_error" => $has_error, 
		                    "error_message" => $error_message
		                );
		                $has_errors += $has_error;
		                if($error_message) {
		                    $errors_messages[] = $error_message;
		                }
		            }
		        }
		        print encoding_normalize::json_encode(array('has_errors' => $has_errors, 'errors_messages' => $errors_messages, 'attachments' => $attachments));
		        break;
			case 'send_mail':
				if(empty($id)) $id = 1;
				$contact_form = new contact_form($id);
				
			    //domForm.toJson au départ de l'appel, les données sont déjà en UTF-8
				$form_fields = json_decode(stripslashes(encoding_normalize::utf8_normalize($form_fields)));
				if($charset != 'utf-8') {
					$form_fields = (object) pmb_utf8_decode($form_fields);
				}
				if(!empty($attachments)) {
				    $attachments = json_decode(encoding_normalize::utf8_normalize(stripslashes($attachments)));
				    if($charset != 'utf-8') {
				        $attachments = (object) pmb_utf8_decode($attachments);
				    }
				    $form_fields->contact_form_parameter_attachments = $attachments;
				}
				$contact_form->set_form_fields($form_fields);
				if($contact_form->check_form()) {
					$contact_form->send_mail();
				}
				// Log - afin de faire des stats sur les mails envoyes
				if($pmb_logs_activate && $contact_form->is_sended()) {
					generate_log();
				}
				print encoding_normalize::json_encode(array('sended' => $contact_form->is_sended(), 'errors_messages' => $contact_form->get_messages(), 'fields_errors' => $contact_form->get_fields_errors()));
				break;
		}
		break;
	case 'object':
	    switch ($action){
	        case 'change':
                $contact_form_object = new contact_form_object($id);
                print encoding_normalize::json_encode(array('id' => $id, 'message' => $contact_form_object->get_translated_message()));
	            break;
	    }
}
?>