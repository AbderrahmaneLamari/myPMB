<?php
// +-------------------------------------------------+
// © 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: paiements.class.php,v 1.13 2021/01/18 12:58:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class paiements{
	
	public $id_paiement = 0;					//Identifiant du paiement 
	public $libelle = '';
	public $commentaire = '';
	 
	//Constructeur.	 
	public function __construct($id_paiement= 0) {
		$this->id_paiement = intval($id_paiement);
		if ($this->id_paiement) {
			$this->load();	
		}
	}	

	// charge le paiement à partir de la base.
	public function load(){
		$q = "select * from paiements where id_paiement = '".$this->id_paiement."' ";
		$r = pmb_mysql_query($q) ;
		if(!pmb_mysql_num_rows($r)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		$obj = pmb_mysql_fetch_object($r);
		$this->libelle = $obj->libelle;
		$this->commentaire = $obj->commentaire;
	}
	
	public function get_form() {
		global $msg, $charset;
		global $mode_content_form;
		
		$content_form = $mode_content_form;
		$content_form = str_replace('!!id!!', $this->id_paiement, $content_form);
		
		$interface_form = new interface_admin_form('modeform');
		if(!$this->id_paiement){
			$interface_form->set_label($msg['acquisition_ajout_mode']);
		}else{
			$interface_form->set_label($msg['acquisition_modif_mode']);
		}
		$content_form = str_replace('!!libelle!!', htmlentities($this->libelle, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!commentaire!!', htmlentities($this->commentaire, ENT_QUOTES, $charset), $content_form);
		
		$interface_form->set_object_id($this->id_paiement)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->libelle." ?")
		->set_content_form($content_form)
		->set_table_name('paiements')
		->set_field_focus('libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $libelle, $comment;
		
		$this->libelle = stripslashes($libelle);
		$this->commentaire = stripslashes($comment);
	}
	
	public function get_query_if_exists() {
		$query = "select count(1) from paiements where libelle = '".addslashes($this->libelle)."' ";
		if ($this->id_paiement) $query .= "and id_paiement != '".$this->id_paiement."' ";
		return $query;
	}
	
	// enregistre le paiement en base.
	public function save(){
		if($this->libelle =='') Die("Erreur de création paiement");
		if($this->id_paiement) {
			$q = "update paiements set libelle ='".addslashes($this->libelle)."', commentaire = '".addslashes($this->commentaire)."' ";
			$q.= "where id_paiement = '".$this->id_paiement."' ";
			pmb_mysql_query($q);
		} else {
			$q = "insert into paiements set libelle = '".addslashes($this->libelle)."', commentaire = '".addslashes($this->commentaire)."' ";
			pmb_mysql_query($q);
			$this->id_paiement = pmb_mysql_insert_id();
		}
	}

	//supprime un paiement de la base
	public static function delete($id= 0) {
		global $msg;
		
		$id = intval($id);
		if($id) {
			$total1 = static::hasFournisseurs($id);
			if ($total1==0) {
				$q = "delete from paiements where id_paiement = '".$id."' ";
				pmb_mysql_query($q);
				return true;
			} else {
				$msg_suppr_err = $msg['acquisition_mode_used'] ;
				if ($total1) $msg_suppr_err .= "<br />- ".$msg['acquisition_mode_used_fou'] ;
				pmb_error::get_instance(static::class)->add_message('321', $msg_suppr_err);
				return false;
			}
		}
		return true;		
	}
	
	//Retourne un Resultset contenant la liste des modes de paiement
	public static function listPaiements() {
		$q = "select * from paiements order by libelle ";
		$r = pmb_mysql_query($q);
		return $r;
	}
	
	//Vérifie si un mode de paiement existe			
	public static function exists($id){
		$id = intval($id);
		$q = "select count(1) from paiements where id_paiement = '".$id."' ";
		$r = pmb_mysql_query($q); 
		return pmb_mysql_result($r, 0, 0);
	}
		
	//Vérifie si le libellé d'un mode de paiement existe déjà			
	public static function existsLibelle($libelle, $id=0){
		$id = intval($id);
		$q = "select count(1) from paiements where libelle = '".$libelle."' ";
		if ($id) $q.= "and id_paiement != '".$id."' ";
		$r = pmb_mysql_query($q); 
		return pmb_mysql_result($r, 0, 0);
	}

	//Vérifie si le mode de paiement est utilisé dans les fournisseurs	
	public static function hasFournisseurs($id){
		$id = intval($id);
		if (!$id) return 0;
		$q = "select count(1) from entites where num_paiement = '".$id."' and type_entite = '0'";
		$r = pmb_mysql_query($q); 
		return pmb_mysql_result($r, 0, 0);
	}
	
	//optimization de la table paiements
	public function optimize() {
		$opt = pmb_mysql_query('OPTIMIZE TABLE paiements');
		return $opt;
	}
}
?>