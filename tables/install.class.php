<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: install.class.php,v 1.4 2021/05/04 14:46:04 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class install {
	
	
	protected static $language = null;
	protected static $messages = [];
	protected static $accepted_languages = null;

	protected const LANGUAGE_DEFAULT = 'fr';
	protected const LANGUAGES_AVAILABLE = [
			'ca', 
			'en',
			'es',
			'fr', 
			'it',
			'pt',
	];
	
	
	/**
	 * Constructeur privé pour éviter l'instanciation
	 */
	private function __construct() 
	{
	}

	
	/**
	 * Récupère les messages en fonction de la langue
	 * 
	 * @param string $lang
	 * @return []
	 */
	public static function getMessages($lang) 
	{
		if(!empty(static::$messages[$lang])) {
			return static::$messages[$lang];
		}
		
		$install_msg = [];
		$install_msg_fr = [];
		$install_msg_lang = [];
		if(is_readable(__DIR__."/fr/messages.php")) {
			require_once __DIR__."/fr/messages.php";
			$install_msg_fr = $install_msg['fr'];
		}
		if( ('fr' != $lang ) && (is_readable(__DIR__."/{$lang}/messages.php")) ){
			require_once __DIR__."/{$lang}/messages.php";
			$install_msg_lang = $install_msg[$lang];
		}
		static::$messages[$lang] = array_merge($install_msg_fr, $install_msg_lang);
		
		return static::$messages[$lang];
	}
	
	
	/**
	 * Récupère la page de language en fonction de la langue
	 *
	 * @param string $lang
	 * @return string
	 */
	public static function getLanguagePage($lang)
	{
		$language_page = "";
		if(is_readable(__DIR__."/{$lang}/language.tpl.php")) {
			require_once __DIR__."/{$lang}/language.tpl.php";
		} else {
			require_once  __DIR__."/fr/language.tpl.php";
		}
		return $language_page;
	}
	
	
	/**
	 * Récupère la page d'installation en fonction de la langue
	 *
	 * @param string $lang
	 * @return string
	 */
	public static function getInstallPage($lang)
	{
		$install_page = "";
		if(is_readable(__DIR__."/{$lang}/install.tpl.php")) {
			require_once __DIR__."/{$lang}/install.tpl.php";
		} else {
			require_once  __DIR__."/fr/install.tpl.php";
		}
		return $install_page;
	}
	
	
	/**
	 * Récupére les templates de la page de compte rendu en fonction de la langue
	 *
	 * @param string $lang
	 * @return [string]
	 */
	public static function getReportTemplates($lang)
	{
		$report_tpl = [];
		if(is_readable(__DIR__."/{$lang}/report.tpl.php")) {
			require_once __DIR__."/{$lang}/report.tpl.php";
		} else {
			require_once  __DIR__."/fr/report.tpl.php";
		}
		return $report_tpl;
	}
	
	
	/**
	 * Restaure un fichier SQL
	 * 
	 * @param string $src
	 * @param string $lang
	 * @param resource $dbh
	 * @return boolean
	 */
	public static function restore($src, $lang, $dbh) 
	{
		if(empty($src)) {
			return false;
		}
		
		switch (true) {
			
			//On cherche le fichier dans la langue definie
			case ( is_readable("./{$lang}/{$src}") ) :
				$src = "./{$lang}/{$src}";
				break; 
				
			//Ou en Francais
			case ( is_readable("./fr/{$src}") ) :
				$src = "./fr/{$src}";
				break; 
				
			//Ou dans le répertoire courant
			case (is_readable("./{$src}") ) :
				$src = "./{$src}";
			break;
			
			default :
				return false;
				break;
		}

		$buffer_sql = file_get_contents($src);
		if(empty($buffer_sql)) {
			return false;
		}
		if (!empty($src)) {
			// open source file
			$SQL = preg_split('/;\s*\n|;\n/m', $buffer_sql);
			$nb_queries = count($SQL);
			for ($i = 0; $i < $nb_queries; $i++) {
				if (!empty($SQL[$i])) {
					pmb_mysql_query($SQL[$i], $dbh);
				}
			}
		}
		return true;
	}
	
	
	/**
	 * Supprime les fichiers temporaires (XML*.tmp)
	 * 
	 * @param string $dir
	 * @return void
	 */
	public static function delTemporaryFiles($dir)
	{
		if($dh = opendir($dir)){
			while(($file = readdir($dh))!== false){
				if(file_exists($dir.$file) && preg_match("/^XML.*?\.tmp$/i",$file)){
					@unlink($dir.$file);
				}
			}
			closedir($dh);
		}
	}
	
	
	/**
	 * Cree les fichiers de connexion a la base de donnees
	 * 
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpassword
	 * @param string $dbname
	 * @param string $charset
	 * @return void
	 */
	public static function createDbParam($dbhost,$dbuser,$dbpassword,$dbname,$charset) 
	{
		$buffer_fic ="<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
				
// paramètres d'accès à la base MySQL
				
// prevents direct script access
if(preg_match('/db_param\.inc\.php/', \$_SERVER['REQUEST_URI'])) {
	include('./forbidden.inc.php'); forbidden();
}
// inclure ici les tableaux des bases de données accessibles
\$_tableau_databases[0]=\"".$dbname."\" ;
\$_libelle_databases[0]=\"".$dbname."\" ;
		
// pour multi-bases
if (isset(\$database)) {
	define('LOCATION', \$database) ;
} else {
	if (!isset(\$_COOKIE[\"PhpMyBibli-DATABASE\"]) || !\$_COOKIE[\"PhpMyBibli-DATABASE\"]) define('LOCATION', \$_tableau_databases[0]);
	else define('LOCATION', \$_COOKIE[\"PhpMyBibli-DATABASE\"]) ;
}
		
// define pour les paramètres de connection. A adapter.
switch(LOCATION):
	case 'remote':	// mettre ici les valeurs pour l'accés distant
		define('SQL_SERVER', 'remote');		// nom du serveur . exemple : http://sql.free.fr
		define('USER_NAME', 'username');	// nom utilisateur
		define('USER_PASS', 'userpwd');		// mot de passe
		define('DATA_BASE', 'dbname');		// nom base de données
		define('SQL_TYPE',  'mysql');		// Type de serveur de base de données
		//\$charset = 'utf-8'; || \$charset = 'iso-8859-1';
		//\$time_zone = 'Europe/Paris'; //Pour modifier l'heure PHP
		//\$time_zone_mysql =  \"'-00:00'\"; //Pour modifier l'heure MySQL
		break;
	case '".$dbname."':
		define('SQL_SERVER', '".$dbhost."');		// nom du serveur
		define('USER_NAME', '".$dbuser."');		// nom utilisateur
		define('USER_PASS', '".$dbpassword."');		// mot de passe
		define('DATA_BASE', '".$dbname."');		// nom base de données
		define('SQL_TYPE',  'mysql');			// Type de serveur de base de données
		// Encode de caracteres de la base de données
		\$charset = \"". $charset. "\" ;
		//\$time_zone = 'Europe/Paris'; //Pour modifier l'heure PHP
		//\$time_zone_mysql =  \"'-00:00'\"; //Pour modifier l'heure MySQL
		break;
	default:		// valeurs pour l'accès local
		define('SQL_SERVER', 'localhost');		// nom du serveur
		define('USER_NAME', 'bibli');			// nom utilisateur
		define('USER_PASS', 'bibli');			// mot de passe
		define('DATA_BASE', 'bibli');			// nom base de données
		define('SQL_TYPE',  'mysql');			// Type de serveur de base de données
		//\$charset = 'utf-8'; || \$charset = 'iso-8859-1';
		//\$time_zone = 'Europe/Paris'; //Pour modifier l'heure PHP
		//\$time_zone_mysql =  \"'-00:00'\"; //Pour modifier l'heure MySQL
		break;
endswitch;
				
\$dsn_pear = SQL_TYPE.\"://\".USER_NAME.\":\".USER_PASS.\"@\".SQL_SERVER.\"/\".DATA_BASE ;
";
		
		$opac_buffer_fic ="<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
				
// paramètres d'accès à la base MySQL
				
// prevents direct script access
if(preg_match('/opac_db_param\.inc\.php/', \$_SERVER['REQUEST_URI'])) {
	include('./forbidden.inc.php'); forbidden();
}
				
// inclure ici les tableaux des bases de données accessibles
\$_tableau_databases[0]=\"".$dbname."\" ;
\$_libelle_databases[0]=\"".$dbname."\" ;
		
// pour multi-bases
if (!isset(\$database)) {
	if (\$_COOKIE[\"PhpMyBibli-OPACDB\"]) \$database=\$_COOKIE[\"PhpMyBibli-OPACDB\"];
	elseif (\$_COOKIE[\"PhpMyBibli-DATABASE\"]) \$database=\$_COOKIE[\"PhpMyBibli-DATABASE\"];
	else \$database=\$_tableau_databases[0];
}
if (array_search(\$database,\$_tableau_databases)===false) \$database=\$_tableau_databases[0];
define('LOCATION', \$database) ;
\$expiration = time() + 30000000; /* 1 year */
setcookie ('PhpMyBibli-OPACDB', \$database, \$expiration);
		
// define pour les paramètres de connection. A adapter.
switch(LOCATION):
	case 'remote':	// mettre ici les valeurs pour l'accés distant
		define('SQL_SERVER', 'remote');		// nom du serveur . exemple : http://sql.free.fr
		define('USER_NAME', 'username');	// nom utilisateur
		define('USER_PASS', 'userpwd');		// mot de passe
		define('DATA_BASE', 'dbname');		// nom base de données
		define('SQL_TYPE',  'mysql');		// Type de serveur de base de données
		//\$charset = 'utf-8'; || \$charset = 'iso-8859-1';
		//\$time_zone = 'Europe/Paris'; //Pour modifier l'heure PHP
		//\$time_zone_mysql =  \"'-00:00'\"; //Pour modifier l'heure MySQL
		break;
	case '".$dbname."':
		define('SQL_SERVER', '".$dbhost."');		// nom du serveur
		define('USER_NAME', '".$dbuser."');		// nom utilisateur
		define('USER_PASS', '".$dbpassword."');		// mot de passe
		define('DATA_BASE', '".$dbname."');		// nom base de données
		define('SQL_TYPE',  'mysql');			// Type de serveur de base de données
		// Encode de caracteres de la base de données
		\$charset = \"". $charset. "\" ;
		//\$time_zone = 'Europe/Paris'; //Pour modifier l'heure PHP
		//\$time_zone_mysql =  \"'-00:00'\"; //Pour modifier l'heure MySQL
		break;
	default:		// valeurs pour l'accès local
		define('SQL_SERVER', 'localhost');		// nom du serveur
		define('USER_NAME', 'bibli');			// nom utilisateur
		define('USER_PASS', 'bibli');			// mot de passe
		define('DATA_BASE', 'bibli');			// nom base de données
		define('SQL_TYPE',  'mysql');			// Type de serveur de base de données
		//\$charset = 'utf-8'; || \$charset = 'iso-8859-1';
		//\$time_zone = 'Europe/Paris'; //Pour modifier l'heure PHP
		//\$time_zone_mysql =  \"'-00:00'\"; //Pour modifier l'heure MySQL
		break;
endswitch;
				
\$dsn_pear = SQL_TYPE.\"://\".USER_NAME.\":\".USER_PASS.\"@\".SQL_SERVER.\"/\".DATA_BASE ;
";
		
		@copy ("../includes/db_param_old_01.inc.php","../includes/db_param_old_02.inc.php");
		@copy ("../includes/db_param.inc.php","../includes/db_param_old_01.inc.php");
		$fptr = fopen("../includes/db_param.inc.php", 'w');
		fwrite ($fptr, $buffer_fic);
		fclose($fptr);
		
		@copy ("../opac_css/includes/opac_db_param_old_01.inc.php","../opac_css/includes/opac_db_param_old_02.inc.php");
		@copy ("../opac_css/includes/opac_db_param.inc.php","../opac_css/includes/opac_db_param_old_01.inc.php");
		$fptr = fopen("../opac_css/includes/opac_db_param.inc.php", 'w');
		fwrite ($fptr, $opac_buffer_fic);
		fclose($fptr);
		
	}
	
	
	/**
	 * Recupere la langue du navigateur ou la langue par défaut si non gérée
	 * 
	 * @return string
	 */
	public static function getLanguage()
	{
		if( !is_null(static::$language)) {
			return static::$language;
		}
		
		static::$language = static::LANGUAGE_DEFAULT;
		static::getAcceptedLanguages();
		if( empty(static::$accepted_languages) ) {
			return static::$language;
		}
		foreach(static::$accepted_languages as $language) {
			if( in_array($language, static::LANGUAGES_AVAILABLE) ) {
				static::$language = $language;
				return static::$language;
			}
		}
		return static::$language;
	}
	
	
	/**
	 * Retourne les langages acceptés depuis l'entête "Accept-Language" par ordre de préférence
	 *
	 * @return array
	 *
	 */
	protected function getAcceptedLanguages()
	{
		if( !is_null(static::$accepted_languages) ) {
			return static::$accepted_languages;
		}
		static::$accepted_languages = [];
		if( empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
			return static::$accepted_languages;
		}
		$accept_headers = explode(',', str_replace(' ', '', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
		$tmp1 = [];
		foreach ($accept_headers as $header) {
			$tmp2 = explode(';',$header);
			$value = str_replace('-', '_', $tmp2[0]);
			$q = '1';
			if( count($tmp2) > 1 ){
				$last = array_pop($tmp2);
				if( false !== $pos = strpos($last, 'q=')) {
					$q = substr($last, $pos+2);
				}
			}
			$tmp1[$value] = $q;
		}
		arsort($tmp1);
		static::$accepted_languages = array_keys($tmp1);
		
		return static::$accepted_languages;
	}
	
	
	/**
	 * Definit les parametres necessaires avant remplissage de la pile d'indexation
	 * 
	 * @param resource $dbh
	 * @return void
	 */
	public static function setPreFillIndexationStackParameters($dbh) 
	{
		//génération des URLs		
		$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
		$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
		$port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
		$tmp = explode('/', $_SERVER["PHP_SELF"]);
		array_pop($tmp);
		array_pop($tmp);
		$pmb_url_base = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.implode('/', $tmp).'/';
		
		$q_pmb_url_base = "update parametres set valeur_param = '".addslashes($pmb_url_base)."' where type_param='pmb' and sstype_param='url_base' ";
		@pmb_mysql_query($q_pmb_url_base, $dbh);
		
		$q_pmb_url_internal = "update parametres set valeur_param = '".addslashes($pmb_url_base)."' where type_param='pmb' and sstype_param='url_internal' ";
		@pmb_mysql_query($q_pmb_url_internal, $dbh);
		
		$q_cms_url_base_cms_build = "update parametres set valeur_param = '".addslashes($pmb_url_base.'opac_css/')."' where type_param='cms' and sstype_param='url_base_cms_build' ";
		@pmb_mysql_query($q_cms_url_base_cms_build, $dbh);

		$q_pmb_opac_url = "update parametres set valeur_param = '".addslashes($pmb_url_base.'opac_css/')."' where type_param='opac' and sstype_param='url_base' ";
		@pmb_mysql_query($q_pmb_opac_url, $dbh);

		$q_opac_url_base = "update parametres set valeur_param = '".addslashes($pmb_url_base.'opac_css/')."' where type_param='opac' and sstype_param='url_base' ";
		@pmb_mysql_query($q_opac_url_base, $dbh);
		
		$q_pmb_indexation_in_progress = "update parametres set valeur_param='0' where type_param='pmb' and sstype_param='indexation_in_progress' ";
		@pmb_mysql_query($q_pmb_indexation_in_progress, $dbh);

		$q_pmb_indexation_needed = "update parametres set valeur_param='0' where type_param='pmb' and sstype_param='indexation_needed' ";
		@pmb_mysql_query($q_pmb_indexation_needed, $dbh);

		$q_pmb_indexation_last_entity = "update parametres set valeur_param='0' where type_param='pmb' and sstype_param='indexation_last_entity' ";
		@pmb_mysql_query($q_pmb_indexation_last_entity, $dbh);
		
	}
	
	
	/**
	 * Remplit la pile d'indexation
	 * 
	 * @param resource $dbh
	 * @return void
	 */
	public static function fillIndexationStack($dbh) 
	{
		//redefini ici car init.inc.php n'a pas ete charge
		if (!defined('TYPE_CATEGORY')) {
			define('TYPE_CATEGORY',3);
		}
		if (!defined('TYPE_INDEXINT')) {
			define('TYPE_INDEXINT',9);
		}
		
		$q = "insert ignore into indexation_stack 
(indexation_stack_entity_id, indexation_stack_entity_type, indexation_stack_datatype, indexation_stack_timestamp, indexation_stack_parent_id, indexation_stack_parent_type) 
select num_noeud, ".TYPE_CATEGORY.", 'all', now(), num_noeud,  ".TYPE_CATEGORY." from categories";
		@pmb_mysql_query($q, $dbh);
		
		$q = "insert ignore into indexation_stack 
(indexation_stack_entity_id, indexation_stack_entity_type, indexation_stack_datatype, indexation_stack_timestamp, indexation_stack_parent_id, indexation_stack_parent_type) 
select indexint_id, ".TYPE_INDEXINT.", 'all', now(), indexint_id,  ".TYPE_INDEXINT." from indexint";
		@pmb_mysql_query($q, $dbh);
		
	}

	/**
	 * Definit les parametres necessaires apres remplissage de la pile d'indexation
	 *
	 * @param resource $dbh
	 * @return void
	 */
	public static function setPostFillIndexationStackParameters($dbh)
	{
		$q_pmb_indexation_needed = "update parametres set valeur_param='1' where type_param='pmb' and sstype_param='indexation_needed' ";
		@pmb_mysql_query($q_pmb_indexation_needed, $dbh);
	}
		
}

