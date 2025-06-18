<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: etagere.class.php,v 1.4.2.2 2022/01/18 21:05:25 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// définition de la classe de gestion des 'auteurs'

if ( ! defined( 'ETAGERE_CLASS' ) ) {
  define( 'ETAGERE_CLASS', 1 );
  
global $class_path;
require_once($class_path."/sort.class.php");
require_once($class_path."/translation.class.php");

class etagere {
	// propriétés
	public $idetagere ;
	public $name = ''			;	// nom de référence
	public $comment = ""		;	// description du contenu du panier
	public $comment_gestion = "";	// Commentaire de gestion
	public $validite = 1		;	// validite de l'étagère permanente ?
	public $validite_date_deb = ''	;	// 	si non permanente date de début
	public $validite_date_fin = ''	;	// 	                  date de fin
	public $validite_date_deb_f = ''	;	// 	si non permanente date de début formatée
	public $validite_date_fin_f = ''	;	// 	                  date de fin formatée
	public $visible_accueil = 1	;	// visible en page d'accueil ?
	public $id_tri = 0;
	public $thumbnail_url = '';
	public $autorisations = ""		;	// autorisations accordées sur ce panier
	public $classementGen = ""		;	// classement

	// constructeur
	public function __construct($etagere_id=0) {
		$this->idetagere = intval($etagere_id);
		$this->getData();
	}
	
	// récupération infos etagere
	public function getData() {
		global $msg ;
		
		$this->name	= '';
		$this->comment	= '';
		$this->comment_gestion	= '';
		$this->autorisations	= "";
		$this->validite = "";
		$this->validite_date_deb = "";
		$this->validite_date_fin = "";
		$this->validite_date_deb_f = "";
		$this->validite_date_fin_f = "";
		$this->visible_accueil = "";
		$this->id_tri = 0;
		$this->thumbnail_url = '';
		$this->classementGen = '';
		if($this->idetagere) {
			$requete = "SELECT idetagere, name, comment, comment_gestion, validite, ";
			$requete .= "validite_date_deb, date_format(validite_date_deb, '".$msg["format_date"]."') as validite_date_deb_f,  ";
			$requete .= "validite_date_fin, date_format(validite_date_fin, '".$msg["format_date"]."') as validite_date_fin_f,  ";
			$requete .= "visible_accueil, autorisations, id_tri, thumbnail_url, etagere_classement FROM etagere WHERE idetagere='$this->idetagere' ";
			$result = pmb_mysql_query($requete);
			if(pmb_mysql_num_rows($result)) {
				$temp = pmb_mysql_fetch_object($result);
				$this->idetagere = $temp->idetagere;
				$this->name = $temp->name;
				$this->comment = $temp->comment;
				$this->comment_gestion = $temp->comment_gestion;
				$this->validite = $temp->validite;
				$this->validite_date_deb = $temp->validite_date_deb;
				$this->validite_date_deb_f = $temp->validite_date_deb_f;
				$this->validite_date_fin = $temp->validite_date_fin;
				$this->validite_date_fin_f = $temp->validite_date_fin_f;
				$this->visible_accueil = $temp->visible_accueil;
				$this->autorisations = $temp->autorisations;
				$this->id_tri = $temp->id_tri;
				$this->thumbnail_url = $temp->thumbnail_url;
				$this->classementGen = $temp->etagere_classement;
			}
		}
	}
	
	public function get_form() {
		global $msg, $charset;
		global $base_path;
		global $PMBuserid;
		global $pmb_javascript_office_editor;
		global $etagere_form;
		
		$form = $etagere_form;
		if($this->idetagere) {
			$form = str_replace('!!formulaire_titre!!', $msg['etagere_edit_etagere'], $form);
			$form = str_replace('!!formulaire_action!!', $base_path."/catalog.php?categ=etagere&sub=gestion&action=save_etagere&idetagere=".$this->idetagere, $form);
			$form = str_replace('!!autorisations_users!!', users::get_form_autorisations($this->autorisations,0), $form);
			$button_duplicate = "<input type='button' class='bouton' value='".htmlentities($msg['duplicate'], ENT_QUOTES, $charset)."' onClick=\"document.location='".$base_path."/catalog.php?categ=etagere&sub=gestion&action=duplicate_etagere&idetagere=".$this->idetagere."'\" />";
			$form = str_replace('!!button_duplicate!!', $button_duplicate, $form);
		} else {
			$form = str_replace('!!formulaire_titre!!', $msg['etagere_new_etagere'], $form);
			$form = str_replace('!!formulaire_action!!', $base_path."/catalog.php?categ=etagere&sub=gestion&action=valid_new_etagere", $form);
			$form = str_replace('!!autorisations_users!!', users::get_form_autorisations($this->autorisations,1), $form);
			$form = str_replace('!!button_duplicate!!', "", $form);
		}
		$form = str_replace('!!formulaire_annuler!!', $base_path."/catalog.php?categ=etagere&sub=gestion&action=", $form);
		$form = str_replace('!!idetagere!!', $this->idetagere, $form);
		$form = str_replace('!!name!!', htmlentities($this->name,ENT_QUOTES, $charset), $form);
		$bouton_suppr = "<input type='button' class='bouton' value=' ".$msg['supprimer']." ' onClick=\"javascript:confirmation_delete(".$this->idetagere.",'".htmlentities(addslashes($this->name),ENT_QUOTES, $charset)."')\" />" ;
		$form = str_replace('<!--!!bouton_suppr!!-->', $bouton_suppr, $form);
		$form = str_replace('!!comment!!', $this->comment, $form);
		$form = str_replace('!!comment_gestion!!', $this->comment_gestion, $form);
		
		if($this->id_tri>0){
			$sort = new sort("notices","base");
			$form = str_replace('!!tri!!', $this->id_tri, $form);
			$form = str_replace('!!tri_name!!', $sort->descriptionTriParId($this->id_tri), $form);
		}else{
			$form = str_replace('!!tri!!', "", $form);
			$form = str_replace('!!tri_name!!', $msg['etagere_form_no_active_tri'], $form);
		}
		if ($this->validite || !$this->idetagere) {
			$form = str_replace('!!checkbox_all!!', "checked", $form);
			$form = str_replace('!!form_visible_deb!!', "", $form);
			$form = str_replace('!!form_visible_fin!!', "", $form);
		} else {
			$form = str_replace('!!checkbox_all!!', "", $form);
			$form = str_replace('!!form_visible_deb!!', $this->validite_date_deb_f, $form);
			$form = str_replace('!!form_visible_fin!!', $this->validite_date_fin_f, $form);
		}
		if ($this->visible_accueil) $form = str_replace('!!checkbox_accueil!!', "checked", $form);
		else $form = str_replace('!!checkbox_accueil!!', "", $form);
			
		$message_folder = static::validate_img_folder();
		$form = str_replace('!!message_folder!!', $message_folder, $form);
		$form = str_replace('!!thumbnail_url!!', $this->thumbnail_url, $form);
		$classementGen = new classementGen('etagere', $this->idetagere);
		$form = str_replace("!!object_type!!",$classementGen->object_type,$form);
		$form = str_replace("!!classements_liste!!",$classementGen->getClassementsSelectorContent($PMBuserid,$classementGen->libelle),$form);
		
		$js_script = confirmation_delete($base_path."/catalog.php?categ=etagere&action=del_etagere&idetagere=");
		if($pmb_javascript_office_editor){
			$js_script .= $pmb_javascript_office_editor;
			$js_script .= "<script type='text/javascript' src='".$base_path."/javascript/tinyMCE_interface.js'></script>";
		}
		$translation = new translation($this->idetagere, 'etagere');
		$form .= $translation->connect('etagere_form');
		return $js_script.$form;
	}
	
	public function set_properties_from_form() {
		global $form_etagere_name, $form_etagere_comment, $form_etagere_comment_gestion;
		global $form_visible_all, $form_visible_deb, $form_visible_fin;
		global $form_visible_accueil;
		global $tri, $f_thumbnail_url, $classementGen_etagere;
		global $autorisations;
		
		$this->name = stripslashes($form_etagere_name);
		$this->comment = stripslashes($form_etagere_comment);
		$this->comment_gestion = stripslashes($form_etagere_comment_gestion);
		$this->validite = $form_visible_all;
		$this->validite_date_deb_f = $form_visible_deb;
		$this->validite_date_fin_f = $form_visible_fin;
		$this->validite_date_deb = extraitdate($form_visible_deb);
		$this->validite_date_fin = extraitdate($form_visible_fin);
		$this->visible_accueil = $form_visible_accueil;
		$this->tri = $tri;
		$this->thumbnail_url = stripslashes($f_thumbnail_url);
		$this->classementGen = stripslashes($classementGen_etagere);
		if (is_array($autorisations)) {
			$this->autorisations=implode(" ",$autorisations);
		}
		else {
			$this->autorisations="1";
		}
	}
	
	// liste des étagères disponibles
	public static function get_etagere_list() {
		global $msg ;
		$etagere_list=array();
		$requete = "SELECT idetagere, name, comment, comment_gestion, validite, ";
		$requete .= "validite_date_deb, date_format(validite_date_deb, '".$msg["format_date"]."') as validite_date_deb_f,  ";
		$requete .= "validite_date_fin, date_format(validite_date_fin, '".$msg["format_date"]."') as validite_date_fin_f,  ";
		$requete .= "visible_accueil, autorisations, etagere_classement FROM etagere order by name ";
		$result = pmb_mysql_query($requete);
		if(pmb_mysql_num_rows($result)) {
			while ($temp = pmb_mysql_fetch_object($result)) {
					$sql = "SELECT COUNT(*) FROM etagere_caddie WHERE etagere_id = ".$temp->idetagere;
					$res = pmb_mysql_query($sql);
					$nbr_paniers = pmb_mysql_result($res, 0, 0);
									
					$etagere_list[] = array( 
						'idetagere' => $temp->idetagere,
						'name' => $temp->name,
						'comment' => $temp->comment,
						'comment_gestion' => $temp->comment_gestion,
						'validite' => $temp->validite,
						'validite_date_deb' => $temp->validite_date_deb,
						'validite_date_fin' => $temp->validite_date_fin,
						'validite_date_deb_f' => $temp->validite_date_deb_f,
						'validite_date_fin_f' => $temp->validite_date_fin_f,
						'visible_accueil' => $temp->visible_accueil,
						'autorisations' => $temp->autorisations,
						'etagere_classement' => $temp->etagere_classement,
						'nb_paniers' => $nbr_paniers
						);
				}
			} 
		return $etagere_list;
	}
	
	// création d'une etagere vide
	public function create_etagere() {
		$requete = "insert into etagere set name='".addslashes($this->name)."', comment='".addslashes($this->comment)."', comment_gestion='".addslashes($this->comment_gestion)."', validite='".$this->validite."', validite_date_deb='".$this->validite_date_deb."', validite_date_fin='".$this->validite_date_fin."', visible_accueil='".$this->visible_accueil."', autorisations='".$this->autorisations."'";
		pmb_mysql_query($requete);
		$this->idetagere = pmb_mysql_insert_id();
		$this->save_translations();
	}
	
	// suppression d'une etagere
	public function delete() {
		$requete = "delete FROM etagere_caddie where etagere_id='".$this->idetagere."' ";
		pmb_mysql_query($requete);
		$this->delete_vignette();
		translation::delete($this->idetagere, "etagere");
		$requete = "delete FROM etagere where idetagere='".$this->idetagere."' ";
		pmb_mysql_query($requete);
			
	}
	
	public function delete_vignette() {
		//Suppression de la vignette d'etagere
	    thumbnail::delete($this->idetagere, 'shelve');
	}
	
	public function create_vignette() {
		$thumbnail_url=$this->thumbnail_url;
		
		// vignette de l'etagere
		$uploaded_thumbnail_url = thumbnail::create($this->idetagere, 'shelve');
		if($uploaded_thumbnail_url) {
			$thumbnail_url = $uploaded_thumbnail_url;
		}
		
		return $thumbnail_url;
	}
	
	// sauvegarde de l'etagere
	public function save_etagere() {
		$this->thumbnail_url = $this->create_vignette();
		if(!$this->thumbnail_url) {
			$this->delete_vignette();
		}
		$requete = "update etagere set name='".addslashes($this->name)."', comment='".addslashes($this->comment)."', comment_gestion='".addslashes($this->comment_gestion)."', validite='".$this->validite."', validite_date_deb='".$this->validite_date_deb."', validite_date_fin='".$this->validite_date_fin."', visible_accueil='".$this->visible_accueil."', autorisations='".$this->autorisations."',id_tri='".$this->tri."',thumbnail_url='".addslashes($this->thumbnail_url)."',etagere_classement='".addslashes($this->classementGen)."' where idetagere='".$this->idetagere."'";
		pmb_mysql_query($requete);
		$this->save_translations();
	}

	public function save_translations() {
		$translation = new translation($this->idetagere, "etagere");
		$translation->update("name", "form_etagere_name");
		$translation->update_text("comment", "form_etagere_comment");
		$translation->update_text("comment_gestion", "form_etagere_comment_gestion");
	}
	
	public static function validate_img_folder () {
		return thumbnail::get_message_folder('shelve');
	}	
	
	public static function check_rights($id) {
		global $PMBuserid;
	
		if ($id) {
			$query = "SELECT autorisations FROM etagere WHERE idetagere='$id' ";
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)) {
				$temp = pmb_mysql_fetch_object($result);
				$rqt_autorisation=explode(" ",$temp->autorisations);
				if (array_search ($PMBuserid, $rqt_autorisation)!==FALSE || $PMBuserid == 1) return $id ;
			}
		}
		return 0 ;
	}
	
	public function get_translated_name() {
		return translation::get_translated_text($this->idetagere, 'etagere', 'name',  $this->name);
	}
	
	public function get_translated_comment() {
		return translation::get_translated_text($this->idetagere, 'etagere', 'comment',  $this->comment);
	}
	
	public function get_translated_comment_gestion() {
		return translation::get_translated_text($this->idetagere, 'etagere', 'comment_gestion',  $this->comment_gestion);
	}
} // fin de déclaration de la classe cart
  
} # fin de déclaration du fichier caddie.class
