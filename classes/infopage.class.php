<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: infopage.class.php,v 1.2 2021/03/08 16:58:52 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $msg;
global $admin_infopages_content_form;
global $charset,$PMBuserid;
global $form_title_infopage, $form_content_infopage, $form_valid_infopage;
global $form_restrict_infopage, $classementGen_infopages;

class infopage {

	/* ---------------------------------------------------------------
		propriétés de la classe
   --------------------------------------------------------------- */

	public $id=0;
	public $title='';
	public $content='';
	public $valid=0;
	public $restrict=0;
	public $classement='';

	public function __construct($id=0) {
		$this->id = intval($id);
		$this->getData();
	}

	/* ---------------------------------------------------------------
		getData() : récupération des propriétés
   --------------------------------------------------------------- */
	public function getData() {
		if(!$this->id) return;
	
		$requete = 'SELECT * FROM infopages WHERE id_infopage='.$this->id;
		$result = @pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
			
		$data = pmb_mysql_fetch_object($result);
		$this->title = $data->title_infopage;
		$this->content = $data->content_infopage;
		$this->valid = $data->valid_infopage;
		$this->restrict = $data->restrict_infopage;
		$this->classement = $data->infopage_classement;
	}

	public function get_form() {
		global $msg;
		global $admin_infopages_content_form;
		global $charset,$PMBuserid;

		$content_form = $admin_infopages_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('infopagesform');
		if(!$this->id){
			$interface_form->set_label($msg['infopages_creer']);
		}else{
			$interface_form->set_label($msg['infopages_modifier']);
		}
		$content_form = str_replace('!!title_infopage!!', htmlentities($this->title, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!content_infopage!!', htmlentities($this->content, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!checkbox!!', ($this->valid ? "checked='checked'" : ""), $content_form);
		$content_form = str_replace('!!restrict_checkbox!!', ($this->restrict ? "checked='checked'" : ""), $content_form);
		$classementGen = new classementGen('infopages', $this->id);
		$content_form = str_replace("!!object_type!!",$classementGen->object_type,$content_form);
		$content_form = str_replace("!!classements_liste!!",$classementGen->getClassementsSelectorContent($PMBuserid,$classementGen->libelle),$content_form);
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->title." ?")
		->set_content_form($content_form)
		->set_table_name('infopages')
		->set_field_focus('form_title_infopage')
		->set_duplicable(true);
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_title_infopage, $form_content_infopage, $form_valid_infopage;
		global $form_restrict_infopage, $classementGen_infopages;
		
		$this->title = stripslashes($form_title_infopage);
		$this->content = stripslashes($form_content_infopage);
		$this->valid = intval($form_valid_infopage);
		$this->restrict = intval($form_restrict_infopage);
		$this->classement = stripslashes($classementGen_infopages);
	}
	
	public function save() {
		$set_values = "SET title_infopage='".addslashes($this->title)."', 
			content_infopage='".addslashes($this->content)."', 
			valid_infopage='".$this->valid."', 
			restrict_infopage='".$this->restrict."', 
			infopage_classement='".addslashes($this->classement)."' " ;
		if($this->id) {
			$requete = "UPDATE infopages $set_values WHERE id_infopage='".$this->id."' ";
			pmb_mysql_query($requete);
		} else {
			$requete = "INSERT INTO infopages $set_values ";
			pmb_mysql_query($requete);
		}
	}

	public static function check_data_from_form() {
		global $form_title_infopage;
		
		if(empty($form_title_infopage)) {
			return false;
		}
		return true;
	}
	
	public static function delete($id) {
		$id = intval($id);
		if ($id) {
			$requete = "DELETE from infopages WHERE id_infopage='$id' ";
			pmb_mysql_query($requete);
			return true;
		}
		return true;
	}
}


