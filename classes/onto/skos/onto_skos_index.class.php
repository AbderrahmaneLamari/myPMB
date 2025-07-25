<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_skos_index.class.php,v 1.9 2022/10/28 13:42:11 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/onto/onto_index.class.php");
require_once($class_path."/onto/common/onto_common_index.class.php");
require_once($class_path.'/onto/onto_handler.class.php');
require_once($class_path.'/skos/skos_datastore.class.php');
require_once($class_path.'/skos/skos_onto.class.php');

/**
 * class onto_skos_index
*/
class onto_skos_index extends onto_common_index {

    /**
     *
     * @var onto_skos_autoposting
     */
    private static $onto_skos_autoposting;

    /**
     *
     * @var array stockage des infos d'indexation de l'autopostage
     */
	private $paths_infos = array(
			'broad' => array(
				'code_champ' => 5,
				'code_ss_champ' => 1,
				'pond' => 30
			),
			'narrow' => array(
				'code_champ' => 6,
				'code_ss_champ' => 1,
				'pond' => 50
			)
	);

	public function update_paths_index($id_item, $paths_preflabels, $narrow = false) {
		global $sphinx_active;

		if ($narrow) {
			$type = 'narrow';
		} else {
			$type = 'broad';
		}

		$this->{'delete_'.$type.'_paths_index'}($id_item);
		$field_order = 1;
		$preflabels_already_indexed = array();

		$tab_words_insert = $tab_fields_insert = array();

		if (is_array($paths_preflabels) && count($paths_preflabels)) {
			$field_order = 1;
			foreach ($paths_preflabels as $preflabels) {
				foreach($preflabels as $preflabel) {
					if (!empty($preflabel) && !in_array($preflabel['preflabel'], $preflabels_already_indexed)) {
						$lang = $preflabel['lang'];
						if (!empty($this->lang_codes[$preflabel['lang']])) {
							$lang = $this->lang_codes[$preflabel['lang']];
						}
						//fields (contenu brut)
						//TODO : on stocke l'id du concept dans la colonne skos_field_global_index.authority_num
						//a voir s'il faut pas stocker l'id de l'autorit� � la place
						$tab_fields_insert[] = "('".$id_item."','".$this->paths_infos[$type]['code_champ']."','".$this->paths_infos[$type]['code_ss_champ']."','".$field_order."','".addslashes($preflabel['preflabel'])."','".$lang."','".$this->paths_infos[$type]['pond']."','".$preflabel['id']."')";

						//words (contenu �clat�)
						$tab_tmp=explode(' ',strip_empty_words($preflabel['preflabel']));
						$word_position = 1;
						foreach($tab_tmp as $word){
							$num_word = indexation::add_word($word, $lang);
							$tab_words_insert[]="(".$id_item.",".$this->paths_infos[$type]['code_champ'].",".$this->paths_infos[$type]['code_ss_champ'].",".$num_word.",".$this->paths_infos[$type]['pond'].",$field_order,$word_position)";
							$word_position++;
						}
						$field_order++;
						$preflabels_already_indexed[] = $preflabel['preflabel'];
					}
				}
			}
		}
		$this->save_elements($tab_words_insert,$tab_fields_insert);

		//SPHINX
		if($sphinx_active){
			$si = new sphinx_concepts_indexer();
			if(is_object($si)) {
				$si->fillIndex($id_item);
			}
		}
	}

	private function delete_broad_paths_index($id_item, $authority_num = 0) {
		$req_del="DELETE FROM skos_words_global_index WHERE id_item ='".$id_item."' AND code_champ='".$this->paths_infos['broad']['code_champ']."'";
		pmb_mysql_query($req_del);
		$req_del="DELETE FROM skos_fields_global_index WHERE id_item ='".$id_item."' AND code_champ='".$this->paths_infos['broad']['code_champ']."'";
		$authority_num *= 1;
		if ($authority_num) {
		    $req_del .= " AND authority_num = ".$authority_num;
		}
		pmb_mysql_query($req_del);
	}

	private function delete_narrow_paths_index($id_item, $authority_num = 0) {
		$req_del="DELETE FROM skos_words_global_index WHERE id_item ='".$id_item."' AND code_champ='".$this->paths_infos['narrow']['code_champ']."'";
		pmb_mysql_query($req_del);
		$req_del="DELETE FROM skos_fields_global_index WHERE id_item ='".$id_item."' AND code_champ='".$this->paths_infos['narrow']['code_champ']."'";
		$authority_num *= 1;
		if ($authority_num) {
		    $req_del .= " AND authority_num = ".$authority_num;
		}
		pmb_mysql_query($req_del);
	}

	public function maj($object_id, $object_uri="",$datatype="all"){
	    global $thesaurus_concepts_autopostage;
	    global $sphinx_active;

	    if($object_id == 0 && $object_uri != ""){
	        $object_id = onto_common_uri::get_id($object_uri);
	    }
	    if($object_id != 0 && !$object_uri){
	        $object_uri = onto_common_uri::get_uri($object_id);
	    }

	    if ($datatype == 'autoposting') {
	        //on ne r�indexe que les chemins des termes g�n�riques ou sp�cifiques
	        return $this->maj_autospoting($object_id, $object_uri);
	    }

	    if ($datatype != 'all') {
	        return parent::maj($object_id, $object_uri,$datatype);
	    }

        if ($thesaurus_concepts_autopostage) {
    	    $this->init_onto_skos_autoposting();
    	    static::$onto_skos_autoposting->set_uri($object_uri);
    	    if (!$this->netbase) {
                //stockage des anciens chemins de l'autopostage
                $old_broaders_id = static::$onto_skos_autoposting->get_ids_from_paths(static::$onto_skos_autoposting->get_paths());
                $old_narowers_id = static::$onto_skos_autoposting->get_ids_from_paths(static::$onto_skos_autoposting->get_paths(true));
            }
        }
        //indexation du concept
        parent::maj($object_id,$object_uri,$datatype);
        $this->index_cp($object_id);
        if ($thesaurus_concepts_autopostage) {
            $this->update_paths($object_id);

            if (!$this->netbase) {
                //reindexation des termes g�n�riques
                if (isset($old_broaders_id) && count($old_broaders_id)) {
                    foreach($old_broaders_id as $broader_id) {
                        indexation_stack::push($broader_id, TYPE_CONCEPT, 'autoposting');
                    }
                }
                //reindexation des termes sp�cifiques
                if (isset($old_narowers_id) && count($old_narowers_id)) {
                    foreach($old_narowers_id as $narrower_id) {
                        indexation_stack::push($narrower_id, TYPE_CONCEPT, 'autoposting');
                    }
                }
            }
        }
        
        //SPHINX
        if($sphinx_active){
            if(!isset(self::$sphinx_indexer)){
                self::$sphinx_indexer = new sphinx_concepts_indexer();
            }
            if(is_object(self::$sphinx_indexer)) {
                self::$sphinx_indexer->fillIndex($object_id);
            }
        }
	    return true;
	}

	protected function init_onto_skos_autoposting() {
	    if (!isset(static::$onto_skos_autoposting)) {
	        static::$onto_skos_autoposting = new onto_skos_autoposting($this->handler);
		    $this->table_prefix = $this->handler->get_onto_name();
		    $this->reference_key = "id_item";
	    }
	    return static::$onto_skos_autoposting;
	}

	protected function maj_autospoting($object_id, $object_uri) {
	    global $thesaurus_concepts_autopostage;
	    if ($thesaurus_concepts_autopostage && $object_id) {
	        $this->init_onto_skos_autoposting();
	        static::$onto_skos_autoposting->set_uri($object_uri);//calcules des nouveaux chemins

	        $this->update_paths($object_id);

            //r�indexation des notices index�s avec le concepts
            index_concept::update_linked_elements($object_id);
	    }
	    return true;
	}

	protected function update_paths($object_id) {
	    if ($object_id) {
	        //calcules des nouveaux chemins
	        static::$onto_skos_autoposting->save_paths();

	        //maj des index de l'autopostage
	        $this->update_paths_index($object_id, static::$onto_skos_autoposting->get_broaders_preflabels());
	        $this->update_paths_index($object_id, static::$onto_skos_autoposting->get_narrowers_preflabels(), true);
	    }
	}

	protected function index_cp($object_id)
	{
	    // Champs persos
	    $p_perso=$this->get_parametres_perso_class('skos');
	    $data=$p_perso->get_fields_recherche_mot_array($object_id);
	    $j=0;
	    $order_fields=1;
	    foreach ( $data as $code_ss_champ => $value ) {
	        $tab_mots=array();
	        //la table pour les recherche exacte
	        $infos = array(
	            'champ' => '1100',
	            'ss_champ' => $code_ss_champ,
	            'pond' => $p_perso->get_pond($code_ss_champ)
	        );
	        foreach($value as $val) {
	            $val = strip_empty_words($val);
	            if($val != ''){
	                $tab_tmp=explode(' ',$val);
	                
	                $tab_fields_insert[] = $this->get_tab_field_insert($object_id, $infos, $j, $val);
	                $j++;
	                foreach($tab_tmp as $mot) {
	                    if(trim($mot)){
	                        $tab_mots[$mot]= "";
	                    }
	                }
	            }
	        }
	        $pos=1;
	        foreach ( $tab_mots as $mot => $langage ) {
	            $num_word = indexation::add_word($mot, $langage);
	            $infos = array(
	                'champ' => '1100',
	                'ss_champ' => $code_ss_champ,
	                'pond' => $p_perso->get_pond($code_ss_champ)
	            );
	            $tab_words_insert[] = $this->get_tab_insert($object_id, $infos, $num_word, $order_fields, $pos);
	            $pos++;
	        }
	        $order_fields++;
	    }
	    $this->save_elements($tab_words_insert,$tab_fields_insert);
	}
	
	public function get_tab_code_champ() {
	    if(empty($this->tab_code_champ)) {
	        $this->init();
	    }
	    return $this->tab_code_champ;
	}
}
