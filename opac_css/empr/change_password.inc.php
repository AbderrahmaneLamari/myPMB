<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: change_password.inc.php,v 1.25 2020/11/20 15:31:56 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $password_key;
global $allow_pwd, $action, $msg, $id_empr, $old_password, $new_password, $confirm_new_password, $empr_login, $opac_websubscribe_password_regexp;

require_once "{$class_path}/password/password.class.php";

if (!$allow_pwd) die();

switch ($action) {
    case "save":
        $emprunteur = new emprunteur($id_empr);
        $password_match = false;
        
        $hash_format = password::get_hash_format($emprunteur->pwd);
        if( 'bcrypt' == $hash_format ) {
        	$password_match = password::verify_hash($old_password, $emprunteur->pwd);
        } elseif( $emprunteur->pwd == password::gen_previous_hash($old_password, $id_empr) ) {
        	$password_match = true;
        }
        
        $status_msg = '';
        $error_msg = '';
        switch(true) {
        	
        	case ( !($password_key || $password_match) ) :
        		$status_msg = $msg['empr_old_password_wrong'];
        		break;
        		
        	case ($new_password != $confirm_new_password) :
        		$status_msg = $msg['empr_password_does_not_match'];
        		break;
        		
        	default :
        		$check_password_rules = emprunteur::check_password_rules($id_empr, $new_password, [], $lang);
        		if( !$check_password_rules['result'] ) {
        			$status_msg = $msg['empr_password_bad_security'];
        			$error_msg = implode('<br />', $check_password_rules['error_msg']);
        			break;
        		}
        		emprunteur::hash_password($empr_login, $new_password);
        		$status_msg = $msg['empr_password_changed'];
        		break;
        }
        
        print "<div id='change-password'>
                   <div id='change-password-container'>
						<div id='change-password-status'>$status_msg</div>
						<br />
						<div id='change-password-error'>$error_msg</div>
                       	<br />
                   </div>
               </div>";
        break;
    case "get_form":
    default:
        print emprunteur_display::get_display_change_password($id_empr);
        break;
}