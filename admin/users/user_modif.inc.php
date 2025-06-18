<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: user_modif.inc.php,v 1.70 2019/12/30 11:10:35 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path.'/user.class.php');
$id = intval($id);
$requete = "SELECT username FROM users WHERE userid='$id' LIMIT 1 ";
$res = pmb_mysql_query($requete);
if (pmb_mysql_num_rows($res)) {
	$usr=pmb_mysql_fetch_object($res);
    
    $param_default = user::get_form($id, 'userform');
    
    echo window_title($msg[1003].$msg[18].$msg[1003].$msg[86].$msg[1003].$usr->username.$msg[1001]);
    
    $user = new user($id);
    print $user->get_user_form($param_default);
    echo form_focus('userform', 'form_nom');
} else{
    echo sprintf($msg['unknown_user_id'], $id);
}
