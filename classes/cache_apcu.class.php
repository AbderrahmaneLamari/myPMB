<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cache_apcu.class.php,v 1.3 2021/04/15 13:55:00 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cache_apcu extends cache_factory {

	public function setInCache($key, $value) {
		global $CACHE_MAXTIME;
		
		return apcu_store($key, $value, $CACHE_MAXTIME);
	}

	public function getFromCache($key) {
		return apcu_fetch($key);
	}

	public function clearCache() {
	    return apcu_clear_cache();
	}
	
	public function deleteCache() {
		global $KEY_CACHE_FILE_XML;
		return apcu_delete(new APCUIterator('#^'.$KEY_CACHE_FILE_XML.'#'));
	}
}