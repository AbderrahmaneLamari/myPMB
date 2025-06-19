<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_selfservice_retour_ui.class.php,v 1.2 2023/02/07 08:19:46 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_configuration_selfservice_retour_ui extends list_configuration_selfservice_ui {
	
	protected function fetch_data() {
		global $msg, $pmb_transferts_actif;
		
		$this->objects = array();
		
		$values = array();
		$values[] = array("value" => "0", "label" => $msg["selfservice_loc_autre_todo_plus_tard"]);
		if ($pmb_transferts_actif) {
			$values[] = array("value" => "1", "label" => $msg["selfservice_loc_autre_todo_gen_trans"]);
			$values[] = array("value" => "2", "label" => $msg["selfservice_loc_autre_todo_catalog"]);
		}
		$values[] = array("value" => "3", "label" => $msg["selfservice_loc_autre_todo_catalog_noloc"]);
		$values[] = array("value" => "4", "label" => $msg["selfservice_loc_autre_todo_refus"]);
		$action = $this->get_parameter('selfservice', 'loc_autre_todo', '', $values);
		$message = $this->get_parameter('selfservice', 'loc_autre_todo_msg');
		$this->add_selfservice('selfservice_loc_autre_todo', $message, $action);
		
		$values = array (
				array ("value" => "0", "label" => $msg["selfservice_resa_ici_todo_plus_tard"] ),
				array ("value" => "1", "label" => $msg["selfservice_resa_ici_todo_valid_resa"] ),
				array ("value" => "4", "label" => $msg["selfservice_resa_ici_todo_refus"] ),
		);
		$action = $this->get_parameter('selfservice', 'resa_ici_todo', '', $values);
		$message = $this->get_parameter('selfservice', 'resa_ici_todo_msg');
		$this->add_selfservice('selfservice_resa_ici_todo', $message, $action);
		
		$values = array();
		$values[] = array("value" => "0", "label" => $msg["selfservice_resa_loc_todo_plus_tard"]);
		if ($pmb_transferts_actif) {
			$values[] = array("value" => "1", "label" => $msg["selfservice_resa_loc_todo_gen_trans"]);
		}
		$values[] = array("value" => "2", "label" => $msg["selfservice_resa_ici_todo_valid_resa"]);
		$values[] = array("value" => "4", "label" => $msg["selfservice_resa_ici_todo_refus"]);
		$action = $this->get_parameter('selfservice', 'resa_loc_todo', '', $values);
		$message = $this->get_parameter('selfservice', 'resa_loc_todo_msg');
		$this->add_selfservice('selfservice_resa_loc_todo', $message, $action);
		

		$values = [
		    ["value" => "0", "label" => $msg["selfservice_resa_ici_todo_valid_no"]],
		    ["value" => "1", "label" => $msg["selfservice_resa_ici_todo_valid_yes"]]
		];
		$action = $this->get_parameter('selfservice', 'resa_ici_todo_valid', '', $values);
		$this->add_selfservice('selfservice_resa_ici_todo_valid', false, $action);

		$message = $this->get_parameter('selfservice', 'retour_retard_msg');
		$this->add_selfservice('selfservice_admin_retour_retard', $message);
		
		$message = $this->get_parameter('selfservice', 'retour_blocage_msg');
		$this->add_selfservice('selfservice_admin_retour_blocage', $message);
		
		$message = $this->get_parameter('selfservice', 'retour_amende_msg');
		$this->add_selfservice('selfservice_admin_retour_amende', $message);
	}
}