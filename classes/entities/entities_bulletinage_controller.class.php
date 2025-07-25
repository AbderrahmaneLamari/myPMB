<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: entities_bulletinage_controller.class.php,v 1.8.4.1 2023/10/24 10:10:50 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once ($class_path."/entities/entities_records_controller.class.php");

class entities_bulletinage_controller extends entities_records_controller {
		
	protected $url_base = './catalog.php?categ=serials&sub=bulletinage';
	
	protected $serial_id;
	
	protected $model_class_name = 'bulletinage';
	
	public function get_object_instance() {
		$model_class_name = $this->get_model_class_name();
		$object_instance = new $model_class_name($this->id, $this->serial_id);
		if(method_exists($model_class_name, 'set_controller')) {
			$model_class_name::set_controller($this);
		}
		return $object_instance;
	}
	
	/**
	 * 8 = droits de modification
	 */
	protected function get_acces_m() {
		global $PMBuserid;
		$acces_m=1;
		if(!$this->id) {
			$acces_m = $this->dom_1->getRights($PMBuserid,$this->serial_id,8);
			if(!$acces_m) {
				$this->error_message = 'mod_seri_error';
			}
		} else {
			$acces_j = $this->dom_1->getJoin($PMBuserid, 8, 'bulletin_notice');
			$q = "select count(1) from bulletins $acces_j where bulletin_id=".$this->id;
			$r = pmb_mysql_query($q);
			if(pmb_mysql_result($r,0,0)==0) {
				$acces_m=0;
				$this->error_message = 'mod_bull_error';
			}
		}
		return $acces_m;
	}
	
	public function proceed_form() {
		global $msg;
		global $serial_header;
	
		// affichage d'un form pour cr�ation, modification d'un p�riodique
		if(!$this->id) {
			// pas d'id, c'est une cr�ation
			print str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4005], $serial_header);
		} else {
			print str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4006], $serial_header);
		}
		$myBulletinage = $this->get_object_instance();
		$perio = new serial_display($myBulletinage->get_serial()->id, 1);
		// titre g�n�ral du p�riodique
		print "
			<div class='notice-perio'>
				<div class='row'>
					<h2>".$perio->header."</h2>
				</div>
				<div class='row'>
					".$perio->isbd."
				</div>
			</div>";
		// affichage du form
		print "<div class=\"row\">".$myBulletinage->do_form().'</div>';
	}
	
	public function proceed_duplicate() {
		global $msg;
		global $serial_header;
	
		// affichage d'un form pour cr�ation, modification d'un p�riodique
		if(!$this->id) {
			// pas d'id, c'est une cr�ation
			print str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4005], $serial_header);
		} else {
			print str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg['bull_duplicate'], $serial_header);
		}
		$myBulletinage = $this->get_object_instance();
		$perio = new serial_display($myBulletinage->get_serial()->id, 1);
		$myBulletinage->bulletin_id = 0;
		
		// titre g�n�ral du p�riodique
		print "
			<div class='notice-perio'>
				<div class='row'>
					<h2>".$perio->header."</h2>
				</div>
				<div class='row'>
					".$perio->isbd."
				</div>
			</div>";
		// affichage du form
		print "<div class=\"row\">".$myBulletinage->do_form().'</div>';
	}
	
	public function proceed_replace() {
		global $msg;
		global $by;
		global $del;
	
		$myBul = $this->get_object_instance();
		$by += 0;
		if(!$by) {
			$myBul->replace_form();
		} else {
			// routine de remplacement
			$rep_result = $myBul->replace($by,1-$del);
			if(!$rep_result) {
				print "<div class='row'><div class='msg-perio'>".$msg["maj_encours"]."</div></div>
					<script type=\"text/javascript\">document.location='./catalog.php?categ=serials&sub=view&serial_id=".$this->serial_id."&bul_id=".$by."'</script>";
			} else {
				error_message($msg[132], $rep_result, 1, "./catalog.php?categ=serials&sub=view&serial_id=".$this->serial_id);
			}
		}
	}
	
	protected function get_permalink($id=0) {
	    if(!$id) {
	        $id = $this->id;
	    }
		return $this->url_base."&action=view&bul_id=" . intval($id);
	}
	
	public function set_serial_id($serial_id=0) {
	    $this->serial_id = (int) $serial_id;
	}
	
	public function proceed_update() {
	    global $create_notice_bul;
	    if(!isset($this->serial_id)){
	        global $serial_id;
	        $this->serial_id = $serial_id;
	    }
	    $myBull = $this->get_object_instance();
	    $myBull->set_properties_from_form();
	    if($create_notice_bul){
    	    $sign = new notice_doublon();
	        $myBull->signature = $sign->gen_signature();
	    }
	    $myBull->save();
	    return $myBull->bulletin_id;
	}
	public function get_display_view($saved="") {
	    global $msg, $current_module, $id_form;
	    $return = "<div class='row'><div class='msg-perio'>".$msg["maj_encours"]."</div></div>";
	    $retour = bulletinage::get_permalink($saved);
	    $return .= "
                <form class='form-$current_module' name=\"dummy\" method=\"post\" action=\"$retour\" style=\"display:none\">
    				<input type=\"hidden\" name=\"id_form\" value=\"$id_form\">
    			</form>
    			<script type=\"text/javascript\">document.dummy.submit();</script>";
	    return $return;
	}
}
