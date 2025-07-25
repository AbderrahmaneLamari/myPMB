<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_logo.class.php,v 1.28.4.1 2023/09/14 08:29:35 jparis Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once($include_path."/templates/cms/cms_logo.tpl.php");

class cms_logo {
	public $id;		// identifiant de l'objet
	public $type;	// type d'objet
	public $data;	// donn�es binaire du logo
	public $img_infos = array(); //infos image (dimensions,mimetype,...)
	
	public function __construct($id="",$type="section"){
		$this->id= $id*1;
		$this->type = $type;
		if($this->id){
			$this->fetch_data_cache();
		}
	}

	protected function fetch_data_cache(){
		if($tmp=cms_cache::get_at_cms_cache($this)){
			$this->restore($tmp);
		}else{
			$this->fetch_data();
			cms_cache::set_at_cms_cache($this);
		}
	}

	protected function restore($cms_object){
		foreach(get_object_vars($cms_object) as $propertieName=>$propertieValue){
			$this->{$propertieName}=$propertieValue;
		}
	}

	protected function fetch_data(){
		$table=$this->get_sql_table();
		if(!$table) return false;
		$rqt = "select ".$this->type."_logo from ".$table." where id_".$this->type." = '".$this->id."'";
		$res = pmb_mysql_query($rqt);
		if(pmb_mysql_num_rows($res)){
			$this->data = pmb_mysql_result($res,0,0);
			if($this->data) {
				$this->get_img_infos();
			}
		}
	}

	protected function get_img_infos() {
		$img_infos = getimagesizefromstring($this->data);
		if($img_infos) {
			$this->img_infos['width'] = $img_infos[0];
			$this->img_infos['height'] = $img_infos[1];
			$this->img_infos['mimetype'] = $img_infos['mime'];
				
			$this->img_infos['render_fct']= false;
			$this->img_infos['render_params'] = array();
			
			switch($this->img_infos['mimetype']) {
				case 'image/png' :
					$this->img_infos['type'] = 'png';
					$this->img_infos['render_fct'] = 'imagepng';
					if(defined('PNG_ALL_FILTERS')) {
						$this->img_infos['render_params'] = array(9, PNG_ALL_FILTERS);
					} else {
						$this->img_infos['render_params'] = array(9);
					}
					break;
				case 'image/jpeg' :
					$this->img_infos['type'] = 'jpeg';
					$this->img_infos['render_fct'] = 'imagejpeg';
					if (strlen($this->data) < 102400) {
						// Si image < 100ko, on ne r�duit pas la qualit�, sinon on laisse le r�glage par d�faut de imagejpeg
						$this->img_infos['render_params'] = array(100);
					}
					break;
				case 'image/gif' :
					$this->img_infos['type'] = 'gif';
					$this->img_infos['render_fct'] = 'imagegif';
					break;					
			}
		}		
	}
	
	public function get_form(){
		global $cms_logo_form_tpl;
		global $cms_logo_form_exist_obj_tpl;
		global $cms_logo_form_new_obj_tpl;

		$form = $cms_logo_form_tpl;
		if($this->id){
			$form = str_replace("!!field!!",$cms_logo_form_exist_obj_tpl,$form);
		}else{
			$form = str_replace("!!field!!",$cms_logo_form_new_obj_tpl,$form);
			$form = str_replace("!!js!!","",$form);
		}
		$form = str_replace("!!id!!",$this->id,$form);
		$form = str_replace("!!type!!",$this->type,$form);
		return $form;
	}

	public function get_field(){
		global $cms_logo_field_tpl;
		global $cms_logo_delete;

		$field = str_replace("!!type!!",$this->type,$cms_logo_field_tpl);

		//si $_FILES n'est pas vide, on a du matos...
		if($cms_logo_delete){
			$result = $this->delete();
			if($result === true){
				$js = "
					var div_vign = window.parent.document.getElementById('cms_logo_vign');
					var old_img = window.parent.document.getElementById('cms_logo_vign_img');
					div_vign.removeChild(old_img);
					var date = new Date();
					var img = document.createElement('img');
					img.setAttribute('id','cms_logo_vign_img');
					img.setAttribute('class','cms_logo_vign');
					img.setAttribute('src','./cms_vign.php?type=".$this->type."&id=".$this->id."&mode=vign'+'&'+ date.getTime());
					div_vign.appendChild(img);";
			}else{
				$js = "
				alert(\"".$result."\");";
			}
		}else{
			if(count($_FILES)){
				$result = $this->save();
				if($result === true){
					$js = "
			var div_vign = window.parent.document.getElementById('cms_logo_vign');
			var old_img = window.parent.document.getElementById('cms_logo_vign_img');
			div_vign.removeChild(old_img);
			var date = new Date();
			var img = document.createElement('img');
			img.setAttribute('id','cms_logo_vign_img');
			img.setAttribute('class','cms_logo_vign');
			img.setAttribute('src','./cms_vign.php?type=".$this->type."&id=".$this->id."&mode=vign'+'&'+date.getTime());
			div_vign.appendChild(img);";
				}else{
					$js = "
						alert(\"".$result."\");";
				}
			}else{
				$js = "	
			var div_vign = window.parent.document.getElementById('cms_logo_vign');
			var old_img = window.parent.document.getElementById('cms_logo_vign_img');
			div_vign.removeChild(old_img);
			var date = new Date();
			var img = document.createElement('img');
			img.setAttribute('id','cms_logo_vign_img');
			img.setAttribute('class','cms_logo_vign');
			img.setAttribute('src','./cms_vign.php?type=".$this->type."&id=".$this->id."&mode=vign'+'&'+date.getTime());
			div_vign.appendChild(img);";
			}
		}
		$field = str_replace("!!js!!",$js,$field);
		return $field;
	}
	
	public function clean_cache($id = 0,$opac=false){
		global $base_path;
		if(!$id) {
			$id = $this->id;
		}
		$path = $base_path."/opac_css/temp/cms_vign/";
		$file_extensions = array("jpg", "jpeg", "png", "gif");
		$dd = opendir($path);
		while($database = readdir($dd)){
			if($database != "." && $database!= ".." && $database != "CVS"){
				$dh = opendir($path . $database);
				while($mode = readdir($dh)){
					if($mode != "." && $mode!= ".." && $mode != "CVS"){
						$mh = opendir($path.$database."/".$mode);
						while($file = readdir($mh)){
							if($file != "." && $file!= ".." && $file != "CVS") {
								$parsed_file = explode(".", $file);
								if(file_exists($path . $database."/".$mode."/".$file) && $parsed_file[0] == $this->type.$id && in_array($parsed_file[1], $file_extensions)) {
								    unlink($path . $database."/".$mode."/".$file);
								}
							}
						}
						closedir($mh);
					}
				}
			}
			closedir($dh);
		}
		closedir($dd);
	}

	public function delete(){
		global $msg;
		
		$table=$this->get_sql_table();
		if(!$table) return $msg['cms_editorial_form_logo_cant_delete'];
		$rqt = "update ".$table." set ".$this->type."_logo='' where id_".$this->type." = '".$this->id."'";
		$res= pmb_mysql_query($rqt);
		if($res){
			$this->clean_cache($this->id);
			return true;
		}else{
			return $msg['cms_editorial_form_logo_cant_delete'];
		}
	}

	public function save(){
		global $msg;
		//on commence par regarder ce qu'on nous a donn�...
		$mimetype = $_FILES['cms_logo_file']['type'];
		//on ne veut que les images
		if(substr($mimetype,0,5) != "image"){
			return $msg['cms_editorial_form_logo_unsupported_file'];
		} else {
			$data = file_get_contents($_FILES['cms_logo_file']['tmp_name']);
		}
		$table=$this->get_sql_table();
		if(!$table) return $msg['cms_editorial_form_logo_cant_save'];
		$rqt = "update ".$table." set ".$this->type."_logo=\"".addslashes($data)."\" where id_".$this->type." = '".$this->id."'";
		$res= pmb_mysql_query($rqt);
		if($res){
			$this->clean_cache($this->id);
			return true;
		}else{
			return $msg['cms_editorial_form_logo_cant_save'];
		}
	}
	
	public function save_from_content($content){
		$table=$this->get_sql_table();
		if(!$table) return false;
		$rqt = "update ".$table." set ".$this->type."_logo=\"".addslashes($content)."\" where id_".$this->type." = '".$this->id."'";
		$res= pmb_mysql_query($rqt);
		if($res){
			$this->clean_cache($this->id);
			return true;
		}else{
			return false;
		}
	}

	protected function get_sql_table(){
		switch ($this->type){
			case "section" :
				$table = "cms_sections";
				break;
			case "article" :
				$table = "cms_articles";
				break;
			default :
				$table ="";
				break;
		}
		return $table;
	}

	protected function convert_to_png($picture){
		$data = file_get_contents($picture);
		$src_img = imagecreatefromstring($data);
		$src_x = imagesx($src_img);
		$src_y = imagesy($src_img);
		$dst_img=imagecreatetruecolor($src_x,$src_y);
		ImageSaveAlpha($dst_img, true);
		ImageAlphaBlending($dst_img, false);
		imagefilledrectangle($dst_img,0,0,$src_x,$src_y,imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$src_x,$src_y,$src_x,$src_y);
		$tmp_path = realpath("./temp");
		imagepng($dst_img,$tmp_path."/tmp_cms_logo");
		$data = file_get_contents($picture);
		unlink($tmp_path."/tmp_cms_logo");
		return $data;
	}

	public function show_picture($mode=''){

  		if(strpos($mode,'custom_') !== false){
	  		$elems = explode('_',$mode);
	  		$size = $elems[1]*1;
	  		if($size>0){
	  			$this->resize($size,$size);
	  		}else{
	  			$this->resize(500,500);
	  		}
	  	}else{
			switch($mode){
				case 'small_vign' :
					$this->resize(16,16);
					break;
				case 'vign' :
					$this->resize(100,100);
					break;
				case 'small' :
					$this->resize(140,140);
					break;
				case 'medium' :
					$this->resize(300,300);
					break;
				case 'big' :
					$this->resize(600,600);
					break;
				case 'large' :
				default :
					$dst_img=$this->resize(0,0);
					if($this->img_infos['type'] == 'png') {
						//Pour les images non redimensionn�es
						imageSaveAlpha($dst_img, true);
					}
					break;
	  		}
  		}
	}
	
	private function init_opac_cache_path($mode){
		global $base_path, $database;
		if(file_exists($base_path."/opac_css")){
		    if(!file_exists($base_path."/opac_css/temp/cms_vign")){
		        mkdir($base_path."/opac_css/temp/cms_vign");
		    }
		    if(!file_exists($base_path."/opac_css/temp/cms_vign/".$database)){
		        mkdir($base_path."/opac_css/temp/cms_vign/".$database);
		    }
		    if(!file_exists($base_path."/opac_css/temp/cms_vign/".$database."/".$mode)){
		        mkdir($base_path."/opac_css/temp/cms_vign/".$database."/".$mode);
		    }
			return true;	
		}
		return false;
	}

	public function get_vign(){
	   $this->resize(100,100);
	}

	public function get_small_vign(){
	   $this->resize(16,16);
	}

	public function get_large(){
		$this->resize(0,0);
	}

	protected function resize($size_x=0,$size_y=0){
		if($this->data){
			if(!count($this->img_infos)) {
				$this->get_img_infos();
			}
			if(!$this->img_infos['render_fct']) {
				header('Content-Type: image/png');
				print file_get_contents(get_url_icon('vide.png'));
				return;
			}

			if(!$size_x && !$size_y){
				header('Content-Type: '.$this->img_infos['mimetype']);
				print $this->data;
				return;
			}
			
			$src_img = imagecreatefromstring($this->data);

			if(!$src_img) {
				header('Content-Type: image/png');
				print file_get_contents(get_url_icon('vide.png'));
				return;
			}
			
			$maxX=$size_x;
			$maxY=$size_y;
			
			$rs=$maxX/$maxY;
			$taillex=$this->img_infos['width'];
			$tailley=$this->img_infos['height'];
			if (!$taillex || !$tailley) {
				header('Content-Type: image/png');
				print file_get_contents(get_url_icon('vide.png'));
				return;
			}
			if (($taillex>$maxX)||($tailley>$maxY)) {
				$r=$taillex/$tailley;
				if (($r<1)&&($rs<1)) {
					//Si x plus petit que y et taille finale portrait
					//Si le format final est plus large en proportion
					if ($rs>$r) {
						$new_h=$maxY;
						$new_w=$new_h*$r;
					} else {
						$new_w=$maxX;
						$new_h=$new_w/$r;
					}
				} else if (($r<1)&&($rs>=1)){
					//Si x plus petit que y et taille finale paysage
					$new_h=$maxY;
					$new_w=$new_h*$r;
				} else if (($r>1)&&($rs<1)) {
					//Si x plus grand que y et taille finale portrait
					$new_w=$maxX;
					$new_h=$new_w/$r;
				} else {
					//Si x plus grand que y et taille finale paysage
					if ($rs<$r) {
						$new_w=$maxX;
						$new_h=$new_w/$r;
					} else {
						$new_h=$maxY;
						$new_w=$new_h*$r;
					}
				}
			} else {
				$new_h = $tailley ;
				$new_w = $taillex ;
			}
			
			$dst_img=imagecreatetruecolor($new_w,$new_h);
			if($this->img_infos['type'] == 'png') {
				imageSaveAlpha($dst_img, true);
				imageAlphaBlending($dst_img, false);
			}
			imagecopyresampled($dst_img,$src_img,0,0,0,0,$new_w,$new_h,$this->img_infos['width'],$this->img_infos['height']);
			if(function_exists($this->img_infos['render_fct'])) {
				$render_params = array_merge(array($dst_img,null),$this->img_infos['render_params']);
				header('Content-Type: '.$this->img_infos['mimetype']);
				call_user_func_array($this->img_infos['render_fct'], $render_params);
			} else {
				header('Content-Type: image/png');
				print file_get_contents(get_url_icon('vide.png'));
			}
			return;
		} else {
			header('Content-Type: image/png');
			print file_get_contents(get_url_icon('vide.png'));
			return;
		}
	}

	public function get_vign_url($mode=""){
		global $opac_url_base, $database;
		return $opac_url_base."cms_vign.php?type=".$this->type."&id=".$this->id."&database=".$database."&mode=".$mode;
	}

	public function format_datas(){
		return array(
		    'small_vign' => $this->data ? $this->get_vign_url("small_vign") : false,
		    'vign' => $this->data ? $this->get_vign_url("vign") : false,
		    'small' => $this->data ? $this->get_vign_url("small") : false,
		    'medium' => $this->data ? $this->get_vign_url("medium") : false,
		    'big' => $this->data ? $this->get_vign_url("big") : false,
		    'large' => $this->data ? $this->get_vign_url("large") : false,
		    'custom' => $this->data ? $this->get_vign_url("custom_") : false,
		    'exists' => $this->data ? true : false
		);
	}


	public static function get_format_data_structure(){
		global $msg;
		return array(
			array(
				'var' => "small_vign",
				'desc' => $msg['cms_module_common_datasource_desc_small_vign']
			),
			array(
				'var' => "vign",
				'desc' => $msg['cms_module_common_datasource_desc_vign']
			),
			array(
				'var' => "small",
				'desc' => $msg['cms_module_common_datasource_desc_small']
			),
			array(
				'var' => "medium",
				'desc' => $msg['cms_module_common_datasource_desc_medium']
			),
			array(
				'var' => "big",
				'desc' => $msg['cms_module_common_datasource_desc_big']
			),
			array(
				'var' => "large",
				'desc' => $msg['cms_module_common_datasource_desc_large']
			),
			array(
				'var' => "custom",
				'desc' => $msg['cms_module_common_datasource_desc_custom']
			),
			array(
				'var' => "exists",
				'desc' => $msg['cms_module_common_datasource_desc_logo_exists']
			)
		);
	}
	
	public function get_id(){
		return $this->id;
	}
	
	public function get_type(){
		return $this->type;
	}
}