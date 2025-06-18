<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: arch_statut.class.php,v 1.1 2021/01/07 13:33:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class arch_statut {
	
	/* ---------------------------------------------------------------
	 propriétés de la classe
	 --------------------------------------------------------------- */
	
	public $id=0;
	public $gestion_libelle='';
	public $opac_libelle='';
	public $visible_opac=1;
	public $visible_opac_abon=0;
	public $visible_gestion=1;
	public $class_html='';
	
	/* ---------------------------------------------------------------
	 empr_codestat($id) : constructeur
	 --------------------------------------------------------------- */
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->getData();
	}
	
	/* ---------------------------------------------------------------
	 getData() : récupération des propriétés
	 --------------------------------------------------------------- */
	public function getData() {
		if(!$this->id) return;
		
		$query = 'SELECT * FROM arch_statut WHERE archstatut_id='.$this->id;
		$result = pmb_mysql_query($query);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		$data = pmb_mysql_fetch_object($result);
		$this->gestion_libelle = $data->archstatut_gestion_libelle;
		$this->opac_libelle = $data->archstatut_opac_libelle;
		$this->visible_opac = $data->archstatut_visible_opac;
		$this->visible_opac_abon = $data->archstatut_visible_opac_abon;
		$this->visible_gestion = $data->archstatut_visible_gestion;
		$this->class_html = $data->archstatut_class_html;
	}
	
	public function get_form() {
		global $msg;
		global $admin_collstate_statut_content_form;
		global $charset;
		
		$content_form = $admin_collstate_statut_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('statutform');
		if(!$this->id){
			$interface_form->set_label($msg['115']);
		}else{
			$interface_form->set_label($msg['118']);
		}
		$content_form = str_replace('!!gestion_libelle!!', htmlentities($this->gestion_libelle,ENT_QUOTES, $charset), $content_form);
		
		//if ($visible_gestion) $checkbox="checked"; else $checkbox="";
		//$content_form = str_replace('!!checkbox_visible_gestion!!', $checkbox, $content_form);
		
		$content_form = str_replace('!!opac_libelle!!', htmlentities($this->opac_libelle,ENT_QUOTES, $charset), $content_form);
		if ($this->visible_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visible_opac!!', $checkbox, $content_form);
		
		if ($this->visible_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visu_abon!!', $checkbox, $content_form);
		
		$couleur = array();
		for ($i=1;$i<=20; $i++) {
			if ($this->class_html=="statutnot".$i) $checked = "checked";
			else $checked = "";
			$couleur[$i]="<span for='statutnot".$i."' class='statutnot".$i."' style='margin: 7px;'><img src='".get_url_icon('spacer.gif')."' width='10' height='10' />
					<input id='statutnot".$i."' type=radio name='form_class_html' value='statutnot".$i."' $checked class='checkbox' /></span>";
			if ($i==10) $couleur[10].="<br />";
			elseif ($i!=20) $couleur[$i].="<b>|</b>";
		}
		$couleurs=implode("",$couleur);
		$content_form = str_replace('!!class_html!!', $couleurs, $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->gestion_libelle." ?")
		->set_content_form($content_form)
		->set_table_name('arch_statut')
		->set_field_focus('form_gestion_libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_gestion_libelle, $form_opac_libelle;
		global $form_visible_gestion, $form_visible_opac, $form_visu_abon, $form_class_html;
		
		$this->gestion_libelle = stripslashes($form_gestion_libelle);
		$this->opac_libelle = stripslashes($form_opac_libelle);
		$this->visible_opac = intval($form_visible_opac);
		$this->visible_opac_abon = intval($form_visu_abon);
		$this->visible_gestion = intval($form_visible_gestion);
		$this->class_html = stripslashes($form_class_html);
	}
	
	public function save() {
		if ($this->id) {
			if ($this->id==1) $visu=", archstatut_visible_gestion=1, archstatut_visible_opac='".$this->visible_opac."', archstatut_visible_opac_abon='".$this->visible_opac_abon."' ";
			else $visu=", archstatut_visible_gestion='".$this->visible_gestion."', archstatut_visible_opac='".$this->visible_opac."', archstatut_visible_opac_abon='".$this->visible_opac_abon."' ";
			$requete = "UPDATE arch_statut SET archstatut_gestion_libelle='".addslashes($this->gestion_libelle)."', archstatut_opac_libelle='".addslashes($this->opac_libelle)."', archstatut_class_html='".addslashes($this->class_html)."' $visu WHERE archstatut_id='".$this->id."' ";
			pmb_mysql_query($requete);
		} else {
			$requete = "INSERT INTO arch_statut SET archstatut_gestion_libelle='".addslashes($this->gestion_libelle)."',archstatut_visible_gestion='".$this->visible_gestion."',archstatut_opac_libelle='".addslashes($this->opac_libelle)."', archstatut_visible_opac='".$this->visible_opac."', archstatut_class_html='".addslashes($this->class_html)."', archstatut_visible_opac_abon='".$this->visible_opac_abon."' ";
			pmb_mysql_query($requete);
		}
	}
	
	public static function delete($id) {
		$id = intval($id);
		if ($id) {
			$total = 0;
			$total = pmb_mysql_result(pmb_mysql_query("select count(1) from collections_state where collstate_statut ='".$id."' "), 0, 0);
			if ($total==0) {
				$requete = "DELETE FROM arch_statut WHERE archstatut_id='$id' ";
				pmb_mysql_query($requete);
				$requete = "OPTIMIZE TABLE arch_statut ";
				pmb_mysql_query($requete);
				return true;
			} else {
				pmb_error::get_instance(static::class)->add_message('294', 'collstate_statut_used');
				return false;
			}
		}
		return true;
	}
} /* fin de définition de la classe */