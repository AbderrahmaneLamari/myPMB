<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_admin_universe_form.class.php,v 1.1.2.2 2023/09/08 09:35:13 rtigero Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php"))
	die("no access");

global $class_path;
require_once($class_path . '/interface/admin/interface_admin_form.class.php');

class interface_admin_universe_form extends interface_admin_form
{
    protected function get_display_duplicate_action()
	{
		global $charset, $msg;
		
		return "<input type='button' class='bouton' name='duplicate_button' id='duplicate_button' value='".htmlentities($this->get_action_duplicate_label(), ENT_QUOTES, $charset)."' />";
	}
    
    public function get_display_ajax()
	{
		global $charset, $msg;
		global $current_module;

		$display = "
		<form class='form-" . $current_module . "' id='" . $this->name . "' name='" . $this->name . "'  method='post' action=\"" . $this->get_url_base() . "&action=save&id=" . $this->object_id . "\" >
			" . $this->get_display_label() . "	
			<div class='form-contenu'>
				" . $this->content_form . "
			</div>	
			<div class='row'>	
				<div class='left'>
					<input type='button' class='bouton' name='cancel_button' id='cancel_button' value='" . $this->get_action_cancel_label() . "' />
					<input type='submit' class='bouton' name='save_button' id='save_button' value='" . $this->get_action_save_label() . "' />
					" . $this->get_display_duplicate_action() . "
				</div>
				<div class='right'>
					" . ($this->object_id ? "<input type='button' class='bouton' name='delete_button' id='delete_button' value='" . htmlentities($this->get_action_delete_label(), ENT_QUOTES, $charset) . "' />" : "") . "
				</div>
			</div>
		<div class='row'></div>
		</form>";
		if (isset($this->table_name) && $this->table_name) {
			$translation = new translation($this->object_id, $this->table_name);
			$display .= $translation->connect($this->name);
		}
		if (isset($this->field_focus) && $this->field_focus) {
			$display .= "<script type='text/javascript'>document.forms['" . $this->name . "'].elements['" . $this->field_focus . "'].focus();</script>";
		}
		return $display;
	}
}