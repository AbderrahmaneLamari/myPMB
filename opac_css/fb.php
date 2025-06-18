<?php
// +-------------------------------------------------+
// © 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: fb.php,v 1.14.8.1 2021/12/24 11:24:36 qvarin Exp $
$base_path=".";

// On ne veut pas charger la BDD ici, mais on a besoins du $charset du opac_db_param
// On met la fonction pour eviter la Fatal
function pmb_setcookie($name, $value = "", $expires = 0, $path = "", $domain = ""){
    $params = session_get_cookie_params();
    $params["expires"]=$expires;
    $params["path"]=$path;
    $params["domain"]=$domain;
    return setcookie($name,$value,$params);
}

require_once($base_path."/includes/global_vars.inc.php");
require_once($base_path.'/includes/opac_config.inc.php');
require_once($base_path.'/includes/opac_db_param.inc.php');

global $url_location, $charset, $url;

$args = explode("&", $url);
$url_location = $args[0]; 
for($i=1; $i<count($args);$i++) {
	$key_value = explode("=",$args[$i]);
	${$key_value[0]} = $key_value[1];
}

if (!isset($title)) {
    $title = "";
}
if (!isset($desc)) {
    $desc = "";
}
if (!isset($id)) {
    $id = 0;
}
if (!isset($opac_view)) {
    $opac_view = "";
}

$url = htmlentities($url_location,ENT_QUOTES,$charset);
if (!empty($id)) {
    $url .= "&id=" . htmlentities($id,ENT_QUOTES,$charset);    
}
if (!empty($opac_view)) {
    $url .= "&opac_view=" . htmlentities($opac_view,ENT_QUOTES,$charset);    
}

print "
<html xmlns='http://www.w3.org/1999/xhtm'
      xmlns:og='http://ogp.me/ns#'
      xmlns:fb='http://www.facebook.com/2008/fbml' charset='".$charset."'>
	<head>
		<meta name='title' content='" . htmlentities(stripslashes($title),ENT_QUOTES,$charset) . "' />
		<meta name='description' content='" . htmlentities(stripslashes($desc),ENT_QUOTES,$charset) . "' />
		<title>" . htmlentities(stripslashes($title),ENT_QUOTES,$charset) . "</title>
		
		<script type='text/javascript'>
			document.location='" . $url . "';
		</script>
	</head>
</html>";