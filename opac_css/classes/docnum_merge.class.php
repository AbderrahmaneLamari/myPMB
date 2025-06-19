<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docnum_merge.class.php,v 1.9.4.1 2023/03/06 08:54:57 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once ($class_path."/explnum.class.php");
//gestion des droits
require_once($class_path."/acces.class.php");

class docnum_merge {
	
	// ---------------------------------------------------------------
	//		propriétés de la classe
	// ---------------------------------------------------------------	
	public $ids;		// MySQL id in table 'notice_tpl'
		
	// ---------------------------------------------------------------
	//		constructeur
	// ---------------------------------------------------------------
	public function __construct($id_notices=0,$docnum_ids=0) {			
		$this->id_notices = $id_notices;	
		$this->docnum_ids = $docnum_ids;
		$this->getData();
	}
	
	// ---------------------------------------------------------------
	//		getData() : récupération infos 
	// ---------------------------------------------------------------
	public function getData() {

	}
	

	
	public function merge(){
		global $gestion_acces_active,$gestion_acces_empr_notice;
		
		$filename_list = array();
		$cpt_doc_num=0;
		if(is_array($this->docnum_ids) && count($this->docnum_ids)){
			foreach($this->docnum_ids as $explnum_id){
				$explnum = new explnum($explnum_id);
				
                $id_for_rigths = $explnum->explnum_notice;
                if($explnum->explnum_bulletin != 0){
					//si bulletin, les droits sont rattachés à la notice du bulletin, à défaut du pério...
                    $req = "select bulletin_notice,num_notice from bulletins where bulletin_id =".$explnum->explnum_bulletin;
					$res = pmb_mysql_query($req);
					if(pmb_mysql_num_rows($res)){
						$row = pmb_mysql_fetch_object($res);
						$id_for_rigths = $row->num_notice;
						if(!$id_for_rigths){
							$id_for_rigths = $row->bulletin_notice;
						}
					}					
                }
					
				//droits d'acces emprunteur/notice
                $rights = 0;
                $dom_2 = 0;
				if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
					$ac= new acces();
					$dom_2= $ac->setDomain(2);
					$rights= $dom_2->getRights($_SESSION['id_empr_session'],$id_for_rigths);
				}					
					
				//Accessibilité des documents numériques aux abonnés en opac
				$req_restriction_abo = "SELECT  explnum_visible_opac, explnum_visible_opac_abon FROM notice_statut, explnum, notices WHERE explnum_notice=notice_id AND statut=id_notice_statut  AND explnum_id='$explnum_id' ";
				$result=pmb_mysql_query($req_restriction_abo);
				if(! pmb_mysql_num_rows($result) ){
					$req_restriction_abo="SELECT explnum_visible_opac, explnum_visible_opac_abon
						FROM notice_statut, explnum, bulletins, notices
						WHERE explnum_bulletin = bulletin_id
						AND num_notice = notice_id
						AND statut = id_notice_statut
						AND explnum_id='$explnum_id' ";
					$result=pmb_mysql_query($req_restriction_abo);
				}			
				$expl_num=pmb_mysql_fetch_array($result);
					
				if( $rights & 16 || (is_null($dom_2) && $expl_num["explnum_visible_opac"] && (!$expl_num["explnum_visible_opac_abon"] || ($expl_num["explnum_visible_opac_abon"] && $_SESSION["user_code"])))){
                	if ($content = $explnum->get_file_content()) {
						$filename="./temp/doc_num_".$explnum_id.session_id().".pdf";
						$filename_list[]=$filename;
						$fp = fopen($filename, "wb");
                        fwrite($fp,  $content);
						fclose($fp);
						
						$cpt_doc_num++;
					}
				}
			}
		}
	
		if($cpt_doc_num>1){			
			$filename_output="./temp/doc_num_output".session_id().".pdf";
			$cmd="pdfunite ".implode(' ',$filename_list)." ".$filename_output;	
			exec($cmd);	
			$contenu_merge = file_get_contents($filename_output);
			unlink($filename_output);
			foreach($filename_list as $filename){
				unlink($filename);
			}	
			header('Content-type: application/pdf');			
			print $contenu_merge;
		}elseif($cpt_doc_num){	
			$contenu_merge = file_get_contents($filename_list[0]);
			header('Content-type: application/pdf');		
			print $contenu_merge;
			unlink($filename_list[0]);
		}
	}	
} // fin class 


