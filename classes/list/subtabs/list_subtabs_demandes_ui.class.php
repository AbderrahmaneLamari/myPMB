<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_subtabs_demandes_ui.class.php,v 1.2 2021/08/06 11:50:07 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_subtabs_demandes_ui extends list_subtabs_ui {
	
	public function get_title() {
		global $msg;
		
		$title = "";
		switch (static::$categ) {
			case 'list':
				$title .= $msg['demandes_gestion'];
				break;
			case 'action':
				$title .= $msg['demandes_gestion'];
				break;
			case 'notes' :
				$title .= $msg['demandes_gestion'];
				break;
			case 'faq':
				break;
			default:
				
				break;
		}
		return $title;
	}
	
	public function get_sub_title() {
		global $msg, $iduser, $idetat;
		
		$sub_title = "";
		switch (static::$categ) {
			case 'list':
				if($idetat) {
					switch ($idetat) {
						case 1:
							$sub_title .= $msg['demandes_menu_a_valide'];
							break;
						case 2:
							$sub_title .= $msg['demandes_menu_en_cours'];
							break;
						case 3:
							$sub_title .= $msg['demandes_menu_refuse'];
							break;
						case 4:
							$sub_title .= $msg['demandes_menu_fini'];
							break;
						case 5:
							$sub_title .= $msg['demandes_menu_abandon'];
							break;
						case 6:
							$sub_title .= $msg['demandes_menu_archive'];
							break;
						case 5:
							$sub_title .= $msg['demandes_menu_abandon'];
							break;
					}
				} else {
					if($iduser == -1) {
						$sub_title .= $msg['demandes_menu_not_assigned'];
					} else {
						$sub_title .= $msg['demandes_menu_all'];
					}
				}
				break;
			case 'action':
				$sub_title .= $msg['demandes_menu_action'];
				break;
			case 'notes' :
				$sub_title .= $msg['demandes_notes'];
				break;
			default:
				break;
		}
		return $sub_title;
	}
	
	protected function _init_subtabs() {
		
	}
}