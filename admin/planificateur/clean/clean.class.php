<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: clean.class.php,v 1.22.2.6 2023/09/15 10:01:35 tsamson Exp $

global $class_path;
require_once($class_path."/scheduler/scheduler_task.class.php");
require_once($class_path."/netbase/netbase.class.php");
require_once($class_path."/netbase/netbase_cache.class.php");
require_once($class_path."/netbase/netbase_records.class.php");
require_once($class_path."/netbase/netbase_entities.class.php");

class clean extends scheduler_task {
	
	protected $ws_method_elements = array();
	protected static $packet_size = 0; // Remise à 0 par défaut : l'indexation par passe pose souci sur nos hebergements
	
	protected function add_element_report($response) {
		if(!empty($response['title'])) {
			$this->add_section_report($response['title']);
			if(!empty($response['bad_user_rights'])) {
				$this->add_rights_bad_user_report();
			} else {
				$this->add_content_report($response['message']);
			}
		}
	}
		
	protected function merge_details_from_response($response=array(), $action='') {
		if(!empty($response['affected'])) {
			$already_affected = intval($this->msg_statut[$action]['details']['affected']);
			$affected = $already_affected + intval($response['affected']);
			
			$response['message'] = str_replace($response['affected'], $affected, $response['message']);
			$response['affected'] = $affected;
			$this->msg_statut[$action]['details'] = $response;
		} else {
			foreach ($response as $method_name=>$sub_response) {
				if(!empty($sub_response['affected'])) {
					if(!empty($this->msg_statut[$action]['details'][$method_name]['affected'])) {
						$already_affected = intval($this->msg_statut[$action]['details'][$method_name]['affected']);
					} else {
						$already_affected = 0;
					}
					$affected = ($already_affected + $sub_response['affected']);
					
					$sub_response['message'] = str_replace($sub_response['affected'], $affected, $sub_response['message']);
					$sub_response['affected'] = $affected;
					
					// 							$response[$method_name] = $sub_response;
					$this->msg_statut[$action]['details'][$method_name] = $sub_response;
				}
			}
		}
		return $this->msg_statut[$action]['details'];
	}
	
	protected function get_ws_method_elements() {
		if(empty($this->ws_method_elements)) {
			$this->ws_method_elements = array(
					INDEX_GLOBAL => 'indexGlobal',
					INDEX_NOTICES => 'indexNotices',
					CLEAN_AUTHORS => 'cleanAuthors',
					CLEAN_PUBLISHERS => 'cleanPublishers',
					CLEAN_COLLECTIONS => 'cleanCollections',
					CLEAN_SUBCOLLECTIONS => 'cleanSubcollections',
					CLEAN_CATEGORIES => 'cleanCategories',
					CLEAN_SERIES => 'cleanSeries',
					CLEAN_TITRES_UNIFORMES => 'cleanTitresUniformes',
					CLEAN_INDEXINT => 'cleanIndexint',
					CLEAN_RELATIONS => 'cleanRelations',
					CLEAN_NOTICES => 'cleanNotices',
					INDEX_ACQUISITIONS => 'indexAcquisitions',
					GEN_SIGNATURE_NOTICE => 'genSignatureNotice',
					GEN_PHONETIQUE => 'genPhonetique',
					NETTOYAGE_CLEAN_TAGS => 'nettoyageCleanTags',
					CLEAN_CATEGORIES_PATH => 'cleanCategoriesPath',
					GEN_DATE_PUBLICATION_ARTICLE => 'genDatePublicationArticle',
					GEN_DATE_TRI => 'genDateTri',
					INDEX_DOCNUM => 'indexDocnum',
					INDEX_RDFSTORE => 'cleanRdfStore',
					INDEX_SYNCHRORDFSTORE => 'cleanSynchroRdfStore',
					INDEX_FAQ => 'cleanFAQ',
					INDEX_CMS => 'cleanCMS',
					INDEX_CONCEPT => 'cleanConcept',
					HASH_EMPR_PASSWORD => 'hashEmprPassword',
					INDEX_AUTHORITIES => 'indexAuthorities',
					GEN_SIGNATURE_DOCNUM => 'genSignatureDocnum',
					GEN_ARK => 'genArk',
					GEN_DOCNUM_THUMBNAIL => 'genDocnumThumbnail',
			);
		}
		return $this->ws_method_elements;
	}
	
	protected function get_ws_method_element($action) {
		$this->get_ws_method_elements();
		if(!empty($this->ws_method_elements[$action])) {
			return $this->ws_method_elements[$action];
		}
		return '';
	}
	
	protected function execution_element($title, $action) {
		$method_name = $this->get_ws_method_element($action);
		$ws_method_name = "pmbesClean_".$method_name;
		if (method_exists($this->proxy, $ws_method_name)) {
			switch ($action) {
				case INDEX_AUTHORITIES:
					$filters = array();
					if(!empty($this->msg_statut[$action]['details'])) {
						$details = $this->msg_statut[$action]['details'];
						
						$methodsindexAuthorities = pmbesClean::getMethodsindexAuthorities();
						foreach ($methodsindexAuthorities as $key=>$methodindexAuthority) {
							if(array_key_exists($methodindexAuthority, $details) !== false) {
								if($key == (count($details)-1)) {
									$filters[] = $methodindexAuthority;
								}
							} else {
								$filters[] = $methodindexAuthority;
							}
						}
					}
					$response = $this->proxy->{$ws_method_name}($filters);
					break;
				default:
					$response = $this->proxy->{$ws_method_name}();
					break;
			}
			if(is_array($response)) {
				if(empty($this->msg_statut[$action]['details'])) {
					$this->msg_statut[$action]['details'] = $response;
				} else {
					$response = $this->merge_details_from_response($response, $action);
				}
			}
			
			//TODO : si action terminée alors
			if(pmbesClean::canGoNextStep()) {
				if(is_array($response)) {
					if(!empty($response['title'])) {
						$this->add_element_report($response);
					} else {
						foreach ($response as $sub_response) {
							$this->add_element_report($sub_response);
						}
					}
				} else {
					$this->add_content_report($response);
				}
			}
			return $response;
		} else {
			$this->add_function_rights_report($method_name,"pmbesClean");
			return array();
		}
	}
	
	protected function get_remaining_actions($actions=array()) {
		$remaining_actions = array();
		if(!empty($this->indicat_progress) && !empty($this->msg_statut)) {
			foreach ($actions as $key=>$action) {
				if(!empty($this->msg_statut[$action]['progression'])) {
					$this->msg_statut[$action]['progression'] = round($this->msg_statut[$action]['progression'], 2);
					// en cours de traitement ?
					if($this->msg_statut[$action]['progression'] > 0 && $this->msg_statut[$action]['progression'] < 100) {
						$remaining_actions[$key] = $action;
					}
				} else {
					$remaining_actions[$key] = $action;
				}
			}
		} else {
			$remaining_actions = $actions;
		}
		return $remaining_actions;
	}
	
	public function execution() {
		global $msg;
		global $acquisition_active,$pmb_indexation_docnum;
		global $base_path;
		
		if (SESSrights & ADMINISTRATION_AUTH) {
			$parameters = $this->unserialize_task_params();
			if(empty($this->indicat_progress)) {
				$this->add_section_report($this->msg["planificateur_clean"]);
			}
			$percent = 0;
			if (method_exists('pmbesClean', 'setPacketSize')) {
				pmbesClean::setPacketSize(static::$packet_size);
			}
			$nb_actions = count($parameters["clean"]);
			scheduler_indexation_stack::set_context('scheduler');
			
			//calcul de la progression entre chaque action 
			$p_value = (int) 100/$nb_actions;
			
			//est-ce une tâche multi-processus
			$remaining_actions = $this->get_remaining_actions($parameters["clean"]);
			$nb_remaining_actions = count($remaining_actions);
			if($nb_remaining_actions < $nb_actions) {
				//mise à jour de la progression
				$percent = ($nb_actions - $nb_remaining_actions)*$p_value;
			}
			foreach ($remaining_actions as $clean) {
				if(!empty($this->msg_statut[$clean]['progression'])) {
					pmbesClean::setProgresion($this->msg_statut[$clean]['progression']);
				} else {
					pmbesClean::initProgresion();
				}
				$response = array();
				$this->listen_commande(array(&$this,"traite_commande"));
				if($this->statut == WAITING) {
					$this->send_command(RUNNING);
				}
				if ($this->statut == RUNNING) {
					switch ($clean) {
						case INDEX_GLOBAL:
						case INDEX_NOTICES:
						case CLEAN_AUTHORS:
						case CLEAN_PUBLISHERS:
						case CLEAN_COLLECTIONS:
						case CLEAN_SUBCOLLECTIONS:
						case CLEAN_CATEGORIES:
						case CLEAN_SERIES:
						case CLEAN_TITRES_UNIFORMES:
						case CLEAN_INDEXINT:
						case CLEAN_RELATIONS:
						case CLEAN_NOTICES:
						case GEN_SIGNATURE_NOTICE:
						case GEN_PHONETIQUE:
						case NETTOYAGE_CLEAN_TAGS:
						case CLEAN_CATEGORIES_PATH:
						case GEN_DATE_PUBLICATION_ARTICLE:
						case GEN_DATE_TRI:
						case HASH_EMPR_PASSWORD:
						case INDEX_AUTHORITIES:
						case GEN_ARK:
						case GEN_DOCNUM_THUMBNAIL:
							$response[$clean] = $this->execution_element(netbase::get_label_proceeding($clean), $clean);
							break;
						case INDEX_ACQUISITIONS:
							if ($acquisition_active) {
								$response[$clean] = $this->execution_element(netbase::get_label_proceeding($clean), $clean);
							} else {
								$this->add_section_report($msg["nettoyage_reindex_acq"]);
								$this->add_content_report($this->msg["clean_acquisition"]);
							}
							break;
						case INDEX_DOCNUM:
							if ($pmb_indexation_docnum) {
								$response[$clean] = $this->execution_element(netbase::get_label_proceeding($clean), $clean);
							} else {
								$this->add_section_report($msg["docnum_reindexer"]);
								$this->add_content_report($this->msg["clean_indexation_docnum"]);
							}
							break;
						case CLEAN_OPAC_SEARCH_CACHE:
							$this->add_section_report($msg["cleaning_opac_search_cache"]);
							$cleaned = false;
							$query = "truncate table search_cache";
							if(pmb_mysql_query($query)){
								$query = "optimize table search_cache";
								pmb_mysql_query($query);
								$cleaned = true;
							}
							$this->add_boolean_content_report($cleaned);
							break;
						case CLEAN_CACHE_AMENDE:
							$this->add_section_report($msg["cleaning_cache_amende"]);
							$cleaned = false;
							$query = "truncate table cache_amendes";
							if(pmb_mysql_query($query)){
								$query = "optimize table cache_amendes";
								pmb_mysql_query($query);
								$cleaned = true;
							}
							$this->add_boolean_content_report($cleaned);
							break;
						case CLEAN_CACHE_TEMPORARY_FILES:
							$this->add_section_report($msg["cleaning_cache_temporary_files"]);
							$cleaned = netbase_cache::clean_files($base_path."/temp");
							if($cleaned) {
								//Correctement réalisé en gestion, on nettoye à l'OPAC
								$cleaned = netbase_cache::clean_files($base_path."/opac_css/temp");
							}
							$this->add_boolean_content_report($cleaned);
							break;
						case CLEAN_CACHE_APCU:
							$this->add_section_report($msg["cleaning_cache_apcu"]);
							$cleaned = netbase_cache::clean_apcu();
							$this->add_boolean_content_report($cleaned);
							break;
						case INDEX_RDFSTORE:
							$response[$clean] = $this->execution_element($msg["nettoyage_rdfstore_reindexation"], $clean);
							break;
						case INDEX_SYNCHRORDFSTORE:
							$response[$clean] = $this->execution_element($msg["nettoyage_synchrordfstore_reindexation"], $clean);
							break;
						case INDEX_FAQ:
							$response[$clean] = $this->execution_element($msg["nettoyage_reindex_faq"], $clean);
							break;
						case INDEX_CMS:
							$response[$clean] = $this->execution_element($msg["nettoyage_reindex_cms"], $clean);
							break;
						case INDEX_CONCEPT:
							$response[$clean] = $this->execution_element($msg["nettoyage_reindex_concept"], $clean);
							break;
						case CLEAN_ENTITIES_DATA:
							$this->add_section_report($msg["cleaning_entities_data"]);
							$cleaned = netbase_records::clean_data();
							$this->add_boolean_content_report($cleaned);
							break;
						case GEN_SIGNATURE_DOCNUM:
							$response[$clean] = $this->execution_element($msg["gen_signature_docnum"], 'genSignatureDocnum');
							break;
						case CLEAN_RECORDS_THUMBNAIL:
							$cleaned = netbase_records::clean_thumbnail();
							$this->add_boolean_content_report($cleaned);
							break;
						case INDEX_DATE_FLOT:
							$this->add_section_report($msg["nettoyage_reindex_date_flot"]);
							$fields_date_flot = netbase_entities::get_custom_fields_date_flot();
							if(!empty($fields_date_flot)) {
								foreach ($fields_date_flot as $prefix=>$fields_id) {
									foreach ($fields_id as $field_id) {
										netbase_entities::index_custom_field_date_flot($prefix, $field_id);
									}
								}
							}
							$this->add_boolean_content_report(true);
							break;
						case CLEAN_DOCNUM_THUMBNAIL:
							$cleaned = netbase_records::clean_docnum_thumbnail();
							$this->add_boolean_content_report($cleaned);
							break;
						case CLEAN_AUTOLOAD_FILES:
							$this->add_section_report($msg["cleaning_autoload_files"]);
							$cleaned = netbase_cache::clean_autoload_files();
							$this->add_boolean_content_report($cleaned);
							break;
					}
// 					if($response) {
// 						$percent += $p_value;
// 						$this->update_progression($percent);
// 					}
					//on met à jour la progression s'il s'agit d'une action hors Webservice ou si l'action en cours est terminée
					if(empty($this->get_ws_method_element($clean)) || pmbesClean::canGoNextStep()) {
						$percent += $p_value;
						$this->msg_statut[$clean]['progression'] = 100;
						$this->update_progression($percent);
					} else {
						//ratio p_value avec la progression de l'action
						$percent += ((pmbesClean::getProgresion()/100)*$p_value);
						if(round($percent, 2) >= round($this->indicat_progress, 2)) {
							$this->msg_statut[$clean]['progression'] = pmbesClean::getProgresion();
							$this->update_progression($percent);
							// Action pouvant être longue :
							// on déroule par paquet en mettant fin à ce processus et en recréant un nouveau
							// permet de libérer la mémoire PHP
							return $this->run();
						}
					}
				}
			}
		} else {
			$this->add_rights_bad_user_report();
		}
		return 0;
	}
	
	protected function add_section_report($content='', $css_class='scheduler_report_section') {
		global $charset;
		$this->report[] = "<tr><th class='".$css_class."'>".htmlentities($content, ENT_QUOTES, $charset)."</th></tr>";
	}
	
	protected function add_content_report($content='', $css_class='scheduler_report_content') {
		global $charset;
		$this->report[] = "<tr><td class='".$css_class."'>".htmlentities($content, ENT_QUOTES, $charset)."</td></tr>";
	}
	
	protected function add_boolean_content_report($flag = false) {
		if($flag) {
			$this->add_content_report('OK');
		} else {
			$this->add_content_report('KO');
		}
	}
}


