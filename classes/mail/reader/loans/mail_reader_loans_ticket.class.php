<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mail_reader_loans_ticket.class.php,v 1.4 2022/08/01 06:44:58 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class mail_reader_loans_ticket extends mail_reader_loans {
	
	protected $cb_doc;
	
	protected static function get_parameter_prefix() {
		return "";
	}
	
	protected function _init_default_settings() {
		parent::_init_default_settings();
		$this->_init_setting_value('sender', 'user');
		$this->_init_setting_value('copy_bcc', '1');
	}
	
	protected function get_mail_to_id() {
		if(!empty($this->id_group)) {
			return $this->get_group()->id_resp;
		} else {
			return parent::get_mail_to_id();
		}
	}
	
	protected function get_mail_to_name() {
		if(!empty($this->id_group)) {
			return $this->get_group()->libelle_resp;
		} else {
			return parent::get_mail_to_name();
		}
	}
	
	protected function get_mail_to_mail() {
		if(!empty($this->id_group)) {
			return $this->get_group()->mail_resp;
		} else {
			return parent::get_mail_to_mail();
		}
	}
	
	protected function get_mail_object() {
		if(!empty($this->id_group)) {
			return get_object_electronic_loan_ticket();
		} else {
			return get_object_electronic_loan_ticket();
		}
	}
	
	protected function get_mail_content() {
		if(!empty($this->id_group)) {
			return get_groupe_content_electronic_loan_ticket($this->id_group);
		} else {
			return get_empr_content_electronic_loan_ticket($this->mail_to_id, $this->cb_doc);
		}
	}
	
	protected function get_mail_headers() {
		global $charset;
		
		$headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=".$charset."\n";
		return $headers;
	}
	
	public function send_mail() {
		if(!empty($this->id_group)) {
			$this->group = new group($this->id_group);
		} else {
			$requete = "select id_empr, empr_mail, empr_nom, empr_prenom from empr where id_empr=".$this->mail_to_id;
			$res = pmb_mysql_query($requete);
			$this->empr=pmb_mysql_fetch_object($res);
		}
		return $this->mailpmb();
	}
	
	public function set_cb_doc($cb_doc) {
		$this->cb_doc = $cb_doc;
	}
}