<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: parameter.class.php,v 1.1.4.1 2021/11/16 13:53:08 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class parameter {

	public function __construct() {
	}

	public static function update($type_param, $sstype_param, $valeur_param) {
		if(empty($type_param) || empty($sstype_param)) {
			return false;
		}
		$varGlobal = $type_param."_".$sstype_param;
		global ${$varGlobal};
		//on enregistre dans la variable globale
		${$varGlobal} = $valeur_param;
		//puis dans la base
		$query = "update parametres set valeur_param='".addslashes($valeur_param)."' where type_param='".$type_param."' and sstype_param='".$sstype_param."'";
		pmb_mysql_query($query);
	}
	
	public static function get_input_activation($type_param, $sstype_param, $valeur_param) {
		global $msg, $javascript_path;
		
		$display = "
			<script type='text/javascript' src='".$javascript_path."/parameter.js'></script>
			<input type='checkbox' class='switch' id='parameter_".$type_param."_".$sstype_param."' name='parameter_".$type_param."_".$sstype_param."' value='1' ".($valeur_param ? "checked='checked'" : "")." onclick=\"parameter_update('".$type_param."', '".$sstype_param."', (this.checked ? 1 : 0));\"/>
			<label for='parameter_".$type_param."_".$sstype_param."'>
				<span id='parameter_".$type_param."_".$sstype_param."_activated' style='color:green;".(!$valeur_param ? " display:none;" : "")."'>".$msg['activated']."</span>
				<span id='parameter_".$type_param."_".$sstype_param."_disabled' style='color:red;".($valeur_param ? " display:none;" : "")."'>".$msg['disabled']."</span>
			</label>
			";
		return $display;
	}
} /* fin de définition de la classe */


