<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_demandes_actions_demande_ui.class.php,v 1.3.4.1 2023/11/16 09:17:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_demandes_actions_demande_ui extends list_demandes_actions_ui {
	
	protected function get_title() {
		global $msg, $charset;
		
		return "<h3>".htmlentities($msg['demandes_action_liste'], ENT_QUOTES, $charset)."</h3><br />";
	}
	
	protected function get_form_title() {
		global $msg;
		
		return $msg['demandes_action_liste'];
	}
		
	protected function init_default_applied_group() {
		$this->applied_group = array();
	}
	
	protected function init_default_columns() {
		$this->add_column('properties_action');
		$this->add_column('type_action');
		$this->add_column('sujet_action');
		$this->add_column('detail_action');
		$this->add_column('statut_action');
		$this->add_column('date_action');
		$this->add_column('deadline_action');
		$this->add_column('creator');
		$this->add_column('time_elapsed');
		$this->add_column('progression_action');
		$this->add_column('notes');
		$this->add_column_selection();
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('pager', 'visible', false);
	}
	
	protected function init_default_pager() {
	    parent::init_default_pager();
	    $this->pager['all_on_page'] = true;
	}
	
	public function get_error_message_empty_list() {
		global $msg, $charset;
		
		return htmlentities($msg['demandes_action_liste_vide'], ENT_QUOTES, $charset);
	}
	
	protected function get_selection_actions() {
		global $msg;
		if(!isset($this->selection_actions)) {
			$this->selection_actions = array();
			$delete_action_link = array(
					'href' => static::get_controller_url_base()."&act=suppr_action",
					'confirm' => $msg["demandes_confirm_suppr"]
			);
			$this->selection_actions[] = $this->get_selection_action('delete_action', $msg['63'], '', $delete_action_link);
		}
		return $this->selection_actions;
	}
	
	protected function get_display_others_actions() {
		return "";
	}
	
	protected function get_name_selected_objects() {
		return "chk_action_".$this->filters['id_demande'];
	}
	
	protected function get_cell_content($object, $property) {
		global $msg, $charset, $pmb_gestion_devise;
		
		$content = '';
		switch($property) {
			case 'statut_action':
				$content .= "<span id='statut_".$object->id_action."' dynamics='demandes,statut' dynamics_params='selector'>".htmlentities($object->workflow->getStateCommentById($object->statut_action),ENT_QUOTES,$charset)."</span>";
				break;
			case 'time_elapsed':
				$content .= "<span dynamics='demandes,temps' dynamics_params='text' id='temps_".$object->id_action."'>".htmlentities($object->time_elapsed.$msg['demandes_action_time_unit'],ENT_QUOTES,$charset)."</span>";
				break;
			case 'cout':
				$content .= "<span dynamics='demandes,cout' dynamics_params='text' id='cout_".$object->id_action."'>".htmlentities($object->cout,ENT_QUOTES,$charset).$pmb_gestion_devise."</span>";
				break;
			case 'progression_action':
				$content .= "
					<span dynamics='demandes,progression' dynamics_params='text' id='progression_".$object->id_action."' >
						<img src='".get_url_icon('jauge.png')."' style='height:16px;' width=\"".$object->progression_action."%\" title='".$object->progression_action."%' />
					</span>";
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_button_add() {
	    global $msg, $charset;
	    global $base_path;
	    
	    return "<input type='button' class='bouton' name='demandes_action_add' value='".htmlentities($msg["demandes_action_add"], ENT_QUOTES, $charset)."' onclick=\"document.location='".$base_path."/demandes.php?categ=action&act=add_action&iddemande=".$this->filters['id_demande']."'\" />";
	}
	
	protected function get_display_left_actions() {
	    return $this->get_button_add();
	}
}