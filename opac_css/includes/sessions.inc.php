<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sessions.inc.php,v 1.22.4.3 2023/10/24 10:10:51 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// fonctions de gestion des sessions

// prevents direct script access
if(preg_match('/sessions\.inc\.php/', $REQUEST_URI)) {
	include('./forbidden.inc.php'); forbidden();
}

global $class_path;
require_once "{$class_path}/password/password.class.php";

define( 'CHECK_EMPR_NO_SESSION', 1 );
define( 'CHECK_EMPR_SESSION_DEPASSEE', 2 );
define( 'CHECK_EMPR_SESSION_INVALIDE', 3 );
define( 'CHECK_EMPR_AUCUN_DROITS', 4 );
define( 'CHECK_EMPR_PB_ENREG_SESSION', 5 );
define( 'CHECK_EMPR_PB_OUVERTURE_SESSION', 6 );
define( 'CHECK_EMPR_SESSION_OK', 7 );

// checkEmpr : authentification de l'utilisateur
function checkEmpr($SESSNAME, $allow=0,$user_connexion='') {
    global $include_path;
	global $checkempr_type_erreur ;
	global $check_messages;
	global $opac_duration_session_auth;
	// par d�faut : pas de session ouverte
	$checkempr_type_erreur = CHECK_EMPR_NO_SESSION ;
	
	// On n'a pa encore globalis� les param�tres, on va chercher la dur�e de session directement dans la table
	$query = "select valeur_param from parametres where type_param = 'opac' and sstype_param = 'duration_session_auth'";
	$result = pmb_mysql_query($query);
	$opac_duration_session_auth = pmb_mysql_result($result, 0, 0);
	
	// r�cup�re les infos de session dans les cookies
	$PHPSESSID = (isset($_COOKIE["$SESSNAME-SESSID"]) ? $_COOKIE["$SESSNAME-SESSID"] : '');
	if ($user_connexion) {
		$PHPSESSLOGIN = $user_connexion; 
	} else {
		if(isset($_COOKIE["$SESSNAME-LOGIN"])) {
			$PHPSESSLOGIN = $_COOKIE["$SESSNAME-LOGIN"];
		} else {
			$PHPSESSLOGIN = '';
		}
	}
	$PHPSESSNAME = (isset($_COOKIE["$SESSNAME-SESSNAME"]) ? $_COOKIE["$SESSNAME-SESSNAME"] : '');
	
	// on r�cup�re l'IP du client
	$ip = $_SERVER['REMOTE_ADDR'];

	// recherche de la session ouverte dans la table
	$query = "SELECT SESSID, login, IP, SESSstart, LastOn, SESSNAME FROM sessions WHERE ";
	$query .= "SESSID='". addslashes($PHPSESSID) . "' and login = '" . addslashes($PHPSESSLOGIN) . "'";
	$result = pmb_mysql_query($query);
	$numlignes = pmb_mysql_num_rows($result);

	if(!$result || !$numlignes) {
		$checkempr_type_erreur = CHECK_EMPR_NO_SESSION ;
		return FALSE;
	}
	
	// v�rification de la dur�e de la session
	$session = pmb_mysql_fetch_object($result);
	// dur�e depuis le dernier rafraichissement
	if(($session->LastOn+$opac_duration_session_auth) < time()) {
		$checkempr_type_erreur = CHECK_EMPR_SESSION_DEPASSEE ;
		return FALSE;
	}
	// dur�e depuis le d�but de la session, max 12h
	if(($session->SESSstart+43200) < time()) {
		$checkempr_type_erreur = CHECK_EMPR_SESSION_DEPASSEE ;
		return FALSE;
	}
	
	// On teste ici si le mdp correspond a la politique des mdp mis en place
	if ($PHPSESSLOGIN !== "") {
	    $query_user_pwd = "SELECT empr_password FROM empr WHERE empr_login = '" . addslashes($PHPSESSLOGIN) . "'";
	    $result_user_pwd = pmb_mysql_query($query_user_pwd);
	    if (pmb_mysql_num_rows($result_user_pwd)) {
	        $user_infos = pmb_mysql_fetch_object($result_user_pwd);
	        $hash_format = password::get_hash_format($user_infos->empr_password);
	        if ('undefined' === $hash_format ) {
	            $_SESSION['password_no_longer_compliant'] = true;
	        }
	    }
	}
	
	// il faut stocker le sessid parce FL r�utilise le tableau session pour aller lire les infos de users !!!
	if($session->SESSID=="") {
		$checkempr_type_erreur = CHECK_EMPR_SESSION_INVALIDE ;
		return FALSE;
	} else {
		$id_session = $session->SESSID ;
		$SESSstart_session = $session->SESSstart ;
	}

	// authentification OK, on remet LAstOn � jour
	$t = time();
	
	// on avait bien stock� le sessid, on va pouvoir remettre � jour le laston, avec sessid dans la clause where au lieu de id en outre.
	$query = "UPDATE sessions SET LastOn='$t' WHERE sessid='$id_session' ";
	$result = pmb_mysql_query($query) or die (pmb_mysql_error());

	if(!$result) {
		$checkempr_type_erreur = CHECK_EMPR_PB_ENREG_SESSION ;
		return FALSE;
	}
	
	// r�cup�ration de la langue de l'utilisateur

	// mise � disposition des variables de la session
	define('SESSlogin'	, addslashes($PHPSESSLOGIN));
	define('SESSname'	, addslashes($SESSNAME));
	define('SESSid'		, addslashes($PHPSESSID));
	define('SESSstart'	, $SESSstart_session);
	
	return TRUE;
	}

// startSession : fonction de d�marrage d'une session
function startSession($SESSNAME, $login) {
	global $stylesheet; /* pour qu'� l'ouverture de la session le user r�cup�re de suite son style */
	global $checkempr_type_erreur ;
	global $PMBdatabase ;
	
	// nettoyage des sessions 'oubli�es'
	cleanTable($SESSNAME);

	// g�n�ration d'un identificateur unique

	// initialisation du g�n�rateur de nombres al�atoires
	mt_srand((float) microtime()*1000000);

	// nombre al�atoire entre 1111111111 et 9999999999
	$SESSID = mt_rand(1111111111, (int)9999999999);

	// d�but session (date UNIX)
	$SESSstart = time();

	// adresse IP du client
	$IP = $_SERVER['REMOTE_ADDR'];

	// inscription de la session dans la table
	$query = "INSERT INTO sessions (SESSID, login, IP, SESSstart, LastOn, SESSNAME) VALUES(";
	$query .= "'$SESSID'";
	$query .= ", '$login'";
	$query .= ", '$IP'";
	$query .= ", '$SESSstart'";
	$query .= ", '$SESSstart'";
	$query .= ", '$SESSNAME' )";

	$result = pmb_mysql_query($query);
	if(!$result) {
		$checkempr_type_erreur = CHECK_EMPR_PB_OUVERTURE_SESSION ;
		return CHECK_EMPR_PB_OUVERTURE_SESSION ;
	}

	// cookie pour le login de l'utilisateur
	pmb_setcookie($SESSNAME."-LOGIN", $login, 0);

	// cookie pour le nom de la session
	pmb_setcookie($SESSNAME."-SESSNAME", $SESSNAME, 0);

	// cookie pour l'ID de session
	pmb_setcookie($SESSNAME."-SESSID", $SESSID, 0);

	//r�-affectation de la cl� � la vol�e pour utilisation sur la m�me page
	//pmb_setcookie n'a pas d'effet immediat
	// #135688 : CAIRN access SSO
	$_COOKIE["$SESSNAME-SESSID"] = $SESSID;

	// cookie pour la base de donn�e
	pmb_setcookie($SESSNAME."-DATABASE", $PMBdatabase, 0);

	// mise � disposition des variables de la session
	if(!defined('SESSlogin')) define('SESSlogin'	, $login);
	if(!defined('SESSname')) define('SESSname'	, $SESSNAME);
	if(!defined('SESSid')) define('SESSid'		, $SESSID);
	if(!defined('SESSstart')) define('SESSstart'	, $SESSstart);
	
	//Ouverture de la session php
	header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Cache-Control: post-check=0, pre-check=0",false);
	if (session_status() !== PHP_SESSION_ACTIVE) {
    	session_cache_limiter('must-revalidate');
    	session_name("pmb".SESSid);
	    session_start();
	}

	return CHECK_EMPR_SESSION_OK ;
}

// cleanTable : nettoyage des sessions termin�es (user non-deconnect�)
function cleanTable($SESSNAME) {
	global $opac_duration_session_auth;
	
	if (!isset($opac_duration_session_auth)) {
	    // On n'a pa encore globalis� les param�tres, on va chercher la dur�e de session directement dans la table
	    $query = "select valeur_param from parametres where type_param = 'opac' and sstype_param = 'duration_session_auth'";
	    $result = pmb_mysql_query($query);
	    $opac_duration_session_auth = pmb_mysql_result($result, 0, 0);
	}
	// heure courante moins une heure
	$time_out = time() - $opac_duration_session_auth;

	// suppression des sessions inactives
	$query = "DELETE FROM sessions WHERE LastOn < ".$time_out." and SESSNAME = '".$SESSNAME."'";
	pmb_mysql_query($query);
}

// sessionDelete : fin d'une session
function sessionDelete($SESSNAME) {
	$login = $_COOKIE[$SESSNAME.'-LOGIN'];

	$PHPSESSID = $_COOKIE["$SESSNAME-SESSID"];
	$PHPSESSLOGIN = $_COOKIE["$SESSNAME-LOGIN"];
	$PHPSESSNAME = $_COOKIE["$SESSNAME-SESSNAME"];



	// alt�ration du cookie-client (au cas o� la suppression ne fonctionnerait pas)

	pmb_setcookie($SESSNAME."-LOGIN", "no_login", 0);
	pmb_setcookie($SESSNAME."-SESSNAME", "no_session", 0);
	pmb_setcookie($SESSNAME."-SESSID", "no_id_session", 0);

	// tentative de suppression ddes cookies

	pmb_setcookie($SESSNAME."-SESSNAME");
	pmb_setcookie($SESSNAME."-SESSID");
	pmb_setcookie($SESSNAME."-LOGIN");

	//Destruction de la session php
	session_destroy();

	// effacement de la session de la table des sessions

	$query = "DELETE FROM sessions WHERE login='".addslashes($login)."'";
	$query .= " AND SESSNAME='".addslashes($SESSNAME)."' and SESSID='".addslashes($PHPSESSID)."'";

	$result = pmb_mysql_query($query);
	if($result)
		return TRUE;

	return FALSE;
}

function check_anonymous_session($SESSNAME){
	global $check_messages;
	// par d�faut : pas de session ouverte
	$checkempr_type_erreur = CHECK_EMPR_NO_SESSION ;
	
	// r�cup�re les infos de session dans les cookies
	$PHPSESSID = (isset($_COOKIE["$SESSNAME-SESSID"]) ? $_COOKIE["$SESSNAME-SESSID"] : '');
	if(isset($_COOKIE["$SESSNAME-LOGIN"])) {
		$PHPSESSLOGIN = $_COOKIE["$SESSNAME-LOGIN"];
	} else {
		$PHPSESSLOGIN = '';
	}
	$PHPSESSNAME = (isset($_COOKIE["$SESSNAME-SESSNAME"]) ? $_COOKIE["$SESSNAME-SESSNAME"] : '');
	
	// on r�cup�re l'IP du client
	$ip = $_SERVER['REMOTE_ADDR'];
	
	// recherche de la session ouverte dans la table
	$query = "SELECT SESSID, login, IP, SESSstart, LastOn, SESSNAME FROM sessions WHERE ";
	$query .= "SESSID='".addslashes($PHPSESSID)."'";
	$result = pmb_mysql_query($query);
	$numlignes = pmb_mysql_num_rows($result);	
	if(!$numlignes){
		startSession($SESSNAME, "");
	}else{
		// On remet LAstOn � jour
		$t = time();
		// on avait bien stock� le sessid, on va pouvoir remettre � jour le laston, avec sessid dans la clause where au lieu de id en outre.
		$query = "UPDATE sessions SET LastOn='$t' WHERE sessid='$PHPSESSID' ";
		$result = pmb_mysql_query($query);
	}
}

/**
 * Destruction de la session
 */
function logout() {
    global $cms_build_activate;
    
    if ($_SESSION["cms_build_activate"]) {
        $cms_build_activate = 1;
    }
    if (isset($_SESSION["build_id_version"]) && $_SESSION["build_id_version"]) {
        $build_id_version = $_SESSION["build_id_version"];
    }
    
    $_SESSION = array();
    
    if (!$cms_build_activate) {
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            pmb_setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        sessionDelete("PmbOpac");
    }
    
    $_SESSION["cms_build_activate"] = $cms_build_activate;
    if (isset($_SESSION["build_id_version"]) && $_SESSION["build_id_version"]) {
        $_SESSION["build_id_version"] = $build_id_version;
    }
}
