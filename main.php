<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.php,v 1.45.2.4 2023/09/25 12:47:56 dbellamy Exp $

// définition du minimum nécéssaire
$base_path=".";
$base_auth = "";
$base_title = "\$msg[308]";
$base_noheader=1;
$base_nocheck=1;

use Pmb\Common\Helper\MySQL;

require_once "$base_path/includes/init.inc.php";

//Est-on déjà authentifié ?
if (!checkUser('PhpMyBibli')) {

    $valid_user = 0;
    /************** Authentification externe  *******************/
    $ext_auth_hook = 1;
    $external_admin_auth_file_exists = file_exists( "$include_path/external_admin_auth.inc.php") ;
    if( $external_admin_auth_file_exists ) {
        require "$include_path/external_admin_auth.inc.php";
    }

    /************** Authentification classique *******************/
    if($valid_user !=1 ) {
    	//Vérification que l'utilisateur existe dans PMB
    	$query = "SELECT userid,username FROM users WHERE username='$user'";
        $result = pmb_mysql_query($query);
    	if (pmb_mysql_num_rows($result)) {
    		//Récupération du mot de passe
    		$dbuser=pmb_mysql_fetch_object($result);

    		/************** Authentification externe  (Vérification mot de passe hors admin uniquement) *******************/
    		if ( $external_admin_auth_file_exists && ($ext_auth_hook !=0) && ($dbuser->userid !=1 ) ) {
    		    require "$include_path/external_admin_auth.inc.php";
    		} else {
    			// on checke si l'utilisateur existe et si le mot de passe est OK


                //$query = "SELECT count(1) FROM users WHERE username='$user' AND pwd=password('$password') ";
                $query = "SELECT count(1) FROM users WHERE username='$user' AND pwd='" . MySQL::password($password) . "'";
                $result = pmb_mysql_query($query);
    			$valid_user = pmb_mysql_result($result, 0, 0);
    		}
    	}
    }
} else {
	$valid_user=2;
}

if(!$valid_user) {
	header("Location: index.php?login_error=1");
} else {
	if ($valid_user==1)
		startSession('PhpMyBibli', $user, $database);
}

if(  defined('SESSlang') && SESSlang  ) {
	$lang=SESSlang;
	$helpdir = $lang;
}

// localisation (fichier XML)
$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
$messages->analyser();
$msg = $messages->table;
require("$include_path/templates/common.tpl.php");
header ("Content-Type: text/html; charset=$charset");

$sphinx_message = check_sphinx_service();
if (!empty($sphinx_message)) {
    print "<script>alert('$sphinx_message')</script>";
}

if ((!$param_licence)||($pmb_bdd_version!=$pmb_version_database_as_it_should_be)||($pmb_subversion_database_as_it_shouldbe!=$pmb_bdd_subversion)) {
	require_once("$include_path/templates/main.tpl.php");
	print $std_header;
	print "<body class='$current_module claro' id='body_current_module' page_name='$current_module'>";
	print $menu_bar;

	print $extra;
	if($use_shortcuts) {
		include("$include_path/shortcuts/circ.sht");
	}
	print $main_layout;

	if ($pmb_bdd_version!=$pmb_version_database_as_it_should_be) {
		echo "<h1>".$msg["pmb_v_db_pas_a_jour"]."</h1>";
		echo "<h1>".$msg[1803]."<span style='color:red'>".$pmb_bdd_version."</span></h1>";
		echo "<h1>".$msg['pmb_v_db_as_it_should_be']."<span style='color:red'>".$pmb_version_database_as_it_should_be."</span></h1>";
		echo "<a href='./admin.php?categ=alter&sub='>".$msg["pmb_v_db_mettre_a_jour"]."</a>";
		echo "<SCRIPT>alert(\"".$msg["pmb_v_db_pas_a_jour"]."\\n".$pmb_version_database_as_it_should_be." <> ".$pmb_bdd_version."\");</SCRIPT>";
	} elseif ($pmb_subversion_database_as_it_shouldbe!=$pmb_bdd_subversion) {
		echo "<h1>Minor changes in database in progress...</h1>";
		include("./admin/misc/addon.inc.php");
		echo "<h1>Changes applied in database.</h1>";
	}

	//On est probablement sur une première connexion à PMB
	$pmb_indexation_must_be_initialized = empty($pmb_indexation_must_be_initialized) ? 0 : intval($pmb_indexation_must_be_initialized);
	if($pmb_indexation_must_be_initialized) {
		echo "<h1>Indexation in progress...</h1>";
		flush();
		ob_flush();
		include("./admin/misc/setup_initialization.inc.php");
		echo "<h1>Indexation applied in database.</h1>";
	}

	if (!$param_licence) {
		include("$base_path/resume_licence.inc.php");
	}

	print $main_layout_end;
	print $footer;

	pmb_mysql_close($dbh);
	exit ;
}
if ($ret_url) {
	if(strpos($ret_url, 'ajax.php') !== false) {
		print "<SCRIPT>document.location=\"".$_SERVER['HTTP_REFERER']."\";</SCRIPT>";
		exit;
	}
	//AR - on évite un redirection vers une url absolue...
	if((strpos($ret_url, 'http://') === false) && (strpos($ret_url, 'https://') === false)) {
	    print "<SCRIPT>document.location=\"$ret_url\";</SCRIPT>";
	    exit ;
	}
}

//chargement de la première page
require_once($include_path."/misc.inc.php");

go_first_tab();

pmb_mysql_close($dbh);
