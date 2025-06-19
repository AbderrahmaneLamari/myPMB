<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmb.php,v 1.3.6.1 2023/03/02 13:25:55 qvarin Exp $

$base_path = ".";
require_once "{$base_path}/includes/init.inc.php";

// fichiers nécessaires au bon fonctionnement de l'environnement
require_once "{$base_path}/includes/common_includes.inc.php";

global $class_path, $from, $opac_empr_password_salt;
global $hash, $url, $id;

if ('' == $opac_empr_password_salt) {
    password::gen_salt_base();
}

if (! empty($hash) && ! empty($url) && ! empty($id)) {

    require_once "{$class_path}/campaigns/campaigns_controller.class.php";
    campaigns_controller::proceed($hash, $url, $id);
} elseif (! empty($hash) && ! empty($url)) {

    if (! isset($from)) {
        $from = '';
    }

    if ($hash == md5("{$opac_empr_password_salt}_{$url}_{$from}")) {
		//Enregistrement du log
		global $pmb_logs_activate;
		if ($pmb_logs_activate) {
			global $log;
			$log->add_log('num_session',session_id());
			$log->save();
		}
		header('Location: '.html_entity_decode($url));
	}
}
