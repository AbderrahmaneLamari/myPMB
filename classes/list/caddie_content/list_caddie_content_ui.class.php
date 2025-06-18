<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_caddie_content_ui.class.php,v 1.7.2.6 2021/12/03 07:10:12 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_caddie_content_ui extends list_caddie_content_root_ui {
		
	protected $instance_notice_tpl_gen;
	
	protected $flag_notice_id;
	
	protected function _get_query_caddie_content() {
		$query = "SELECT caddie_content.object_id FROM caddie_content";
		switch (static::$object_type) {
			case 'NOTI' :
				$query .= " left join notices on object_id=notice_id " ;
				break;
			case 'EXPL' :
				$query .= " left join exemplaires on object_id=expl_id " ;
				break;
			case 'BULL' :
				$query .= " left join bulletins on object_id=bulletin_id " ;
				break;
			default:
			    break;
		}
		$query .= $this->_get_query_filters_caddie_content();
		$query .= " AND caddie_id='".static::$id_caddie."'";
		return $query;
	}
	
	protected function _get_query_select_fields($tablename, $alias='') {
		$query_select_fields = '';
		$fields = $this->get_keep_fields($tablename);
		if($alias) {
			$builded_fields = array();
			foreach ($fields as $field) {
				$builded_fields[] = $field." as ".$alias."_".$field;
			}
			$query_select_fields .= $alias.".".implode(', '.$alias.'.', $builded_fields);
		} else {
			$query_select_fields .= $tablename.".".implode(', '.$tablename.'.', $fields);
		}
		return $query_select_fields;
	}
	
	protected function _get_query_base() {
		switch (static::$object_type) {
			case 'NOTI':
				$query = "SELECT notices.notice_id as id,
					".$this->_get_query_select_fields('notices').", 
                    ".$this->_get_query_select_fields('collections').",
                    ".$this->_get_query_select_fields('publishers', 'p1').",
                    ".$this->_get_query_select_fields('publishers', 'p2').",
                    ".$this->_get_query_select_fields('series').",
                    ".$this->_get_query_select_fields('sub_collections').",
                    ".$this->_get_query_select_fields('indexint')."
					FROM notices
					left join series on serie_id=notices.tparent_id
					left join publishers p1 on p1.ed_id=notices.ed1_id
					left join publishers p2 on p2.ed_id=notices.ed2_id
					left join collections on notices.coll_id=collection_id
					left join sub_collections on notices.subcoll_id=sub_coll_id
					left join indexint on notices.indexint=indexint_id 
					WHERE notices.notice_id IN (".$this->_get_query_caddie_content().")";
				break;
			case 'EXPL':
			    $query_select = "SELECT exemplaires.expl_id as id, 
                    ".$this->_get_query_select_fields('exemplaires').",
                    ".$this->_get_query_select_fields('docs_type').",
                    ".$this->_get_query_select_fields('docs_section').",
                    ".$this->_get_query_select_fields('docs_statut').",
                    ".$this->_get_query_select_fields('docs_location').",
                    ".$this->_get_query_select_fields('docs_codestat').",
                    ".$this->_get_query_select_fields('notices').", 
                    ".$this->_get_query_select_fields('collections').",
                    ".$this->_get_query_select_fields('indexint').",
                    ".$this->_get_query_select_fields('publishers', 'p1').",
                    ".$this->_get_query_select_fields('publishers', 'p2').",
                    ".$this->_get_query_select_fields('series').",
                    ".$this->_get_query_select_fields('sub_collections').",
					".$this->_get_query_select_fields('lenders').",
                    bulletins.*";
			    
				$query_from_join = "
					FROM exemplaires
					, docs_type
					, docs_section
					, docs_statut
					, docs_location
					, docs_codestat
					, lenders
					, notices left join series on serie_id=notices.tparent_id
					left join publishers p1 on p1.ed_id=notices.ed1_id
					left join publishers p2 on p2.ed_id=notices.ed2_id
					left join collections on notices.coll_id=collection_id
					left join sub_collections on notices.subcoll_id=sub_coll_id
					left join indexint on notices.indexint=indexint_id
					left join bulletins on bulletins.bulletin_notice = notices.notice_id 
					WHERE exemplaires.expl_id IN (".$this->_get_query_caddie_content().")
					AND exemplaires.expl_typdoc = docs_type.idtyp_doc
					AND exemplaires.expl_section = docs_section.idsection
					AND exemplaires.expl_statut = docs_statut.idstatut
					AND exemplaires.expl_location = docs_location.idlocation
					AND exemplaires.expl_codestat = docs_codestat.idcode
					AND exemplaires.expl_owner = lenders.idlender";
				
				$table_tempo_name_EXPL_NOTI = 'caddie_content_ui_'.md5(uniqid("",true));
				pmb_mysql_query("CREATE TEMPORARY TABLE ".$table_tempo_name_EXPL_NOTI." ".$query_select." ".$query_from_join." AND (exemplaires.expl_notice=notices.notice_id AND exemplaires.expl_notice <> 0)");

				$table_tempo_name_EXPL_BULL = 'caddie_content_ui_'.md5(uniqid("",true));
				pmb_mysql_query("CREATE TEMPORARY TABLE ".$table_tempo_name_EXPL_BULL." ".$query_select." ".$query_from_join." AND (exemplaires.expl_bulletin=bulletins.bulletin_id AND exemplaires.expl_bulletin <> 0)");
				
				$query = "SELECT * FROM ".$table_tempo_name_EXPL_NOTI." UNION SELECT * FROM ".$table_tempo_name_EXPL_BULL;
				break;
			case 'BULL':
				$query = "select bulletins.bulletin_id as id, bulletins.* from bulletins where bulletin_id IN (".$this->_get_query_caddie_content().") ";
				break;
			default:
			    break;
		}
		return $query;
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
	
		$this->set_filter_from_form('notice_tpl');
		parent::set_filters_from_form();
	}
	
	/**
	 * Affichage du formulaire d'options
	 */
	public function get_options_content_form() {
		global $msg;
	
		$options_content_form = parent::get_options_content_form();
		if(!isset($this->filters['notice_tpl'])) $this->filters['notice_tpl'] = 0;
		$sel_notice_tpl=notice_tpl_gen::gen_tpl_select($this->objects_type."_notice_tpl",$this->filters['notice_tpl'],'',1,1);
		if($sel_notice_tpl) {
			$sel_notice_tpl= "
				<div class='".$this->objects_type."_notice_tpl_content'>
					<div class='colonne3'>
						<div class='row'>
							<label>".$msg['caddie_select_notice_tpl']."</label>
						</div>
						<div class='row'>
							".$sel_notice_tpl."
						</div>
					</div>
				</div>";
		}
		$options_content_form .= $sel_notice_tpl;
		return $options_content_form;
	}
	
	/**
	 * Objet de la liste du document bibliographique
	 */
	protected function get_display_export_noti_content_object_list($object, $line) {
		$display = "";
		$myCart = caddie_root::get_instance_from_object_type(static::$object_type, static::$id_caddie);
		if ($myCart->type=="EXPL"){
			$rqt_test = "select expl_notice as id from exemplaires where expl_id='".$object->id."' ";
			$res_notice = pmb_mysql_query($rqt_test);
			$obj_notice = pmb_mysql_fetch_object($res_notice) ;
			if (!$obj_notice->id) {
				$rqt_test = "select num_notice as id from bulletins join exemplaires on bulletin_id=expl_bulletin where expl_id='".$object->id."' ";
				$res_notice = pmb_mysql_query($rqt_test);
				$obj_notice = pmb_mysql_fetch_object($res_notice) ;
			}
			if((!isset($this->flag_notice_id[$obj_notice->id]) || !$this->flag_notice_id[$obj_notice->id]) && $obj_notice->id){
				$this->flag_notice_id[$obj_notice->id]=1;
				$display .= $this->instance_notice_tpl_gen->build_notice($obj_notice->id);
			}
		} elseif ($myCart->type=="NOTI") $display .= $this->instance_notice_tpl_gen->build_notice($object->id);
		if ($myCart->type=="BULL"){
			$rqt_test = $rqt_tout = "select num_notice as id from bulletins where bulletin_id = '".$object->id."' ";
			$res_notice = pmb_mysql_query($rqt_test);
			$obj_notice = pmb_mysql_fetch_object($res_notice);
			if((!isset($this->flag_notice_id[$obj_notice->id]) || !$this->flag_notice_id[$obj_notice->id]) && $obj_notice->id){
				$this->flag_notice_id[$obj_notice->id]=1;
				$display .= $this->instance_notice_tpl_gen->build_notice($obj_notice->id);
			}
		}
		return $display;
	}
	
	/**
	 * Liste des objets du document bibliographique
	 */
	public function get_display_export_noti_content_list() {
		$display = '';
		if(isset($this->applied_group[0]) && $this->applied_group[0]) {
			$grouped_objects = $this->get_grouped_objects();
			foreach($grouped_objects as $group_label=>$objects) {
				$display .= "
					<div class='list_ui_content_list_group ".$this->objects_type."_content_list_group' colspan='".count($this->columns)."'>
						".$group_label."
					</div>";
				foreach ($objects as $i=>$object) {
					$display .= $this->get_display_export_noti_content_object_list($object, $i);
				}
			}
		} else {
			foreach ($this->objects as $i=>$object) {
					$display .= $this->get_display_export_noti_content_object_list($object, $i);
			}
		}
		return $display;
	}
	
	public function get_display_export_noti_list() {
		global $charset;
		
		$display = "";
		
		$notice_tpl = $this->objects_type."_notice_tpl";
		global ${$notice_tpl};
		$this->instance_notice_tpl_gen=new notice_tpl_gen(${$notice_tpl});
		if(count($this->objects)) {
			$display .= $this->get_display_export_noti_content_list();
		}
		return "<!DOCTYPE html><html lang='".get_iso_lang_code()."'><head><meta charset=\"".$charset."\" /></head><body>".$display."</body></html>";
	}
	
	protected function get_keep_fields($tablename) {
		$keep_fields = array();
		switch ($tablename) {
			case 'collections':
				$keep_fields[] = 'collection_id';
				$keep_fields[] = 'collection_name';
				$keep_fields[] = 'collection_parent';
				$keep_fields[] = 'collection_issn';
				$keep_fields[] = 'collection_web';
				$keep_fields[] = 'collection_comment';
				break;
			case 'indexint':
				$keep_fields[] = 'indexint_id';
				$keep_fields[] = 'indexint_name';
				$keep_fields[] = 'indexint_comment';
				break;
			case 'notices':
				$keep_fields[] = 'notice_id';
				$keep_fields[] = 'typdoc';
				$keep_fields[] = 'tit1';
				$keep_fields[] = 'tit2';
				$keep_fields[] = 'tit3';
				$keep_fields[] = 'tit4';
				$keep_fields[] = 'tnvol';
				$keep_fields[] = 'year';
				$keep_fields[] = 'nocoll';
				$keep_fields[] = 'mention_edition';
				$keep_fields[] = 'code';
				$keep_fields[] = 'npages';
				$keep_fields[] = 'ill';
				$keep_fields[] = 'size';
				$keep_fields[] = 'accomp';
				$keep_fields[] = 'n_gen';
				$keep_fields[] = 'n_contenu';
				$keep_fields[] = 'n_resume';
				$keep_fields[] = 'lien';
				$keep_fields[] = 'eformat';
				$keep_fields[] = 'index_l';
				$keep_fields[] = 'niveau_biblio';
				$keep_fields[] = 'niveau_hierar';
				$keep_fields[] = 'prix';
				$keep_fields[] = 'commentaire_gestion';
				$keep_fields[] = 'thumbnail_url';
				$keep_fields[] = 'date_parution';
				$keep_fields[] = 'indexation_lang';
				$keep_fields[] = 'notice_is_new';
				$keep_fields[] = 'notice_date_is_new';
				break;
			case 'publishers':
				$keep_fields[] = 'ed_id';
				$keep_fields[] = 'ed_name';
				$keep_fields[] = 'ed_adr1';
				$keep_fields[] = 'ed_adr2';
				$keep_fields[] = 'ed_cp';
				$keep_fields[] = 'ed_ville';
				$keep_fields[] = 'ed_pays';
				$keep_fields[] = 'ed_web';
				$keep_fields[] = 'ed_comment';
				break;
			case 'series':
				$keep_fields[] = 'serie_id';
				$keep_fields[] = 'serie_name';
				break;
			case 'sub_collections':
				$keep_fields[] = 'sub_coll_id';
				$keep_fields[] = 'sub_coll_name';
				$keep_fields[] = 'sub_coll_parent';
				$keep_fields[] = 'sub_coll_issn';
				$keep_fields[] = 'subcollection_web';
				$keep_fields[] = 'subcollection_comment';
				break;
		}
		switch (static::$object_type) {
			case 'NOTI':
				switch ($tablename) {
					case 'notices':
						$keep_fields[] = 'create_date';
						$keep_fields[] = 'update_date';
						break;
				}
				break;
			case 'EXPL':
				switch ($tablename) {
					case 'docs_codestat':
						$keep_fields[] = 'codestat_libelle';
						break;
					case 'docs_location' :
						$keep_fields[] = 'location_libelle';
						break;
					case 'docs_section' :
						$keep_fields[] = 'section_libelle';
						$keep_fields[] = 'section_libelle_opac';
						break;
					case 'docs_statut' :
						$keep_fields[] = 'statut_libelle';
						$keep_fields[] = 'statut_libelle_opac';
						break;
					case 'docs_type':
						$keep_fields[] = 'tdoc_libelle';
						break;
					case 'exemplaires':
						$keep_fields[] = 'expl_id';
						$keep_fields[] = 'expl_cb';
						$keep_fields[] = 'expl_cote';
						$keep_fields[] = 'expl_date_depot';
						$keep_fields[] = 'expl_date_retour';
						$keep_fields[] = 'expl_note';
						$keep_fields[] = 'expl_prix';
						$keep_fields[] = 'expl_lastempr';
						$keep_fields[] = 'last_loan_date';
						$keep_fields[] = 'create_date';
						$keep_fields[] = 'update_date';
						$keep_fields[] = 'expl_comment';
						$keep_fields[] = 'expl_nbparts';
						break;
					case 'lenders':
						$keep_fields[] = 'idlender';
						$keep_fields[] = 'lender_libelle';
						break;
				}
				break;
			case 'BULL':
				break;
		}
		
		return $keep_fields;
	}
	
	protected function get_exclude_fields() {
		$exclude_fields = array(
				'tparent_id',
				'ed1_id',
				'ed2_id',
				'coll_id',
				'subcoll_id',
				'indexint',
				'statut',
				'signature',
				'opac_visible_bulletinage',
				'map_echelle_num',
				'map_projection_num',
				'map_ref_num',
				'map_equinoxe',
				'index_serie',
				'index_matieres',
				'niveau_hierar',
				'origine_catalogage',
				'index_n_gen',
				'index_n_contenu',
				'index_n_resume',
				'index_sew',
				'index_wew',
				'opac_serialcirc_demande',
				'num_notice_usage',
				'is_numeric'
		);
		switch (static::$object_type) {
			case 'NOTI':
				break;
			case 'EXPL':
				$exclude_fields[] = 'expl_notice';
				$exclude_fields[] = 'expl_bulletin';
				$exclude_fields[] = 'expl_typdoc';
				$exclude_fields[] = 'expl_section';
				$exclude_fields[] = 'expl_statut';
				$exclude_fields[] = 'expl_location';
				$exclude_fields[] = 'expl_codestat';
				$exclude_fields[] = 'expl_owner';
				$exclude_fields[] = 'transfert_location_origine';
				$exclude_fields[] = 'transfert_statut_origine';
				$exclude_fields[] = 'transfert_section_origine';
				$exclude_fields[] = 'idtyp_doc';
				$exclude_fields[] = 'tdoc_owner';
				$exclude_fields[] = 'expl_retloc';
				$exclude_fields[] = 'expl_abt_num';
				$exclude_fields[] = 'expl_ref_num';
				$exclude_fields[] = 'expl_pnb_flag';
				break;
			case 'BULL':
				$exclude_fields[] = 'index_titre';
				$exclude_fields[] = 'num_notice';
				break;
			default:
			    break;
		}
		return $exclude_fields;
	}
	
	protected function get_main_fields() {
		switch (static::$object_type) {
			case 'NOTI':
				return array_merge(
						$this->get_describe_fields('notices', 'notices', 'notices'),
						array('serie_name' => $this->get_describe_field('titrserie', 'notices', 'notices')),
						array('collection_name' => $this->get_describe_field('coll', 'notices', 'notices')),
						array('sub_coll_name' => $this->get_describe_field('subcoll', 'notices', 'notices')),
						array('publisher_name' => $this->get_describe_field('editeur', 'notices', 'notices')),
						array('indexint_name' => $this->get_describe_field('indexint', 'notices', 'notices')),
						array('statut_name' => $this->get_describe_field('statut', 'notices', 'notices'))
				);
				break;
			case 'EXPL':
				return array_merge(
						$this->get_describe_fields('exemplaires', 'items', 'exemplaires'),
						$this->get_describe_fields('notices', 'notices', 'notices'),
						array('tdoc_libelle' => $this->get_describe_field('tdoc_libelle', 'items', 'docs_type')),
						array('section_libelle' => $this->get_describe_field('section_libelle', 'items', 'docs_section')),
						array('statut_libelle' => $this->get_describe_field('statut_libelle', 'items', 'docs_statut')),
						array('location_libelle' => $this->get_describe_field('location_libelle', 'items', 'expl_location')),
						array('codestat_libelle' => $this->get_describe_field('codestat_libelle', 'items', 'docs_codestat')),
						array('serie_name' => $this->get_describe_field('titrserie', 'notices', 'notices')),
						array('collection_name' => $this->get_describe_field('coll', 'notices', 'notices')),
						array('sub_coll_name' => $this->get_describe_field('subcoll', 'notices', 'notices')),
						array('publisher_name' => $this->get_describe_field('editeur', 'notices', 'notices')),
						array('indexint_name' => $this->get_describe_field('indexint', 'notices', 'notices')),
						array('statut_name' => $this->get_describe_field('statut', 'notices', 'notices')),
						array('lender_libelle' => $this->get_describe_field('lender_libelle', 'items', 'lenders'))
				);
				break;
			case 'BULL':
				return array_merge(
						array('bulletin_numero' => 'bulletin_numero', 'mention_date' => 'mention_date', 'date_date' => 'date_date', 'bulletin_titre' => 'bulletin_titre', 'bulletin_cb' => 'bulletin_cb')
				);
				break;
			default:
			    break;
		}
		
	}
	
	protected function add_authors_available_columns() {
		return array(
				'author_main' => '244',
// 				'authors_others' => '246',
				'authors_secondary' => '247'
		);
	}
	
	protected function add_categories_available_columns() {
		return array(
				'categories' => '134'
		);
	}
	
	protected function add_languages_available_columns() {
		return array(
				'langues' => '710',
				'languesorg' => '711'
		);
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		parent::init_available_columns();
		switch (static::$object_type) {
			case 'NOTI':
				$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $this->add_authors_available_columns());
				$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $this->add_categories_available_columns());
				$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $this->add_languages_available_columns());
				$this->available_columns['main_fields']['opac_permalink'] = 'opac_permalink';
				$this->add_custom_fields_available_columns('notices', 'notice_id');
				break;
			case 'EXPL':
				$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $this->add_authors_available_columns());
				$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $this->add_categories_available_columns());
				$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $this->add_languages_available_columns());
				$this->available_columns['main_fields']['total_loans'] = 'expl_prets_nb';
				$this->add_custom_fields_available_columns('expl', 'expl_id');
				break;
			case 'BULL':
				$this->available_columns['main_fields']['opac_permalink'] = 'opac_permalink';
				break;
			default:
			    break;
		}
	}
	
	protected function init_default_columns() {
		switch (static::$object_type) {
			case 'NOTI':
				$this->add_column('notice_id');
				$this->add_column('niveau_biblio');
				$this->add_column('typdoc');
				$this->add_column('tit1');
				$this->add_column('tit4');
				$this->add_column('serie_name');
				$this->add_column('tnvol');
				$this->add_column('author_main');
				$this->add_column('authors_secondary');
				$this->add_column('publisher_name');
				$this->add_column('collection_name');
				$this->add_column('year');
				$this->add_column('date_parution');
				$this->add_column('code');
				$this->add_column('n_gen');
				$this->add_column('n_contenu');
				$this->add_column('n_resume');
				$this->add_column('indexint_name');
				$this->add_column('categories');
				$this->add_column('langues');
				break;
			default:
				parent::init_default_columns();
				break;
		}
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
	    if ($this->applied_sort[0]['by']) {
	        $sort_by = $this->applied_sort[0]['by'];
	        switch($sort_by) {
	            case 'author_main':
	            case 'authors_others':
	            case 'authors_secondary':
	            case 'categories':
	            case 'langues':
	            case 'languesorg':
	            case 'typdoc':
	            case 'statut_name':
	            case 'publisher_name':
	                $this->applied_sort_type = 'OBJECTS';
	                return '';
	            case 'year':
	            	return $this->_get_query_order_sql_build('date_parution');
	            default :
	                return parent::_get_query_order();
	        }
	    }
	}
	
	/**
	 * Fonction de callback
	 * @param object $a
	 * @param object $b
	 */
	protected function _compare_objects($a, $b) {
	    if($this->applied_sort[0]['by']) {
	        $sort_by = $this->applied_sort[0]['by'];
	        switch($sort_by) {
	            case 'authors_others':
	                //TODO
	                break;
	            case 'categories':
	                $categories_a = strip_tags($this->get_cell_categories_content($a));
	                $categories_b = strip_tags($this->get_cell_categories_content($b));
	                return $this->strcmp($categories_a, $categories_b);
	                break;
	            case 'publisher_name':
	                //@TODO
	                break;
	            default :
	                return parent::_compare_objects($a, $b);
	                break;
	        }
	    }
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('tit1');
	}
	
	protected function get_cell_categories_content($object) {
		global $opac_thesaurus;
		global $opac_categories_categ_in_line;
		global $pmb_keyword_sep;
		
		$content = '';
		$notice_id = $this->get_notice_id_from_object($object);
		$record_datas = record_display::get_record_datas($notice_id);
		$categories = $record_datas->get_categories();
		foreach($categories as $id_thes => $thesaurus) {
			if($opac_thesaurus) {
				foreach ($thesaurus as $i=>$categorie) {
					if($opac_categories_categ_in_line) {
						if(!$i) {
							$content .= "<p><strong>".$categorie['object']->thes->libelle_thesaurus."</strong></p>";
						} else {
							$content .= $pmb_keyword_sep;
						}
						$content .= "<span>".$categorie['format_label']."</span>";
					} else {
						$content .= "<p>[".$categorie['object']->thes->libelle_thesaurus."] ".$categorie['object']->libelle."</p>";
					}
				}
			} else {
				foreach ($thesaurus as $i=>$categorie) {
					if($opac_categories_categ_in_line) {
						if($i) {
							$content .= $pmb_keyword_sep;
						}
						$content .= "<span>".$categorie['object']->libelle."</span>";
					} else {
						$content .= "<p>".$categorie['object']->libelle."</p>";
					}
				}
			}
		}
		return $content;
	}
	
	protected function get_cell_group_label($group_label, $indice=0) {
		$content = '';
		switch($this->applied_group[$indice]) {
			case 'typdoc':
				$marc_list_instance = marc_list_collection::get_instance('doctype');
				$content .= $marc_list_instance->table[$group_label];
				break;
			default :
				$content .= parent::get_cell_group_label($group_label, $indice);
				break;
		}
		return $content;
	}
	
	protected function get_grouped_label($object, $property) {
	    $grouped_label = '';
	    switch($property) {
	        case 'authors_others':
	            //TODO
	            break;
	        case 'categories':
	            $grouped_label = strip_tags($this->get_cell_categories_content($object));
	            break;
	        case 'publisher_name':
	            //@TODO
	            break;
	        default:
	            $grouped_label = parent::get_grouped_label($object, $property);
	            break;
	    }
	    return $grouped_label;
	}
	
	protected function _get_object_property_author_main($object) {
		$notice_id = $this->get_notice_id_from_object($object);
		$record_datas = record_display::get_record_datas($notice_id);
		return $record_datas->get_auteurs_principaux();
	}
	
	protected function _get_object_property_authors_secondary($object) {
		$notice_id = $this->get_notice_id_from_object($object);
		$record_datas = record_display::get_record_datas($notice_id);
		return $record_datas->get_auteurs_secondaires();
	}
	
	protected function _get_object_property_langues($object) {
		$notice_id = $this->get_notice_id_from_object($object);
		$record_datas = record_display::get_record_datas($notice_id);
		$langues = $record_datas->get_langues();
		return record_display::get_lang_list($langues['langues']);
	}
	
	protected function _get_object_property_languesorg($object) {
		$notice_id = $this->get_notice_id_from_object($object);
		$record_datas = record_display::get_record_datas($notice_id);
		$langues = $record_datas->get_langues();
		return record_display::get_lang_list($langues['languesorg']);
	}
	
	protected function _get_object_property_statut_name($object) {
		$notice_id = $this->get_notice_id_from_object($object);
		$record_datas = record_display::get_record_datas($notice_id);
		return $record_datas->get_statut_notice();
	}
	
	protected function _get_object_property_typdoc($object) {
		$marc_list_instance = marc_list_collection::get_instance('doctype');
		return $marc_list_instance->table[$object->typdoc];
	}
	
	protected function _get_object_property_opac_permalink($object) {
		global $opac_url_base;
		
		switch (static::$object_type) {
			case 'BULL':
				return $opac_url_base."index.php?lvl=bulletin_display&id=".$object->id;
			case 'NOTI':
				return $opac_url_base."index.php?lvl=notice_display&id=".$object->id;
		}
	}
	
	protected function _get_object_property_total_loans($object) {
		return exemplaire::get_nb_prets_from_id($object->id);
	}
	
	protected function get_cell_content($object, $property) {
		$notice_id = $this->get_notice_id_from_object($object);
		switch($property) {
			case 'authors_others':
				//TODO
				break;
			case 'categories':
				$content = $this->get_cell_categories_content($object);
				break;
			case 'publisher_name' :
			    $publishers_name = array();
			    $record_datas = record_display::get_record_datas($notice_id);
			    $publishers = $record_datas->get_publishers();
			    if(count($publishers)) {
			        foreach ($publishers as $publisher) {
			            $publishers_name[] = $publisher->get_isbd();
			        }
			    }
			    $content = implode(' / ',$publishers_name);
			    break;
			default :
				$content = parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	public function get_export_icons() {
		global $msg;
		
		if($this->get_setting('display', 'search_form', 'export_icons')) {
			$export_icons = "<img  src='".get_url_icon('texte_ico.gif')."' style='border:0px' class='align_top' onMouseOver ='survol(this);' onclick=\"start_export('EXPORT_NOTI');\" alt='".$msg['etatperso_export_notice']."' title='".$msg['etatperso_export_notice']."'/>&nbsp;&nbsp;";
			$export_icons .= parent::get_export_icons();
			return $export_icons;
		}
		return "";
	}
	
	public function get_notice_id_from_object($object) {
		switch (static::$object_type) {
			case 'EXPL':
				return $object->notice_id;
			case 'BULL':
				//Notice de bulletin ? Si non notice de pério
				if($object->num_notice) {
					return $object->num_notice;
				} else {
					return $object->bulletin_notice;
				}
			case 'NOTI':
			default:
				return $object->id;
		}
	}
	
	public static function get_controller_url_base() {
		global $base_path;
		
		return $base_path.'/catalog.php?categ=caddie&sub=action&quelle=edition&action=choix_quoi&object_type='.static::$object_type.'&idcaddie='.static::$id_caddie.'&item=0';
	}
}