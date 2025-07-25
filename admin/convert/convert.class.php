<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: convert.class.php,v 1.12.2.1 2023/04/21 12:32:37 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once ("$include_path/parser.inc.php");
require_once ($base_path."/admin/convert/start_import.class.php");
require_once ($base_path."/admin/convert/start_export.class.php");

//R�cup�ration du chemin du fichier de param�trage de l'import
function _item_convert_($param) {
	global $export_type;
	global $i;
	global $param_path;
	global $export_type_l;

	if ($i == $export_type) {
		$param_path = $param['PATH'];
		$export_type_l = $param['NAME'];
	}
	$i ++;
}

function _item_export_list_convert_($param) {
	global $export_list;
	global $i, $iall;
	
	if ($param["EXPORT"]=="yes") {
		$t=array();
		$t["NAME"]=$param["EXPORTNAME"];
		$t["PATH"]=$param["PATH"];
		$t["ID"]=$i;
		$t["IDALL"]=$iall;
		$export_list[]=$t;
		$i++;
	}
	$iall++;
}

//R�cup�ration du param�tre d'import
function _output_convert_($param) {
	global $output;
	global $output_type;
	global $output_params;

	$output = $param['IMPORTABLE'];
	$output_type = $param['TYPE'];
	$output_params = $param;
}

function _input_convert_($param) {
	global $specialexport;
	global $input_type;
	global $input_params;

	$input_type = $param['TYPE'];
	$input_params = $param;
	
	if (isset($param["SPECIALEXPORT"]) && $param["SPECIALEXPORT"]=="yes") {
		$specialexport=true; 
	} else $specialexport=false;
}

//R�cup�ration des �tapes de conversion
function _step_convert_($param) {
	global $step;

	$step[] = $param;
}

//R�cup�ration du nom de l'import
function _import_name_convert_($param) {
	global $import_name;

	$import_name = $param['value'];
}

class convert {

	public $export_type;
	public $id_notice;
	public $prepared_notice;
	public $output_notice;
    public $message_convert;
    public $error;
    
    public function __construct($notice,$type_convert,$do_not_start_yet=false) {
		global $i;
		global $param_path;
		global $specialexport;
		global $output_type;
		global $output_params;
		global $step;
		global $export_type;
		global $base_path;
		global $class_path;
		global $include_path;
		global $msg;
		
		$step=array();    		
		
		$this->export_type=$type_convert;
		$export_type=$type_convert;
			
		//R�cup�ration du r�pertoire
		$i = 0;
		$param_path = "";
		if (file_exists("$base_path/admin/convert/imports/catalog_subst.xml")) {
			$fic_catal = "$base_path/admin/convert/imports/catalog_subst.xml";
		} else {
			$fic_catal = "$base_path/admin/convert/imports/catalog.xml";
		}
		_parser_($fic_catal, array("ITEM" => "_item_convert_"), "CATALOG");

		//Lecture des param�tres
		_parser_("$base_path/admin/convert/imports/".$param_path."/params.xml", array("IMPORTNAME" => "_import_name_convert_","STEP" => "_step_convert_","OUTPUT" => "_output_convert_","INPUT" => "_input_convert_"), "PARAMS");
		
		//En fonction du type de fichier de sortie, inclusion du script de gestion des sorties
		$output_instance = start_export::get_instance_from_output_type($output_type);
			
		$this->prepared_notice=$notice;
		if (!$do_not_start_yet) {
			$this->output_notice.=$this->transform();
		}
    }
    
    public function get_exports() {
    	global $export_list;
    	global $i, $iall;
    	global $base_path;
    	$i=0;
    	$iall=0;
		if (file_exists("$base_path/admin/convert/imports/catalog_subst.xml"))
			$fic_catal = "$base_path/admin/convert/imports/catalog_subst.xml";
		else
			$fic_catal = "$base_path/admin/convert/imports/catalog.xml";
		
		_parser_($fic_catal, array("ITEM" => "_item_export_list_convert_"), "CATALOG");
    	return $export_list;
    }
    
    public function get_header() {
    	global $output_params;
    	global $output_type;
    	
    	if(isset($output_params['SCRIPT'])) {
    		$class_name = str_replace('.class.php', '', $output_params['SCRIPT']);
    		if(class_exists($class_name)) {
    			$instance = new $class_name();
    			return $instance->_get_header_($output_params);
    		}
    	}
    	$output_instance = start_export::get_instance_from_output_type($output_type);
    	if(is_object($output_instance)) {
    		return $output_instance->_get_header_($output_params);
    	} else {
    		return _get_header_($output_params);
    	}
    }
    
    public function get_footer() {
    	global $output_params;
    	global $output_type;
    	
    	if(isset($output_params['SCRIPT'])) {
    		$class_name = str_replace('.class.php', '', $output_params['SCRIPT']);
    		if(class_exists($class_name)) {
    			$instance = new $class_name();
    			return $instance->_get_footer_($output_params);
    		}
    	}
    	$output_instance = start_export::get_instance_from_output_type($output_type);
    	if(is_object($output_instance)) {
    		return $output_instance->_get_footer_($output_params);
    	} else {
    		return _get_footer_($output_params);
    	}
    }
    
    public function transform() {
   		global $step;
		global $param_path;
		global $n_errors;
		global $message_convert;
    	global $input_type;
    	global $base_path;
    	global $include_path;
    	global $class_path;
    	global $input_params;
    	global $msg;
    	
    	$notice=$this->prepared_notice;
    	
    	//Inclusion des librairies �ventuelles
		for ($i = 0; $i < count($step); $i ++) {
			if ($step[$i]['TYPE'] == "custom") {
				//echo "imports/".$param_path."/".$step[$i][SCRIPT][0][value];
				require_once ("$base_path/admin/convert/imports/".$param_path."/".$step[$i]['SCRIPT'][0]['value']);
			}
		}

		require_once ("$base_path/admin/convert/xmltransform.php");

		//En fonction du type de fichier d'entr�e, inclusion du script de gestion des entr�es
		$input_instance = start_import::get_instance_from_input_type($input_type);

		for ($i = 0; $i < count($step); $i ++) {
			$s = $step[$i];
			$islast=($i==count($step)-1);
			$isfirst=($i==0);
			switch ($s['TYPE']) {
					case "xmltransform" :
						$r = perform_xslt($notice, $s, $islast, $isfirst, $param_path);
						break;
					case "toiso" :
						$r = toiso($notice, $s, $islast, $isfirst, $param_path);
						break;
					case "isotoxml" :
						$r = isotoxml($notice, $s, $islast, $isfirst, $param_path);
						break;
					case "texttoxml":
						$r = texttoxml($notice, $s, $islast, $isfirst, $param_path);
						break;
					case "custom" :
						eval("\$r=".$s['CALLBACK'][0]['value']."(\$notice, \$s, \$islast, \$isfirst, \$param_path);");
						break;
			}
			if (!$r['VALID']) {
				$this->n_errors=true;
				$this->message_convert= $r['ERROR'];
				$notice = "";
				break;
			} else {
				$notice = $r['DATA'];
				if(!empty($r['WARNING'])){
					$this->n_errors=true;
					$this->message_convert= $r['WARNING'];
				}
			}
		}
		return $notice;
    }

	// R�cup�ration de l'id � partir du nom de l'export
	public function get_id_by_path($path) {
	   	global $export_list;
		if (empty($export_list)) start_export::get_exports() ;
		for ($i=0;$i<count($export_list);$i++) {
			if ($export_list[$i]["PATH"]==$path) return $export_list[$i]["IDALL"] ;
		}
	}

	protected static function get_converted_field_uni($zone, $ss_zone, $value, $others_ss_zone = array(), $ind = '') {
		global $charset;
		
		$data = "";
		if ($value) {
			$data.="  <f c='".$zone."' ind='  '>\n";
			$data.="    <s c='".$ss_zone."'>".htmlspecialchars(trim($value),ENT_QUOTES,$charset)."</s>\n";
			if(!empty($others_ss_zone)) {
				foreach ($others_ss_zone as $ss_zone_code=>$ss_zone_value) {
					$data.="    <s c='".$ss_zone_code."'>".htmlspecialchars(trim($ss_zone_value),ENT_QUOTES,$charset)."</s>\n";
				}
			}
			$data.="  </f>\n";
		}
		return $data;
	}
	
	/**
	 * Permet de convertir une chaine avec le bon encodage (global $charset)
	 * Si l'encodage n'a pas fonctionne, retourne la chaine initiale
	 *
	 * @param string $str
	 * @return string
	 */
	public static function convert_encoding($str) {
		global $charset;
		
		if(function_exists("mb_convert_encoding")){
			$encoding = mb_detect_encoding($str, ["UTF-8", "ISO-8859-1"]);
			if ($encoding && (strtolower($encoding) != $charset)) {
				$convert = mb_convert_encoding($str, $charset, $encoding);
				return $convert ? $convert : $str;
			}
		}
		return $str;
	}
}

?>