<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: equation.class.php,v 1.12 2022/03/23 15:13:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// définition de la classe de gestion des 'équations de recherche'
global $class_path;
require_once($class_path."/search.class.php");

class equation {

	// ---------------------------------------------------------------
	//		propriétés de la classe
	// ---------------------------------------------------------------
	public $id_equation=0;	
	public $num_classement=1; 
	public $nom_equation="";
	public $comment_equation="";
	public $requete="";
	public $proprio_equation=0;
	public $search_class;
	public $human_query = "" ;

	// ---------------------------------------------------------------
	//		constructeur
	// ---------------------------------------------------------------
	public function __construct($id=0) {
		$this->id_equation = intval($id);
		//Instantiation d'une classe recherche
		$this->search_class=new search(false);
		$this->getData();
	}

	// ---------------------------------------------------------------
	//		getData() : récupération infos
	// ---------------------------------------------------------------
	public function getData() {
		$this->num_classement = 1 ;
		$this->nom_equation="";
		$this->comment_equation="";
		$this->requete="";
		$this->proprio_equation=0;
		$this->human_query = "" ;
		if ($this->id_equation) {
			$query = "SELECT num_classement, nom_equation,comment_equation,requete, proprio_equation FROM equations WHERE id_equation='".$this->id_equation."' " ;
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)) {
				$temp = pmb_mysql_fetch_object($result);
			 	$this->num_classement	= $temp->num_classement ;
				$this->nom_equation		= $temp->nom_equation ;
				$this->comment_equation	= $temp->comment_equation ;	
				$this->requete			= $temp->requete ;
				$this->proprio_equation	= $temp->proprio_equation ;	
				$this->human_query = $this->search_class->make_serialized_human_query($this->requete) ;
			}
		}
	}
	
	// ---------------------------------------------------------------
	//		show_form : affichage du formulaire de saisie
	// ---------------------------------------------------------------
	public function show_form() {
		global $msg, $charset;
		global $dsi_equation_content_form;
		
		$content_form = $dsi_equation_content_form;
		$content_form = str_replace('!!id_equation!!', $this->id_equation, $content_form);
		
		$interface_form = new interface_dsi_form('saisie_equation');
		if(!$this->id_equation){
			$interface_form->set_label($msg['dsi_equ_form_creat']);
		}else{
			$interface_form->set_label($msg['dsi_equ_form_modif']);
		}
		$content_form = str_replace('!!nom_equation!!', htmlentities($this->nom_equation,ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!num_classement!!', show_classement_utilise ('EQU', $this->num_classement, 0), $content_form);
		$content_form = str_replace('!!comment_equation!!', htmlentities($this->comment_equation,ENT_QUOTES, $charset), $content_form);
		
		$content_form = str_replace('!!requete!!', htmlentities($this->requete,ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!requete_human!!', $this->search_class->make_serialized_human_query($this->requete), $content_form);
		/*
		 if ($this->proprio_equation==0) {
		 $content_form = str_replace('!!proprio_equation!!', htmlentities($msg['dsi_equ_no_proprio'],ENT_QUOTES, $charset), $content_form);
		 } else {
		 $content_form = str_replace('!!proprio_equation!!', "Choix de proprio à faire", $content_form);
		 }
		 */
		$content_form = str_replace('!!proprio_equation!!', '', $content_form);
		
		if($this->id_equation) {
			$button_modif_requete = "<input type='button' class='bouton' value=\"$msg[dsi_equ_modif_requete]\" onClick=\"document.modif_requete_form_$this->id_equation.submit();\">";
			$form_modif_requete = $this->make_hidden_search_form();
		} else {
			$button_modif_requete = "";
			$form_modif_requete = "";
		}
		$content_form = str_replace('!!bouton_modif_requete!!', $button_modif_requete,  $content_form);
		
		$interface_form->set_object_id($this->id_equation)
		->set_confirm_delete_msg($msg['confirm_suppr'])
		->set_content_form($content_form)
		->set_table_name('equations')
		->set_field_focus('nom_equation')
		->set_duplicable(true);
		$display = $interface_form->get_display();
		//formulaire caché intégré hors formulaire de l'équation
		$display .= $form_modif_requete;
		return $display;
	}
	
	public function set_properties_from_form() {
		global $num_classement;
		global $equ_human;
		global $nom_bannette;
		global $equation;
		global $id_empr;
		global $empr_nom, $empr_prenom;

		$this->num_classement = intval($num_classement);
		$this->nom_equation = trim(stripslashes($equ_human));
		$this->comment_equation = $empr_nom." ".$empr_prenom.' -> '.trim(stripslashes($nom_bannette));
		$this->requete = stripslashes($equation);
		$this->proprio_equation = intval($id_empr);
	}
	
	// ---------------------------------------------------------------
	//		save
	// ---------------------------------------------------------------
	public function save() {
		if ($this->id_equation) {
			// update
			$query = "UPDATE equations set ";
			$clause = " WHERE id_equation='".$this->id_equation."'";
		} else {
			$query = "insert into equations set ";
			$clause = "";
		}
		$query.="num_classement='$this->num_classement',";
		$query.="nom_equation='".addslashes($this->nom_equation)."',";
		$query.="comment_equation='".addslashes($this->comment_equation)."',";
		$query.="requete='".addslashes($this->requete)."',";
		$query.="proprio_equation='".$this->proprio_equation."'";
		$query.=$clause ;
		pmb_mysql_query($query);
		if (!$this->id_equation) $this->id_equation = pmb_mysql_insert_id() ;
	}
	
	// ---------------------------------------------------------------
	//		delete() : suppression 
	// ---------------------------------------------------------------
	public function delete() {
		global $msg;
		
		if (!$this->id_equation)
			// impossible d'accéder à cette équation
			return $msg[409];
	
		$query = "delete from bannette_equation WHERE num_equation='$this->id_equation'";
		pmb_mysql_query($query);
		$query = "delete from equations WHERE id_equation='$this->id_equation'";
		pmb_mysql_query($query);
	}

	// pour maj de requete d'équation
    public function make_hidden_search_form($url="") {
    	global $search;
    	global $charset;
    	$url = "./catalog.php?categ=search&mode=6" ;
    	// remplir $search
    	$this->search_class->unserialize_search($this->requete);
    	
    	$r="<form name='modif_requete_form' action='$url' style='display:none' method='post'>";
    	
    	for ($i=0; $i<count($search); $i++) {
    		$inter="inter_".$i."_".$search[$i];
    		global ${$inter};
    		$op="op_".$i."_".$search[$i];
    		global ${$op};
    		$field_="field_".$i."_".$search[$i];
    		global ${$field_};
    		$field=${$field_};
    		
    		$r.="<input type='hidden' name='search[]' value='".htmlentities($search[$i],ENT_QUOTES,$charset)."'/>";
    		$r.="<input type='hidden' name='".$inter."' value='".htmlentities(${$inter},ENT_QUOTES,$charset)."'/>";
    		$r.="<input type='hidden' name='".$op."' value='".htmlentities(${$op},ENT_QUOTES,$charset)."'/>";
    		for ($j=0; $j<count($field); $j++) {
    			$r.="<input type='hidden' name='".$field_."[]' value='".htmlentities($field[$j],ENT_QUOTES,$charset)."'/>";
    		}
    	}
    	$r.="<input type='hidden' name='id_equation' value='$this->id_equation'/>";
    	$r.="</form>";
    	return $r;
    }

} # fin de définition de la classe equation
