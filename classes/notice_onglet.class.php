<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_onglet.class.php,v 1.2 2021/01/12 07:42:45 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class notice_onglet {

	/* ---------------------------------------------------------------
		propriétés de la classe
   --------------------------------------------------------------- */

	public $id=0;
	public $name='';

	public function __construct($id=0) {
		$this->id = intval($id);
		$this->getData();
	}

	/* ---------------------------------------------------------------
		getData() : récupération des propriétés
   --------------------------------------------------------------- */
	public function getData() {
		if(!$this->id) return;
	
		$requete = 'SELECT * FROM notice_onglet WHERE id_onglet='.$this->id;
		$result = @pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) return;
			
		$data = pmb_mysql_fetch_object($result);
		$this->name = $data->onglet_name;
	}

	public function get_form() {
		
		global $msg;
		global $admin_onglet_content_form;
		global $charset;
		
		$content_form = $admin_onglet_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('ongletform');
		if(!$this->id){
			$interface_form->set_label($msg['admin_noti_onglet_ajout']);
		}else{
			$interface_form->set_label($msg['admin_noti_onglet_modification']);
		}
		$content_form = str_replace('!!nom!!', htmlentities($this->name, ENT_QUOTES, $charset), $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->name." ?")
		->set_content_form($content_form)
		->set_table_name('notice_onglet')
		->set_field_focus('form_nom');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_nom;
		
		$this->name = stripslashes($form_nom);
	}
	
	public function save() {
		// O.K.,  now if item already exists UPDATE else INSERT
		if($this->id) {
			$requete = "UPDATE notice_onglet SET onglet_name='".addslashes($this->name)."' WHERE id_onglet='".$this->id."' ";
			pmb_mysql_query($requete);
		} else {
			$requete = "SELECT count(1) FROM notice_onglet WHERE onglet_name='".addslashes($this->name)."' LIMIT 1 ";
			$res = pmb_mysql_query($requete);
			$nbr = pmb_mysql_result($res, 0, 0);
			if($nbr == 0){
				$requete = "INSERT INTO notice_onglet (onglet_name) VALUES ('".addslashes($this->name)."') ";
				$res = pmb_mysql_query($requete);
				$this->id = pmb_mysql_insert_id();
			}
		}
	}

	public static function check_data_from_form() {
		global $form_nom;
		
		if(empty($form_nom)) {
			return false;
		}
		return true;
	}
	
	public static function delete($id) {
		$id = intval($id);
		if ($id) {
			$req="UPDATE authperso SET authperso_notice_onglet_num=0 where authperso_notice_onglet_num=".$id;
			pmb_mysql_query($req);
			
			$requete = "DELETE FROM notice_onglet WHERE id_onglet='$id' ";
			pmb_mysql_query($requete);
			
			$requete = "OPTIMIZE TABLE origine_notice ";
			pmb_mysql_query($requete);
			return true;
		}
		return true;
	}
} /* fin de définition de la classe */


