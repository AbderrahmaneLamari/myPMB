<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
/* 

Ce logiciel est un programme informatique servant � g�rer une biblioth�que
ou un centre de documentation et notamment le catalogue des ouvrages et le
fichier des lecteurs. PMB est conforme � la d�claration simplifi�e de la CNIL
en ce qui concerne le respect de la Loi Informatique et Libert�s applicable
en France.

Ce logiciel est r�gi par la licence CeCILL soumise au droit fran�ais et
respectant les principes de diffusion des logiciels libres. Vous pouvez
utiliser, modifier et/ou redistribuer ce programme sous les conditions
de la licence CeCILL telle que diffus�e par le CEA, le CNRS et l'INRIA 
sur le site "http://www.cecill.info".

En contrepartie de l'accessibilit� au code source et des droits de copie,
de modification et de redistribution accord�s par cette licence, il n'est
offert aux utilisateurs qu'une garantie limit�e.  Pour les m�mes raisons,
seule une responsabilit� restreinte p�se sur l'auteur du programme,  le
titulaire des droits patrimoniaux et les conc�dants successifs.

A cet �gard  l'attention de l'utilisateur est attir�e sur les risques
associ�s au chargement,  � l'utilisation,  � la modification et/ou au
d�veloppement et � la reproduction du logiciel par l'utilisateur �tant
donn� sa sp�cificit� de logiciel libre, qui peut le rendre complexe �
manipuler et qui le r�serve donc � des d�veloppeurs et des professionnels
avertis poss�dant  des  connaissances  informatiques approfondies.  Les
utilisateurs sont donc invit�s � charger  et  tester  l'ad�quation  du
logiciel � leurs besoins dans des conditions permettant d'assurer la
s�curit� de leurs syst�mes et ou de leurs donn�es et, plus g�n�ralement,
� l'utiliser et l'exploiter dans les m�mes conditions de s�curit�.

Le fait que vous puissiez acc�der � cet en-t�te signifie que vous avez
pris connaissance de la licence CeCILL, et que vous en avez accept� les
termes.

 */
// +-------------------------------------------------+
// $Id: index.php,v 1.23.4.2 2023/07/20 08:14:40 jparis Exp $

// d�finition du minimum n�c�ssaire
$base_path=".";

include_once "{$base_path}/includes/error_report.inc.php";
require_once "{$base_path}/includes/pmb_cookie.inc.php";
include_once "{$base_path}/includes/config.inc.php";

if (!file_exists("$include_path/db_param.inc.php")) {
    // Pas de fichier pr�sent, on s'assure quand m�me qu'il n'y a pas d�j� eu une installation
    if(file_exists($base_path."/tables/install.php")){
        // Fichier d'installation pr�sent, on renvoie dessus !
        header("Location: $base_path/tables/install.php");
    }
    // Si on est encore la, on n'a pas �t� redirig� ;
    die("Fichier db_param.inc.php absent / Missing file db_param.inc.php");
}
require_once "$include_path/db_param.inc.php";
require_once "$include_path/mysql_connect.inc.php";
$dbh = connection_mysql();

require_once "$include_path/sessions.inc.php";

require_once "$include_path/misc.inc.php";
include_once "$javascript_path/misc.inc.php";


// r�cup�ration des messages avec localisation
// localisation (fichier XML)
include_once "$class_path/XMLlist.class.php";

$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
$messages->analyser();
$msg = $messages->table;

// temporaire :
$inst_language = "";
$current_module = "";

// Chargement de l'autoload des librairies externes
require_once $base_path.'/vendor/autoload.php';
// Chargement de l'autoload back-office
require_once __DIR__."/classes/autoloader/classLoader.class.php";
$al = classLoader::getInstance();
$al->register();

require_once "$include_path/templates/index.tpl.php";
if (!$dbh) {
	header ("Content-Type: text/html; charset=".$charset);
	print $index_header;
	print $extra_version;
	print "<br /><br /><div class='erreur'> $__erreur_cnx_base__ </div><br /><br />" ;
	print $msg["cnx_base_err1"]." <a href='./tables".$inst_language."/install.php'>./tables/install.php</a> ? <br /><br />.".$msg["cnx_base_err2"];
	print $index_footer;
	exit ;
}

require_once "$include_path/templates/common.tpl.php";

// affichage du form de login
if (!isset($demo) || $demo=="") $demo = 0;
header ("Content-Type: text/html; charset=$charset");

if (!isset($login_error) || !$login_error) {
	//Est-on d�j� authentifi� ?
	if (checkUser('PhpMyBibli')) {
		header("Location: ./main.php");
		exit();
	}
}

print $index_layout;

if ($demo) {
	if (!isset($login_error) || !$login_error) {
		$login_form_demo = str_replace("!!erreur!!", "&nbsp;", $login_form_demo);
		print $login_form_demo;
	} else {
		$login_form_demo = str_replace("!!erreur!!", $login_form_error, $login_form_demo);
		print $login_form_demo;
	}
} else {
	if (!isset($login_error) || !$login_error) {
		$login_form = str_replace("!!erreur!!", "&nbsp;", $login_form);
	} else {
		$login_form = str_replace("!!erreur!!", $login_form_error, $login_form);
	}
	if (isset($login_message) && $login_message) {
		$login_form = str_replace("!!login_message!!", $login_message, $login_form);
	} else {
		$login_form = str_replace("!!login_message!!", "", $login_form);
	}
	print $login_form;
}

print form_focus('login', 'user');
print $index_footer;
