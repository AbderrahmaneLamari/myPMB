<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mail.class.php,v 1.8.2.8 2021/12/07 13:59:37 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

use PHPMailer\PHPMailer\PHPMailer;

class mail {
    
	protected $id;
	
	protected $to_name;
	
	protected $to_mail;
	
	protected $object;
	
	protected $content;
	
	protected $from_name;
	
	protected $from_mail;
	
	protected $headers;
	
	protected $copy_cc;
	
	protected $copy_bcc;
	
	protected $do_nl2br;
	
	protected $attachments;
	
	protected $reply_name;
	
	protected $reply_mail;
	
	protected $is_mailing;
	
	protected $date;
	
	protected $sended;
	
	protected $error;
	
	protected $from_uri;
	
	const SMTP_AUTH_TYPES = [
	    'CRAM-MD5', 
	    'LOGIN', 
	    'PLAIN', 
	    'XOAUTH2',
	];
	
	public static $table_name = 'mails';
	
	protected static $server_configuration;
	
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->fetch_data();
	}
	
	protected function init_properties() {
		$this->to_name = '';
		$this->to_mail = array();
		$this->object = '';
		$this->content = '';
		$this->from_name = '';
		$this->from_mail = '';
		$this->headers = array();
		$this->copy_cc = array();
		$this->copy_bcc = array();
		$this->do_nl2br = 0;
		$this->attachments = array();
		$this->reply_name = '';
		$this->reply_mail = '';
		$this->date = date('Y-m-d H:i:s');
		$this->sended = 0;
		$this->error = '';
		$this->from_uri = '';
	}
	
	protected function fetch_data() {
		$this->init_properties();
		if($this->id) {
			$query = "select * from ".static::$table_name." where id_mail = ".$this->id;
			$result = pmb_mysql_query($query);
			$row = pmb_mysql_fetch_assoc($result);
			$this->to_name = $row['mail_to_name'];
			$this->to_mail = explode(';', $row['mail_to_mail']);
			$this->object = $row['mail_object'];
			$this->content = $row['mail_content'];
			$this->from_name = $row['mail_from_name'];
			$this->from_mail = $row['mail_from_mail'];
			$this->headers = encoding_normalize::json_decode($row['mail_headers']);
			$this->copy_cc = explode(';', $row['mail_copy_cc']);
			$this->copy_bcc = explode(';', $row['mail_copy_bcc']);
			$this->do_nl2br = $row['mail_do_nl2br'];
			$this->attachments = encoding_normalize::json_decode($row['mail_attachments']);
			$this->reply_name = $row['mail_reply_name'];
			$this->reply_mail = $row['mail_reply_mail'];
			$this->date = $row['mail_date'];
			$this->sended = $row['mail_sended'];
			$this->error = $row['mail_error'];
			$this->from_uri = $row['mail_from_uri'];
		}
	}
	
	public function add() {
		global $supervision_mails_active;
		
		if(!$this->table_exists() || !$supervision_mails_active) {
			return false;
		}
		$query = "insert into ".static::$table_name." set
			mail_to_name = '".addslashes($this->to_name)."',
			mail_to_mail = '".addslashes(implode(';', $this->to_mail))."',
			mail_object = '".addslashes($this->object)."',
			mail_content = '".addslashes($this->content)."',
			mail_from_name = '".addslashes($this->from_name)."',
			mail_from_mail = '".addslashes($this->from_mail)."',
			mail_headers = '".addslashes(encoding_normalize::json_encode($this->headers))."',
			mail_copy_cc = '".addslashes(implode(';', $this->copy_cc))."',
			mail_copy_bcc = '".addslashes(implode(';', $this->copy_bcc))."',
			mail_do_nl2br = '".$this->do_nl2br."',
			mail_attachments = '".addslashes(encoding_normalize::json_encode($this->attachments))."',
			mail_reply_name = '".addslashes($this->reply_name)."',
			mail_reply_mail = '".addslashes($this->reply_mail)."',
			mail_date = '".addslashes($this->date)."',
			mail_sended = '".intval($this->sended)."',
			mail_error = '".addslashes($this->error)."',
			mail_from_uri = '".addslashes($this->from_uri)."'";
		$result = pmb_mysql_query($query);
		if($result) {
			$this->id = pmb_mysql_insert_id();
			return true;
		} else {
			return false;
		}
	}
	
	public function delete() {
		$query = "delete from ".static::$table_name." where id_mail = ".$this->id;
		pmb_mysql_query($query);
		if($this->table_is_empty()) {
			$query = "ALTER TABLE ".static::$table_name." AUTO_INCREMENT = 1";
			pmb_mysql_query($query);
		}
	}
	
	protected function table_is_empty() {
		$query = "select count(*) from ".static::$table_name;
		$result = pmb_mysql_query($query);
		if(pmb_mysql_result($result, 0, 0) == 0) {
			return true;
		}
		return false;
	}
	
	protected function table_exists() {
		$query = "SHOW TABLES LIKE '".static::$table_name."'";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			return true;
		}
		return false;
	}
	
	public function send() {
		global $pmb_mail_methode,$pmb_mail_html_format,$pmb_mail_adresse_from;
		global $pmb_mail_list_unsubscribe_mailto, $opac_contact_form;
		global $opac_url_base, $opac_biblio_name;
		global $charset;
		
		$param = explode(",",$pmb_mail_methode);
		if (!$param) {
			$param=array() ;
		}
		
		$mail = new PHPMailer();
		//$mail->SMTPDebug=1;
		$mail->CharSet = $charset;
		$mail->SMTPAutoTLS=false;
		
		global $SMTPOptions;
		switch ($param[0]) {
			case 'smtp':
				// $pmb_mail_methode = méthode, hote:port, auth, name, pass, (ssl|tls), auth_type (CRAM-MD5|LOGIN|PLAIN)
				$mail->isSMTP();
				$mail->Host=$param[1];
				if (!empty($SMTPOptions)){
				    $mail->SMTPOptions = $SMTPOptions;
				}
				if (isset($param[2]) && $param[2]) {
					$mail->SMTPAuth=true ;
					$mail->Username=$param[3] ;
					$mail->Password=$param[4] ;
					if (isset($param[5]) && $param[5]) {
						$mail->SMTPSecure = $param[5]; // pour traitement connexion SSL
						$mail->SMTPAutoTLS=true;
					}
					if( isset($param[6]) && in_array($param[6], mail::SMTP_AUTH_TYPES) ) {
					    $mail->AuthType = $param[6];
					}
				}
				break ;
			default:
			case 'php':
				$mail->isMail();
				$this->to_name = "";
				break;
		}
		
		if ($pmb_mail_html_format) {
			$mail->isHTML(true);
		}
		
		if (trim($pmb_mail_adresse_from)) {
			$tmp_array_email = explode(';',$pmb_mail_adresse_from);
			if (!isset($tmp_array_email[1])) {
				$tmp_array_email[1]='';
			}
			$mail->setFrom($tmp_array_email[0],$tmp_array_email[1]);
			//Le paramètre ci-dessous est utilisé comme destinataires pour les réponses automatiques (erreur de destinataire, validation anti-spam, ...)
			$mail->Sender=$this->from_mail;
		} else {
			$mail->setFrom($this->from_mail,$this->from_name);
		}
		
		for ($i=0; $i<count($this->to_mail); $i++) {
			$mail->addAddress($this->to_mail[$i], $this->to_name);
		}
		for ($i=0; $i<count($this->copy_cc); $i++) {
			if(trim($this->copy_cc[$i])){
				$mail->addCC($this->copy_cc[$i]);
			}
		}
		for ($i=0; $i<count($this->copy_bcc); $i++) {
			if(trim($this->copy_bcc[$i])){
				$mail->addBCC($this->copy_bcc[$i]);
			}
		}
		if($this->reply_mail && $this->reply_name) {
			$mail->addReplyTo($this->reply_mail, $this->reply_name);
		} else {
			$mail->addReplyTo($this->from_mail, $this->from_name);
		}
		$mail->Subject = $this->object;
		if ($pmb_mail_html_format) {
			if ($this->do_nl2br) {
				$mail->Body=wordwrap(nl2br($this->content),70);
			} else {
				$mail->Body=wordwrap($this->content,70);
			}
			if ($pmb_mail_html_format==2) {
				$mail->MsgHTML($mail->Body);
			}
		} else {
			$this->content=str_replace("<hr />",PHP_EOL."*******************************".PHP_EOL,$this->content);
			$this->content=str_replace("<hr />",PHP_EOL."*******************************".PHP_EOL,$this->content);
			$this->content=str_replace("<br />",PHP_EOL,$this->content);
			$this->content=str_replace("<br />",PHP_EOL,$this->content);
			$this->content=str_replace(PHP_EOL.PHP_EOL.PHP_EOL,PHP_EOL.PHP_EOL,$this->content);
			$this->content=strip_tags($this->content);
			$this->content=html_entity_decode($this->content,ENT_QUOTES, $charset) ;
			$mail->Body=wordwrap($this->content,70);
		}
		for ($i=0; $i<count($this->attachments) ; $i++) {
			if ($this->attachments[$i]["contenu"] && $this->attachments[$i]["nomfichier"]) {
				$mail->addStringAttachment($this->attachments[$i]["contenu"], $this->attachments[$i]["nomfichier"]) ;
			}
		}
		
		if(!empty($this->is_mailing) && !empty($pmb_mail_list_unsubscribe_mailto)) {
			// Lien de désinscription ?
			$mailto = "mailto:".$pmb_mail_list_unsubscribe_mailto."?subject=".rawurlencode($this->object);
			if($opac_contact_form) {
				$mail->addCustomHeader("List-Unsubscribe","<".$mailto.">, <".$opac_url_base."index.php?lvl=contact_form>");
			} else {
				$mail->addCustomHeader("List-Unsubscribe","<".$mailto.">");
			}
			if($opac_biblio_name) {
				$mail->XMailer = trim(strip_tags(clean_string($opac_biblio_name)));
			}
		}
		
		if (!$mail->send()) {
			$this->error = $mail->ErrorInfo;
			$retour=false;
			global $error_send_mail ;
			$error_send_mail[] = $mail->ErrorInfo ;
			PHP_log::register(PHP_log::prepare("Object : ".$this->object.". \r\nSender : ".$this->from_mail.". \r\nRecipient(s) : ".implode(', ', $this->to_mail)), $this->error);
			global $pmb_display_errors ;
			if($pmb_display_errors) {
				echo "Erreur SMTP: ".$mail->ErrorInfo."<br/>";
				echo "Détail: <pre>".print_r($mail,true)."</pre>";
				echo "Arret du script, mail non envoyé";
				die();
			}
		} else {
			$this->date = date('Y-m-d H:i:s');
			$retour=true ;
		}
		$this->add();
		if ($param[0]=='smtp') {
			$mail->smtpClose();
		}
		unset($mail);
		
		return $retour ;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_to_name() {
		return $this->to_name;
	}
	
	public function get_to_mail() {
		return $this->to_mail;
	}
	
	public function get_object() {
		return $this->object;
	}
	
	public function get_from_name() {
		return $this->from_name;
	}
	
	public function get_from_mail() {
		return $this->from_mail;
	}
	
	public function get_copy_cc() {
		return $this->copy_cc;
	}
	
	public function get_copy_bcc() {
		return $this->copy_bcc;
	}
	
	public function get_reply_name() {
		return $this->reply_name;
	}
	
	public function get_reply_mail() {
		return $this->reply_mail;
	}
	
	public function get_date() {
		return $this->date;
	}
	
	public function get_sended() {
		return $this->sended;
	}
	
	public function get_error() {
		return $this->error;
	}
	
	public function get_from_uri() {
		return $this->from_uri;
	}
	
	public function set_to_name($to_name) {
		$this->to_name = $to_name;
		return $this;
	}
	
	public function set_to_mail($to_mail) {
		$this->to_mail = $to_mail;
		return $this;
	}
	
	public function set_object($object) {
		$this->object = $object;
		return $this;
	}
	
	public function set_content($content) {
		$this->content = $content;
		return $this;
	}
	
	public function set_from_name($from_name) {
		$this->from_name = $from_name;
		return $this;
	}
	
	public function set_from_mail($from_mail) {
		$this->from_mail = $from_mail;
		return $this;
	}
	
	public function set_headers($headers) {
		$this->headers = $headers;
		return $this;
	}
	
	public function set_copy_cc($copy_cc) {
		$this->copy_cc = $copy_cc;
		return $this;
	}
	
	public function set_copy_bcc($copy_bcc) {
		$this->copy_bcc = $copy_bcc;
		return $this;
	}
	
	public function set_do_nl2br($do_nl2br) {
		$this->do_nl2br = $do_nl2br;
		return $this;
	}
	
	public function set_attachments($attachments) {
		$this->attachments = $attachments;
		return $this;
	}
	
	public function set_reply_name($reply_name) {
		$this->reply_name = $reply_name;
		return $this;
	}
	
	public function set_reply_mail($reply_mail) {
		$this->reply_mail = $reply_mail;
		return $this;
	}
	
	public function set_sended($sended) {
		$this->sended = $sended;
		return $this;
	}
	
	public function set_error($error) {
		$this->error = $error;
		return $this;
	}
	
	public function set_from_uri($from_uri) {
		$this->from_uri = $from_uri;
		return $this;
	}
	
	public function set_is_mailing($is_mailing) {
		$this->is_mailing = $is_mailing;
		return $this;
	}
	
	public static function set_server_configuration($server_configuration) {
		static::$server_configuration = $server_configuration;
	}
	
	public static function get_configuration_form($parameters=array()) {
		
	}
	
	public static function get_list_types_uri() {
		global $msg;
		
		$types_uri = array();
		$types_uri['pdf.php?pdfdoc=mail_liste_pret'] = $msg['imprimer_liste_pret'];
		$types_uri['circ.php?categ=relance'] = $msg['relance_to_do'];
		return $types_uri;
	}
}
	
