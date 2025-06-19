<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: netbase_cache.class.php,v 1.3.2.1 2023/06/14 14:59:25 rtigero Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class netbase_cache {
	
	public function __construct() {
		
	}
	
	protected static function is_temporary_file($file) {
		if(substr($file, 0, 3) == "XML" && substr($file, strlen($file)-4, 4) == ".tmp") {
			return true;
		}
		if(substr($file, 0, 4) == "h2o_") {
			return true;
		}
		return false;
	}
	
	public static function clean_files($folder_path) {
		if(is_dir($folder_path)) {
			$dh = opendir($folder_path);
			while(($file = readdir($dh)) !== false){
				if(!is_dir($folder_path.'/'.$file) && $file != "." && $file != ".." && $file != "CVS"){
					if(static::is_temporary_file($file)) {
						unlink($folder_path.'/'.$file);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public static function clean_apcu() {
		//Vidons également le cache APCU s'il est activé
		$cache_php=cache_factory::getCache();
		if(is_object($cache_php) && get_class($cache_php) == 'cache_apcu') {
			return $cache_php->clearCache();
		}
		return false;
	}
	
	public static function clean_autoload_files() {
		// Suppression des fichiers d'autoload back office
		@unlink(__DIR__ . "/../../temp/classLoader_paths.php");
		@unlink(__DIR__ . "/../../temp/classLoader_duplicates.php");
		@unlink(__DIR__ . "/../../temp/classLoader.lock");
		
		// Suppression des fichiers d'autoload front office
		@unlink(__DIR__ . "/../../opac_css/temp/classLoader_paths.php");
		@unlink(__DIR__ . "/../../opac_css/temp/classLoader_duplicates.php");
		@unlink(__DIR__ . "/../../opac_css/temp/classLoader.lock");
		return true;
	}
} // fin de déclaration de la classe netbase
