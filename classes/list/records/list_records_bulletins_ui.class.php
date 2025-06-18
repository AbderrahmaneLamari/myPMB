<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_records_bulletins_ui.class.php,v 1.8 2021/03/18 08:56:37 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/serials.class.php");

class list_records_bulletins_ui extends list_records_ui {
	
	protected function _get_query_base() {
		$aq_members = $this->get_aq_members();
		$query = 'SELECT bulletin_id,'.$aq_members["select"].' as pert FROM bulletins 
				JOIN notices ON bulletin_notice=notice_id ';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new bulletinage($row->bulletin_id);
	}
	
	protected function _get_query_order() {
	    if ($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'pert':
					$order .= 'pert, index_sew, date_date, bulletin_id';
					break;
				default :
					$order .= parent::_get_query_order();
					break;
			}
			if($order) {
				return $this->_get_query_order_sql_build($order);
			} else {
				return "";
			}
		}
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'empty' => 'search_empty_field',
						'caddie' => 'caddie_de_BULL',
						'bulletin_numero' => '4025',
						'mention_date' => 'bulletin_mention_periode',
						'aff_date_date' => '4026',
						'bulletin_titre' => 'bulletin_mention_titre',
						'expl' => 'bulletin_nb_exemplaires',
						'record_header' => 'titre_perio_query',
				        'record_isbd' => 'serial_isbd'
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		
		$content = '';
		switch ($property) {
			case 'caddie':
				// gestion des paniers de bulletins
				$cart_click_bull = "onClick=\"openPopUp('./cart.php?object_type=BULL&item=$object->bulletin_id', 'cart')\"";
				$content .= "<img src='".get_url_icon('basket_small_20x20.gif')."' class='align_middle' alt='basket' title='$msg[400]' $cart_click_bull>";
				break;
			case 'bulletin_numero':
				$url =  bulletinage::get_permalink($object->bulletin_id);
				$content .= "<a href='$url'>$object->bulletin_numero</a>";
				break;
			case 'expl':
				if (!empty($object->expl)) {
					$content .= count($object->expl)." ".$msg['bulletin_nb_exemplaires'];
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
}