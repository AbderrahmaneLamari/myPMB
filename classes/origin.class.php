<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: origin.class.php,v 1.5 2021/01/20 12:55:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/*
 * Classe de gestion d'une origine...
 */
class origin {
	public $id;			// Identifiant de l'origine
	public $type;			// Type associé à l'origine
	public $name;			// Nom de l'origine
	public $country;		// Pays d'orgine
	public $diffusible;	// Booléen pour définir si les éléments de l'origine sont exportables...
	
	
	public function __construct($id=0,$type="authorities"){
		$this->type = $type;
		$this->id = intval($id);
		if($this->id){
			$this->_fetch_data();
		}else{
			$this->name = "";
			$this->country = "";
			$this->diffusible = true;
		}
	}
	
	private function _fetch_data(){
		$query = "select * from origin_".$this->type." where id_origin_".$this->type." = ".$this->id;
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			$row = pmb_mysql_fetch_assoc($result);
			$this->name = $row['origin_'.$this->type."_name"];
			$this->country = $row['origin_'.$this->type."_country"];
			$this->diffusible = ($row['origin_'.$this->type."_diffusible"]==1 ? true : false);
		}
	}
	
	public function get_form(){
		global $msg,$charset;
		global $origin_content_form;
		
		$content_form = $origin_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('origin');
		if(!$this->id){
			$interface_form->set_label($msg['authorities_origin_add']);
		}else{
			$interface_form->set_label($msg['authorities_origin_modif']);
		}
		$content_form = str_replace('!!origin_name!!', htmlentities($this->name, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!origin_country!!', htmlentities($this->country, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace("!!checked!!",($this->diffusible ? "checked='checked'" : ""),$content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->name." ?")
		->set_content_form($content_form)
		->set_table_name('origin_'.$this->type)
		->set_field_focus('origin_name');
		return $interface_form->get_display();
	}
	
	public function is_diffusible(){
		return $this->diffusible;
	}
	
	public function set_properties_from_form() {
		global $origin_name, $origin_country, $origin_diffusible;
		
		$this->name = stripslashes($origin_name);
		$this->country = stripslashes($origin_country);
		$this->diffusible = ($origin_diffusible ? true : false);
	}
	
	public function save(){
		if($this->name != ""){
			if($this->id){
				$query = "update origin_".$this->type ." set ";
				$where = "where id_origin_".$this->type." = ".$this->id;	
			}else{
				$query = "insert into origin_".$this->type ." set ";
				$where = "";
			}
			$query .= "origin_".$this->type."_name = '".addslashes($this->name)."',";
			$query .= "origin_".$this->type."_country = '".addslashes($this->country)."',";
			$query .= "origin_".$this->type."_diffusible = '".($this->is_diffusible() ? 1:0)."' ";
			$result = pmb_mysql_query($query.$where);
			if($result) return true;
			else return false;
		}
		return false;
	}
	
	public static function delete($id){
		$type = 'authorities';
		$id = intval($id);
		if($id) {
			if($id < 2){
				// le catalogue interne et la BnF, c'est pas négociable !
			}else{
				//TODO check utilisation
				$query = "delete from origin_".$type." where id_origin_".$type." = ".$id;
				pmb_mysql_query($query);
				return true;
			}
		}
		return true;
	} 
	
	public static function gen_combo_box($type="authorities",$name="authorities_origin"){
		global $charset;
		
		$query = "select id_origin_".$type.",origin_".$type."_name from origin_".$type;
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			$selector = "
			<select name='".$name."'>";	
			while ($row = pmb_mysql_fetch_assoc($result)){
				$selector.= " 
				<option value='".$row['id_origin_'.$type]."'>".htmlentities($row['origin_'.$type.'_name'],ENT_QUOTES,$charset)."</option>";
			}
			$selector .= "
			</select>";
		}
		return $selector;
	}
	
	public static function import($type="authorities",$origin){
		if($origin!=""){
			$query = "select id_origin_".$type." from origin_".$type." where  origin_".$type."_name = '".$origin['origin']."'";
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)){
				return pmb_mysql_result($result,0,0);
			}else{
				$query = "insert into origin_".$type." set 
					origin_".$type."_name = '".$origin['origin']."',
					origin_".$type."_country = '".$origin['country']."'";
				$result = pmb_mysql_query($query);
				if($result) return pmb_mysql_insert_id();
			}
		}
		return false;
		
	}
}