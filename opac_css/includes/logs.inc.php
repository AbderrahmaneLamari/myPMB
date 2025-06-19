<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: logs.inc.php,v 1.9.4.1 2023/04/04 12:26:50 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $class_path, $pmb_logs_exclude_robots;
global $pmb_logs_activate;

require_once($base_path."/classes/record_log.class.php");
require_once($class_path."/cookies_consent.class.php");

if($pmb_logs_activate){

    // Pas de log sur les requetes autres que GET et POST
    if (! in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
        $pmb_logs_activate = 0;
    }

    // Exclusion des robots
	$tab_logs_exclude_robots = array();
	$tab_logs_exclude_robots = explode(",", $pmb_logs_exclude_robots);
	if ($tab_logs_exclude_robots[0]) {
		$robots = array('BOT','SPIDER','CRAWL','QWANTIFY','SLURP');
		foreach ($robots as $robot) {
			if (preg_match('/'.$robot.'/i',$_SERVER['HTTP_USER_AGENT'])){
				$pmb_logs_activate = 0;
			}
		}
	}

    // Exclusion d'adresses IP
	if (count($tab_logs_exclude_robots) > 1) {
		$ip_adress = array();
		for($i=1;$i<count($tab_logs_exclude_robots);$i++) {
			$ip_adress[] = $tab_logs_exclude_robots[$i];
		}
		if (in_array($_SERVER['REMOTE_ADDR'], $ip_adress) || (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $ip_adress))) {
		    $pmb_logs_activate = 0;
		}
	}
	//Opposition à l'utilisation des cookies, aucun enregistrement de logs
	if (cookies_consent::is_opposed_pmb_logs_service()) {
		$pmb_logs_activate = 0;
	}
	global $log;
	$log = new record_log();	
}
