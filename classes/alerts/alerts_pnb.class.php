<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alerts_pnb.class.php,v 1.2 2022/02/11 09:39:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class alerts_pnb extends alerts {
	
	protected function get_module() {
		return 'edit';
	}
	
	protected function get_section() {
		return 'alert_pnb';
	}
	
	protected function fetch_data() {
		global $pmb_pnb_alert_end_offers, $pmb_pnb_alert_staturation_offers;
		global $pmb_pnb_alert_threshold_tokens;
		
		$this->data = array();
		
		$pmb_pnb_alert_end_offers = intval($pmb_pnb_alert_end_offers);
		$pmb_pnb_alert_staturation_offers = intval($pmb_pnb_alert_staturation_offers);
		
		$query = "SELECT pnb_order_line_id FROM pnb_orders WHERE
			DATE_ADD(pnb_order_offer_date_end, INTERVAL - " . $pmb_pnb_alert_end_offers . " DAY) < NOW() ";
		if($this->is_num_rows_from_query($query)){
			$this->add_data('pnb', 'alert_pnb_end', 'orders', '&alert_end_offers=1');
		}
		
		$query = "select * from pnb_orders
        join pnb_loans on pnb_loan_order_line_id = pnb_order_line_id
        group by pnb_order_line_id having count(id_pnb_loan) >= pnb_order_nb_simultaneous_loans - " . $pmb_pnb_alert_staturation_offers . " limit 1";
		if($this->is_num_rows_from_query($query)){
			$this->add_data('pnb', 'alert_pnb_saturation', 'orders', '&alert_staturation_offers=1');
		}
		
		// Seuil d'alerte sur nombre de jetons restants
		$query = "SELECT pnb_order_line_id FROM pnb_orders WHERE pnb_current_nta < ".$pmb_pnb_alert_threshold_tokens;
		if($this->is_num_rows_from_query($query)){
			$this->add_data('pnb', 'pnb_alert_threshold_tokens', 'orders', '&alert_threshold_tokens=1');
		}
	}
}