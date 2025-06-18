<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: harvest_profil_import.class.php,v 1.7 2021/01/29 09:34:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;

require_once($class_path."/harvest.class.php");	
require_once($include_path."/templates/harvest_profil_import.tpl.php");
require_once($include_path."/parser.inc.php");

class harvest_profil_import {
	public $id=0;
	public $info=array();
	public $fields_id=array();
	public $fields=array();
	
	public function __construct($id=0) {
		$this->id=intval($id);
		$this->fetch_data();
	}
	
	public function fetch_data() {
		global $include_path;
		
		$this->info=array(
				'id' => $this->id,
				'name' => '',
				'num_harvest' => 0
		);
		
		$nomfichier=$include_path."/harvest/harvest_fields.xml";
		if (file_exists($nomfichier)) {
			$fp = fopen($nomfichier, "r");		
			if ($fp) {
				//un fichier est ouvert donc on le lit
				$xml = fread($fp, filesize($nomfichier));
				//on le ferme
				fclose($fp);			
				$param=_parser_text_no_function_($xml,"HARVEST");
				$this->fields=$param["FIELD"];				
			}
  		}
  		$this->fields_id=array();
  		foreach($this->fields as $key => $field){
  			$this->fields_id[$this->fields[$key]["ID"]]=$field;			
  		}
		if(!$this->id) return;
		$req="select * from harvest_profil_import where id_harvest_profil_import=". $this->id;
		
		$resultat=pmb_mysql_query($req);	
		if (pmb_mysql_num_rows($resultat)) {
			$r=pmb_mysql_fetch_object($resultat);		
			$this->info['id']= $r->id_harvest_profil_import;	
			$this->info['name']= $r->harvest_profil_import_name;	
		}	
		$this->info['fields']=array();	
		$req="select * from harvest_profil_import_field where num_harvest_profil_import=".$this->id." order by harvest_profil_import_field_order";
		$resultat=pmb_mysql_query($req);	
		if (pmb_mysql_num_rows($resultat)) {
			while($r=pmb_mysql_fetch_object($resultat)){						
				$this->info['fields'][$r->harvest_profil_import_field_xml_id]['id']= $r->harvest_profil_import_field_xml_id;	
				$this->info['fields'][$r->harvest_profil_import_field_xml_id]['xml']= $r->harvest_profil_import_field_xml_id;	
				$this->info['fields'][$r->harvest_profil_import_field_xml_id]['flagtodo']= $r->harvest_profil_import_field_flag;	
			}
		}	
		
		if(isset($this->info['num_harvest']) && $this->info['num_harvest']) {
			$this->info['harvest']=new harvest($this->info['num_harvest']);
		}
	}
	   
	public function get_notice($id, $notice_uni = array()) {
		$memo=array();
		
		$req="select * from notices where notice_id=".$id." ";
		$resultat=pmb_mysql_query($req);	
		if ($r=pmb_mysql_fetch_object($resultat)) {
			$code=$r->code;
			$notice_extern= $this->info['harvest']->havest_notice($code);
			foreach($notice_extern as $contens){				
				if($this->info['fields'][$contens['xml_id']]){					
					if($this->info['fields'][$contens['xml_id']]['flagtodo']==1){
						// on remplace les champs par les nouvelles valeurs
						$memo[]=$contens;
						foreach($notice_uni['f'] as $index=>$uni_field){		
							if($contens['ufield'] && $contens['usubfield']){	
								// si champ et sous champ, on delete les anciens champs/sous-champ
								
								
							}elseif($contens['ufield']) {
								// si pas de sous champ on efface tout 
							}
							
							
						}
					}else if($this->info['fields'][$contens['xml_id']]['flagtodo']==2){
						// on ajoute
						
					}	
				}	
			}
			printr($memo);				
			printr($notice_uni['f']);
		}
	}
       
	public function get_form() {
		global $harvest_content_form_tpl, $harvest_form_elt_tpl,$msg,$charset;
		
		$content_form = $harvest_content_form_tpl;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		$interface_form = new interface_admin_form('harvest');
		if(!$this->id){
			$interface_form->set_label($msg['admin_harvest_profil_form_add']);
		}else{
			$interface_form->set_label($msg['admin_harvest_profil_form_edit']);
		}
		$content_form = str_replace('!!name!!', htmlentities($this->info['name'], ENT_QUOTES, $charset), $content_form);
		
		$elt_list="";
		foreach($this->fields as $field){	// pour tout les champs unimarc à récolter
			$elt=$harvest_form_elt_tpl;
			$elt=str_replace("!!pmb_field_msg!!",$msg[$field["NAME"]],$elt);
			
			if($this->id){
				// Edition: les valeurs des champs sont issues de la base
				$elt=str_replace("!!flagtodo_checked_".$this->info['fields'][$field["ID"]]['flagtodo']."!!"," checked='checked' ",$elt);
				
			} else {
				// Création:les valeurs des champs sont issues du fichier XML
				
			}
			$elt=str_replace("!!flagtodo_checked_0!!"," checked='checked' ",$elt);
			$elt=str_replace("!!flagtodo_checked_1!!","",$elt);
			$elt=str_replace("!!flagtodo_checked_2!!","",$elt);
			
			$elt=str_replace("!!id!!",$field["ID"],$elt);
			$elt_list.=$elt;
		}
		$content_form=str_replace('!!elt_list!!',$elt_list,$content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->info['name']." ?")
		->set_content_form($content_form)
		->set_table_name('harvest_profil_import')
		->set_field_focus('name');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $name, $num_harvest;
		
		$this->info['name']=stripslashes($name);
		$this->info['num_harvest']=intval($num_harvest);
	}
	
	public function save() {
		if(!$this->id){ // Ajout
			$req="INSERT INTO harvest_profil_import SET 
				harvest_profil_import_name='".addslashes($this->info['name'])."'
			";	
			pmb_mysql_query($req);
			$this->id = pmb_mysql_insert_id();
		} else {
			$req="UPDATE harvest_profil_import SET 
				harvest_profil_import_name='".addslashes($this->info['name'])."'
				where 	id_harvest_profil_import=".$this->id;	
			pmb_mysql_query($req);			
		
			$req=" DELETE from harvest_profil_import_field WHERE num_harvest_profil_import=".$this->id;
			pmb_mysql_query($req);					
		}
		$cpt_fields=0;
		foreach($this->fields as $field ){
			$var="flagtodo_".$field["ID"];
			global ${$var};
    		$flagtodo=${$var}+0;
    		
			$req="INSERT INTO harvest_profil_import_field SET 
				num_harvest_profil_import=".$this->id.",
				harvest_profil_import_field_xml_id=".$field["ID"].",	
				harvest_profil_import_field_flag=".$flagtodo.",					
				harvest_profil_import_field_order=".$cpt_fields++."	
			";	
			pmb_mysql_query($req);
			$harvest_field_id = pmb_mysql_insert_id();	
    		
		}
		$this->fetch_data();
	}	
	
	public static function delete($id) {
		$id = intval($id);
		if($id) {
			$req=" DELETE from harvest_profil_import_field WHERE num_harvest_profil_import_field=".$id;
			pmb_mysql_query($req);
			$req=" DELETE from  harvest_profil_import where id_harvest_profil_import=". $id;
			pmb_mysql_query($req);
		}
		return true;
	}	
	    
} //harvest class end


class harvest_profil_imports {	
	public $info=array();
	
	public function __construct() {
		$this->fetch_data();
	}
	
	public function fetch_data() {
		$this->info=array();
		$i=0;
		$req="select * from harvest_profil_import ";		
		$resultat=pmb_mysql_query($req);	
		if (pmb_mysql_num_rows($resultat)) {
			while($r=pmb_mysql_fetch_object($resultat)){	
				$this->info[$i]= new harvest_profil_import($r->id_harvest_profil_import);					
				$i++;
			}
		}	
	}	
	
	public function get_sel($sel_name,$sel_id=0) {
		$tpl="<select name='$sel_name' >";				
		foreach($this->info as $elt){
			if($elt->info['id']==$sel_id){
				$tpl.="<option value=".$elt->info['id']." selected='selected'>".$elt->info['name']."</option>";
			} else {
				$tpl.="<option value=".$elt->info['id'].">".$elt->info['name']."</option>";
			}
		}
		$tpl.="</select>";
		return $tpl;
	}		
} //harvests class end