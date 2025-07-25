<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scheduler_tasks_type.class.php,v 1.5.6.2 2023/05/26 14:07:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($include_path."/parser.inc.php");
require_once($include_path."/templates/taches.tpl.php");
require_once($include_path."/connecteurs_out_common.inc.php");
require_once($class_path."/upload_folder.class.php");
require_once($class_path."/xml_dom.class.php");
require_once($class_path."/scheduler/scheduler_task.class.php");

class scheduler_tasks_type {
	
	protected $id;						// identifiant du type de t�che
	protected $name;					// nom du type de t�che
	protected $path;					// chemin du type de t�che
	protected $comment;					// commentaire sur le type de t�che
	protected $number;
	protected $parameters;
	
	protected $timeout;					// Temps limite d'ex�cution
	protected $histo_day;				// Historique de conservation en jour 
	protected $histo_number;			// Historique de conservation en nombre
	protected $restart_on_failure;		// Replanifier la t�che automatiquement en cas d'�chec
	protected $alert_mail_on_failure;	// Alerter par mail en cas d'�chec ?
	protected $mail_on_failure='';		// Adresses mails destinataires
	
	protected $states;					// listing des �tats
	protected $commands;				// listing des commandes
	protected $dir_upload_boolean;		// La t�che a-t-elle besoin d'un r�pertoire d'upload?
	protected $msg;						// Messages propres au type de t�che
	
	public function __construct($id=0) {
		$this->id = intval($id);
	}
	
	//fichier de commandes
	public function parse_manifest() {
		global $base_path;
		$xml_commands=file_get_contents($base_path."/admin/planificateur/workflow.xml");
		$xml_dom_commands = new xml_dom($xml_commands);
		
		$filename = $base_path."/admin/planificateur/".$this->path."/manifest.xml";
		//fichier manifest sp�cifique
		$xml_manifest=file_get_contents($filename);
		$xml_dom_manifest = new xml_dom($xml_manifest);
			
		$this->states = $this->parse_states($xml_dom_commands, $xml_dom_manifest);
		$this->commands = $this->parse_commands($xml_dom_commands, $xml_dom_manifest);
		$this->dir_upload_boolean = $this->parse_dir_upload($xml_dom_manifest);
	}
	
	// listing des �tats
	public function parse_states($xml_dom_commands, $xml_dom_manifest) {
		$tab_states = array();
	
		$nodes_nostates_manifest = $xml_dom_manifest->get_nodes("manifest/capacities/nostates/state");
		$nodes_states_manifest = $xml_dom_manifest->get_nodes("manifest/capacities/states/state");
	
		$nodes_states = $xml_dom_commands->get_nodes("workflow/states/state");
		foreach ($nodes_states as $id=>$node_state) {
			$t=array();
			$state_impossible = false;
			if ($nodes_nostates_manifest) {
				foreach ($nodes_nostates_manifest as $node_nostate_manifest) {
					if (($xml_dom_manifest->get_attribute($node_nostate_manifest, "name")) == ($xml_dom_commands->get_attribute($node_state,"name"))){
						$state_impossible = true;
					}
				}
			}
			//etat possible
			if (!$state_impossible) {
				$t["id"] = $xml_dom_commands->get_attribute($node_state,"id");
				$t["name"] = $xml_dom_commands->get_attribute($node_state,"name");
				$nodes_next_states = $xml_dom_commands->get_nodes("workflow/states/state[$id]/nextState");
				$t2 = array();
				if ($nodes_next_states) {
					foreach ($nodes_next_states as $index=>$node_next_state) {
						$command_impossible = false;
						if ($nodes_states_manifest) {
							foreach ($nodes_states_manifest as $k=>$node_state_manifest) {
								if (($xml_dom_manifest->get_attribute($node_state_manifest, "name")) == ($xml_dom_commands->get_attribute($node_state,"name"))){
									$nodes_nocommands_manifest = $xml_dom_manifest->get_nodes("manifest/capacities/states/state[$k]/nocommand");
									if ($nodes_nocommands_manifest) {
										foreach ($nodes_nocommands_manifest as $node_nocommand_manifest) {
											if (($xml_dom_manifest->get_attribute($node_nocommand_manifest, "commands")) == ($xml_dom_commands->get_attribute($node_next_state,"commands"))){
												$command_impossible = true;
											}
										}
									}
								}
							}
						}
						if (!$command_impossible) {
							$t2[$index]["command"] = $xml_dom_commands->get_attribute($node_next_state,"commands");
							$t2[$index]["dontsend"] = $xml_dom_commands->get_attribute($node_next_state,"dontsend");
							$t2[$index]["value"] = $xml_dom_commands->get_value("workflow/states/state[$id]/nextState[$index]");
							$value = $index;
						}
					}
				}
				if ($nodes_states_manifest) {
					foreach ($nodes_states_manifest as $k=>$node_state_manifest) {
						if (($xml_dom_manifest->get_attribute($node_state_manifest, "name")) == ($xml_dom_commands->get_attribute($node_state,"name"))){
							$nodes_add_commands_manifest = $xml_dom_manifest->get_nodes("manifest/capacities/states/state[$k]/nextState");
							if ($nodes_add_commands_manifest) {
								foreach ($nodes_add_commands_manifest as $node_add_command_manifest) {
									//ajout des nouvelles commandes
									$value++;
									$t2[$value]["command"] = $xml_dom_manifest->get_attribute($node_add_command_manifest, "commands");
									$t2[$value]["dontsend"] = $xml_dom_manifest->get_attribute($node_add_command_manifest,"dontsend");
									$t2[$value]["value"] = $xml_dom_manifest->get_value("manifest/capacities/states/state[$k]/nextState");
								}
							}
						}
					}
				}
				$t["nextState"] = $t2;
				$tab_states[$t["name"]]=$t;
			}
		}
		return $tab_states;
	}
	
	// listing des commandes
	public function parse_commands($xml_dom_commands, $xml_dom_manifest) {
		global $msg;
	
		$tab_commands=array();
		$nodes_commands = $xml_dom_commands->get_nodes("workflow/commands/command");
		if ($nodes_commands) {
			foreach ($nodes_commands as $node_command) {
				$t=array();
				$t["id"] = $xml_dom_commands->get_attribute($node_command,"id");
				$t["name"] = $xml_dom_commands->get_attribute($node_command,"name");
				$t["label"] = $msg[str_replace("msg:", "", $xml_dom_commands->get_attribute($node_command,"label"))];
	
				$tab_commands[$t["name"]]=$t;
			}
		}
	
		$nodes_commands_manifest = $xml_dom_manifest->get_nodes("manifest/capacities/commands/command");
		if ($nodes_commands_manifest) {
			foreach ($nodes_commands_manifest as $node_command_manifest) {
				$t=array();
				$t["id"] = $xml_dom_manifest->get_attribute($node_command_manifest,"id");
				$t["name"] = $xml_dom_manifest->get_attribute($node_command_manifest,"name");
				if($xml_dom_manifest->get_attribute($node_command_manifest,"label")) {
					$t["label"] = $this->msg[str_replace("msg:", "", $xml_dom_manifest->get_attribute($node_command_manifest,"label"))];
				} else {
					$t["label"] = "";
				}
				$tab_commands[$t["name"]]=$t;
			}
		}
		return $tab_commands;
	}
	
	// Est-ce une t�che qui demande un r�pertoire d'upload pour des fichiers g�n�r�s??
	public function parse_dir_upload($xml_dom_manifest) {
		$node_directory = $xml_dom_manifest->get_node("manifest/directory_upload");
		if ($node_directory) {
			return $xml_dom_manifest->get_value("manifest/directory_upload");
		} else {
			return "0";
		}
	}
	
	//affichage du formulaire global au type de t�che
	public function get_form() {
		global $charset, $admin_planificateur_global_params;
	
		$this->fetch_global_properties();
		$admin_planificateur_global_params=str_replace("!!script_js!!","",$admin_planificateur_global_params);
		$admin_planificateur_global_params=str_replace("!!special_form!!","",$admin_planificateur_global_params);
		//Remplacement des valeurs par d�faut
		$admin_planificateur_global_params=str_replace("!!id!!",$this->id,$admin_planificateur_global_params);
		$admin_planificateur_global_params=str_replace("!!comment!!",htmlentities(scheduler_tasks::get_catalog_element($this->id, 'COMMENT'),ENT_QUOTES,$charset),$admin_planificateur_global_params);
				
		//ce type de t�che n�cessite-t-il d'un r�pertoire d'upload pour les documents num�riques?
		$admin_planificateur_global_params=str_replace("!!div_upload!!","",$admin_planificateur_global_params);
//		if ($dir_upload_boolean != "0") {
//			$up = new upload_folder($rep_upload);
//			$nom_chemin = $up->formate_nom_to_path($up->repertoire_nom.$path_upload);
//			$admin_planificateur_global_params=str_replace("!!div_upload!!","<div class='row'>
//				<div class='colonne3'><label for='timeout'/>".$msg["print_numeric_ex_title"]."</label></div>
//						<div class='colonne_suite'>
//							".$msg["planificateur_upload"]." :
//							<input type='text' name='path' id='path' value='!!path!!' class='saisie-50emr' READONLY />
//							<input type='button' id='upload_path' class='bouton' onclick='upload_openFrame(event)' value='...' name='upload_path' />
//							<input id='id_rep' type='hidden' value='!!id_rep!!' name='id_rep' />
//						</div>
//				</div>",$admin_planificateur_global_params);
//		} else {
//			$admin_planificateur_global_params=str_replace("!!div_upload!!","",$admin_planificateur_global_params);
//		}
	
		$admin_planificateur_global_params=str_replace("!!timeout!!",$this->timeout,$admin_planificateur_global_params);
		$admin_planificateur_global_params=str_replace("!!histo_day!!",$this->histo_day,$admin_planificateur_global_params);
		$admin_planificateur_global_params=str_replace("!!histo_number!!",$this->histo_number,$admin_planificateur_global_params);
		$admin_planificateur_global_params=str_replace("!!restart_on_failure_checked!!",($this->restart_on_failure ? "checked=checked" : ""),$admin_planificateur_global_params);
		$params_alert_mail = explode(",",$this->alert_mail_on_failure);
		$admin_planificateur_global_params=str_replace("!!alert_mail_on_failure_checked!!",($params_alert_mail[0] ? " checked=checked " : ""),$admin_planificateur_global_params);
		$admin_planificateur_global_params=str_replace("!!mail_on_failure!!",(isset($params_alert_mail[1]) ? $params_alert_mail[1] : ''),$admin_planificateur_global_params);
		return $admin_planificateur_global_params;
	}
	
	public function get_params() {
		$t = array();
		$t["timeout"] = $this->timeout;
		$t["histo_day"] = $this->histo_day;
		$t["histo_number"] = $this->histo_number;
		$t["restart_on_failure"] = $this->restart_on_failure;
		$t["alert_mail_on_failure"] = $this->alert_mail_on_failure;
		return $t;
	}
	
	public function set_properties_from_form() {
		global $timeout, $histo_day, $histo_number, $restart_on_failure, $alert_mail_on_failure, $mail_on_failure; 
		
		$this->timeout=intval($timeout);
		$this->histo_day=intval($histo_day);
		$this->histo_number=intval($histo_number);
		$this->restart_on_failure=($restart_on_failure ? "1" : "0");
		$this->alert_mail_on_failure=($alert_mail_on_failure ? "1" : "0").($mail_on_failure ? ",".$mail_on_failure : "");
	}
	
	//Sauvegarde des propri�t�s g�n�rales
	public function save_global_properties() {
		$query = "replace into taches_type (id_type_tache,parameters, timeout, histo_day, histo_number, restart_on_failure, alert_mail_on_failure) values('".$this->id."',
		'".serialize($this->parameters)."','".$this->timeout."','".$this->histo_day."','".$this->histo_number."','".$this->restart_on_failure."','".$this->alert_mail_on_failure."')";
		return pmb_mysql_query($query);
	}
	
	public function fetch_default_global_values() {
		$this->parameters="";
		$this->timeout=15;
		$this->histo_day=7;
		$this->histo_number=3;
		$this->restart_on_failure=0;
		$this->alert_mail_on_failure=0;
	}
	
	//Propri�tes globales d'un type de tache du planificateur (timeout, histo_day, ...)
	public function fetch_global_properties() {
		$query="select parameters, timeout, histo_day, histo_number, restart_on_failure, alert_mail_on_failure from taches_type where id_type_tache='".$this->id."'";
		$resultat=pmb_mysql_query($query);
		if ($resultat && pmb_mysql_num_rows($resultat)) {
			$r=pmb_mysql_fetch_object($resultat);
			$this->parameters=unserialize($r->parameters);
			$this->timeout=$r->timeout;
			$this->histo_day=$r->histo_day;
			$this->histo_number=$r->histo_number;
			$this->restart_on_failure=$r->restart_on_failure;
			$this->alert_mail_on_failure=$r->alert_mail_on_failure;
		} else {
			$this->fetch_default_global_values();
		}
	}
	
	public function get_number() {
		if(!isset($this->number)) {
			$res = pmb_mysql_query("select * from planificateur where num_type_tache=".$this->id);
			$this->number = pmb_mysql_num_rows($res);
		}
		return $this->number;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_name() {
		return $this->name;
	}
	
	public function set_name($name) {
		$this->name = $name;
	}
	
	public function get_path() {
		return $this->path;
	}
	
	public function set_path($path) {
		$this->path = $path;
	}
	
	public function get_comment() {
		return get_msg_to_display($this->comment);
	}
	
	public function set_comment($comment) {
		$this->comment = $comment;
	}
	
	public function get_states() {
		if(!isset($this->states)) {
			$this->parse_manifest();
		}
		return $this->states;
	}
	
	public function get_commands() {
		if(!isset($this->commands)) {
			$this->parse_manifest();
		}
		return $this->commands;
	}
}