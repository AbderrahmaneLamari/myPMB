<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authorities_statut.class.php,v 1.1 2021/01/21 07:42:27 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class authorities_statut {
	
	/* ---------------------------------------------------------------
	 propriétés de la classe
	 --------------------------------------------------------------- */
	
	public $id=0;
	public $label='';
	public $class_html='statutnot1';
	public $available_for=array();
	
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->getData();
	}
	
	/* ---------------------------------------------------------------
	 getData() : récupération des propriétés
	 --------------------------------------------------------------- */
	public function getData() {
		if(!$this->id) return;
		
		$requete = 'SELECT * FROM authorities_statuts WHERE id_authorities_statut='.$this->id;
		$result = @pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		
		$data = pmb_mysql_fetch_object($result);
		$this->label = $data->authorities_statut_label;
		$this->class_html = $data->authorities_statut_class_html;
		$this->available_for = unserialize($data->authorities_statut_available_for);
	}
	
	public function get_form() {
		global $msg,$charset;
		global $admin_authorities_statut_content_form;
		
		$content_form = $admin_authorities_statut_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('statutform');
		if($this->id){
			$interface_form->set_label($msg['118']);
		}else{
			$interface_form->set_label($msg['115']);
		}
		$couleur=array();
		for ($i=1;$i<=20; $i++) {
			if ($this->class_html == "statutnot".$i){
				$checked = "checked";
			}
			else {
				$checked = "";
			}
			$couleur[$i]="<span for='statutnot".$i."' class='statutnot".$i."' style='margin: 7px;'><img src='".get_url_icon('spacer.gif')."' width='10' height='10' />
					<input id='statutnot".$i."' type=radio name='form_class_html' value='statutnot".$i."' $checked class='checkbox' /></span>";
			if ($i==10) $couleur[10].="<br />";
			elseif ($i!=20) $couleur[$i].="<b>|</b>";
		}
		
		$couleurs=implode("",$couleur);
		$content_form = str_replace("!!class_html!!", $couleurs, $content_form);
		
		$content_form = str_replace("!!gestion_libelle!!", htmlentities($this->label,ENT_QUOTES,$charset),$content_form);
		
		$authorities = static::get_authorities_list();
		$i=0;
		$authorities_list = '';
		foreach($authorities as $value => $name){
			if($i!= 0 && $i % 5 == 0){
				$authorities_list.= "<br>";
			}
			$authorities_list.= "<span style='margin-right:5px;'><input".($this->id==1 ? " disabled='disabled'" : "")." type='checkbox'".( (in_array($value,$this->available_for) || $this->id == 1) ? " checked='checked'" : "")." name='form_available_for[]' value='".$value."'/> $name</span>";
			$i++;
		}
		
		$content_form = str_replace("!!list_authorities!!", $authorities_list, $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->label." ?")
		->set_content_form($content_form)
		->set_table_name('authorities_statuts')
		->set_field_focus('form_gestion_libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_gestion_libelle,$form_class_html, $form_available_for;
		
		$this->label = stripslashes($form_gestion_libelle);
		$this->class_html = stripslashes($form_class_html);
		
		if($this->id == 1) {
			$form_available_for = array_keys(static::get_authorities_list());
		}
		$this->available_for = $form_available_for;
	}
	
	public function save() {
		if($this->label){
			if($this->id){
				$query = " update authorities_statuts set ";
				$where = "where id_authorities_statut = ".$this->id;
			}else{
				$query = " insert into authorities_statuts set ";
				$where = "";
			}
			$query.="
				authorities_statut_label = '".addslashes($this->label)."',
				authorities_statut_class_html = '".addslashes($this->class_html)."',
				authorities_statut_available_for = '".addslashes(serialize($this->available_for))."' ";
			$result = pmb_mysql_query($query.$where);
			if(!$result){
				return false;
			}
		}
		return true;
	}
	
	public static function check_data_from_form() {
		global $form_gestion_libelle;
		
		if(empty($form_gestion_libelle)) {
			return false;
		}
		return true;
	}
	
	public static function delete($id) {
		global $msg;
		
		$id=intval($id);
		if($id==1) return true;
		
		$used = static::check_used($id);
		if(!count($used)){
			$query = "delete from authorities_statuts where id_authorities_statut = ".$id;
			pmb_mysql_query($query);
			return true;
		} else {
			$msg_suppr_err= $msg['authorities_statut_used'].'<br/>';
			foreach($used as $auth){
				$msg_suppr_err.=$auth['link'].'<br/>';
			}
			pmb_error::get_instance(static::class)->add_message('authorities_statut_used', $msg_suppr_err);
			return false;
		}
	}
	
	public static function check_used($id){
		global $msg;
		global $base_path;
		
		$id=intval($id);
		$used = array();
		$query = "select type_object, count(*) as used FROM authorities where num_statut = ".$id." group by type_object order by used desc";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			while($row = pmb_mysql_fetch_object($result)){
				if($row->used != 0){
					switch ($row->type_object){
						case AUT_TABLE_AUTHORS:
							$categ='auteurs';
							$name= $msg['133'];
							break;
						case AUT_TABLE_CATEG:
							$categ='categories';
							$name= $msg['134'];
							break;
						case AUT_TABLE_PUBLISHERS:
							$categ='editeurs';
							$name= $msg['135'];
							break;
						case AUT_TABLE_COLLECTIONS:
							$categ='collections';
							$name= $msg['136'];
							break;
						case AUT_TABLE_SUB_COLLECTIONS:
							$categ='souscollections';
							$name= $msg['137'];
							break;
						case AUT_TABLE_SERIES:
							$categ='series';
							$name= $msg['333'];
							break;
						case AUT_TABLE_INDEXINT:
							$categ='indexint';
							$name= $msg['indexint_menu'];
							break;
						case AUT_TABLE_TITRES_UNIFORMES:
							$categ='titres_uniformes';
							$name= $msg['aut_menu_titre_uniforme'];
							break;
						case AUT_TABLE_CONCEPT:
							$categ='concepts';
							$name= $msg['ontology_skos_menu'];
							break;
						default://Authperso
							$categ='authperso&id_authperso='.($row->type_object-1000);
							$name= $msg['authperso_multi_search_title'];
							
							break;
					}
					$used[]=array(
							'type'=>$row->type_object,
							'used'=>$row->used,
							'categ'=>$categ,
							'msg'=>$name,
							'link'=>'<a href="'.$base_path.'/autorites.php?categ='.$categ.'&authority_statut='.$id.'">'.$name.'( '.$row->used.' )</a>',
					);
				}
			}
		}
		return $used;
	}
	
	private static function get_authorities_list(){
		global $msg,$thesaurus_concepts_active,$pmb_use_uniform_title;
		$authorities = array(
				AUT_TABLE_AUTHORS => $msg['133'],
				AUT_TABLE_CATEG => $msg['134'],
				AUT_TABLE_PUBLISHERS => $msg['135'],
				AUT_TABLE_COLLECTIONS => $msg['136'],
				AUT_TABLE_SUB_COLLECTIONS => $msg['137'],
				AUT_TABLE_SERIES => $msg['333'] ,
				AUT_TABLE_INDEXINT => $msg['indexint_menu']
		);
		if($pmb_use_uniform_title){
			$authorities[AUT_TABLE_TITRES_UNIFORMES] = $msg['aut_menu_titre_uniforme'];
		}
		if($thesaurus_concepts_active){
			$authorities[AUT_TABLE_CONCEPT] = $msg['ontology_skos_menu'];
		}
		
		
		$authpersos= authpersos::get_instance();
		$info_authpersos=$authpersos->get_data();
		foreach($info_authpersos as $authperso){
			$authorities[($authperso['id']+1000)] = $authperso['name'];
		}
		return $authorities;
	}
} /* fin de définition de la classe */


