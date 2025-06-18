<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_statut.class.php,v 1.3 2021/01/07 14:05:23 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// définition de la classe de gestion des status de notices

class notice_statut {

	/* ---------------------------------------------------------------
		propriétés de la classe
   --------------------------------------------------------------- */

	public $id=0;
	public $gestion_libelle='';
	public $opac_libelle='';
	public $visible_opac=1;
	public $visible_gestion=1;
	public $expl_visible_opac=1;
	public $class_html="";
	public $visible_opac_abon=0;
	public $expl_visible_opac_abon=0;
	public $explnum_visible_opac=1;
	public $explnum_visible_opac_abon=0;
	public $scan_request_opac=0;
	public $scan_request_opac_abon=0;

	/* ---------------------------------------------------------------
			docs_statut($id) : constructeur
	   --------------------------------------------------------------- */
	public function __construct($id=0) {
		$this->id = $id+0;
		$this->getData();
	}

	/* ---------------------------------------------------------------
		getData() : récupération des propriétés
   --------------------------------------------------------------- */
	public function getData() {
		if(!$this->id) return;
	
		/* récupération des informations du statut */
	
		$requete = 'SELECT * FROM notice_statut WHERE id_notice_statut='.$this->id;
		$result = @pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
			
		$data = pmb_mysql_fetch_object($result);
		$this->gestion_libelle = $data->gestion_libelle;
		$this->opac_libelle = $data->opac_libelle;
		$this->visible_opac = $data->notice_visible_opac;
		$this->visible_gestion = $data->notice_visible_gestion;
		$this->expl_visible_opac = $data->expl_visible_opac;
		$this->class_html = $data->class_html;
		$this->visible_opac_abon = $data->notice_visible_opac_abon;
		$this->expl_visible_opac_abon = $data->expl_visible_opac_abon;
		$this->explnum_visible_opac = $data->explnum_visible_opac;
		$this->explnum_visible_opac_abon = $data->explnum_visible_opac_abon;
		$this->scan_request_opac = $data->notice_scan_request_opac;
		$this->scan_request_opac_abon = $data->notice_scan_request_opac_abon;
	}

	public function get_form() {
		global $msg;
		global $admin_notice_statut_content_form;
		global $charset;
		
		$content_form = $admin_notice_statut_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('statutform');
		if(!$this->id){
			$interface_form->set_label($msg['115']);
		}else{
			$interface_form->set_label($msg['118']);
		}
		$content_form = str_replace('!!gestion_libelle!!', htmlentities($this->gestion_libelle,ENT_QUOTES, $charset), $content_form);
		if ($this->visible_gestion) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visible_gestion!!', $checkbox, $content_form);
		
		$content_form = str_replace('!!opac_libelle!!', htmlentities($this->opac_libelle,ENT_QUOTES, $charset), $content_form);
		if ($this->visible_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visible_opac!!', $checkbox, $content_form);
		
		if ($this->expl_visible_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visu_expl!!', $checkbox, $content_form);
		
		if ($this->visible_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visu_abon!!', $checkbox, $content_form);
		
		// $expl_visible_opac_abon=0, $explnum_visible_opac=1, $explnum_visible_opac_abon=0
		if ($this->expl_visible_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_expl_visu_abon!!', $checkbox, $content_form);
		
		if ($this->explnum_visible_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_explnum_visu!!', $checkbox, $content_form);
		
		if ($this->explnum_visible_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_explnum_visu_abon!!', $checkbox, $content_form);
		
		if ($this->scan_request_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_scan_request_opac!!', $checkbox, $content_form);
		
		if ($this->scan_request_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_scan_request_opac_abon!!', $checkbox, $content_form);
		
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
		->set_table_name('notice_statut')
		->set_field_focus('form_gestion_libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_gestion_libelle, $form_opac_libelle, $form_class_html;
		global $form_visible_gestion, $form_visible_opac, $form_visu_expl, $form_visu_abon, $form_expl_visu_abon, $form_explnum_visu_abon;
		global $form_scan_request_opac, $form_scan_request_opac_abon, $form_explnum_visu;
		
		$this->gestion_libelle = stripslashes($form_gestion_libelle);
		$this->opac_libelle = stripslashes($form_opac_libelle);
		$this->visible_gestion = intval($form_visible_gestion);
		$this->visible_opac = intval($form_visible_opac);
		$this->expl_visible_opac = intval($form_visu_expl);
		$this->class_html = stripslashes($form_class_html);
		$this->visible_opac_abon = intval($form_visu_abon);
		$this->expl_visible_opac_abon = intval($form_expl_visu_abon);
		$this->explnum_visible_opac = intval($form_explnum_visu);
		$this->explnum_visible_opac_abon = intval($form_explnum_visu_abon);
		$this->scan_request_opac = intval($form_scan_request_opac);
		$this->scan_request_opac_abon = intval($form_scan_request_opac_abon);
	}
	
	public function save() {
		// O.K.,  now if item already exists UPDATE else INSERT
		if ($this->id) {
			if ($this->id==1) {
				$visu=", notice_visible_gestion=1 ";
			} else {
				$visu=", notice_visible_gestion='".$this->visible_gestion."' ";
			}
			$visu .= ", notice_visible_opac='".$this->visible_opac."', expl_visible_opac='".$this->expl_visible_opac."', notice_visible_opac_abon='".$this->visible_opac_abon."', expl_visible_opac_abon='".$this->expl_visible_opac_abon."', explnum_visible_opac='".$this->explnum_visible_opac."', explnum_visible_opac_abon='".$this->explnum_visible_opac_abon."', notice_scan_request_opac='".$this->scan_request_opac."', notice_scan_request_opac_abon='".$this->scan_request_opac_abon."'";
			$requete = "UPDATE notice_statut SET gestion_libelle='".addslashes($this->gestion_libelle)."', opac_libelle='".addslashes($this->opac_libelle)."', class_html='".addslashes($this->class_html)."' $visu WHERE id_notice_statut='".$this->id."' ";
			pmb_mysql_query($requete);
		} else {
			$requete = "INSERT INTO notice_statut SET gestion_libelle='".addslashes($this->gestion_libelle)."',notice_visible_gestion='".$this->visible_gestion."',opac_libelle='".addslashes($this->opac_libelle)."', notice_visible_opac='".$this->visible_opac."', expl_visible_opac='".$this->expl_visible_opac."', class_html='".addslashes($this->class_html)."', notice_visible_opac_abon='".$this->visible_opac_abon."', expl_visible_opac_abon='".$this->expl_visible_opac_abon."', explnum_visible_opac='".$this->explnum_visible_opac."', explnum_visible_opac_abon='".$this->explnum_visible_opac_abon."', notice_scan_request_opac='".$this->scan_request_opac."', notice_scan_request_opac_abon='".$this->scan_request_opac_abon."' ";
			pmb_mysql_query($requete);
		}
	}

	public static function delete($id) {
		if ($id && $id!=1 && $id!=2) {
			$total = 0;
			$total = pmb_mysql_result(pmb_mysql_query("select count(1) from notices where statut ='".$id."' "), 0, 0);
			if ($total==0) {
				$requete = "DELETE FROM notice_statut WHERE id_notice_statut='$id' ";
				pmb_mysql_query($requete);
				$requete = "OPTIMIZE TABLE notice_statut ";
				pmb_mysql_query($requete);
				return true;
			} else {
				pmb_error::get_instance(static::class)->add_message('noti_statut_noti', 'noti_statut_used');
				return false;
			}
		} else {
			pmb_error::get_instance(static::class)->add_message('noti_statut_noti', 'noti_statut_delete_forbidden');
			return false;
		}
		return true;
	}
} /* fin de définition de la classe */


