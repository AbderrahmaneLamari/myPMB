<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: requirements_inc.php,v 1.7 2021/05/03 07:10:08 rtigero Exp $

// prevents direct script access
if(preg_match('/requirements_inc\.php/', $_SERVER['REQUEST_URI'])) {
	include('../../includes/forbidden.inc.php'); 
	forbidden();
}
global $base_path;

require_once(__DIR__.'/classes/verif.class.php');
require_once(__DIR__."/extensions.php");
require_once(__DIR__."/php_requirements.php");
require_once(__DIR__."/mysql_requirements.php");

$requirements_page="
<!DOCTYPE html>
<html>
	<head>
		<title>{$install_msg['req_window_title']}</title>
		<meta charset='utf-8'>
		<style type='text/css'>
			body {
				font-family: Verdana, Arial, sans-serif;
				background: #eeeae4;
                text-align:center;
			}
			.bouton {
				color: #fff;
				font-size: 12pt;
				font-weight: bold;
				border: 1px outset #D47800;
				background-color: #5483AC;
			}
            .error {
                color : red;
                font-size : 1.3em;
            }
			.bouton:hover {
				border-style: inset;
				border: 1px solid #ED8600;
				background-color: #7DC2FF;
			}
            th, td {
                border: 1px solid black;
                padding: 5px;
                text-align: left;
            }
            table {
                border: 1px solid black;
                background-color : white;
            }
            button:disabled{
               background:  #fff;
               color: #ccc;
               cursor: no-drop;
            }
		</style>
	</head>
    <body>
        <h1>{$install_msg['req_title']}</h1>";
//génération de la table des extensions requises
$requirements_table = "
<table id='requirements_table'>
    <tr>
        <th>{$install_msg['req_ext_name']}</th>
        <th>{$install_msg['req_ext_required']}</th>
        <th>{$install_msg['req_ext_state']}</th>
    </tr>
";

$verif = new verif($extensions, $php_suggested_setup, $mysql_suggested_setup, $install_msg);

//vérif de la version php
$checkPhpVersion = $verif->checkPhpVersion();

if(!$checkPhpVersion['check']){
    $version = explode(".", phpversion());
    $version = $version[0].".".$version[1];
    print $requirements_page."
        <p class='error'>
            {$install_msg['req_wrong_php_version_1']} <b>$version</b> {$install_msg['req_wrong_php_version_2']} (>= <b>{$checkPhpVersion['required_version']['min']}</b>, <= <b>{$checkPhpVersion['required_version']['max']}</b>)
        </p>";
    exit();
}

$checkExtensions = $verif->checkExtensions();

$missRequired = false;
foreach($checkExtensions as $requirement) {
    $requirements_table .= "<tr>";
    $name = $requirement['name'];
    $optional = $requirement['optional'] == 1 ? $install_msg['req_optional'] : $install_msg['req_required'];
    $requirements_table .= "<td>$name {$requirement['version']}</td>";
    
    switch($requirement["state"]){
        case 0:
            $missRequired = true;
            $requirements_table.="
                <td>$optional</td>
                <td>{$install_msg['req_not_installed']} <img src='$base_path/images/error.gif' style='height:16px;' /></td>";
            break;
        case 1:
            $requirements_table.="
                <td>$optional</td>
                <td>{$install_msg['req_installed']} <img src='$base_path/images/tick.gif' style='height:16px;' /></td>";
            break;
        case 2:
            $requirements_table.="
                <td>$optional</td>
                <td>{$install_msg['req_not_installed']} <img src='$base_path/images/warning.gif' style='height:16px;' /></td>";
            break;
    }
    $requirements_table .= "</tr>";
}
$requirements_table .= "</table>";


//génération de la table des recommandation de paramétrage php
$php_suggested_table = "
<table>
    <tr>
        <th>{$install_msg['req_php_suggested_table_th_1']}</th>
        <th>{$install_msg['req_php_suggested_table_th_2']}</th>
        <th>{$install_msg['req_php_suggested_table_th_3']}</th>
    </tr>
";

$checkPhp = $verif->checkPHP();

foreach ($checkPhp as $paramName => $paramValues)
{
    $name = $paramValues['name'];
    $currentValue = $paramValues['current_value'];
    $suggestedValue = $paramValues['suggested_value'];
    $required = $paramValues['required'];
    switch($required){
        case 0:
            $missRequired = true;
            $required = "<img src='$base_path/images/error.gif' style='height:16px;' />";
            break;
        case 1:
            $required = "<img src='$base_path/images/tick.gif' style='height:16px;' />";
            break;
        case 2:
            $required = "<img src='$base_path/images/warning.gif' style='height:16px;' />";
            break;
            
    }
    $php_suggested_table.="
        <tr>
            <td>$name</td>
            <td>$suggestedValue</td>
            <td>$currentValue $required</td>
    ";
    $php_suggested_table .= "</tr>";
}
$php_suggested_table .= "</table>";


if($missRequired == true){
    $requirements_page .= "<p class='error'>{$install_msg['req_missing_requirements']}</p>";
} else {
    $requirements_page .=" <p>{$install_msg['req_intro']}</p>";
}
$requirements_page .="
        <div style='display:flex; justify-content:space-evenly; align-items: start;'>
            $requirements_table
            $php_suggested_table
        </div>
        <form style='margin-top:20px; margin-bottom:20px;' method='post' action='install.php'>
            <input type='hidden' id='requirements' name='requirements' value='requirements' />
            <input type='hidden' name='install_lang' value='{$install_lang}' />
            <input type='hidden' name='install_step' value='mysql_requirements' />
            <button class='bouton' type='submit' ".(($missRequired == 1) ?"disabled":"").">{$install_msg['req_continue_button_label']}</button>
        </form>
    </body>
</html>";

