<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: netbase_records.class.php,v 1.2.2.1 2021/12/13 15:31:05 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/thumbnail.class.php");
require_once($class_path."/explnum.class.php");

class netbase_records {
	
	protected static $cleaned_records = array();
	
	protected static $cleaned_authorities = array();
	
	public function __construct() {
		
	}
	
	public static function clean_thumbnail() {
		global $opac_url_base;
		
		if(thumbnail::is_valid_folder('record')) {
			$query = "select notice_id, thumbnail_url from notices where thumbnail_url like 'data:image%'";
			$result = pmb_mysql_query($query);
			if (pmb_mysql_num_rows($result)) {
				while($row = pmb_mysql_fetch_object($result)) {
					$created = thumbnail::create_from_base64($row->notice_id, 'records', $row->thumbnail_url);
					if($created) {
						$thumbnail_url = $opac_url_base."getimage.php?noticecode=&vigurl=";
						$thumbnail_url .= "&notice_id=".$row->notice_id;
						$query = "update notices set thumbnail_url = '".addslashes($thumbnail_url)."', update_date=update_date where notice_id = ".$row->notice_id;
						pmb_mysql_query($query);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public static function clean_docnum_thumbnail() {
		if(thumbnail::is_valid_folder('docnum')) {
			$query = "SELECT explnum_id, explnum_vignette FROM explnum WHERE length(explnum_vignette) > 1000";
			$result = pmb_mysql_query($query);
			if (pmb_mysql_num_rows($result)) {
				while($row = pmb_mysql_fetch_assoc($result)) {
					explnum::upload_thumbnail($row["explnum_vignette"], $row["explnum_id"]);
				}
			}
			return true;
		}
		return false;
	}
	
	protected static function clean_field_data($field='') {
		global $charset;
		
		if(empty($field)) {
			return false;
		}
		$query = "SELECT notice_id, ".$field." FROM notices";
		$result = pmb_mysql_query($query);
		if($result && pmb_mysql_num_rows($result)) {
			while($row = pmb_mysql_fetch_object($result)) {
				$decoded_field = html_entity_decode($row->{$field}, ENT_QUOTES, $charset);
				if($row->{$field} != $decoded_field) {
					$query = "UPDATE notices SET ".$field." = '".addslashes($decoded_field)."', update_date=update_date WHERE notice_id =".$row->notice_id;
					pmb_mysql_query($query);
					if(!in_array($row->notice_id, static::$cleaned_records)) {
						static::$cleaned_records[] = $row->notice_id;
					}
				}
			}
		}
	}
	
	public static function clean_data() {
		//Nettoyons les résumés
		static::clean_field_data('n_resume');
		//Nettoyons les notes de contenu
		static::clean_field_data('n_contenu');
		//Nettoyons les notes générales
		static::clean_field_data('n_gen');
		return true;
	}
	
	public static function get_cleaned_records() {
		return static::$cleaned_records;
	}
	
	public static function get_cleaned_authorities() {
		return static::$cleaned_authorities;
	}
} // fin de déclaration de la classe netbase_records
