<?php 
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pnb_controller.class.php,v 1.7 2021/02/09 15:30:46 jlaurent Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
global $lvl, $sub;

require_once $class_path.'/pnb/pnb.class.php';

class pnb_controller {
	
	public function proceed() {
		global $lvl;
		global $sub;
		$empr_id = $_SESSION['id_empr_session']*1;
		if (!$empr_id) {
			die();
		}
		
		$pnb = new pnb();
		
		switch ($lvl) {
		    case 'all' :
			case 'pnb_loan_list' :
				print $pnb->get_empr_loans_list($empr_id);
				break;
			case 'pnb_devices' :
				switch($sub){
					default:
						print $pnb->get_devices_list($empr_id);
						break;
					case 'save': 
						$pnb->save_devices_list($empr_id);
						print $pnb->get_devices_list($empr_id);
						break;
				}
				break;
			case 'pnb_parameters':
				switch($sub){
					default:
						print $pnb->get_parameters($empr_id);
						break;
					case 'save':
						$pnb->save_parameters($empr_id);
						print $pnb->get_parameters($empr_id);
						break;
					break;
				}
		}
	}
	
	public function proceed_ajax($action) {
	    
		$pnb = new pnb();
		switch($action) {
			case 'loan' :
			    global $empr_pnb_device,$notice_id;
			    $pnb->loan_book($empr_pnb_device,$notice_id);
				break;
			case 'get_loan_form':
			    global $notice_id;
			    $pnb->get_loan_form($notice_id);
				break;
			case 'post_loan_info':
			    global $empr_pnb_device,$notice_id,$pass, $hint_pass;
			    $pnb->loan_book($empr_pnb_device,$notice_id, $pass, $hint_pass);
				break;
			case 'returnLoan';
			     global $id_empr,$expl_id, $fromPortal, $drm;
			     echo encoding_normalize::json_encode($pnb->return_book($id_empr, $expl_id, $fromPortal, $drm));
				break;
			case 'extendLoan';
    			global $id_empr,$expl_id, $fromPortal, $drm;
    			echo encoding_normalize::json_encode($pnb->extend_loan($id_empr, $expl_id, $fromPortal, $drm));
				break;
			case 'get_empr_devices_list';
			global $notice_id;
			echo encoding_normalize::json_encode($pnb->get_empr_devices_list($notice_id));
				break;
		}
	}
}
