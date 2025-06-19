<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesDatabase.class.php,v 1.9.2.2 2023/09/18 13:34:46 jparis Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $base_path, $include_path, $class_path;
global $pmb_version_database_as_it_should_be, $pmb_subversion_database_as_it_shouldbe;

require_once($class_path."/external_services.class.php");

if(!function_exists('form_relance')) {
	function form_relance ($maj_suivante="lancement") {
		return '';
	}
} 

if(!function_exists('traite_rqt')) {
	function traite_rqt($requete="", $message="") {
				
		$retour="";
		pmb_mysql_query($requete) ;
		$erreur_no = pmb_mysql_errno();
		if (!$erreur_no) {
			$retour = "Successful";
		} else {
			switch ($erreur_no) {
				case "1060":
					$retour = "Field already exists, no problem.";
					break;
				case "1061":
					$retour = "Key already exists, no problem.";
					break;
				case "1091":
					$retour = "Object already deleted, no problem.";
					break;
				default:
					$retour = "Error may be fatal : ".pmb_mysql_error();
					break;
			}
		}
		return $message . PHP_EOL.' >> '.$retour.PHP_EOL;
	}
} 

class pmbesDatabase extends external_services_api_class {
	
	public function get_current_version(){

		$query ="select valeur_param from parametres where type_param = 'pmb' and sstype_param ='bdd_version'";
		$result = pmb_mysql_query($query);
		$pmb_bdd_version = "v1.0";
		if(pmb_mysql_num_rows($result)){
			$pmb_bdd_version = pmb_mysql_result($result,0,0);
		}
		return $pmb_bdd_version;
	}
	
	public function get_current_subversion(){

		$query ="select valeur_param from parametres where type_param = 'pmb' and sstype_param ='bdd_subversion'";
		$result = pmb_mysql_query($query);
		$pmb_bdd_subversion = "0";
		if(pmb_mysql_num_rows($result)){
			$pmb_bdd_subversion = pmb_mysql_result($result,0,0);
		}
		return $pmb_bdd_subversion;
	}
	
	public function get_version_informations() {

		global $pmb_version_database_as_it_should_be;
		global $pmb_subversion_database_as_it_shouldbe;
		
		$tmp= array(
			'currentVersion' => $this->get_current_version(),
			'currentSubVersion' => $this->get_current_subversion(),
			'shouldbeVersion' => $pmb_version_database_as_it_should_be,
			'shouldbeSubVersion' => $pmb_subversion_database_as_it_shouldbe
		);
		return $tmp;
	}
	
	public function need_update(){
		
		global $pmb_version_database_as_it_should_be;
		global $pmb_subversion_database_as_it_shouldbe;

		if( $this->get_current_version() != $pmb_version_database_as_it_should_be ){
			return ['need' => true];
		} 
		if( $this->get_current_subversion() != $pmb_subversion_database_as_it_shouldbe ){
			return ['need' => true];
		}
		return ['need' => false];
	}
	
	public function update(){
		
		global $base_path;
		global $lang;
		global $class_path;
		global $include_path;
		global $pmb_version_database_as_it_should_be;
		global $pmb_subversion_database_as_it_shouldbe;
		
		//Les requetes sont en iso / l'affichage est en iso ou en utf8
		//on harmonise pour eviter les melanges
		global $charset;
		$charset = 'iso-8859-1';
		pmb_mysql_query("set names latin1");

		
		//Allons chercher les messages
		include_once("$class_path/XMLlist.class.php");
		$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
		$messages->analyser();
		$msg = $messages->table;
		
		//On evite d'afficher des erreurs dans le message de retour
		if(!isset($REQUEST_URI)) {
			$REQUEST_URI = '';
		}
		//les globales PMB ! 
		include($include_path."/start.inc.php");
		
		//On evite d'afficher des erreurs dans le message de retour (suite)
		global $pmb_display_errors;
		$pmb_display_errors = 0;

		$update_result = array();
		$update_result['result'] = true;
		$update_result['informations'] = "";
		
		$check = $this->need_update();
		
		if($check['need']){
			
			
			ob_start();
			
			$pmb_bdd_version = $this->get_current_version();
			$pmb_bdd_subversion = $this->get_current_subversion();
			
			if( $pmb_bdd_version != $pmb_version_database_as_it_should_be ){
				
				// Ne pas supprimer $action, $version_pmb_bdd et $maj_a_faire car utilisés dans les alter
				$action = "lancement";
				$version_pmb_bdd = $pmb_bdd_version;

				switch (substr($pmb_bdd_version,0,2)) {
					case "v1":
						include ($base_path."/admin/misc/alter_v1.inc.php") ;
						break ;
					case "v2":
						include ($base_path."/admin/misc/alter_v2.inc.php") ;
						break ;
					case "v3":
						include ($base_path."/admin/misc/alter_v3.inc.php") ;
						break ;
					case "v4" :
						if(substr($pmb_version_database_as_it_should_be,0,2) == "v5" && ($pmb_bdd_version == "v4.97" || $pmb_bdd_version == "v4.96" || $pmb_bdd_version == "v4.95" || $pmb_bdd_version == "v4.94")){
							include ($base_path."/admin/misc/alter_v5.inc.php") ;
						}else{
							include ($base_path."/admin/misc/alter_v4.inc.php") ;
						}
						break ;
					case "v5" :
						include ($base_path."/admin/misc/alter_v5.inc.php") ;
						break ;
					case "v6" :
						include ($base_path."/admin/misc/alter_v6.inc.php") ;
						break ;
				}
				ob_clean();
				// Ne pas supprimer $action, $version_pmb_bdd et $maj_a_faire car utilisés dans les alter_vX.inc.php
				$action = $maj_a_faire;
				switch (substr($action,0,2)) {
					case "v1":
						include ($base_path."/admin/misc/alter_v1.inc.php") ;
						break ;
					case "v2":
						include ($base_path."/admin/misc/alter_v2.inc.php") ;
						break ;
					case "v3":
						include ($base_path."/admin/misc/alter_v3.inc.php") ;
						break ;
					case "v4" :
						if(substr($pmb_version_database_as_it_should_be,0,2) == "v5" && ($pmb_bdd_version == "v4.97" || $pmb_bdd_version == "v4.96" || $pmb_bdd_version == "v4.95" || $pmb_bdd_version == "v4.94")){
							include ($base_path."/admin/misc/alter_v5.inc.php") ;
						}else{
							include ($base_path."/admin/misc/alter_v4.inc.php") ;
						}
						break ;
					case "v5" :
						include ($base_path."/admin/misc/alter_v5.inc.php") ;
						break ;
					case "v6" :
					    include ($base_path."/admin/misc/alter_v6.inc.php") ;
					    break ;
				}			
				$ob = ob_get_contents();
				if( false !== $ob ) {
					$update_result['informations'] = strip_tags($ob);
					$update_result['informations']  = str_replace('ActionRésultat', '', $update_result['informations'] );
				}
				
			} else {

				ob_clean();
				require_once($base_path."/admin/misc/addon.inc.php");				
				$ob = ob_get_contents();
				if( false !== $ob ) {
					$update_result['informations'] = strip_tags($ob);
				}
			}
			
			ob_end_clean();
			
		} else {
			//Et la le message est encore dans un autre charset
			$update_result['informations'] = $this->msg['update_msg_database_already_updated'];			
			$is_utf8 = mb_detect_encoding($update_result['informations'], 'UTF-8', true);
			if('UTF-8' != $is_utf8) {
				$update_result['informations'] = utf8_encode($update_result['informations']);
			}
			return $update_result;
		}
		//on retourne systématiquement le message en utf8
		//il faudra corriger si besoin au niveau du connecteur
		$update_result['informations'] = utf8_encode($update_result['informations']);
		return $update_result;
	}
}