<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scheduler_planning.class.php,v 1.17.4.3 2023/05/26 14:07:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
	
global $class_path;
require_once($class_path."/scheduler/scheduler_task_calendar.class.php");
require_once($class_path."/scheduler/scheduler_tasks.class.php");

class scheduler_planning {
	
	protected $id;
	
	protected $id_type;
	
	protected $libelle_tache;
	
	protected $desc_tache;
	
	protected $num_user;
	
	protected $param;
	
	protected $statut;
	
	protected $rep_upload;
	
	protected $path_upload;
	
	protected $perio_heure;
	
	protected $perio_minute;
	
	protected $perio_jour_mois;
	
	protected $perio_jour;
	
	protected $perio_mois;
	
	protected $calc_next_heure_deb;
	
	protected $calc_next_date_deb;
	
	protected $repertoire_nom;
	
	protected $repertoire_path;
	
	protected $msg;
	
	public function __construct($id=0) {
		$this->id = intval($id);
		if(static::class != 'scheduler_planning') {
			$this->get_messages();
		}
	}
	
	//messages
	public function get_messages() {
		global $base_path, $lang;
	
		$tache_path = $base_path."/admin/planificateur/".str_replace(array('scheduler_', '_planning'), '', static::class);
		if (file_exists($tache_path."/messages/".$lang.".xml")) {
			$file_name=$tache_path."/messages/".$lang.".xml";
		} else if (file_exists($tache_path."/messages/fr_FR.xml")) {
			$file_name=$tache_path."/messages/fr_FR.xml";
		}
		if ($file_name) {
			$xmllist=new XMLlist($file_name);
			$xmllist->analyser();
			$this->msg=$xmllist->table;
		}
	}
	
	//recherche les informations de la tâche planifiée si elles est existante, dans le cas d'une modif...
	public function get_property_task_bdd() {
		$query = "SELECT id_planificateur, num_type_tache, libelle_tache, desc_tache, num_user, param, statut, rep_upload, path_upload, perio_heure, perio_minute,
			perio_jour_mois, perio_jour, perio_mois, calc_next_heure_deb, calc_next_date_deb,repertoire_nom, repertoire_path
			 FROM planificateur left join upload_repertoire on rep_upload=repertoire_id
			 where id_planificateur=".$this->id;
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			$row = pmb_mysql_fetch_object($result);
			$this->id_type = $row->num_type_tache;
			$this->libelle_tache = $row->libelle_tache;
			$this->desc_tache = $row->desc_tache;
			$this->num_user = $row->num_user;
			$this->param = unserialize($row->param);
			$this->statut = $row->statut;
			$this->rep_upload = $row->rep_upload;
			$this->path_upload = $row->path_upload;
			$this->perio_heure = $row->perio_heure;
			$this->perio_minute = $row->perio_minute;
			$this->perio_jour_mois = $row->perio_jour_mois;
			$this->perio_jour = $row->perio_jour;
			$this->perio_mois = $row->perio_mois;
			$this->calc_next_heure_deb = $row->calc_next_heure_deb;
			$this->calc_next_date_deb = $row->calc_next_date_deb;
			$this->repertoire_nom = $row->repertoire_nom;
			$this->repertoire_path = $row->repertoire_path;
		} else {
			$this->libelle_tache ="";
			$this->desc_tache ="";
			$this->num_user ="";
			$scheduler_tasks_type = new scheduler_tasks_type($this->id_type);
			$scheduler_tasks_type->fetch_global_properties();
			$this->param  = $scheduler_tasks_type->get_params(); 
			$this->statut  = "1";
			$this->rep_upload  = "0";
			$this->path_upload  = "";
			$this->perio_heure  = "*";
			$this->perio_minute  = "01";
			$this->perio_jour_mois  = "";
			$this->perio_jour  = "";
			$this->perio_mois  = "";
			$this->calc_next_heure_deb  = "";
			$this->calc_next_date_deb  = "";
			$this->repertoire_nom  = "";
			$this->repertoire_path  = "";
		}
	}
	
	protected function get_users_selector() {
		global $msg;
		
		$result = pmb_mysql_query("select esuser_id, esuser_username from es_esusers");
		if (pmb_mysql_num_rows($result)) {
			$selector = "<select name='form_users'>";
			while ($row = pmb_mysql_fetch_object($result)) {
				if ($row->esuser_id == $this->num_user) {
					$selector .="<option value='".$row->esuser_id."' selected>".$row->esuser_username."</option>";
				} else {
					$selector .="<option value='".$row->esuser_id."'>".$row->esuser_username."</option>";
				}
			}
			$selector .= "</select>";
		} else {
			$selector = "* ".$msg["planificateur_task_users_unknown"];
		}
		return $selector;
	}
	
	protected function get_checkboxes($name, $selected_properties=array()) {
		global $msg;
		
		switch($name) {
			case 'chkbx_task_quotidien':
				$effective = 31;
				$msg_all = $msg["planificateur_task_all_days_of_month"];
				break;
			case 'chkbx_task_hebdo':
				$effective = 7;
				$msg_all = $msg["planificateur_task_all_days"];
				break;
			case 'chkbx_task_mensuel':
				$effective = 12;
				$msg_all = $msg["planificateur_task_all_months"];
				break;
		}
		$checkboxes = "<div class='schedulersgroupcheckbox'>";
		$checkboxes .= "<span class='scheduler_chkbx_task_all'>";
		$checkboxes .= "<input type='checkbox' id='".$name."_0' name='".$name."[]'  value='*' ".(isset($selected_properties[0]) && $selected_properties[0] == '*' ? "checked='checked'" : "" )." onchange='changePerio(this, \"*\",\"".$name."\",".$effective.");'> ".$msg_all."</input>";
		$checkboxes .= "</span>";
		for ($i=1; $i<=$effective; $i++) {
			$cochee = false;
			if(is_array($selected_properties)) {
				for ($j=0; $j<sizeof($selected_properties); $j++) {
					if ($selected_properties[$j] == $i) {
						$cochee = true;
					}
				}
			}
			$checkboxes.= "<span class='scheduler_".$name."'>";
			$checkboxes .= "<input type='checkbox' id='".$name."_".$i."' name='".$name."[]'  value='".$i."' ".($cochee == true ? "checked" : "''" )." onchange='changePerio(this, $i,\"".$name."\",".$effective.");'/> ";
			switch($name) {
				case 'chkbx_task_quotidien':
					$checkboxes .= $i." ";
					break;
				case 'chkbx_task_hebdo':
					$checkboxes .= $msg["week_days_$i"]." ";
					break;
				case 'chkbx_task_mensuel':
					$checkboxes .= ucfirst($msg[$i+1005])." ";
					break;
			}
			$checkboxes.="</span>";
		}
		$checkboxes .= "</div>";
		return $checkboxes;
	}
	// affichage du formulaire de la tâche
	public function get_form() {
		global $charset, $msg;
		global $planificateur_form, $action, $subaction;
		
		$dir_upload_boolean = 0;
		
		//Récupération des données du formulaire
		if ($subaction == "change") {
			$this->set_properties_from_form();
			//les paramètres ont été re-sérialisés dans set_properties_from_form() 
			$this->param = unserialize($this->param);
		} else {
			//Récupération des données de la base
			$this->get_property_task_bdd();
		}
		
		$form = $planificateur_form;
		$form=str_replace("!!script_js!!","
			<script type='text/javascript' src='./javascript/select.js'></script>
			<script type='text/javascript' src='./javascript/upload.js'></script>",$form);
		$form=str_replace("!!submit_action!!","return checkForm();",$form);
		$form=str_replace("!!libelle_type_task!!",scheduler_tasks::get_catalog_element($this->id_type, 'COMMENT'),$form);
		$form=str_replace("!!task_name!!",htmlentities($this->libelle_tache,ENT_QUOTES,$charset),$form);
		$form=str_replace("!!task_desc!!",htmlentities($this->desc_tache,ENT_QUOTES,$charset),$form);
		
		$form=str_replace("!!task_users!!",$this->get_users_selector(),$form);
	
		$form=str_replace("!!task_statut!!","<input type='checkbox' name='task_active' id='task_active' value='".$this->statut."' ".($this->statut ? " checked " : "''" )." onchange='changeStatut();'/>",$form);
	
		//ce type de tâche nécessite-t-il d'un répertoire d'upload pour les documents numériques?
		if ($dir_upload_boolean) {
			$up = new upload_folder($this->rep_upload);
			if ($subaction == 'change') {
				$nom_chemin = $up->formate_path_to_nom($this->path_upload);
			} else {
				$nom_chemin = $up->formate_nom_to_path($up->repertoire_nom.$this->path_upload);
			}
			$form=str_replace("!!div_upload!!","<div class='row'>
				<div class='colonne3'><label for='timeout'/>".$msg["print_numeric_ex_title"]."</label></div>
						<div class='colonne_suite'>
							".$msg["planificateur_upload"]." :
							<input type='text' name='path' id='path' value='!!path!!' class='saisie-50emr' READONLY />
							<input type='button' id='upload_path' class='bouton' onclick='upload_openFrame(event)' value='...' name='upload_path' />
							<input id='id_rep' type='hidden' value='!!id_rep!!' name='id_rep' />
						</div>
				</div>",$form);
		} else {
			$nom_chemin = "";
			$form=str_replace("!!div_upload!!","",$form);
		}
		$form = str_replace('!!path!!', htmlentities($nom_chemin ,ENT_QUOTES, $charset), $form);
		$form = str_replace('!!id_rep!!', htmlentities($this->rep_upload ,ENT_QUOTES, $charset), $form);
	
		$form=str_replace("!!task_perio_heure!!","<input type='text' id='task_perio_heure' name='task_perio_heure' value='".$this->perio_heure."' class='saisie-5em'/>",$form);
		$form=str_replace("!!task_perio_min!!","<input type='text' id='task_perio_min' name='task_perio_min' value='".$this->perio_minute."' class='saisie-5em'/>",$form);
	
		$form=str_replace("!!help!!",
				"<a onclick='openPopUp(\"./admin/planificateur/help.php?action_help=configure_time\",\"help\",500,600,-2,-2,\"scrollbars=yes,menubar=0\"); w.focus(); return false;' href='#'>
			<img style='border:0px' class='center' title='Aide...' alt='Aide...' src='".get_url_icon('aide.gif')."' /></a>",$form);
	
		$form=str_replace("!!task_perio_quotidien!!",$this->get_checkboxes('chkbx_task_quotidien', explode(',', $this->perio_jour_mois)),$form);
		$form=str_replace("!!task_perio_hebdo!!",$this->get_checkboxes('chkbx_task_hebdo', explode(',', $this->perio_jour)),$form);
		$form=str_replace("!!task_perio_mensuel!!",$this->get_checkboxes('chkbx_task_mensuel', explode(',', $this->perio_mois)),$form);
	
		$form=str_replace("!!timeout!!",$this->param["timeout"],$form);
		$form=str_replace("!!histo_day_checked!!",($this->param["histo_day"] != "" ? " checked " : ""),$form);
		$form=str_replace("!!histo_number_checked!!",($this->param["histo_number"] != "" ? " checked " : ""),$form);
		$form=str_replace("!!histo_day!!",$this->param["histo_day"],$form);
		$form=str_replace("!!histo_day_visible!!",($this->param["histo_day"] == "" ? "disabled" : ""),$form);
		$form=str_replace("!!histo_number!!",$this->param["histo_number"],$form);
		$form=str_replace("!!histo_number_visible!!",($this->param["histo_number"] == "" ? "disabled" : ""),$form);
		$form=str_replace("!!restart_on_failure_checked!!",($this->param["restart_on_failure"] ? " checked " : ""),$form);
		$params_alert_mail = explode(",",$this->param["alert_mail_on_failure"]);
		$form=str_replace("!!alert_mail_on_failure_checked!!",($params_alert_mail[0] ? " checked " : ""),$form);
		$form=str_replace("!!mail_on_failure!!",(isset($params_alert_mail[1]) ? $params_alert_mail[1] : ''),$form);
	
		//Inclusion du formulaire spécifique au type de tâche
		$form=str_replace("!!specific_form!!", $this->show_form($this->param),$form);
		
		if ($action == "duplicate") {
			$this->id = 0;
		}
		if (!$this->id) {
			$bt_save=static::format_url("&action=edit&type_id=".$this->id_type);
			$bt_duplicate="";
			$bt_suppr="";
		} else {
			$bt_save=static::format_url("&action=edit&type_id=".$this->id_type."&id=".$this->id);
			$bt_duplicate="<input type='button' class='bouton' value='".$msg["tache_duplicate_bouton"]."' onclick='document.location=\"".static::format_url("&action=duplicate&type_id=".$this->id_type."&id=".$this->id)."\"' />";
			$bt_suppr="<input type='button' class='bouton' value='".$msg["63"]."' onClick='location.href=\"".static::format_url("&action=delete&type_id=".$this->id_type."&id=".$this->id)."\"'/>";
		}
		$form=str_replace("!!bt_save!!",$bt_save,$form);
		$form=str_replace("!!bt_duplicate!!",$bt_duplicate,$form);
		$form=str_replace("!!bt_supprimer!!",$bt_suppr,$form);
	
		return $form;
	}
	
	public function show_form() {
		// à surcharger
	}
	
	public function make_serialized_task_params() {
		global $timeout, $histo_day, $histo_number, $restart_on_failure, $alert_mail_on_failure, $mail_on_failure;
	
		$t = array();
		$t["timeout"] = ($timeout != "0" ? stripslashes($timeout) : "");
		$t["histo_day"] = ($histo_day != "0" ? stripslashes($histo_day) : "");
		$t["histo_number"] = ($histo_number != "0" ? stripslashes($histo_number) : "");
		$t["restart_on_failure"] = (intval($restart_on_failure) ? "1" : "0");
		$t["alert_mail_on_failure"] = $alert_mail_on_failure.($mail_on_failure ? ",".$mail_on_failure : "");
	
		return $t;
	}
	
	protected function build_perio_property_from_form($perio_property) {
		$built = '';
		if ($perio_property[0] == '*') {
			$built .= $perio_property[0].",";
		} else {
			for ($i=0; $i<sizeof($perio_property); $i++) {
				$built .= $perio_property[$i].",";
			}
		}
		$built = ($built != '' ? substr($built,0,strlen($built)-1) : '*');
		return $built;
	}
	
	public function set_properties_from_form() {
		global $task_name,$task_desc,$form_users,$task_active;
		global $id_rep,$path;
		global $task_perio_heure, $task_perio_min, $chkbx_task_quotidien, $chkbx_task_hebdo, $chkbx_task_mensuel;
		
		$this->libelle_tache = stripslashes($task_name);
		$this->desc_tache = stripslashes($task_desc);
		$this->num_user = intval($form_users);
		
		$this->param = $this->make_serialized_task_params();

		$this->statut = intval($task_active);
		
		$this->rep_upload = intval($id_rep);
		if ($this->rep_upload && $path) {
			$up = new upload_folder($this->rep_upload);
			$this->path_upload = $up->formate_path_to_save($up->formate_path_to_nom(stripslashes($path)));
		} else {
			$this->path_upload = "";
		}
		
		$this->perio_heure = ($task_perio_heure == '' ? '00' : stripslashes($task_perio_heure));
		$this->perio_minute = ($task_perio_min == '' ? '00' : stripslashes($task_perio_min));
		
		$this->perio_jour_mois = $this->build_perio_property_from_form($chkbx_task_quotidien);
		$this->perio_jour = $this->build_perio_property_from_form($chkbx_task_hebdo);
		$this->perio_mois = $this->build_perio_property_from_form($chkbx_task_mensuel);
	}
	
	//sauvegarde des données du formulaire,
	public function save_property_form() {
		$this->set_properties_from_form();
		
		// est-ce une nouvelle tâche ??
		if (!$this->id) {
			//Nouvelle planification
			$requete="insert into planificateur (num_type_tache, libelle_tache, desc_tache, num_user, param, statut, rep_upload, path_upload, perio_heure,
				perio_minute, perio_jour_mois, perio_jour, perio_mois)
				values(".$this->id_type.",'".addslashes($this->libelle_tache)."','".addslashes($this->desc_tache)."',
				'".$this->num_user."','".addslashes($this->param)."','".$this->statut."','".$this->rep_upload."','".$this->path_upload."','".$this->perio_heure."','".$this->perio_minute."',
				'".$this->perio_jour_mois."','".$this->perio_jour."','".$this->perio_mois."')";
			pmb_mysql_query($requete);
			$this->id = pmb_mysql_insert_id();
		} else {
			//Mise à jour des informations
			$requete="update planificateur
				set num_type_tache = '".$this->id_type."',
				libelle_tache = '".addslashes($this->libelle_tache)."',
				desc_tache = '".addslashes($this->desc_tache)."',
				num_user = '".$this->num_user."',
				param = '".addslashes($this->param)."',
				statut = '".$this->statut."',
				rep_upload = '".$this->rep_upload."',
				path_upload = '".$this->path_upload."',
				perio_heure = '".$this->perio_heure."',
				perio_minute = '".$this->perio_minute."',
				perio_jour_mois = '".$this->perio_jour_mois."',
				perio_jour = '".$this->perio_jour."',
				perio_mois = '".$this->perio_mois."'
				where id_planificateur='".$this->id."'";
			pmb_mysql_query($requete);
		}
	
		//calcul de la prochaine exécution
		$this->calcul_execution();
		//Vérification des paramètres enregistrés
		$this->checkParams();
		// insertion d'une nouvelle tâche si aucune n'est planifiée
		$this->insertOfTask($this->statut);
	}
	
	//Suppression d'une planification de tâche associée à un type de tâche
	public function delete() {
		global $msg;
		global $template_result, $confirm, $disabled;
	
		//disabled == 1 then statut = 0
		$disabled = intval($disabled);
		if ($disabled) {
			if ($this->id) {
				$query = "update planificateur set statut=0 where id_planificateur=".$this->id;
				pmb_mysql_query($query);
			}
		}
		$template_result=str_replace("!!id!!", $this->id,$template_result);
		$template_result=str_replace("!!libelle_type_task!!", scheduler_tasks::get_catalog_element($this->id_type, 'COMMENT'),$template_result);
			
		//on vérifie tout d'abord que la tâche soit désactivée
		$query_active = "select statut from planificateur where id_planificateur=".$this->id;
		$result = pmb_mysql_query($query_active);
		if (pmb_mysql_num_rows($result)) {
			$value_statut = pmb_mysql_result($result, 0, "statut");
		} else {
			$value_statut = 0;
		}
	
		if (!$value_statut) {
			$body = "<div class='center'>".$msg["planificateur_confirm_phrase"]."<br />
			<a href='".static::format_url("&action=delete&type_id=".$this->id_type."&id=".$this->id."&confirm=1")."'>
			".$msg["40"]."
			</a> - <a href='".static::format_url("&type_id=".$this->id_type."&id=".$this->id."&confirm=0")."'>
			".$msg["39"]."
				</a>
				</div>
			";
		} else {
			$body = "<div class='center'>".$msg["planificateur_error_active"]."<br />
			<a href='".static::format_url("&action=delete&type_id=".$this->id_type."&id=".$this->id."&disabled=1")."'>
			".$msg["40"]."
			</a> - <a href='".static::format_url("&type_id=".$this->id_type."&id=".$this->id."&disabled=0")."'>
			".$msg["39"]."
				</a>
				</div>
			";
		}
	
		$template_result=str_replace("!!BODY!!",$body,$template_result);
	
		//Confirmation de suppression
		$confirm = intval($confirm);
		if ($confirm) {
			//Vérifie si une tâche est en cours sur cette planification
			$query_check = "select id_tache from taches where num_planificateur=".$this->id." and status <> 3";
			$result = pmb_mysql_query($query_check);
			if (pmb_mysql_num_rows($result) == '1') {
				// ne pas la supprimer !
				$ident_tache = pmb_mysql_result($result, 0,"id_tache");
			} else {
				$ident_tache = 0;
			}
			//suppression des tâches à l'exclusion de celle en cours
			$query="select id_tache from taches where num_planificateur=".$this->id."
				and id_tache <> ".$ident_tache;
			$result = pmb_mysql_query($query);
			$tasks_ids = array();
			while ($row = pmb_mysql_fetch_object($result)) {
				scheduler_log::delete('scheduler_'.scheduler_tasks::get_catalog_element($this->id_type, 'NAME').'_task_'.$row->id_tache.'.log');
				$tasks_ids[] = $row->id_tache;
			}
			if(count($tasks_ids)) {
				$query = "delete from taches
								where id_tache IN (".implode(',', $tasks_ids).")";
				pmb_mysql_query($query);
			}
			$requete="delete from planificateur where id_planificateur=".$this->id."";
			pmb_mysql_query($requete);
	
			//et les documents numériques qu'en fait-on???
	
			print "<script>document.location.href='".static::format_url()."';</script>";
		}
		return $template_result;
	}
	
	/* Calcul prochaine execution */
	public function calcul_execution() {
		if ($this->id) {
			$call_calendar = new scheduler_task_calendar($this->id);
			$jour = $call_calendar->new_date["JOUR"];
			$mois = $call_calendar->new_date["MOIS"];
			$annee = $call_calendar->new_date["ANNEE"];
			$heure = $call_calendar->new_date["HEURE"];
			$minute = $call_calendar->new_date["MINUTE"];
			if ($jour != "00") {
				$date_exec = $annee."-".$mois."-".$jour;
				$heure_exec = $heure.":".$minute;
			} else {
				$date_exec = "0000-00-00";
				$heure_exec = "00:00";
			}
		} else {
			$date_exec = "0000-00-00";
			$heure_exec = "00:00";
		}
		//mise à jour de la prochaine planification
		$query = "update planificateur set calc_next_heure_deb='".$heure_exec."', calc_next_date_deb='".$date_exec."'
		where id_planificateur=".$this->id;
		pmb_mysql_query($query);
	}
	
	//vérification de deux paramètres génériques (historique, nb exécution conservées)
	public function checkParams() {
		$requete = "select param, num_type_tache  from planificateur where id_planificateur=".$this->id;
		
		$resultat=pmb_mysql_query($requete);
		if (pmb_mysql_num_rows($resultat) > 0) {
			$r=pmb_mysql_fetch_object($resultat);
			$params=unserialize($r->param);
			$this->id_type=$r->num_type_tache;
			if ($params) {
				foreach ($params as $index=>$param) {
					if (($index == "histo_day") && ($param != "") && ($param !="0")) {
						
						$query = "select id_tache from taches where num_planificateur ='".$this->id."'
							and end_at < DATE_SUB(curdate(), INTERVAL ".$param." DAY)
							and end_at != '0000-00-00 00:00:00'";
						$result = pmb_mysql_query($query);
						$tasks_ids = array();
						while ($row = pmb_mysql_fetch_object($result)) {
							scheduler_log::delete('scheduler_'.scheduler_tasks::get_catalog_element($this->id_type, 'NAME').'_task_'.$row->id_tache.'.log');
							$tasks_ids[] = $row->id_tache;
						}
						if(count($tasks_ids)) {
							$query = "delete from taches
								where id_tache IN (".implode(',', $tasks_ids).")";
							pmb_mysql_query($query);
						}
					}
					if (($index == "histo_number") && ($param != "") && ($param !="0")) {
						//check nbre exécution
						$requete_select = "select count(*) as nbre from taches where num_planificateur =".$this->id."
								and end_at != '0000-00-00 00:00:00'";
						$result = pmb_mysql_query($requete_select);
						$nb = pmb_mysql_result($result, 0,"nbre");
	
						if ($nb > $param) {
							$nb_r = $nb - $param;
							$query = "select id_tache from taches where num_planificateur=".$this->id."
								and end_at != '0000-00-00 00:00:00'
								order by end_at ASC
								limit ".$nb_r;
							$result = pmb_mysql_query($query);
							$tasks_ids = array();
							while ($row = pmb_mysql_fetch_object($result)) {
								scheduler_log::delete('scheduler_'.scheduler_tasks::get_catalog_element($this->id_type, 'NAME').'_task_'.$row->id_tache.'.log');
								$tasks_ids[] = $row->id_tache;
							}
							if(count($tasks_ids)) {
								$query = "delete from taches
								where id_tache IN (".implode(',', $tasks_ids).")";
								pmb_mysql_query($query);
							}
								
							// il faut aussi effacer les documents numériques...
							//en base...
							$query_del_docnum = "delete from taches_docnum where num_tache not in (select id_tache from taches)";
							pmb_mysql_query($query_del_docnum);
						}
					}
				}
			}
		}
	}
	
	public function insertOfTask($active = 0) {
		$active = intval($active);
		if ($active == 0) {
			//statut de la tâche
			$query_state = "select statut from planificateur where id_planificateur=".$this->id;
			$result_query_state = pmb_mysql_query($query_state);
			if (pmb_mysql_num_rows($result_query_state) > 0) {
				$active = pmb_mysql_result($result_query_state,0, "statut");
			}
		}
		// on recherche si cette planification possède une tâche en attente ou en cours d'exécution...
		$query = "select t.id_tache, t.num_planificateur, p.statut
			from taches t, planificateur p
			where t.num_planificateur=p.id_planificateur
			and t.end_at='0000-00-00 00:00:00' and t.num_planificateur=".$this->id;
		$result_query = pmb_mysql_query($query);
	
		// nouvelle planification && planification activée
		if ((pmb_mysql_num_rows($result_query) == 0) && ($active == 1)) {
			//insertion de la tâche planifiée
			$requete="insert into taches (num_planificateur, status, commande, indicat_progress,id_process)
					values('".$this->id."',1,0,0,0)";
			pmb_mysql_query($requete);
			// modification planification && planification désactivée
		} else if ((pmb_mysql_num_rows($result_query) == 1) && ($active == 0)) {
			//il faut vérifier que la tâche ne soit pas déjà planifiée, si oui on la supprime
			if (pmb_mysql_num_rows($result_query) >= 1) {
				$requete="delete from taches where start_at='0000-00-00 00:00:00' and num_planificateur='".$this->id."'";
				pmb_mysql_query($requete);
			}
		}
	}
	
	public function get_formatted_setting($name, $label, $value) {
		return array(
				'name' => $name,
				'label' => $label,
				'value' => $value
		);
	}
	
	public function get_formatted_settings() {
		$formatted_settings = [];
		return $formatted_settings;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_id_type() {
		return $this->id_type;
	}
	
	public function get_libelle_tache() {
		return $this->libelle_tache;
	}
	
	public function get_desc_tache() {
		return $this->desc_tache;
	}
	
	public function get_param() {
		return $this->param;
	}
	
	public function get_statut() {
		return $this->statut;
	}
	
	public function get_perio_heure() {
		return $this->perio_heure;
	}
	
	public function get_perio_minute() {
		return $this->perio_minute;
	}
	
	public function get_perio_jour_mois() {
		return $this->perio_jour_mois;
	}
	
	public function get_perio_jour() {
		return $this->perio_jour;
	}
	
	public function get_perio_mois() {
		return $this->perio_mois;
	}
	
	public function get_next_execution() {
		if($this->statut){
			return formatdate($this->calc_next_date_deb)." ".$this->calc_next_heure_deb;
		}
		return '';
	}
	
	public function set_id_type($id_type=0) {
		$this->id_type = intval($id_type);
	}
	
	public static function is_already_in_progress($num_planificateur) {
		$num_planificateur = intval($num_planificateur);
		$query = "SELECT count(*) FROM taches WHERE num_planificateur = ".$num_planificateur." AND status=".RUNNING;
		$result = pmb_mysql_query($query);
		return pmb_mysql_result($result, 0);
	}
	
	protected static function format_url($url='') {
		global $base_path;
		
		return $base_path.'/admin.php?categ=planificateur&sub=manager'.$url;
	}
}