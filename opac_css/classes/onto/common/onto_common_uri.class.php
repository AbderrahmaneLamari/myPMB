<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_common_uri.class.php,v 1.7 2021/12/28 13:30:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/**
 * class onto_common_uri
 * G�n�ration des URI. La classe s'appuie sur un num�ro auto en base de donn�es.
 * L'URI par d�faut est prefix+"#"+numero auto. Si pas de pr�fixe :
 * class_uri+"#"+numero auto.
 * (Le # est � confirmer)
 * 
 * L'URI est stoqu�e dans la table de donn�es associ�e au num�ro auto. Le num�ro
 * auto est utilis� dans les tables PMB.
 * 
 */
class onto_common_uri {

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/

	/**
	 * derni�re URI g�n�r�e
	 * @access private
	 */
	private static $last_uri;

	/**
	 * G�n�re une nouvelle URI. Cette m�thode est apell�e par save() de onto_handler
	 *
	 * @param string class_uri URI de la classe d'objets
	 * @param string uri_prefix Pr�fixe � employer pour l'URI. Si vide, on prend celui de la classe.
	 * @return void
	 * @static
	 * @access public
	 */
	public static function get_new_uri($class_uri, $uri_prefix="") {
		if($uri_prefix){
			$class_uri=$uri_prefix;
		}
		$last_uri="";
		$max=1;
		//On cherche le max des id + 1
		$query='SELECT MAX(uri_id)+1 FROM onto_uri';
		$result=pmb_mysql_query($query);
		
		if(pmb_mysql_num_rows($result)){
			$max=pmb_mysql_result($result,0,0);
		}
		if(!$max) $max =1;
		
		$query='SELECT 1 FROM onto_uri WHERE uri="'.addslashes($class_uri.$max).'"';
		$result=pmb_mysql_query($query);
		if(!pmb_mysql_error() && !pmb_mysql_num_rows($result)){
			$last_uri=$class_uri.$max;
		}else{
			do{
				$max++;
				$query='SELECT 1 FROM onto_uri WHERE uri="'.addslashes($class_uri.$max).'"';
				$result=pmb_mysql_query($query);
			}while (pmb_mysql_num_rows($result));
		}
		
		$last_uri=$class_uri.$max;
		$query='INSERT INTO onto_uri SET uri="'.addslashes($last_uri).'"';
		pmb_mysql_query($query);
		
		//On initialise last_uri.
		self::$last_uri=$last_uri;
		return self::$last_uri;
	} // end of member function get_new_uri

	/**
	 * G�n�re une URI temporaire (bas�e sur microtime ?)
	 * @param string class_uri URI de la classe d'objets
	 * 
	 * @return void
	 * @static
	 * @access public
	 */
	public static function get_temp_uri($class_uri=""){
		$temp_uri = $class_uri."_temp_".(microtime(true)*10000);
		self::set_new_uri($temp_uri);
		return $temp_uri;
	} // end of member function get_temp_uri

	/**
	 * 
	 *
	 * @param string uri V�rifie si une URI est temporaire.

	 * @return void
	 * @access public
	 */
	public static function is_temp_uri($uri) {
		if(preg_match("/\_temp\_/", $uri)){
			return true;
		}else{
			return false;
		}
	} // end of member function is_temp_uri

	/**
	 * 
	 *
	 * @return void
	 * @access public
	 */
	public static function get_last_uri() {
		return self::$last_uri;
	} // end of member function get_last_uri

	public static function get_name_from_uri($uri,$pmb_name){
		$tmp=array();
		$tmp=preg_split("/\/|\#/", $uri);
		return trim($pmb_name.'_'.strtolower(end($tmp)));
	}
	
	public static function set_new_uri($uri){
		$query = "select uri_id from onto_uri where uri ='".addslashes($uri)."'";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			//ca existe d�j� !
			return pmb_mysql_result($result,0,0);
		}
		$query = "insert into onto_uri set uri = '".addslashes($uri)."'";
		$result = pmb_mysql_query($query);
		return pmb_mysql_insert_id();
	}

	public static function get_uri($id_uri){
		$uri = '';
		$query = "select uri from onto_uri where uri_id ='".$id_uri."'";
		$result = pmb_mysql_query($query);
		if($result && pmb_mysql_num_rows($result)){
			$uri = pmb_mysql_result($result,0,0);
		}
		return $uri;
	} 
	
	public static function get_id($uri){
		$id = 0;
		$query = "select uri_id from onto_uri where uri = '".addslashes($uri)."'";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			$id = pmb_mysql_result($result,0,0);
		}
		return $id;		
	}
	
	public static function replace_temp_uri($temp_uri, $class_uri, $uri_prefix="") {
		if($uri_prefix){
			$class_uri=$uri_prefix;
		}
		$last_uri="";
		$max=1;
		//On cherche le max des id + 1
		$query='SELECT MAX(uri_id)+1 FROM onto_uri';
		$result=pmb_mysql_query($query);
		
		if(pmb_mysql_num_rows($result)){
			$max=pmb_mysql_result($result,0,0);
		}
		
		$query='SELECT 1 FROM onto_uri WHERE uri="'.addslashes($class_uri.$max).'"';
		$result=pmb_mysql_query($query);
		if (!pmb_mysql_error() && !pmb_mysql_num_rows($result)) {
			$last_uri=$class_uri.$max;
		} else {
			do{
				$max++;
				$query='SELECT 1 FROM onto_uri WHERE uri="'.addslashes($class_uri.$max).'"';
				$result=pmb_mysql_query($query);
			}while (pmb_mysql_num_rows($result));
		}
		
		$last_uri=$class_uri.$max;
		$query='update onto_uri SET uri="'.addslashes($last_uri).'" where uri="'.$temp_uri.'"';
		pmb_mysql_query($query);
		
		//On initialise last_uri.
		self::$last_uri=$last_uri;
		return self::$last_uri;	
	}
} // end of onto_common_uri
