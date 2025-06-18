<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authperso_admin.class.php,v 1.19 2021/03/05 08:38:15 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($include_path."/templates/authperso_admin.tpl.php");
require_once($include_path."/templates/parametres_perso.tpl.php");
require_once($class_path."/custom_parametres_perso.class.php");

class authperso_admin {
	public $id=0;
	public $info=array();
	
	
	public function __construct($id=0) {
		$this->id=intval($id);
		$this->fetch_data();
	}
	
	public function fetch_data() {
		$this->info=array();
		$this->info['fields']=array();
		if(!$this->id) {
			$this->info['name']= '';
			$this->info['onglet_num']= 0;
			$this->info['isbd_script']= '';
			$this->info['view_script']= '';
			$this->info['opac_search']= 0;
			$this->info['opac_multi_search']= 0;
			$this->info['gestion_search']= 0;
			$this->info['gestion_multi_search']= 0;
			$this->info['oeuvre_event']= 0;
			$this->info['comment']= '';
			$this->info['responsability_authperso']= 0;
			return;
		}
		
		$req="select * from authperso where id_authperso=". $this->id;		
		$resultat=pmb_mysql_query($req);	
		if (pmb_mysql_num_rows($resultat)) {
			$r=pmb_mysql_fetch_object($resultat);		
			$this->info['id']= $r->id_authperso;	
			$this->info['name']= $r->authperso_name;
			$this->info['onglet_num']= $r->authperso_notice_onglet_num;		
			$this->info['isbd_script']= $r->authperso_isbd_script;			
			$this->info['view_script']= $r->authperso_view_script;			
			$this->info['opac_search']= $r->authperso_opac_search;			
			$this->info['opac_multi_search']= $r->authperso_gestion_multi_search;			
			$this->info['gestion_search']= $r->authperso_gestion_search;			
			$this->info['gestion_multi_search']= $r->authperso_gestion_multi_search;	
			$this->info['oeuvre_event']= $r->authperso_oeuvre_event;				
			$this->info['comment']= $r->authperso_comment;	
			$this->info['responsability_authperso']= $r->authperso_responsability_authperso;	
			$this->info['onglet_name']="";
			$req="SELECT * FROM notice_onglet where id_onglet=".$r->authperso_notice_onglet_num;
			$resultat=pmb_mysql_query($req);
			if (pmb_mysql_num_rows($resultat)) {
				$r_onglet=pmb_mysql_fetch_object($resultat);	
				$this->info['onglet_name']= $r_onglet->onglet_name;						
			}
		} else {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
		}
	}
 
	public function get_form() {
		global $authperso_form_tpl,$msg,$charset;		
		
		$tpl=$authperso_form_tpl;
		if($this->id){
			$tpl=str_replace('!!msg_title!!',$msg['admin_authperso_form_edit'],$tpl);
			//bouton supprimer
			$req="select * from authperso_authorities where authperso_authority_authperso_num=". $this->id;
			$res = pmb_mysql_query($req);
			if((pmb_mysql_num_rows($res))) {
				$tpl=str_replace('!!delete!!','', $tpl);
			} else {
				$tpl=str_replace('!!delete!!',"<input type='button' class='bouton' value='".$msg['admin_authperso_delete']."'  onclick=\"document.getElementById('auth_action').value='delete';this.form.submit();\"  />", $tpl);
			}
		}else{ 
			$tpl=str_replace('!!msg_title!!',$msg['admin_authperso_form_add'],$tpl);
			$tpl=str_replace('!!delete!!',"",$tpl);
		}
		$notice_onglet_list=gen_liste ("SELECT * FROM notice_onglet", 
				"id_onglet", "onglet_name", "notice_onglet", "", $this->info['onglet_num'], 0, $msg["admin_authperso_notice_onglet_no"],0,$msg["admin_authperso_notice_onglet_sel"]);
		
		if($this->info['opac_multi_search']) $search_multi_checked= " checked='checked' ";
		else $search_multi_checked= "";
		$search_simple_checked=array();
		$search_simple_checked[$this->info['opac_search']+0]= " checked='checked' ";
		$search_tpl="
			<input type='radio' ".(isset($search_simple_checked[0]) ? $search_simple_checked[0] : '')." name='search_simple' value='0' >".$msg["admin_authperso_opac_search_no"]."
			<input type='radio' ".(isset($search_simple_checked[1]) ? $search_simple_checked[1] : '')." name='search_simple' value='1' >".$msg["admin_authperso_opac_search_yes"]."
			<input type='radio' ".(isset($search_simple_checked[2]) ? $search_simple_checked[2] : '')." name='search_simple' value='2' >".$msg["admin_authperso_opac_search_yes_active"]."
		";		
		if($this->info['gestion_multi_search']) $search_multi_checked_gestion= " checked='checked' ";
		else $search_multi_checked_gestion= "";
		$search_simple_checked_gestion=array();
		$search_simple_checked_gestion[$this->info['gestion_search']+0]= " checked='checked' ";
		$search_tpl_gestion="
			<input type='radio' ".(isset($search_simple_checked_gestion[0]) ? $search_simple_checked_gestion[0] : '')." name='gestion_search_simple' value='0' >".$msg["admin_authperso_gestion_search_no"]."
			<input type='radio' ".(isset($search_simple_checked_gestion[1]) ? $search_simple_checked_gestion[1] : '')." name='gestion_search_simple' value='1' >".$msg["admin_authperso_gestion_search_yes"]."
			<input type='radio' ".(isset($search_simple_checked_gestion[2]) ? $search_simple_checked_gestion[2] : '')." name='gestion_search_simple' value='2' >".$msg["admin_authperso_gestion_search_yes_active"]."
		";
		$fields_options="<select id='fields_options' name='fields_options'>";
		$fields_options.=$this->get_fields_options();
		$fields_options.="</select>";
		
		$fields_options_view="<select id='fields_options_view' name='fields'>";
		$fields_options_view.=$this->get_fields_options();
		$fields_options_view.="</select>";
		if($this->info['oeuvre_event']){
			$tpl=str_replace('!!oeuvre_event!!'," checked='checked' ",$tpl);
		}else{
			$tpl=str_replace('!!oeuvre_event!!',"",$tpl);
		}
		if($this->info['responsability_authperso']){
			$tpl=str_replace('!!responsability_authperso!!'," checked='checked' ",$tpl);
		}else{
			$tpl=str_replace('!!responsability_authperso!!',"",$tpl);
		}
		$tpl=str_replace('!!name!!',htmlentities($this->info['name'], ENT_QUOTES, $charset),$tpl);
		$tpl=str_replace('!!notice_onglet_list!!',$notice_onglet_list,$tpl);
		$tpl=str_replace('!!fields_options!!',$fields_options,$tpl);
		$tpl=str_replace('!!isbd_script!!',htmlentities($this->info['isbd_script'], ENT_QUOTES, $charset),$tpl);
		$tpl=str_replace('!!fields_options_view!!',$fields_options_view,$tpl);
		$tpl=str_replace('!!view_script!!',htmlentities($this->info['view_script'], ENT_QUOTES, $charset),$tpl);
		$tpl=str_replace('!!search_simple!!',$search_tpl,$tpl);
		$tpl=str_replace('!!search_multi!!',$search_multi_checked,$tpl);
		$tpl=str_replace('!!search_simple_gestion!!',$search_tpl_gestion,$tpl);
		$tpl=str_replace('!!search_multi_gestion!!',$search_multi_checked_gestion,$tpl);
		$tpl=str_replace('!!comment!!',htmlentities($this->info['comment'], ENT_QUOTES, $charset),$tpl);
		$tpl=str_replace('!!id_authperso!!',$this->id,$tpl);
		 
		return $tpl;
	}

	public function set_properties_from_form() {
		global $name, $notice_onglet, $isbd_script, $view_script, $comment;
		global $search_simple, $search_multi, $gestion_search_simple, $gestion_search_multi;
		global $oeuvre_event, $responsability_authperso;
		
		$this->info['name']= stripslashes($name);
		$this->info['onglet_num']= intval($notice_onglet);
		$this->info['isbd_script']= stripslashes($isbd_script);
		$this->info['view_script']= stripslashes($view_script);
		$this->info['opac_search']= intval($search_simple);
		$this->info['opac_multi_search']= intval($search_multi);
		$this->info['gestion_search']= intval($gestion_search_simple);
		$this->info['gestion_multi_search']= intval($gestion_search_multi);
		$this->info['oeuvre_event']= intval($oeuvre_event);
		$this->info['comment']= stripslashes($comment);
		$this->info['responsability_authperso']= intval($responsability_authperso);
		$this->info['onglet_name']="";
	}
	
	public function save() {
		global $base_path;
		
		$fields="
			authperso_name='".addslashes($this->info['name'])."',
			authperso_notice_onglet_num='".addslashes($this->info['onglet_num'])."',
			authperso_isbd_script='".addslashes($this->info['isbd_script'])."' ,
			authperso_view_script='".addslashes($this->info['view_script'])."' ,
			authperso_opac_search='".$this->info['opac_search']."',
			authperso_opac_multi_search='".$this->info['opac_multi_search']."',
			authperso_gestion_search='".$this->info['gestion_search']."',
			authperso_gestion_multi_search='".$this->info['gestion_multi_search']."',
			authperso_oeuvre_event='".$this->info['oeuvre_event']."',
			authperso_comment='".addslashes($this->info['comment'])."',
			authperso_responsability_authperso='".$this->info['responsability_authperso']."'
		";		
		if(!$this->id){ // Ajout
			$req="INSERT INTO authperso SET $fields ";	
			pmb_mysql_query($req);
			$this->id = pmb_mysql_insert_id();
		} else {
			$req="UPDATE authperso SET $fields where id_authperso=".$this->id;	
			pmb_mysql_query($req);
			$isbd_template_path = $base_path.'/temp/'.LOCATION.'_authperso_isbd_'.$this->id;
			if(file_exists($isbd_template_path)){
				unlink($isbd_template_path);
			}
			$view_template_path = $base_path.'/temp/'.LOCATION.'_authperso_view_'.$this->id;
			if(file_exists($view_template_path)){
				unlink($view_template_path);
			}
		}	
		$this->fetch_data();
	}	
	
	public static function delete($id) {
		global $option_navigation,$option_visibilite;
		
		$id = intval($id);
		if($id) {
			$p_perso=new custom_parametres_perso("authperso","authperso",$id,"./admin.php?categ=authorities&sub=authperso&auth_action=edition&id_authperso=".$id,$option_navigation,$option_visibilite);
			if(count($p_perso->t_fields) == 0) {
				$p_perso->delete_all();
				
				$query = "delete from authperso_authorities where  authperso_authority_authperso_num = '".$id."' ";
				pmb_mysql_query($query);
				
				$req="DELETE from authperso WHERE id_authperso=".$id;
				pmb_mysql_query($req);
				return true;
			} else {
				pmb_error::get_instance(static::class)->add_message('', 'authperso_used_custom_fields');
				return false;
			}
		}
		return true;
	}	

	public function fields_edition() {
		global $msg;
		
		$option_visibilite = array();
		$option_visibilite["multiple"] = "block";
		$option_visibilite["obligatoire"] = "block";
		$option_visibilite["search"] = "block";
		$option_visibilite["export"] = "none";
		$option_visibilite["filters"]="none";
		$option_visibilite["exclusion"] = "none";
		$option_visibilite["opac_sort"] = "block";
		
		$option_navigation = array();
		$option_navigation['msg_title'] = $msg["admin_menu_docs_perso_authperso"]." : ".$this->info['name'];
		$option_navigation['url_return_list'] = "./admin.php?categ=authorities&sub=authperso&auth_action=";
		$option_navigation['msg_return_list'] = $msg["admin_authperso_return_list"];

		$option_navigation['url_update_global_index'] = "./admin.php?categ=authorities&sub=authperso&auth_action=update_global_index&id_authperso=".$this->id;
		$option_navigation['msg_update_global_index'] = $msg["admin_authperso_update_global_index"];
		
		$p_perso = new custom_parametres_perso("authperso", "authperso", $this->id, "./admin.php?categ=authorities&sub=authperso&auth_action=edition&id_authperso=".$this->id, $option_navigation, $option_visibilite);
		
		$p_perso->proceed();
	}
	
	public function get_fields_options(){
		$p_perso=new custom_parametres_perso("authperso","authperso",$this->id);
				
		return $p_perso->get_selector_options_1()."<option value='{% for index_concept in index_concepts %}
   {{index_concept.label}}
{% endfor %}'>index_concepts</option>";
	}		
} //authperso class end