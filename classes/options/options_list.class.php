<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: options_list.class.php,v 1.1.2.2 2021/07/02 09:32:57 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once ($class_path."/options/options.class.php");

/**
 * TODO : à câbler sur la refonte
 * @author dgoron
 *
 */
class options_list extends options {
    
	public function init_default_parameters() {
		parent::init_default_parameters();
		$this->parameters['MULTIPLE'][0]['value'] = '';
		$this->parameters['AUTORITE'][0]['value'] = '';
		$this->parameters['CHECKBOX'][0]['value'] = '';
		$this->parameters['CHECKBOX_NB_ON_LINE'][0]['value'] = '';
		$this->parameters['NUM_AUTO'][0]['value'] = '';
		$this->parameters['UNSELECT_ITEM'][0]['VALUE'] = '';
		$this->parameters['UNSELECT_ITEM'][0]['value'] = '';
		$this->parameters['DEFAULT_VALUE'][0]['value'] = '';
	}
	
	protected function get_hidden_fields_form() {
		global $_custom_prefixe_;
		return "<input type='hidden' name='first' value='0' />
				<input type='hidden' name='name' value='".$this->name."' />
				<input type='hidden' name='type' value='".$this->type."' />
				<input type='hidden' name='idchamp' value='".$this->idchamp."' />
				<input type='hidden' name='_custom_prefixe_' value='".$_custom_prefixe_."' />";
	}
	
	public function get_content_form() {
    	global $msg, $charset;
    	
		$content_form = $this->get_line_content_form($msg["procs_options_liste_multi"], 'MULTIPLE', 'checkbox', 'yes');
		$content_form .= $this->get_line_content_form($msg["pprocs_options_liste_authorities"], 'AUTORITE', 'checkbox', 'yes');
		$content_form .= "
		<tr>
			<td>".$msg['pprocs_options_liste_checkbox']."</td>
			<td>
				<input type='checkbox' value='yes' name='CHECKBOX' ".($this->parameters['CHECKBOX'][0]['value']=="yes" ? "checked='checked'" : "")." />
				&nbsp;".$msg['pprocs_options_liste_checkbox_nb_on_line']."<input class='saisie-2em' type='text' name='CHECKBOX_NB_ON_LINE' value='".htmlentities($this->parameters['CHECKBOX_NB_ON_LINE'][0]['value'],ENT_QUOTES,$charset)."' />
			</td>					
		</tr>";
		$content_form .= $this->get_line_form($msg["num_auto_list"], 'NUM_AUTO', 'checkbox', 'yes');
		$content_form .= "
		<tr>
			<td>".$msg['procs_options_choix_vide']."</td>
			<td>".$msg['procs_options_value']." : <input type='text' size='5' name='UNSELECT_ITEM_VALUE' value='".htmlentities($this->parameters['UNSELECT_ITEM'][0]['VALUE'],ENT_QUOTES,$charset)."' />&nbsp;".$msg['procs_options_label']." : <input type='text' name='UNSELECT_ITEM_LIB' value='".htmlentities($this->parameters['UNSELECT_ITEM'][0]['value'],ENT_QUOTES,$charset)."' /></td>
		</tr>
		<tr>
			<td>".$msg["proc_options_default_value"]."</td>
			<td>".$msg['procs_options_value']." : <input type='text' class='saisie-10em' name='DEFAULT_VALUE' value='".htmlentities($this->parameters['DEFAULT_VALUE'][0]['value'],ENT_QUOTES,$charset)."' /></td>
		</tr>
		";
        return $content_form;
    }
    
    protected function get_additional_content_form() {
    	global $msg, $charset, $_custom_prefixe_;
    	global $ITEM, $DEL, $ORDRE, $VALUE;
    	
    	$content_form = "
		<hr />".$msg['procs_options_liste_options']."<br />";
		
		if ($this->idchamp) {
			$content_form .= "<table border=1>
				<tr>
					<td></td>
					<td><b>".$msg["parperso_options_list_value"]."</b></td>
					<td><b>".$msg["parperso_options_list_lib"]."</b></td>
					<td><b>".$msg["parperso_options_list_order"]."</b></td>
				</tr>\n";
			$n=0;
			$requete="SELECT datatype FROM ".$_custom_prefixe_."_custom WHERE idchamp = $this->idchamp";
			$resultat = pmb_mysql_query($requete);
			$dtype = pmb_mysql_result($resultat,0,0);
		
			for ($i=0; $i<count($ITEM); $i++) {
			    if (!isset($DEL[$i]) || (isset($DEL[$i]) && $DEL[$i] != 1)) {
					//Recherche de la valeur dans les notices
					$is_deletable=true;
					if($VALUE[$i] !== "") {
						$r_deletable="select count(".$_custom_prefixe_."_custom_origine) as C,".$_custom_prefixe_."_custom_".$dtype." as T from ".$_custom_prefixe_."_custom_values where ".$_custom_prefixe_."_custom_champ=".$this->idchamp." and ".$_custom_prefixe_."_custom_".$dtype."='".addslashes($VALUE[$i])."' GROUP BY T";
						$r_del=pmb_mysql_query($r_deletable);
						if (pmb_mysql_num_rows($r_del)) {
							$objdel = pmb_mysql_fetch_object($r_del);
							if ($objdel->T != $VALUE[$i]){
								$is_deletable=true;
							}elseif($objdel->C > 0){
								$is_deletable=false;
							}else{
								$is_deletable=true;
							}
						}
					}
					$content_form .= "<tr><td ".(!$is_deletable?"title='".htmlentities($msg['perso_field_used'],ENT_QUOTES,$charset)."' ":"")."><input type=\"hidden\" name=\"EXVAL[]\" value=\"".htmlentities($VALUE[$i],ENT_QUOTES,$charset)."\"><input type=\"checkbox\" name=\"DEL[$n]\" value=\"1\" ".(!$is_deletable?"disabled='disabled' ":"")."></td>
						<td ".(!$is_deletable?"title='".htmlentities($msg['perso_field_used'],ENT_QUOTES,$charset)."' ":"")."><input class='saisie-10em' type=\"text\" value=\"".htmlentities($VALUE[$i],ENT_QUOTES,$charset)."\" name=\"VALUE[]\" ".(!$is_deletable?"readonly='readonly' ":"")."></td>
						<td><input class='saisie-20em' type=\"text\" value=\"".htmlentities($ITEM[$i],ENT_QUOTES,$charset)."\" name=\"ITEM[]\"></td>";
					$content_form .= "<td><input class='saisie-10em' type=\"text\" value=\"".htmlentities($ORDRE[$i],ENT_QUOTES,$charset)."\" name=\"ORDRE[]\"></td>";
					$content_form .= "</tr>";
					$n++;
				}
			}
			$content_form .= "</table>";
		} else {
			$content_form .= "<b>".$msg["parperso_options_list_before_rec"]."</b>";
		}
		return $content_form;
    }
    
    protected function get_buttons_form() {
    	global $msg;
    	
    	$buttons = '';
    	if ($this->idchamp) {
    		$buttons .= "
				<input class='bouton' type='submit' value='".$msg['ajouter']."' onClick='this.form.first.value=2' />&nbsp;
				<input class='bouton' type='submit' value='".$msg['procs_options_suppr_options_coche']."' onClick='this.form.first.value=3' />&nbsp;
				<input class='bouton' type='submit' value='".$msg["proc_options_sort_list"]."' onClick='this.form.first.value=4' />&nbsp;
			";
		}
		$buttons .= "<input class='bouton' type='submit' value='".$msg[77]."' onClick='this.form.first.value=1'>";
    	return $buttons;
    }
    
    public function set_parameters_from_form() {
    	global $msg, $_custom_prefixe_;
    	global $MULTIPLE, $AUTORITE, $CHECKBOX, $NUM_AUTO;
    	global $VALUE, $ITEM, $ORDRE;
    	global $UNSELECT_ITEM_VALUE, $UNSELECT_ITEM_LIB, $DEFAULT_VALUE, $CHECKBOX_NB_ON_LINE;
    	
    	parent::set_parameters_from_form();
    	$this->parameters['MULTIPLE'][0]['value'] = "no";
    	if ($MULTIPLE == "yes") {
    		$this->parameters['MULTIPLE'][0]['value'] = "yes";
    	}
    	
    	$this->parameters['AUTORITE'][0]['value'] = "no";
    	if ($AUTORITE == "yes") {
    		$this->parameters['AUTORITE'][0]['value'] = "yes";
    	}
    	
    	$this->parameters['CHECKBOX'][0]['value'] = "no";
    	if ($CHECKBOX == "yes") {
    		$this->parameters['CHECKBOX'][0]['value'] = "yes";
    	}
    	
    	$this->parameters['NUM_AUTO'][0]['value'] = "no";
    	if ($NUM_AUTO == "yes") {
    		$this->parameters['NUM_AUTO'][0]['value'] = "yes";
    	}
    	
    	/*
    	 * On regarde si il n'y a pas un doubon dans les valeurs
    	 */
    	//On enlève les valeurs vide
    	if (!$VALUE) $VALUE = array();
    	foreach ( $VALUE as $key => $value ) {
    		if($value === ""){
    			unset($VALUE[$key]);
    			unset($ITEM[$key]);
    			unset($ORDRE[$key]);
    		}
    	}
    	//Pour que les clés se suivent
    	$VALUE=array_merge($VALUE);
    	$ITEM=array_merge($ITEM);
    	$ORDRE=array_merge($ORDRE);
    	//Pour tester si il y a des doublons
    	$temp=array_flip($VALUE);
    	if(is_array($VALUE) && (count($temp) != count($VALUE))){
	
	    	print "<script>
				alert('".$msg["parperso_valeur_existe_liste"]."');
				history.go(-1);
			</script>";
			exit();
		}
	
		if ($this->idchamp) {
			$requete="delete from ".$_custom_prefixe_."_custom_lists where ".$_custom_prefixe_."_custom_champ=".$this->idchamp;
			pmb_mysql_query($requete);
			$requete="SELECT datatype FROM ".$_custom_prefixe_."_custom WHERE idchamp = $this->idchamp";
			$resultat = pmb_mysql_query($requete);
			$dtype = pmb_mysql_result($resultat,0,0);
		}
		for ($i=0; $i<count($ITEM); $i++) {
			if($VALUE[$i] !== "") {
				/* On ne met pas a jour car on ne peut modifier que les valeurs qui ne sont pas utilisées*/
				/*
				$requete="UPDATE ".$_custom_prefixe_."_custom_values SET ".$_custom_prefixe_."_custom_".$dtype." = '".$VALUE[$i]."' WHERE  ".$_custom_prefixe_."_custom_champ = $this->idchamp AND ".$_custom_prefixe_."_custom_$dtype = '".$EXVAL[$i]."'";
				pmb_mysql_query($requete);*/
				$requete="insert into ".$_custom_prefixe_."_custom_lists (".$_custom_prefixe_."_custom_champ, ".$_custom_prefixe_."_custom_list_value, ".$_custom_prefixe_."_custom_list_lib, ordre) values(".$this->idchamp.", '".$VALUE[$i]."','".$ITEM[$i]."','".$ORDRE[$i]."')";
				pmb_mysql_query($requete);
			}			
		}
		
		$this->parameters['UNSELECT_ITEM'][0]['VALUE']=stripslashes($UNSELECT_ITEM_VALUE);
		$this->parameters['UNSELECT_ITEM'][0]['value']="<![CDATA[".stripslashes($UNSELECT_ITEM_LIB)."]]>";	
		$this->parameters['DEFAULT_VALUE'][0]['value']=stripslashes($DEFAULT_VALUE);
		$this->parameters['CHECKBOX_NB_ON_LINE'][0]['value']=stripslashes($CHECKBOX_NB_ON_LINE);
    }
}
?>