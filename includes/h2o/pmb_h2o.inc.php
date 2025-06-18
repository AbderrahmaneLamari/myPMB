<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmb_h2o.inc.php,v 1.52.2.6 2022/01/10 10:35:57 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $include_path, $class_path, $charset;

require_once($include_path."/h2o/h2o.php");
require_once($include_path."/misc.inc.php");

class pmb_StringFilters extends FilterCollection {
	
	public static function limitstring($string, $max = 50, $ends = "[...]"){
		global $charset;
		$string = html_entity_decode ($string, ENT_NOQUOTES, $charset);
		
		if(pmb_strlen($string)> $max){
			$string = pmb_substr($string,0,($max - pmb_strlen($ends))).$ends;
		}
		return $string;
	}
	
	public static function printf($string, $arg1, $arg2= "", $arg3= "", $arg4= "", $arg5= "", $arg6= "", $arg7= "", $arg8= "", $arg9= ""){
		return sprintf($string,$arg1,$arg2,$arg3,$arg4,$arg5,$arg6,$arg7,$arg8,$arg9);
	}
	
	public static function replace($subject, $search, $replace) {
	    if (($search[0] == '[') && ($search[strlen($search)-1] == ']')) {
	        $search = trim($search, '[]');
	        $search = explode(',', $search);
	    }
	    if (($replace[0] == '[') && ($replace[strlen($replace)-1] == ']')) {
	        $replace = trim($replace, '[]');
	        $replace = explode(',', $replace);
	    }
	    if (($subject[0] == '[') && ($subject[strlen($subject)-1] == ']')) {
	        $subject = trim($subject, '[]');
	        $subject = explode(',', $subject);
	    }
	    return str_replace($search, $replace, $subject);
	}
	
	public static function pregmatchreplace($subject, $search, $replace) {
	    
	    $search = str_replace("%5C", "\\", $search);
	    
	    if (($search[0] == '[') && ($search[strlen($search)-1] == ']')) {
	        $search = trim($search, '[]');
	        $search = explode(',', $search);
	    }
	    if (($replace[0] == '[') && ($replace[strlen($replace)-1] == ']')) {
	        $replace = trim($replace, '[]');
	        $replace = explode(',', $replace);
	    }
	    if (($subject[0] == '[') && ($subject[strlen($subject)-1] == ']')) {
	        $subject = trim($subject, '[]');
	        $subject = explode(',', $subject);
	    }
	    
	    return preg_replace($search, $replace, $subject);
	}
	
	// retourne le reste de $string à la position $start 
	public static function substr($string, $start) {
		if(!$string) return '';
		return substr($string, $start);
	}
	
	// retourne le nombre d'occurrence d'une chaine
	public static function substr_count($string, $needle) {
	    if(!$string) return '';
	    return substr_count($string,$needle);
	}
	
	// retourne le reste de $string après la premiere occurence de $needle
	public static function substring($string, $needle) {
		if(!$string) return '';
		if(!$needle) return $string;
		$str = strstr($string, $needle);
		if ($str) {
			return substr($str, strlen($needle));
		}
		return $string; 
	}
	
	// retourne le reste de $string jusqu'à la premiere occurence de $needle
	public static function substring_until($string, $needle) {
		if(!$string) return '';
		if(!$needle) return $string;
		$str = strpos($string, $needle);
		if ($str) {
			return substr($string, 0, $str);
		}
		return $string;
	}
	
	public static function addslashes($string) {
		return addslashes($string);
	}
	
	public static function divisibleby($inputString,$number)
	{
	    if(!is_numeric(trim($inputString))){
	        return $inputString;
	    }
	    $numeric = intval(trim($inputString));
	    if(($numeric % $number) > 0) {
	        return false;
	    }
	    return true;
	}
	
	public static function strtotimestamp($string)
	{
	    $date = new DateTime(detectFormatDate($string));
	    return $date->format('U');
	}
}

class pmb_DateFilters extends FilterCollection {
    
    public static function strftime($date, $format) {
        global $lang;
        
        $timestamp = null;
        if ($date instanceof DateTime) {
            $timestamp = $date->getTimestamp();
        } else {
            $timestamp = is_numeric($date) ? $date : strtotime($date);
        }
        
        switch ($lang) {
            case 'fr_FR':
                setlocale(LC_TIME, 'fr_FR.UTF-8');
                return strftime($format, $timestamp);
			default:
                setlocale(LC_TIME, 'en_US.UTF-8');
                return strftime($format, $timestamp);
        }
    }

	public static function year($date){
		$cleandate = detectFormatDate($date);
		if($cleandate != "0000-00-00"){
			return date("Y",strtotime($cleandate));
		}
		return $date;
	}

	public static function month($date){
		$cleandate = detectFormatDate($date);
		if($cleandate != "0000-00-00"){
			return date("m",strtotime($cleandate));
		}
		return $date;
	}

	public static function monthletter($date){
		global $msg;
		$month = self::month($date);
		if($month != $date){
			return ucfirst($msg['10'.str_pad($month+5,2,"0",STR_PAD_LEFT)]);
		}
		return $date;
	}
	
	public static function shortmonthletter($date){
		global $msg;
		$cleandate = detectFormatDate($date);
		if($cleandate != "0000-00-00"){
			return ucfirst($msg['short_'.strtolower(date("F",strtotime($cleandate)))]);
		}
		return $date;
	}

	public static function day($date){
		$cleandate = detectFormatDate($date);
		if($cleandate != "0000-00-00"){
			return date("d",strtotime($cleandate));
		}
		return $date;
	}
	
	public static function beforetoday($date){
	    $tmp = DateTime::createFromFormat("U",$date);
	    $diff = $tmp->diff(new Datetime());
	    return ($diff->format("%r") === "" );
	}
}

class pmb_CoreFilters extends FilterCollection {

	public static function url_proxy($string, $from=''){
		global $opac_url_base;
		
		$url_proxy = $opac_url_base."pmb.php?url=".urlencode($string);
		if($from) {
			$url_proxy .= "&from=".$from;
		}
		$url_proxy .= "&hash=".md5($string.$from);
		return $url_proxy;
	}
	
	public static function is_internal($url) {
	    global $opac_url_base;
	    
	    if(strpos($url, "./") === 0) {
	        return true;
	    }
	    
	    if(strpos($url, $opac_url_base) === 0) {
	        return true;
	    }
	    
	    return false;
	}
}

class Sqlvalue_Tag extends H2o_Node{
	private $struct_name;
	
	
	public function __construct($argstring, $parser, $position){
		$this->struct_name = $argstring;
		$this->pmb_query = $parser->parse('endsqlvalue');
	}
	
	public function render($context,$stream){
		$query_stream = new StreamWriter;
		$this->pmb_query->render($context, $query_stream);
		$query = $query_stream->close();
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			$struct =array();
			while ($row = pmb_mysql_fetch_assoc($result)){
				$struct[]=$row;
			}
			$context->set($this->struct_name,$struct);
		}else{
			$context->set($this->struct_name,0);
		}
	}
}

class Sparqlvalue_Tag extends H2o_Node{
	private $struct_name;
	private $endpoint;

	public function __construct($argstring, $parser, $position){
		$params = explode(" ",$argstring);
		$this->struct_name = $params[0];
		$this->endpoint = $params[1];
		$this->sparql_query = $parser->parse('endsparqlvalue');
	}

	public function render($context,$stream){
		global $class_path;

		$query_stream = new StreamWriter;
		$this->sparql_query->render($context, $query_stream);
		$query = $query_stream->close();
		
		require_once ("$class_path/rdf/arc2/ARC2.php");
		$config = array(
			'remote_store_endpoint' => $this->endpoint,
			'remote_store_timeout' => 10
		);
		$store = ARC2::getRemoteStore($config);
		$context->set($this->struct_name,$store->query($query,'rows'));
	}
}

class Tplnotice_Tag extends H2o_Node{
	private $id_tpl;

	public function __construct($argstring, $parser, $position){
		$this->id_tpl = $argstring;
		$this->pmb_notice = $parser->parse('endtplnotice');
	}

	public function render($context,$stream){
		global $class_path;
		$query_stream = new StreamWriter;
		$this->pmb_notice->render($context, $query_stream);
		$notice_id = $query_stream->close();
		$notice_id = (int) $notice_id;
		$query = "select count(notice_id) from notices where notice_id=".$notice_id;
		$result = pmb_mysql_query($query);
		if($result && pmb_mysql_result($result, 0)){
			require_once ("$class_path/notice_tpl_gen.class.php");
			$tpl=notice_tpl_gen::get_instance($this->id_tpl);
			$this->content=$tpl->build_notice($notice_id);
			$stream->write($this->content);
		}
	}
}

class Imgbase64_Tag extends H2o_Node{
    private $argument;
    public function __construct($argstring, $parser, $pos = 0) {
        $this->argument = $argstring;
    }
    
    public function render($context, $stream) {
        global $charset;
        $path = $this->argument;
        try{
         $stream->write("data:image/".pathinfo($path,PATHINFO_EXTENSION).";base64,".base64_encode(file_get_contents($path)));
        }catch(Exception $e){
        }
    }
}

class Setvalue_Tag extends H2o_Node{
    private $varName;
    private $value;
    
    public function __construct($argstring, $parser, $position){
        $params = explode(" ",$argstring);
        $this->varName = array_shift($params);
        $this->value = implode(" ",$params);
    }
    
    /**
     * @param H2o_Context $context
     * @param StreamWriter $stream
     */
    public function render($context,$stream){
        if (preg_match('/^\w/', $this->value)) {
            $this->value = symbol($this->value);
        }
        $context->set($this->varName,$context->resolve($this->value), $context::CURRENT_CONTEXT);
    }
}

class SetGlobalvalue_Tag extends H2o_Node{
    private $varName;
    private $value;
    
    public function __construct($argstring, $parser, $position){
        $params = explode(" ",$argstring);
        $this->varName = array_shift($params);     
        $this->value = implode(" ",$params);
    }
    
    /**
     * @param H2o_Context $context
     * @param StreamWriter $stream
     */
    public function render($context,$stream){
        if (preg_match('/^\w/', $this->value)) {
            $this->value = symbol($this->value);
        }
        $context->set($this->varName,$context->resolve($this->value), $context::GLOBAL_CONTEXT);
    }
}

class Frbrcadre_Tag extends H2o_Node{
    private $id_cadre;
    private $entity_id;
    private $entity_type;
    private $args = [];
    
    public function __construct($argstring, $parser, $position){
        $this->args = H2o_Parser::parseArguments($argstring);
        if(count($this->args) == 0 && count( $this->args) > 3 ){
            throw new TemplateSyntaxError('FRBRcadre demande des arguments');
        }
    }
    
    public function render($context,$stream){
        $this->entity_id =  $context->resolve($this->args[0]);
        $this->entity_type =  $context->resolve($this->args[1]);
        $this->id_cadre =  $context->resolve($this->args[2]);;
        
        $frbr_build = frbr_build::get_instance($this->entity_id, $this->entity_type);
        if (!empty($frbr_build)) {
            $datanodes_data = $frbr_build->get_datanodes_data();
            $cadres = $frbr_build->get_cadres();
            if (count($cadres) && !empty($this->id_cadre)) {
                if (!empty($cadres[$this->id_cadre]['cadre_object']) && class_exists($cadres[$this->id_cadre]['cadre_object'])) {
                    $view_instance = new $cadres[$this->id_cadre]['cadre_object']($this->id_cadre);
                    $stream->write($view_instance->show_cadre($datanodes_data));
                }
            }
        }
    }
}

class Arraysort_Tag extends H2o_Node {
    private $array_name;
    private $array;
    private $args = [];
    private $array_direction;
    
    public function __construct ($argstring, $parser, $position) {
        $this->args = H2o_Parser::parseArguments($argstring);
        if(count($this->args) == 0 && count($this->args) > 2){
            throw new TemplateSyntaxError('Arraysort demande des arguments');
        }
    }
    
    public function render ($context,$stream) {
        $this->array_name = substr($this->args[0], 1);
        $this->array = $context->resolve($this->args[0]);
        $this->array_direction = $context->resolve($this->args[1]);
        
        if (is_array($this->array)) {
            if ($this->array_direction == 'desc') {
                arsort($this->array);
            } else {
                asort($this->array);
            }
            $context->set($this->array_name,$this->array);
        }
    }
}

class Arrayunique_Tag extends H2o_Node {
    private $array_name;
    private $array;
    private $args = [];
    
    public function __construct ($argstring, $parser, $position) {
        $this->args = H2o_Parser::parseArguments($argstring);
        if(count($this->args) == 0 && count( $this->args) > 1) {
            throw new TemplateSyntaxError('Arrayunique demande des arguments');
        }
    }
    
    public function render ($context,$stream) {
        $this->array_name = substr($this->args[0], 1);
        $this->array = $context->resolve($this->args[0]);
        
        if (is_array($this->array)) {
            $this->array = array_unique($this->array);
            $context->set($this->array_name,$this->array);
        }
    }
}

Class Arrayadd_Tag extends H2o_Node{
    private $args = [];
    
    public function __construct($argstring, $parser, $position){
        $this->args = H2o_Parser::parseArguments($argstring);
        if(count($this->args) == 0 && count( $this->args) > 3){
            throw new TemplateSyntaxError('Arrayadd demande des arguments');
        }
    }
    
    public function render($context,$stream){
        $array_name = substr($this->args[0], 1);
        $array = $context->resolve($this->args[0]);
        $value = $context->resolve($this->args[1]);
        if (isset($this->args[2])) {
            $key = $context->resolve($this->args[2]);
        }
        
        if (is_array($array)) {
            if (isset($key)) {
                $array[$key] = $value;
            } else {
                $array[] = $value;
            }
            $context->set($array_name,$array);
        }
    }
}


function pmb_H2O_recurse_object($object,$property){
    if(is_object($object)){
        if ((isset($object->{$property}) || method_exists($object, '__get'))) {
            return $object->{$property};
        }
        if (method_exists($object, $property)) {
            return call_user_func_array(array($object, $property), array());
        }
        if (method_exists($object, "get_".$property)) {
            return call_user_func_array(array($object, "get_".$property), array());
        }
        if (method_exists($object, "get".ucfirst($property))) {
            return call_user_func_array(array($object, "get".ucfirst($property)), array());
        } 
        if (method_exists($object, "is_".$property)) {
            return call_user_func_array(array($object, "is_".$property), array());
        }
        if (method_exists($object, "is".ucfirst($property))) {
            return call_user_func_array(array($object, "is".ucfirst($property)), array());
        }
    }
    return null;
}


function imgLookup($name, $context) {
	$value = null;
	$img = str_replace(":img.","",$name);
	if($img != $name) {
		$value = get_url_icon($img);
	}
	return $value;
}

function sessionLookup($name, $context) {
	$session = str_replace(":session.", "", $name);
	if ($session != $name) {
		if (isset($_SESSION[$session])) {
			return $_SESSION[$session];
		}
	}
	return null;
}

function messagesLookup($name,$context){
	global $msg;
	$value = null;
	$code = str_replace(":msg.","",$name);
	if($code != $name && isset($msg[$code])){
		$value = $msg[$code];
	}
	return $value;
}

function cmsLookup($name,$context){
	$type = substr($name, strpos($name, ':')+1, strpos($name, '.')-1);
	$code = str_replace(":".$type.".","",$name);
	$obj = null;
	if($type == "article" || $type == "section"){
		$attributes = explode('.', $code);
		$id = array_shift($attributes);
		
		if($id && is_numeric($id)){
			$cms_class = 'cms_'.$type;
			$obj = new $cms_class($id);
			$obj = $obj->format_datas();
			for($i=0 ; $i<count($attributes) ; $i++){
				$attribute = $attributes[$i];
				if(is_array($obj)){
					$obj = $obj[$attribute];
				} else if(is_object($obj)){
					if (is_object($obj) && (isset($obj->{$attribute}) || method_exists($obj, '__get'))) {
						$obj = $obj->{$attribute};
					} else if (method_exists($obj, $attribute)) {
						$obj = call_user_func_array(array($obj, $attribute), array());
					} else if (method_exists($obj, "get_".$attribute)) {
						$obj = call_user_func_array(array($obj, "get_".$attribute), array());
					} else if (method_exists($obj, "is_".$attribute)) {
						$obj = call_user_func_array(array($obj, "is_".$attribute), array());
					} else {
						$obj = null;
					}
				} else{
					$obj = null;
					break;
				}
			}
		}
	}
	return $obj;
}

function globalLookup($name, $context) {
	$global = str_replace(":global.", "", $name);
	if ($global != $name) {
		global ${$global};

		if (isset(${$global})) {
			return ${$global};
		}
	}
	return null;
}

function recursive_lookup($name,$context) {
	$obj = null;
	$attributes = explode('.', $name);
	$first=true;

	// on fait une "récursion" sur chaque attribut
	for($i=0 ; $i<count($attributes) ; $i++){
	    $attribute = $attributes[$i];
	    //le premier commence par ":"
	    if($i=== 0){
	        $attribute = substr($attributes[0], 1);
	    }
	    //Au premier coup, le premier attributt peut lui aussi être en "lazyload"
	    if($first){
	        //On regarde dans le contexte
    	    foreach ($context->scopes as $layers) {
    	    	if (isset($layers[$attribute])) {
    	    		$obj = $layers[$attribute];
    	    		$first = false;
    	    		break;
    	    	} 
    	        // Pour chaque élément poussé dans le contexte
        	    foreach ($layers as $layer){
        	        // On regarde si c'est dans un objet
        	        $obj = pmb_H2O_recurse_object($layer,$attribute);
        	        if($obj !== null){
        	            // On s'assure de ne pas repasser dans ce cas pour le reste de la "récursion"
        	            $first = false;
        	            break(2);
        	        }
        	    }
        	}
	    }else{
	        // La récupération d'un élement de tableau ne fonctionne que pour le premier "niveau", après c'est à vérifier à la main
	        if(is_array($obj)){
	            if(isset($obj[$attribute])){
	                $obj = $obj[$attribute];
	            }else{
	                $obj=null;
	            }
	        }else{
	            $obj = pmb_H2O_recurse_object($obj,$attribute);
	        }
	    }
	    // Si obj est null à cet instant, on évite de continuer le traitement pour rien
	    if($obj === null){
	        return null;
	    }
	}
    return $obj;
}


function session_varsLookup($name, $context) {
	global $id_empr,
	$empr_cb,
	$empr_nom,
	$empr_prenom,
	$empr_adr1,
	$empr_adr2,
	$empr_cp,
	$empr_ville,
	$empr_mail,
	$empr_tel1,
	$empr_tel2,
	$empr_prof,
	$empr_year,
	$empr_categ,
	$empr_codestat,
	$empr_sexe,
	$empr_login,
	$empr_ldap,
	$empr_location,
	$empr_date_adhesion,
	$empr_date_expiration,
	$empr_statut;
	
	$value = null;
	
	$datas = array();
	$datas['session_vars']['view'] = (isset($_SESSION['opac_view']) ? $_SESSION['opac_view'] : '');
	$datas['session_vars']['id_empr'] = $_SESSION['id_empr_session'];
	$datas['session_vars']['empr_cb'] =$empr_cb;
	$datas['session_vars']['empr_nom'] = $empr_nom;
	$datas['session_vars']['empr_prenom'] = $empr_prenom;
	$datas['session_vars']['empr_adr1'] = $empr_adr1;
	$datas['session_vars']['empr_adr2'] = $empr_adr2;
	$datas['session_vars']['empr_cp'] = $empr_cp;
	$datas['session_vars']['empr_ville'] = $empr_ville;
	$datas['session_vars']['empr_mail'] = $empr_mail;
	$datas['session_vars']['empr_tel1'] = $empr_tel1;
	$datas['session_vars']['empr_tel2'] = $empr_tel2;
	$datas['session_vars']['empr_prof'] = $empr_prof;
	$datas['session_vars']['empr_year'] = $empr_year;
	$datas['session_vars']['empr_categ'] = $empr_categ;
	$datas['session_vars']['empr_codestat'] = $empr_codestat;
	$datas['session_vars']['empr_sexe'] = $empr_sexe;
	$datas['session_vars']['empr_login'] = $empr_login;
	$datas['session_vars']['empr_location'] = $empr_location;
	$datas['session_vars']['empr_date_adhesion'] = $empr_date_adhesion;
	$datas['session_vars']['empr_date_expiration'] = $empr_date_expiration;
	$datas['session_vars']['empr_statut'] = $empr_statut;
	
	$code = str_replace(":session_vars.","",$name);
	if ($code != $name && isset($datas['session_vars'][$code])) {
		$value = $datas['session_vars'][$code];
	}
	return $value;
}

function env_varsLookup($name, $context) {
	global $opac_url_base;

	$value = null;

	$datas = array();
	$datas['env_vars']['script'] = basename($_SERVER['SCRIPT_NAME']);
	$datas['env_vars']['request'] = basename($_SERVER['REQUEST_URI']);
	$datas['env_vars']['opac_url'] = $opac_url_base;
	$datas['env_vars']['browser'] = cms_module_root::get_browser();
	$datas['env_vars']['platform'] = cms_module_root::get_platform();
	$datas['env_vars']['server_addr'] = $_SERVER['SERVER_ADDR'];
	$datas['env_vars']['remote_addr'] = $_SERVER['REMOTE_ADDR'];

	$code = str_replace(":env_vars.","",$name);
	if ($code != $name && isset($datas['env_vars'][$code])) {
		$value = $datas['env_vars'][$code];
	}
	return $value;
}

class H2o_collection {
	protected static $h2o_collection;
	
	/**
	 *
	 * @param string $file
	 * @param array $options
	 * @return H2o
	 */
	public static function get_instance($file, $options = array()) {
		if (!isset(static::$h2o_collection)) {
			static::$h2o_collection = array();
		}
		if (!isset(static::$h2o_collection[$file])) {
			static::$h2o_collection[$file] = array();
		}
		if (!isset(static::$h2o_collection[$file][serialize($options)])) {
			static::$h2o_collection[$file][serialize($options)] = new H2o($file, $options);
		} else {
			$e = new Exception();
			$trace = $e->getTrace();
			$loop = false;
			$count_trace = count($trace);
			for ($i = 2; $i < $count_trace; $i++) {
				if ($trace[$i]['function'] == $trace[1]['function']) {
				    $loop = true;
					if (isset($trace[$i]['argument']) && ($trace[$i]['argument'] != $trace[1]['argument'])) {
                        $loop = false;
					}
				}
			}
			if ($loop) {
				return new H2o($file, $options);
			}
		}
		return static::$h2o_collection[$file][serialize($options)];
	}
	
	public static function addLookup($lookup) {
    	if (is_callable($lookup)) {
			if (!in_array($lookup, H2o_Context::$lookupTable)) {
				H2o_Context::$lookupTable[] = $lookup;
			}
		} else {
			die('damm it');
		}
	}
}
//pour ajouter un fichier perso provenant d'un git client
if (file_exists($include_path."/ext_pmb_h2o.inc.php")) {
    require_once $include_path."/ext_pmb_h2o.inc.php";
}

h2o::addTag(array("sqlvalue"));
h2o::addTag(array("sparqlvalue"));
h2o::addTag(array("tplnotice"));
h2o::addTag(array("imgbase64"));
h2o::addTag(array("setvalue"));
h2o::addTag(array("setglobalvalue"));
h2o::addTag(array("frbrcadre"));
h2o::addTag(array("arraysort"));
h2o::addTag(array("arrayunique"));
h2o::addTag(array("arrayadd"));

h2o::addFilter(array('pmb_StringFilters'));
h2o::addFilter(array('pmb_DateFilters'));
h2o::addFilter(array('pmb_CoreFilters'));

H2o::addLookup("imgLookup");
H2o::addLookup("globalLookup");
H2o::addLookup("cmsLookup");
H2o::addLookup("sessionLookup");
H2o::addLookup("messagesLookup");
H2o::addLookup("recursive_lookup");
H2o::addLookup("session_varsLookup");
H2o::addLookup("env_varsLookup");