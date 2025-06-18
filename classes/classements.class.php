<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: classements.class.php,v 1.11 2021/03/15 11:02:58 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// définition de la classe de gestion des classements de la DSI

class classement {
	// propriétés
	public $id_classement ;
	public $nom_classement = '';
	public $nom_classement_opac = '';
	public $type_classement = 'BAN';
	public $order = 0;
	
	// ---------------------------------------------------------------
	//	constructeur
	// ---------------------------------------------------------------
	public function __construct($id_classement=0) {
		$this->id_classement = intval($id_classement);
		$this->getData();
	}

	// ---------------------------------------------------------------
	public function getData() {
		$this->type_classement	= 'BAN';
		$this->nom_classement	= '';	
		$this->nom_classemen_opac = '';	
		$this->order = 0;
		if($this->id_classement) {
			$requete = "SELECT type_classement, nom_classement, classement_opac_name, classement_order FROM classements WHERE id_classement='$this->id_classement' ";
			$result = @pmb_mysql_query($requete);
			if (pmb_mysql_num_rows($result)) {
				$temp = pmb_mysql_fetch_object($result);
				pmb_mysql_free_result($result);
				if($temp->type_classement) {
					$this->type_classement = $temp->type_classement;
				}
				$this->nom_classement = $temp->nom_classement;
				$this->nom_classement_opac = $temp->classement_opac_name;
				$this->order = $temp->classement_order;
			}
		}
	}

	// ---------------------------------------------------------------
	//		show_form : affichage du formulaire de saisie
	// ---------------------------------------------------------------
	public function show_form($type="pro") {
		global $msg, $charset;
		global $dsi_classement_content_form;
	
		$content_form = $dsi_classement_content_form;
		if ($this->id_classement) {
			$type_classement = $msg['dsi_clas_type_class_'.$this->type_classement] ;
		} else {
			$type_classement = "<select id='type_classement' name='type_classement'><option value='BAN'>".$msg['dsi_clas_type_class_BAN']."</option><option value='EQU'>".$msg['dsi_clas_type_class_EQU']."</OPTION></select>";
		}
		$content_form = str_replace('!!id_classement!!', $this->id_classement, $content_form);
		$content_form = str_replace('!!nom_classement!!', htmlentities($this->nom_classement,ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!nom_classement_opac!!', htmlentities($this->nom_classement_opac,ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!type_classement!!', $type_classement, $content_form);
		
		$interface_form = new interface_dsi_form('saisie_classement');
		if(!$this->id_classement){
			$interface_form->set_label($msg['dsi_clas_form_creat']);
		}else{
			$interface_form->set_label($msg['dsi_clas_form_modif']);
		}
		
		$interface_form->set_object_id($this->id_classement)
		->set_confirm_delete_msg($msg['confirm_suppr'])
		->set_content_form($content_form)
		->set_table_name('classements')
		->set_field_focus('nom_classement');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $nom_classement;
		global $nom_classement_opac;
		global $type_classement;
		
		$this->nom_classement = stripslashes($nom_classement);
		$this->nom_classement_opac = stripslashes($nom_classement_opac);
		$this->type_classement = stripslashes($type_classement);
	}
	
	// ---------------------------------------------------------------
	public function save() {
		if ($this->id_classement) {
			$query = "update classements set nom_classement='".addslashes($this->nom_classement)."', classement_opac_name='".addslashes($this->nom_classement_opac)."' where id_classement='".$this->id_classement."'";
			pmb_mysql_query($query);
		} else {
			$set_order='';
			if($this->type_classement == 'BAN'){
				$requete="select max(classement_order) as ordre from classements where (type_classement='BAN' or type_classement='') ";
				$resultat=pmb_mysql_query($requete);
				$ordre_max=@pmb_mysql_result($resultat,0,0);
				$this->order = ($ordre_max+1);
				$set_order= ', classement_order= '.$this->order.' ';
			}
			$query = "insert into classements set nom_classement='".addslashes($this->nom_classement)."', classement_opac_name='".addslashes($this->nom_classement_opac)."', type_classement='".addslashes($this->type_classement)."' ".$set_order;
			pmb_mysql_query($query);
			$this->id_classement = pmb_mysql_insert_id() ;
		}
	}
	
	// ---------------------------------------------------------------
	public function delete() {
		if ($this->id_classement==1) return ;
		$requete = "delete FROM classements where id_classement='".$this->id_classement."' ";
		pmb_mysql_query($requete);
	}
	
	// ---------------------------------------------------------------
	public function set_order($sens) {
		if($this->type_classement == 'BAN'){
			$query_where = "(type_classement='BAN' or type_classement='')";
		} else {
			$query_where = "(type_classement='EQU')";
		}
		switch ($sens){
			case "up":
				$requete="select classement_order from classements where id_classement=".$this->id_classement;
				$resultat=pmb_mysql_query($requete);
				$ordre=pmb_mysql_result($resultat,0,0);
				$requete="select max(classement_order) as ordre from classements where ".$query_where." and classement_order<$ordre";
				$resultat=pmb_mysql_query($requete);
				$ordre_max=@pmb_mysql_result($resultat,0,0);
				if ($ordre_max != '') {
					$requete="select id_classement from classements where ".$query_where." and classement_order=$ordre_max limit 1";
					$resultat=pmb_mysql_query($requete);
					$idchamp_max=pmb_mysql_result($resultat,0,0);
					$requete="update classements set classement_order='".$ordre_max."' where id_classement=".$this->id_classement;
					pmb_mysql_query($requete);
					$requete="update classements set classement_order='".$ordre."' where id_classement=".$idchamp_max;
					pmb_mysql_query($requete);
				}
				break;
			case "down":
				$requete="select classement_order from classements where id_classement=".$this->id_classement;
				$resultat=pmb_mysql_query($requete);
				$ordre=pmb_mysql_result($resultat,0,0);
				$requete="select min(classement_order) as ordre from classements where ".$query_where." and classement_order>$ordre";
				$resultat=pmb_mysql_query($requete);
				$ordre_min=@pmb_mysql_result($resultat,0,0);
				if ($ordre_min != '') {
					$requete="select id_classement from classements where ".$query_where." and classement_order=$ordre_min limit 1";
					$resultat=pmb_mysql_query($requete);
					$idchamp_min=pmb_mysql_result($resultat,0,0);
					$requete="update classements set classement_order='".$ordre_min."' where id_classement=".$this->id_classement;
					pmb_mysql_query($requete);
					$requete="update classements set classement_order='".$ordre."' where id_classement=".$idchamp_min;
					pmb_mysql_query($requete);
				}
				break;		
		}
	}
	
} // fin de déclaration de la classe classement
  
