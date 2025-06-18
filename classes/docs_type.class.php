<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docs_type.class.php,v 1.18.2.1 2021/12/27 07:42:28 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/translation.class.php");

// définition de la classe de gestion des 'docs_type'

if ( ! defined( 'DOCSTYPE_CLASS' ) ) {
  define( 'DOCSTYPE_CLASS', 1 );

class docs_type {
	/* ---------------------------------------------------------------
		propriétés de la classe
   -------------------------------------------------------------- */
	public $id=0;
	public $libelle='';
	public $duree_pret=31;
	public $duree_resa=15;
	public $tdoc_codage_import="";
	public $tdoc_owner=0;
	public $tarif_pret='0.00';
	public $short_loan_duration=1;

	/* ---------------------------------------------------------------
			docs_type($id) : constructeur
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
	
		/* récupération des informations de la catégorie */
		$requete = 'SELECT * FROM docs_type WHERE idtyp_doc='.$this->id.' LIMIT 1;';
		$result = pmb_mysql_query($requete) or die (pmb_mysql_error()." ".$requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
			
		$data = pmb_mysql_fetch_object($result);
		$this->id = $data->idtyp_doc;		
		$this->libelle = $data->tdoc_libelle;
		$this->duree_pret = $data->duree_pret;
		$this->duree_resa = $data->duree_resa;
		$this->tdoc_codage_import = $data->tdoc_codage_import;
		$this->tdoc_owner = $data->tdoc_owner;
		$this->tarif_pret = $data->tarif_pret;
		$this->short_loan_duration = $data->short_loan_duration;
	}

	public function get_form() {
		global $admin_typdoc_content_form, $msg, $charset;
		global $pmb_quotas_avances, $pmb_short_loan_management;
		global $pmb_gestion_financiere, $pmb_gestion_tarif_prets;
		
		$content_form = $admin_typdoc_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('typdocform');
		if(!$this->id){
			$interface_form->set_label($msg['122']);
		}else{
			$interface_form->set_label($msg['124']);
		}
		$content_form = str_replace('!!libelle!!', htmlentities($this->libelle, ENT_QUOTES, $charset), $content_form);
		
		$form_pret='';
		if (!$pmb_quotas_avances) {
			$form_pret = "
			<div class='row' >
				<label class='etiquette' for='form_pret'>".$msg[123]."</label>
			</div>
			<div class='row' >
				<input type='text' id='form_pret' name='form_pret' value='".$this->duree_pret."' maxlength='10' class='saisie-10em' />
			</div>";
		}
		$content_form = str_replace('<!-- form_pret -->', $form_pret, $content_form);
		
		$form_short_loan_duration='';
		if (!$pmb_quotas_avances && $pmb_short_loan_management) {
			$form_short_loan_duration = "
			<div class='row' >
				<label class='etiquette' for='form_short_loan_duration'>".$msg['short_loan_duration_wdays']."</label>
			</div>
			<div class='row' >
				<input type='text' id='form_short_loan_duration' name='form_short_loan_duration' value='".$this->short_loan_duration."' maxlength='10' class='saisie-10em' />
			</div>";
		}
		$content_form = str_replace('<!-- form_short_loan_duration -->', $form_short_loan_duration, $content_form);
		
		$form_resa='';
		if (!$pmb_quotas_avances) {
			$form_resa = "
			<div class='row' >
				<label class='etiquette' for='form_resa'>".$msg['duree_resa']."</label>
			</div>
			<div class='row' >
				<input type='text' id='form_resa' name='form_resa' value='".$this->duree_resa."' maxlength='10' class='saisie-10em' />
			</div>";
		}
		$content_form = str_replace('<!-- form_resa -->', $form_resa, $content_form);
		
		$content_form = str_replace('!!tdoc_codage_import!!', $this->tdoc_codage_import, $content_form);
		$combo_lender= gen_liste ("select idlender, lender_libelle from lenders order by lender_libelle ", "idlender", "lender_libelle", "form_tdoc_owner", "", $this->tdoc_owner, 0, $msg[556],0,$msg["proprio_generique_biblio"]);
		$content_form = str_replace('<!-- lender -->', $combo_lender, $content_form);
		
		$tarif_pret='';
		if (($pmb_gestion_financiere)&&($pmb_gestion_tarif_prets==1)) {
			$tarif_pret="
			<div class='row'>
				<label class='etiquette' for='form_tarif_pret'>".$msg['typ_doc_tarif']."</label>
			</div>
			<div class='row'>
				<input type='text' id='form_tarif_pret' name='form_tarif_pret' value='".$this->tarif_pret."' maxlength='10' class='saisie-5em' />
			</div>";
		}
		$content_form = str_replace('<!-- tarif_pret -->', $tarif_pret, $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->libelle." ?")
		->set_content_form($content_form)
		->set_table_name('docs_type')
		->set_field_focus('form_libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_libelle, $form_pret, $form_resa, $form_short_loan_duration, $form_tarif_pret, $form_tdoc_codage_import, $form_tdoc_owner;
		
		$this->libelle = stripslashes($form_libelle);
		$this->duree_pret = intval($form_pret);
		$this->duree_resa = intval($form_resa);
		$this->tdoc_owner = intval($form_tdoc_owner);
		$this->tdoc_codage_import = stripslashes($form_tdoc_codage_import);
		$this->tarif_pret = stripslashes($form_tarif_pret);
		$this->short_loan_duration = intval($form_short_loan_duration);
	}
	
	public function get_query_if_exists() {
		return "SELECT count(1) FROM docs_type WHERE (tdoc_libelle='".addslashes($this->libelle)."' AND idtyp_doc!='".$this->id."' )";
	}
	
	public function save() {
		global $pmb_quotas_avances, $pmb_gestion_financiere, $pmb_gestion_tarif_prets, $pmb_short_loan_management;
		
		// O.k., now if the id already exist UPDATE else INSERT
		$q =(($this->id)?"update ":"insert into ");
		$q.= "docs_type set tdoc_libelle='".addslashes($this->libelle)."', ";
		$q.= ((!$pmb_quotas_avances)?"duree_pret='".$this->duree_pret."', duree_resa='".$this->duree_resa."', ":'');
		$q.= ((!$pmb_quotas_avances && $pmb_short_loan_management)?"short_loan_duration='".$this->short_loan_duration."', ":'');
		$q.= (($pmb_gestion_financiere && $pmb_gestion_tarif_prets==1)?"tarif_pret='".addslashes($this->tarif_pret)."', ":'');
		$q.= "tdoc_codage_import='".addslashes($this->tdoc_codage_import)."', tdoc_owner='".$this->tdoc_owner."' ";
		$q.= (($this->id)?"where idtyp_doc=".$this->id." ":'');
		pmb_mysql_query($q);
		if(!$this->id) {
			$this->id = pmb_mysql_insert_id();
		}
		$translation = new translation($this->id, "docs_type");
		$translation->update("tdoc_libelle", "form_libelle");
		return true;
	}
	
	public static function check_data_from_form() {
		global $form_libelle;
		
		if(empty($form_libelle)) {
			return false;
		}
		return true;
	}
	
	// ---------------------------------------------------------------
	//		import() : import d'un type de document
	// ---------------------------------------------------------------
	public static function import($data) {
		// cette méthode prend en entrée un tableau constitué des informations suivantes :
		//	$data['tdoc_libelle'] 	
		//	$data['duree_pret']
		//	$data['tdoc_codage_import']
		//	$data['tdoc_owner']
	
		// check sur le type de  la variable passée en paramètre
		if ((empty($data) && !is_array($data)) || !is_array($data)) {
		    // si ce n'est pas un tableau ou un tableau vide, on retourne 0
			return 0;
			}
		// check sur les éléments du tableau
		$long_maxi = pmb_mysql_field_len(pmb_mysql_query("SELECT tdoc_libelle FROM docs_type limit 1"),0);
		$data['tdoc_libelle'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['tdoc_libelle']))),0,$long_maxi));
		$long_maxi = pmb_mysql_field_len(pmb_mysql_query("SELECT tdoc_codage_import FROM docs_type limit 1"),0);
		$data['tdoc_codage_import'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['tdoc_codage_import']))),0,$long_maxi));
	
		if($data['tdoc_owner']=="") $data['tdoc_owner'] = 0;
		if($data['tdoc_libelle']=="") return 0;
		/* tdoc_codage_import est obligatoire si tdoc_owner != 0 */
		//if(($data['tdoc_owner']!=0) && ($data['tdoc_codage_import']=="")) return 0;
		
		// préparation de la requête
		$key0 = addslashes($data['tdoc_libelle']);
		$key1 = addslashes($data['tdoc_codage_import']);
		$key2 = $data['tdoc_owner'];
		
		/* vérification que le type doc existe */
		$query = "SELECT idtyp_doc FROM docs_type WHERE tdoc_codage_import='${key1}' and tdoc_owner = '${key2}' LIMIT 1 ";
		$result = pmb_mysql_query($query);
		if(!$result) die("can't SELECT docs_type ".$query);
		$docs_type  = pmb_mysql_fetch_object($result);
	
		/* le type de doc existe, on retourne l'ID */
		if($docs_type->idtyp_doc) return $docs_type->idtyp_doc;
	
		// id non-récupérée, il faut créer la forme.
		/* une petite valeur par défaut */
		if ($data['duree_pret']=="") $data['duree_pret']=0;
		
		$query  = "INSERT INTO docs_type SET ";
		$query .= "tdoc_libelle='".$key0."', ";
		$query .= "duree_pret='".$data['duree_pret']."', ";
		$query .= "tdoc_codage_import='".$key1."', ";
		$query .= "tdoc_owner='".$key2."' ";
		$result = pmb_mysql_query($query);
		if(!$result) die("can't INSERT into docs_type ".$query);
	
		return pmb_mysql_insert_id();
	} /* fin méthode import */

	public static function delete($id) {
		global $msg, $admin_liste_jscript;
		
		$id = intval($id);
		if($id) {
			// requête sur 'exemplaires' pour voir si ce typdoc est encore utilisé
			$total = 0;
			$total = pmb_mysql_result(pmb_mysql_query("select count(1) from exemplaires where expl_typdoc ='".$id."' "), 0, 0);
			if ($total==0) {
				translation::delete($id, "docs_type");
				$q = "DELETE FROM docs_type WHERE idtyp_doc=$id ";
				pmb_mysql_query($q);
				return true;
			} else {
				$msg_suppr_err = $admin_liste_jscript;
				$msg_suppr_err .= $msg[1700]." <a href='#' onclick=\"showListItems(this);return(false);\" what='typdoc_docs' item='".$id."' total='".$total."' alt=\"".$msg["admin_docs_list"]."\" title=\"".$msg["admin_docs_list"]."\"><img src='".get_url_icon('req_get.gif')."'></a>" ;
				pmb_error::get_instance(static::class)->add_message('294', $msg_suppr_err);
				return false;
			}
		}
		return true;
	}
	
	/* une fonction pour générer des combo Box 
   paramêtres :
	$selected : l'élément sélectioné le cas échéant
   retourne une chaine de caractères contenant l'objet complet */
	public static function gen_combo_box ( $selected ) {
		global $msg;
		$requete="select idtyp_doc, tdoc_libelle from docs_type order by tdoc_libelle ";
		$champ_code="idtyp_doc";
		$champ_info="tdoc_libelle";
		$nom="book_doctype_id";
		$on_change="";
		$liste_vide_code="0";
		$liste_vide_info=$msg['class_typdoc'];
		$option_premier_code="";
		$option_premier_info="";
		$gen_liste_str="";
		$resultat_liste=pmb_mysql_query($requete) or die (pmb_mysql_error()." ".$requete);
		$gen_liste_str = "<select name=\"$nom\" onChange=\"$on_change\">\n" ;
		$nb_liste=pmb_mysql_num_rows($resultat_liste);
		if ($nb_liste==0) {
			$gen_liste_str.="<option value=\"$liste_vide_code\">$liste_vide_info</option>\n" ;
		} else {
			if ($option_premier_info!="") {	
				$gen_liste_str.="<option value=\"".$option_premier_code."\" ";
				if ($selected==$option_premier_code) $gen_liste_str.="selected" ;
				$gen_liste_str.=">".$option_premier_info."\n";
			}
			$i=0;
			while ($i<$nb_liste) {
				$gen_liste_str.="<option value=\"".pmb_mysql_result($resultat_liste,$i,$champ_code)."\" " ;
				if ($selected==pmb_mysql_result($resultat_liste,$i,$champ_code)) {
					$gen_liste_str.="selected" ;
					}
				$gen_liste_str.=">".pmb_mysql_result($resultat_liste,$i,$champ_info)."</option>\n" ;
				$i++;
			}
		}
		$gen_liste_str.="</select>\n" ;
		return $gen_liste_str ;
	} /* fin gen_combo_box */

	public function get_translated_libelle() {
		return translation::get_translated_text($this->id, 'docs_type', 'tdoc_libelle', $this->libelle);
	}
} /* fin de définition de la classe */

} /* fin de délaration */


