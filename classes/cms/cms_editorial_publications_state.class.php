<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_editorial_publications_state.class.php,v 1.1 2021/03/03 07:57:00 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once($include_path."/templates/cms/cms_editorial_publications_states.tpl.php");

class cms_editorial_publications_state {
	public $id;
	public $label='';
	public $opac_show=0;
	public $auth_opac_show=0;
	public $class_html='';
	
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->fetch_data();
	}

	protected function fetch_data(){
		if(!$this->id) return;
		
		/* récupération des informations du statut */
		
		$requete = 'SELECT * FROM cms_editorial_publications_states WHERE id_publication_state='.$this->id;
		$result = @pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		
		$data = pmb_mysql_fetch_object($result);
		$this->label = $data->editorial_publication_state_label;
		$this->opac_show = $data->editorial_publication_state_opac_show;
		$this->auth_opac_show = $data->editorial_publication_state_auth_opac_show;
		$this->class_html = $data->editorial_publication_state_class_html;
	}
	
	public function get_form(){
		global $msg,$charset;
		global $cms_editorial_publication_state_content_form;
		
		$content_form = $cms_editorial_publication_state_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('cms_editorial_publication_state_form');
		if(!$this->id){
			$interface_form->set_label($msg['editorial_content_publication_state_add']);
		}else{
			$interface_form->set_label($msg['editorial_content_publication_state_edit']);
		}
		$content_form = str_replace("!!label!!",htmlentities($this->label,ENT_QUOTES,$charset),$content_form);
		$content_form = str_replace("!!visible!!",($this->opac_show ? "checked='checked'": ""),$content_form);
		$content_form = str_replace("!!visible_abo!!",($this->auth_opac_show ? "checked='checked'": ""),$content_form);
		
		$couleur = array();
		for ($i=1;$i<=20; $i++) {
			if ($this->class_html == "statutnot".$i) $checked = "checked";
			else $checked = "";
			$couleur[$i]="<span for='statutnot".$i."' class='statutnot".$i."' style='margin: 7px;'><img src='".get_url_icon('spacer.gif')."' width='10' height='10' />
					<input id='statutnot".$i."' type=radio name='cms_editorial_publication_state_class_html' value='statutnot".$i."' $checked class='checkbox' /></span>";
			if ($i==10) $couleur[10].="<br />";
			elseif ($i!=20) $couleur[$i].="<b>|</b>";
		}
		$couleurs=implode("",$couleur);
		$content_form = str_replace('!!class_html!!', $couleurs, $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->label." ?")
		->set_content_form($content_form)
		->set_table_name('cms_editorial_publications_states')
		->set_field_focus('cms_editorial_publication_state_label');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $cms_editorial_publication_state_label,$cms_editorial_publication_state_visible,$cms_editorial_publication_state_visible_abo;
		global $cms_editorial_publication_state_class_html;
		
		$this->label = stripslashes($cms_editorial_publication_state_label);
		$this->opac_show = ($cms_editorial_publication_state_visible ? 1 : 0);
		$this->auth_opac_show = ($cms_editorial_publication_state_visible_abo ? 1 : 0);
		$this->class_html = stripslashes($cms_editorial_publication_state_class_html);
	}
	
	public function save(){
		
		if($this->id){
			$query = "update cms_editorial_publications_states set ";
			$clause = "where id_publication_state = ".$this->id;
		}else{
			$query = "insert into cms_editorial_publications_states set ";
			$clause = "";
		}
		$query.= "
			editorial_publication_state_label = '".addslashes($this->label)."',
			editorial_publication_state_opac_show = ".$this->opac_show.",
			editorial_publication_state_auth_opac_show = ".$this->auth_opac_show.",
			editorial_publication_state_class_html = '".addslashes($this->class_html)."'";
		$query.= " ".$clause;
		pmb_mysql_query($query);
	}
	
	public static function delete($id){
		global $msg;
		$id =intval($id);
		if($id){
			//on regarde si le statut est utilisé dans les rubriques
			$query = "select id_section from cms_sections where section_publication_state = ".$id;
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)){
				$error = $msg['publication_state_used_in_section'];
			}else{
				//on regarde si le statut est utilisé dans les articles
				$query = "select id_article from cms_articles where article_publication_state = ".$id;
				$result = pmb_mysql_query($query);
				if(pmb_mysql_num_rows($result)){
					$error = $msg['publication_state_used_in_article'];
				}
			}
		}
		if($error){
			pmb_error::get_instance(static::class)->add_message('', $msg['cant_delete'].". ".$error);
			return false;
		}else{
			$query = "delete from cms_editorial_publications_states where id_publication_state = ".$id;
			pmb_mysql_query($query);
			return true;
		}
	}
}