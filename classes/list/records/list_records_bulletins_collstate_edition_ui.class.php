<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_records_bulletins_collstate_edition_ui.class.php,v 1.9 2021/03/26 10:28:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/list/records/list_records_bulletins_collstate_edition_ui.tpl.php");

class list_records_bulletins_collstate_edition_ui extends list_records_bulletins_ui {
	
	/**
	 * Initialisation de la pagination par défaut
	 */
	protected function init_default_pager() {
		parent::init_default_pager();
		$this->pager['nb_per_page'] = 100;
	}
	
	protected function init_default_columns() {
		$this->add_column('empty', 'caddie_de_NOTI');
		$this->add_column('caddie');
		$this->add_column('bulletin_numero');
		$this->add_column('mention_date');
		$this->add_column('aff_date_date');
		$this->add_column('bulletin_titre');
		$this->add_column('expl');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'options', false);
		$this->set_setting_column('default', 'align', 'left');
	}
	
	/**
	 * Initialisation du groupement appliqué à la recherche
	 */
	public function init_applied_group($applied_group=array()) {
		$this->applied_group = array(0 => 'record_header');
	}
	
	protected function _get_query_order() {
		$this->applied_sort_type = 'SQL';
		return " order by pert, index_sew, date_date DESC, bulletin_id DESC";
	}
	
	/**
	 * Affichage des filtres du formulaire de recherche
	 */
	public function get_search_filters_form() {
		global $list_records_bulletins_collstate_edition_ui_search_filters_form_tpl;
		
		$search_filters_form = $list_records_bulletins_collstate_edition_ui_search_filters_form_tpl;
		$search_filters_form = str_replace("!!user_query!!", $this->filters['user_query'], $search_filters_form);
		$search_filters_form = str_replace('!!objects_type!!', $this->objects_type, $search_filters_form);
		return $search_filters_form;
	}
	
	/**
	 * Liste des objets
	 */
	public function get_display_content_list() {
		global $msg;
		
		$display = '';
		if(isset($this->applied_group[0]) && $this->applied_group[0]) {
			$grouped_objects = $this->get_grouped_objects();
			foreach($grouped_objects as $group_label=>$objects) {
				// lien d'ajout d'une notice mère à un caddie
				$cart_click_noti = "onClick=\"openPopUp('./cart.php?object_type=NOTI&item=".$objects[0]->bulletin_notice."', 'cart')\"";
				$url = "./catalog.php?categ=serials&sub=view&serial_id=".$objects[0]->bulletin_notice;
				$display .= "
					<tr>
						<td>
							<img src='".get_url_icon('basket_small_20x20.gif')."' class='align_middle' alt='basket' title='".$msg[400]."' ".$cart_click_noti.">
						</td>
						<td class='list_ui_content_list_group ".$this->objects_type."_content_list_group' colspan='".(count($this->columns)-1)."'>
							<a href='".$url."'>
                                ".$group_label."
                            </a>
						</td>
					</tr>";
				foreach ($objects as $i=>$object) {
					$display .= $this->get_display_content_object_list($object, $i);
				}
			}
		} else {
			foreach ($this->objects as $i=>$object) {
				$display .= $this->get_display_content_object_list($object, $i);
			}
		}
		return $display;
	}
}