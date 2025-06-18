<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: explnum_statut.class.php,v 1.3 2021/01/07 14:25:37 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/translation.class.php");

// définition de la classe de gestion des status de documents numériques

class explnum_statut {

	/* ---------------------------------------------------------------
		propriétés de la classe
   --------------------------------------------------------------- */

	public $id=0;
	public $gestion_libelle='';
	public $opac_libelle='';
	public $class_html="";
	public $visible_opac=1;
	public $consult_opac=1;
	public $download_opac=1;
	public $visible_opac_abon=0;
	public $consult_opac_abon=0;
	public $download_opac_abon=0;
	public $thumbnail_visible_opac_override=0;
	
	/* ---------------------------------------------------------------
			docs_statut($id) : constructeur
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
	
		/* récupération des informations du statut */
	
		$requete = 'SELECT * FROM explnum_statut WHERE id_explnum_statut='.$this->id;
		$result = @pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
			
		$data = pmb_mysql_fetch_object($result);
		$this->gestion_libelle = $data->gestion_libelle;
		$this->opac_libelle = $data->opac_libelle;
		$this->class_html = $data->class_html;
		$this->visible_opac = $data->explnum_visible_opac;
		$this->consult_opac = $data->explnum_consult_opac;
		$this->download_opac = $data->explnum_download_opac;
		$this->visible_opac_abon = $data->explnum_visible_opac_abon;
		$this->consult_opac_abon = $data->explnum_consult_opac_abon;
		$this->download_opac_abon = $data->explnum_download_opac_abon;
		$this->thumbnail_visible_opac_override = $data->explnum_thumbnail_visible_opac_override;
	}

	public function get_form() {
		global $msg;
		global $admin_docnum_statut_content_form;
		global $charset;
		
		$content_form = $admin_docnum_statut_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('statutform');
		if(!$this->id){
			$interface_form->set_label($msg['115']);
		}else{
			$interface_form->set_label($msg['118']);
		}
		$content_form = str_replace('!!gestion_libelle!!', htmlentities($this->gestion_libelle,ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!libelle_suppr!!', addslashes($this->gestion_libelle), $content_form);
		
		$content_form = str_replace('!!opac_libelle!!', htmlentities($this->opac_libelle,ENT_QUOTES, $charset), $content_form);
		if ($this->visible_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visible_opac!!', $checkbox, $content_form);
		
		if ($this->consult_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_consult_opac!!', $checkbox, $content_form);
		
		if ($this->download_opac) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_download_opac!!', $checkbox, $content_form);
		
		if ($this->visible_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_visible_opac_abon!!', $checkbox, $content_form);
		
		if ($this->consult_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_consult_opac_abon!!', $checkbox, $content_form);
		
		if ($this->download_opac_abon) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_download_opac_abon!!', $checkbox, $content_form);
		
		if ($this->thumbnail_visible_opac_override) $checkbox="checked"; else $checkbox="";
		$content_form = str_replace('!!checkbox_thumbnail_visible_opac_override!!', $checkbox, $content_form);
		
		$couleur=array();
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
		->set_table_name('explnum_statut')
		->set_field_focus('form_gestion_libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_gestion_libelle, $form_opac_libelle, $form_class_html, $form_visible_opac, $form_consult_opac, $form_download_opac;
		global $form_visible_opac_abon, $form_consult_opac_abon, $form_download_opac_abon, $form_thumbnail_visible_opac_override;
		
		$this->gestion_libelle = stripslashes($form_gestion_libelle);
		$this->opac_libelle = stripslashes($form_opac_libelle);
		$this->class_html = stripslashes($form_class_html);
		$this->visible_opac = intval($form_visible_opac);
		$this->consult_opac = intval($form_consult_opac);
		$this->download_opac = intval($form_download_opac);
		$this->visible_opac_abon = intval($form_visible_opac_abon);
		$this->consult_opac_abon = intval($form_consult_opac_abon);
		$this->download_opac_abon = intval($form_download_opac_abon);
		$this->thumbnail_visible_opac_override = intval($form_thumbnail_visible_opac_override);
	}
	
	public function save() {
		if ($this->id) {
			$requete = 'UPDATE explnum_statut SET
						gestion_libelle="'.addslashes($this->gestion_libelle).'",
						opac_libelle="'.addslashes($this->opac_libelle).'",
						class_html="'.addslashes($this->class_html).'",
						explnum_visible_opac="'.$this->visible_opac.'",
						explnum_consult_opac="'.$this->consult_opac.'",
						explnum_download_opac="'.$this->download_opac.'",
						explnum_visible_opac_abon="'.$this->visible_opac_abon.'",
						explnum_consult_opac_abon="'.$this->consult_opac_abon.'",
						explnum_download_opac_abon="'.$this->download_opac_abon.'",
						explnum_thumbnail_visible_opac_override="'.$this->thumbnail_visible_opac_override.'"
			 			WHERE id_explnum_statut="'.$this->id.'" ';
			pmb_mysql_query($requete);
		} else {
			$requete = 'INSERT INTO explnum_statut SET
						gestion_libelle="'.addslashes($this->gestion_libelle).'",
						opac_libelle="'.addslashes($this->opac_libelle).'",
						class_html="'.addslashes($this->class_html).'",
						explnum_visible_opac="'.$this->visible_opac.'",
						explnum_consult_opac="'.$this->consult_opac.'",
						explnum_download_opac="'.$this->download_opac.'",
						explnum_visible_opac_abon="'.$this->visible_opac_abon.'",
						explnum_consult_opac_abon="'.$this->consult_opac_abon.'",
						explnum_download_opac_abon="'.$this->download_opac_abon.'",
						explnum_thumbnail_visible_opac_override="'.$this->thumbnail_visible_opac_override.'" ';
			pmb_mysql_query($requete);
			$this->id = pmb_mysql_insert_id();
		}
		$translation = new translation($this->id, "explnum_statut");
		$translation->update("gestion_libelle", "form_gestion_libelle");
		$translation->update("opac_libelle", "form_opac_libelle");
	}

	public static function delete($id) {
		$id = intval($id);
		if ($id && $id!=1) {
			$total = 0;
			$total = pmb_mysql_result(pmb_mysql_query("select count(1) from explnum where explnum_docnum_statut ='".$id."' "), 0, 0);
			if ($total==0) {
				translation::delete($id, "explnum_statut");
				$requete = "DELETE FROM explnum_statut WHERE id_explnum_statut='$id' ";
				pmb_mysql_query($requete);
				$requete = "OPTIMIZE TABLE explnum_statut ";
				pmb_mysql_query($requete);
				return true;
			} else {
				pmb_error::get_instance(static::class)->add_message('docnum_statut_docnum', 'docnum_statut_used');
				return false;
			}
		}
		return true;
	}
} /* fin de définition de la classe */


