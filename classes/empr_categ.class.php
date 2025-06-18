<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: empr_categ.class.php,v 1.3 2021/01/12 07:42:45 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/translation.class.php");

class empr_categ {

	/* ---------------------------------------------------------------
		propriétés de la classe
   --------------------------------------------------------------- */

	public $id=0;
	public $libelle='';
	public $duree_adhesion=365;
	public $tarif_abt='0.00';
	public $age_min=0;
	public $age_max=0;
	
	/* ---------------------------------------------------------------
			empr_categ($id) : constructeur
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
	
		$query = 'SELECT * FROM empr_categ WHERE id_categ_empr='.$this->id;
		$result = pmb_mysql_query($query);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
			
		$data = pmb_mysql_fetch_object($result);
		$this->libelle = $data->libelle;
		$this->duree_adhesion = $data->duree_adhesion;
		$this->tarif_abt = $data->tarif_abt;
		$this->age_min = $data->age_min;
		$this->age_max = $data->age_max;
	}

	public function get_form() {
		global $msg;
		global $admin_categlec_content_form ;
		global $charset;
		global $pmb_gestion_financiere,$pmb_gestion_abonnement;
		
		$content_form = $admin_categlec_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('categform');
		if(!$this->id){
			$interface_form->set_label($msg['524']);
		}else{
			$interface_form->set_label($msg['525']);
		}
		$content_form = str_replace('!!libelle!!', htmlentities($this->libelle, ENT_QUOTES, $charset), $content_form);
		
		$content_form = str_replace('!!duree_adhesion!!', htmlentities($this->duree_adhesion,ENT_QUOTES, $charset), $content_form);
		
		if (($pmb_gestion_financiere)&&($pmb_gestion_abonnement==1)) {
			$tarif_adhesion="
		<div class='row'>
			<label class='etiquette' for='form_tarif_adhesion'>".$msg["empr_categ_tarif"]."</label>
		</div>
		<div class='row'>
			<input type=text name='form_tarif_adhesion' id='form_tarif_adhesion' value='".htmlentities($this->tarif_abt,ENT_QUOTES,$charset)."' maxlength='10' class='saisie-5em' />
		</div>
		";
		} else $tarif_adhesion="";
		$content_form = str_replace('!!tarif_adhesion!!', $tarif_adhesion, $content_form);
		$content_form = str_replace('!!age_min!!', htmlentities($this->age_min,ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!age_max!!', htmlentities($this->age_max,ENT_QUOTES, $charset), $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->libelle." ?")
		->set_content_form($content_form)
		->set_table_name('empr_categ')
		->set_field_focus('form_libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_libelle, $form_duree_adhesion, $form_tarif_adhesion, $form_age_min, $form_age_max;
		
		$this->libelle = stripslashes($form_libelle);
		$this->duree_adhesion = stripslashes($form_duree_adhesion);
		$this->tarif_abt = stripslashes($form_tarif_adhesion);
		$this->age_min = intval($form_age_min);
		$this->age_max = intval($form_age_max);
	}
	
	public function get_query_if_exists() {
		return "SELECT count(1) FROM empr_categ WHERE (libelle='".addslashes($this->libelle)."' AND id_categ_empr!='".$this->id."' )";
	}
	
	public function save() {
		// O.K.,  now if item already exists UPDATE else INSERT
		if($this->id) {
			$requete = "UPDATE empr_categ SET libelle='".addslashes($this->libelle)."', duree_adhesion='".addslashes($this->duree_adhesion)."', tarif_abt='".addslashes($this->tarif_abt)."', age_min='".$this->age_min."', age_max='".$this->age_max."' WHERE id_categ_empr=".$this->id;
			$res = pmb_mysql_query($requete);
		} else {
			$requete = "SELECT count(1) FROM empr_categ WHERE libelle='".addslashes($this->libelle)."' LIMIT 1 ";
			$res = pmb_mysql_query($requete);
			$nbr = pmb_mysql_result($res, 0, 0);
			if($nbr == 0) {
				$requete = "INSERT INTO empr_categ (id_categ_empr,libelle,duree_adhesion,tarif_abt,age_min, age_max) VALUES ('', '".addslashes($this->libelle)."','".addslashes($this->duree_adhesion)."','".addslashes($this->tarif_abt)."','".$this->age_min."','".$this->age_max."') ";
				$res = pmb_mysql_query($requete);
				$this->id = pmb_mysql_insert_id();
			}
		}
		$translation = new translation($this->id, "empr_categ");
		$translation->update("libelle", "form_libelle");
	}
	
	public static function check_data_from_form() {
		global $form_libelle;
		
		if(empty($form_libelle)) {
			return false;
		}
		return true;
	}
	
	public static function delete($id) {
		$id = intval($id);
		if($id) {
			$total = 0;
			$total = pmb_mysql_result(pmb_mysql_query("select count(1) from empr where empr_categ ='".$id."' "), 0, 0);
			if ($total==0) {
				$test = pmb_mysql_result(pmb_mysql_query("select count(1) from search_persopac_empr_categ where id_categ_empr ='".$id."' "), 0, 0);
				if($test == 0){
					translation::delete($id, "empr_categ");
					$requete = "DELETE FROM empr_categ WHERE id_categ_empr='$id' ";
					pmb_mysql_query($requete);
					$requete = "OPTIMIZE TABLE empr_categ ";
					pmb_mysql_query($requete);
					$requete = "delete from search_persopac_empr_categ where id_categ_empr = $id";
					pmb_mysql_query($requete);
					return true;
				}else{
					pmb_error::get_instance(static::class)->add_message('294', 'empr_categ_cant_delete_search_perso');
					return false;
				}
			} else {
				pmb_error::get_instance(static::class)->add_message('294', '1708');
				return false;
			}
		}
		return true;
	}
	
	public function get_translated_libelle() {
		return translation::get_translated_text($this->id, 'empr_categ', 'libelle', $this->libelle);
	}
	
} /* fin de définition de la classe */