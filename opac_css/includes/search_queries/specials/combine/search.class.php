<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search.class.php,v 1.52.2.2 2023/04/20 08:49:27 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once($include_path."/rec_history.inc.php");

//Classe de gestion de la recherche sp�cial "combine"

class combine_search {
	public $id;
	public $n_ligne;
	public $params;
	public $search;
    public const IS_RESPONSIVE = true;

	//Constructeur
    public function __construct($id,$n_ligne,$params,&$search) {
		$this->id=$id;
		$this->n_ligne=$n_ligne;
		$this->params=$params;
		$this->search=&$search;
	}

	/**
	 * Fonction de r�cup�ration des op�rateurs disponibles pour ce champ sp�cial (renvoie un tableau d'op�rateurs)
	 * @return array Op�rateurs disponibles
	 */
	public function get_op() {
		$operators = array();
		if ($_SESSION["nb_queries"]!=0) {
			$operators["EQ"]="=";
		}
		return $operators;
	}

	/**
	 * Fonction de r�cup�ration de l'affichage de la saisie du crit�re
	 * @return string Chaine html
	 */
	public function get_input_box() {
		global $msg;
		global $charset;
		global $get_input_box_id;
			
		//R�cup�ration de la valeur de saisie
		$valeur_="field_".$this->n_ligne."_s_".$this->id;
		global ${$valeur_};
		$valeur=${$valeur_};
		
		$r = "";
		if ($_SESSION["nb_queries"]!=0) {
			if(!$get_input_box_id)$get_input_box_id="input_box_id_0";
			else	$get_input_box_id++;

			//$r="&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td colspan='3'>";
			$r .="<script type='text/javascript' src='./includes/javascript/tablist.js'></script>
			<div id='$get_input_box_id' class='notice-parent'>
    			<img src='./getgif.php?nomgif=plus' class='img_plus' name='imEx' id='$get_input_box_id"."Img' title='".addslashes($msg['plus_detail'])."' alt='".addslashes($msg['expand'])."' border='0' onClick=\"expandBase('$get_input_box_id', true); return false;\" hspace='3'>
    			<span class='notice-heada'>
    			<input type='hidden' name='field_".$this->n_ligne."_s_".$this->id."[]'  id='".$get_input_box_id."_value' value='!!value_selected!!'/>
    			<label id='".$get_input_box_id."_label' >!!label_selected!!</label>
    			</span>
			</div>
			<div id='$get_input_box_id"."Child' class='notice-child' style='margin-bottom:6px;display:none;width:94%'>
    			<table class='table-no-border'>
    			!!contenu!!
    			</table>
			</div>
			";

			if ($valeur) {
				if ($valeur[0]=='-1') {
					$r=str_replace("!!value_selected!!","-1", $r);
					$r=str_replace("!!label_selected!!",$msg["default_search_histo"], $r);
				}
			} else {
				$r=str_replace("!!value_selected!!","-1", $r);
				$r=str_replace("!!label_selected!!",$msg["default_search_histo"], $r);
			}

			$style_odd="class='odd' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='odd'\" ";
			$style_even="class='even' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='even'\" ";
			$onclick="onClick=\"document.getElementById('".$get_input_box_id."_label').innerHTML='".addslashes($msg["default_search_histo"])."';document.getElementById('".$get_input_box_id."_value').value='-1';expandBase('$get_input_box_id', true); return false;\"";

			$liste="<tr $style_even><td $onclick >".$msg["default_search_histo"]."</td></tr>";
			$bool=false;

			//parcours de l'historique des recherches
			for ($i=$_SESSION["nb_queries"]; $i>=1; $i--) {
				if(is_array($_SESSION["notice_view".$i]) && $_SESSION["notice_view".$i]["search_mod"]){
					$temp=html_entity_decode(strip_tags(($i).") ".substr(get_human_query_level_two($i),strpos(get_human_query_level_two($i),":")+2,strlen(get_human_query_level_two($i))-(strpos(get_human_query_level_two($i),":")+2))),ENT_QUOTES,$charset);
					if($_SESSION["search_type".$i] != "module"){
						$onclick="onClick=\"document.getElementById('".$get_input_box_id."_label').innerHTML=this.innerHTML;document.getElementById('".$get_input_box_id."_value').value='$i';expandBase('$get_input_box_id', true); return false;\"";
						if($i%2 == 0) $style=$style_odd;
						else $style=$style_even;
						$liste.="<tr $style><td $onclick >" . htmlentities($temp,ENT_QUOTES,$charset) . "</td></tr>";
					}
					if ($valeur) {
						if ($valeur[0]==$i) {
							$r=str_replace("!!value_selected!!","$i", $r);
							$r=str_replace("!!label_selected!!", htmlentities($temp,ENT_QUOTES,$charset), $r);
						}
					}
				}
			}
			$r=str_replace("!!contenu!!",$liste, $r);
		} else {
			$r .= "<b>".$msg["histo_empty"]."</b>";
		}
		return $r;
	}

	/**
	 * Fonction de conversion de la saisie en quelque chose de compatible avec l'environnement
	 */
	public function transform_input() {
	}

	/**
	 * Fonction de cr�ation de la requ�te (retourne une table temporaire)
	 * @return string Nom de la table temporaire
	 */
	public function make_search() {
			
		//R�cup�ration de la valeur de saisie
		$valeur_="field_".$this->n_ligne."_s_".$this->id;
		global ${$valeur_};
		$valeur=${$valeur_};
			
		if (!$this->is_empty($valeur)) {
			//enregistrement de l'environnement courant
			$this->search->push();

			if (!is_array($valeur)) {
			    $valeur = [$valeur];
			}
			
			if(is_numeric($valeur[0])){
				$mc = self::simple2mc($valeur[0]);
			}else{
				$mc = $valeur[0];
				$mc['search_instance'] = new search($mc['search_type']);
				$mc['search_instance']->unserialize_search($mc['serialized_search']);
			}
			$es = $mc['search_instance'];
			
			if($mc["search_type"]=="search_simple_fields" && $es !== null){
				$table_tempo=$es->make_search("tempo_".md5(serialize($valeur[0]).microtime(true)));
			} else {
				$searcher = new searcher_extended();
				$searcher->get_result();
				$table_tempo = $searcher->table;
			}
			
			//restauration de l'environnement courant
			$this->search->pull();
		}
		return $table_tempo;
	}
	
	/**
	 * Fonction de cr�ation de la recherche s�rialis�e (retourne un tableau s�rialis�)
	 * @return string Nom du tableau s�rialis�
	 */
	public function serialize_search() {
			
		//R�cup�ration de la valeur de saisie
		$valeur_="field_".$this->n_ligne."_s_".$this->id;
		global ${$valeur_};
		$valeur=${$valeur_};
			
		if (!$this->is_empty($valeur)) {
			//enregistrement de l'environnement courant
			$this->search->push();
				
			$mc = self::simple2mc($valeur[0]);
				
			$es = $mc['search_instance'];
				
			$retour=$es->serialize_search();
				
			//restauration de l'environnement courant
			$this->search->pull();
		}
		return $retour;
	}
	
	public function get_recursive() {
			
		//R�cup�ration de la valeur de saisie
		$valeur_="field_".$this->n_ligne."_s_".$this->id;
		global ${$valeur_};
		$valeur=${$valeur_};
	
		if (!$this->is_empty($valeur)) {
			//enregistrement de l'environnement courant
			$this->search->push();
			$mc = self::simple2mc($valeur[0],true);
			//restauration de l'environnement courant
			$this->search->pull();
		}
		unset($mc['search_instance']);
		return $mc;
	}
	
	

	/**
	 * Fonction de traduction litt�rale de la requ�te effectu�e (renvoie un tableau des termes saisis)
	 * @return array
	 */
	public function make_human_query() {
		global $include_path;

		$litteral=array();

		//R�cup�ration de la valeur de saisie
		$valeur_="field_".$this->n_ligne."_s_".$this->id;
		global ${$valeur_};
		$valeur=${$valeur_};

		if (!$this->is_empty($valeur)) {
			$litteral[0]= get_human_query_level_two($valeur[0]);
		}
		return $litteral;
	}

	public function make_unimarc_query() {
		//R�cup�ration de la valeur de saisie
		$valeur_="field_".$this->n_ligne."_s_".$this->id;
		global ${$valeur_};
		$valeur=${$valeur_};

		if (!$this->is_empty($valeur)) {

			//enregistrement de l'environnement courant
			$this->search->push();
			
			//on instancie la classe search avec le nom de la nouvelle table temporaire
			switch ($_SESSION["search_type".$valeur[0]]) {
				case 'simple_search':
					global $search;
					if(empty($search)) {
						$search=array();
					}
					switch($_SESSION["notice_view".$valeur[0]]["search_mod"]) {
						case 'title':
							$search[0]="f_6";
							$op_="BOOLEAN";
							$valeur_champ=$_SESSION["user_query".$valeur[0]];
							break;
						case 'all':
							$search[0]="f_7";
							$op_="BOOLEAN";
							$valeur_champ=$_SESSION["user_query".$valeur[0]];
							break;
						case 'abstract':
							$search[0]="f_13";
							$op_="BOOLEAN";
							$valeur_champ=$_SESSION["user_query".$valeur[0]];
							break;
						case 'keyword':
							$search[0]="f_12";
							$op_="BOOLEAN";
							$valeur_champ=$_SESSION["user_query".$valeur[0]];
							break;
						case 'author_see':
							$search[0]="f_8";
							$op_="EQ";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
						case 'categ_see':
							$search[0]="s_6";
							$op_="EQ";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
						case 'indexint_see':
							$search[0]="f_2";
							$op_="EQ";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
						case 'coll_see':
							$search[0]="f_4";
							$op_="EQ";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
						case 'publisher_see':
							$search[0]="f_3";
							$op_="EQ";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
						case 'subcoll_see':
							$search[0]="f_5";
							$op_="EQ";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
						case 'titre_uniforme_see':
							$search[0]="f_27";
							$op_="EQ";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
						case 'section_see':
							$search[0]="s_5";
							$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
							break;
					}
					//op�rateur
					$op="op_0_".$search[0];
					global ${$op};
					${$op}=$op_;

					//contenu de la recherche
					$field="field_0_".$search[0];
					$field_=array();
					$field_[0]=$valeur_champ;
					global ${$field};
					${$field}=$field_;

					//op�rateur inter-champ
					$inter="inter_0_".$search[0];
					global ${$inter};
					${$inter}="";

					//variables auxiliaires
					$fieldvar_="fieldvar_0_".$search[0];
					global ${$fieldvar_};
					${$fieldvar_}="";
					$fieldvar=${$fieldvar_};

					$es=new search("search_simple_fields");
					break;
				case 'extended_search':
					get_history($valeur[0]);
					$es=new search();
					break;
				case 'term_search':
					global $search;
					if(empty($search)) {
						$search=array();
					}
					$search[0]="f_1";
					$op_="EQ";
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];

					//op�rateur
					$op="op_0_".$search[0];
					global ${$op};
					${$op}=$op_;

					//contenu de la recherche
					$field="field_0_".$search[0];
					$field_=array();
					$field_[0]=$valeur_champ;
					global ${$field};
					${$field}=$field_;

					//op�rateur inter-champ
					$inter="inter_0_".$search[0];
					global ${$inter};
					${$inter}="";

					//variables auxiliaires
					$fieldvar_="fieldvar_0_".$search[0];
					global ${$fieldvar_};
					${$fieldvar_}="";
					$fieldvar=${$fieldvar_};

					$es=new search("search_simple_fields");
					break;
				case 'module':
					global $search;
					if(empty($search)) {
						$search=array();
					}
					switch($_SESSION["notice_view".$valeur[0]]["search_mod"]) {
						case 'authperso_see':
							$search[0]="f_30";
							break;
						case 'concept_see':
							$search[0]="f_29";
							break;
						case 'serie_see':
							$search[0]="f_28";
							break;
						case 'publisher_see':
							$search[0]="f_3";
							break;
						case "titre_uniforme_see" :
							$search[0]="f_27";
							break;
						case "subcoll_see" :
							$search[0]="f_5";
							break;
						case "coll_see" :
							$search[0]="f_4";
							break;
						case 'author_see' :
							$search[0]="f_8";
							break;
						case 'categ_see':
							$search[0]="s_6";
							break;
						case 'indexint_see':
							$search[0]="f_2";
							break;
						case 'etagere_see':
							$search[0]="f_14";
							break;
						case 'section_see':
							$search[0]="s_5";
							global $search_localisation;
							$search_localisation=$_SESSION["notice_view".$valeur[0]]["search_location"];
							break;
					}

					$op_="EQ";
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];

					//op�rateur
					$op="op_0_".$search[0];
					global ${$op};
					${$op}=$op_;

					//contenu de la recherche
					$field="field_0_".$search[0];
					$field_=array();
					$field_[0]=$valeur_champ;
					global ${$field};
					${$field}=$field_;

					//op�rateur inter-champ
					$inter="inter_0_".$search[0];
					global ${$inter};
					${$inter}="";

					//variables auxiliaires
					$fieldvar_="fieldvar_0_".$search[0];
					global ${$fieldvar_};
					//fieldvar attention pour la section
					${$fieldvar_}="";
					$fieldvar=${$fieldvar_};

					$es=new search("search_simple_fields");
					break;

			}

			$mt=$es->make_unimarc_query();

			//restauration de l'environnement courant
			$this->search->pull();

		}
		return $mt;
	}

	/**
	 * Fonction de d�coupage d'une chaine trop longue
	 * @param string $valeur Chaine � d�couper
	 * @return string Chaine d�coup�e
	 */
	public function cutlongwords($valeur) {
		if (strlen($valeur)>=50) {
			$pos=strrpos(substr($valeur,0,50)," ");
			if ($pos) {
				$valeur=substr($valeur,0,$pos+1)."...";
			}
		}
		return $valeur;
	}

	/**
	 * Fonction de v�rification du champ saisi ou s�lectionn�
	 * @param array $valeur Champ saisi ou s�lectionn�
	 * @return boolean true si vide
	 */
	public function is_empty($valeur) {
	    if (is_array($valeur) && count($valeur) > 0) {
	        if ($valeur[0]=="-1") {
	            return true;
	        } else {
	            return ($valeur[0] === false);
	        }
		} else {
		    // empty("0") = true
		    if (empty($valeur) && $valeur != "0") {
    			return true;
		    }
	        return false;
		}
	}

	/**
	 * Transforme une recherche simple en recherche multicrit�re
	 * @param int $index_history index de la recherche dans l'historique
	 * @return array Tableau (
	 * 			'serialized_search' => Recherche s�rialis�e,
				'search_type' => type de recherche,
				'search_instance' => instance de search
				)
	 */
	public static function simple2mc($index_history,$recursive_history=false) {
		global $opac_indexation_docnum_allfields;
		global $opac_search_other_function;
		$table_tempo = "";
		$xml_file="search_simple_fields";
		//on instancie la classe search avec le nom de la nouvelle table temporaire
		if(isset($_SESSION["search_type".$index_history])) {
			switch ($_SESSION["search_type".$index_history]) {
				case 'simple_search':
					global $search;
					if(empty($search)) {
						$search=array();
					}
					if ($opac_search_other_function) search_other_function_get_history($index_history);
					if(isset($_SESSION["notice_view".$index_history]["search_mod"])) {
						switch($_SESSION["notice_view".$index_history]["search_mod"]) {
							case 'title':
								$search[0]="f_6";
								$op_="BOOLEAN";
								$valeur_champ=$_SESSION["user_query".$index_history];
								break;
							case 'all':
								$search[0]="f_7";
								$op_="BOOLEAN";
								$valeur_champ=$_SESSION["user_query".$index_history];
								$t["is_num"][0]= $opac_indexation_docnum_allfields;
								$t["ck_affiche"][0]=$opac_indexation_docnum_allfields;
								break;
							case 'abstract':
								$search[0]="f_13";
								$op_="BOOLEAN";
								$valeur_champ=$_SESSION["user_query".$index_history];
								break;
							case 'keyword':
								$search[0]="f_12";
								$op_="BOOLEAN";
								$valeur_champ=$_SESSION["user_query".$index_history];
								break;
							case 'author_see':
								$search[0]="f_8";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'categ_see':
								$search[0]="s_6";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'indexint_see':
								$search[0]="f_2";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'coll_see':
								$search[0]="f_4";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'publisher_see':
								$search[0]="f_3";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'subcoll_see':
								$search[0]="f_5";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'titre_uniforme_see':
								$search[0]="f_27";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'serie_see':
								$search[0]="f_28";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'concept_see':
								$search[0]="f_29";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'docnum':
								$search[0]="f_16";
								$op_="BOOLEAN";
								$valeur_champ = $_SESSION["user_query".$index_history];
								break;
							case 'authperso_see':
								$search[0]="f_30";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'etagere_see':
								$search[0]="f_14";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
							case 'section_see':
								$xml_file='';
								$search[0]="s_5";
								$op_="EQ";
								$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
								break;
						}
						//op�rateur
						$op="op_0_".$search[0];
						global ${$op};
						${$op}=$op_;
		
						//contenu de la recherche
						$field="field_0_".$search[0];
						$field_=array();
						$field_[0]=(isset($valeur_champ) ? $valeur_champ : '');
						global ${$field};
						${$field}=$field_;
		
						//op�rateur inter-champ
						$inter="inter_0_".$search[0];
						global ${$inter};
						${$inter}="";
		
						//variables auxiliaires
						$fieldvar_="fieldvar_0_".$search[0];
						global ${$fieldvar_};
						if(isset($t)) ${$fieldvar_}=$t;
						else ${$fieldvar_}="";
						$fieldvar=${$fieldvar_};
	
						if(isset($_SESSION["typdoc".$index_history]) && $_SESSION["typdoc".$index_history]){
							$search[1]="f_9";
							$op_="EQ";
							$valeur_champ=$_SESSION["typdoc".$index_history];
							//op�rateur
							$op="op_1_".$search[1];
							global ${$op};
							${$op}=$op_;
		
							//contenu de la recherche
							$field="field_1_".$search[1];
							$field_=array();
							$field_[0]=$valeur_champ;
							global ${$field};
							${$field}=$field_;
		
							//op�rateur inter-champ
							$inter="inter_1_".$search[1];
							global ${$inter};
							${$inter}="and";
						}
					}	
					$es=new search($xml_file);
					$serialized = $es->serialize_search();
					break;
				case 'extended_search':
					if(isset($search[0]) && $search[0] == 's_9'){
						$es=new search("search_simple_fields");
						$serialized = $es->serialize_search($recursive_history);
						$search_type="search_simple_fields";
					}else{
						get_history($index_history);
						$es=new search("search_fields");
						$serialized = $es->serialize_search($recursive_history);
						$search_type="search_fields";
					}
					break;
				case 'term_search':
					global $search;
					if(empty($search)) {
						$search=array();
					}
					$search[0]="f_1";
					$op_="EQ";
					$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
	
					//op�rateur
					$op="op_0_".$search[0];
					global ${$op};
					${$op}=$op_;
	
					//contenu de la recherche
					$field="field_0_".$search[0];
					$field_=array();
					$field_[0]=$valeur_champ;
					global ${$field};
					${$field}=$field_;
	
					//op�rateur inter-champ
					$inter="inter_0_".$search[0];
					global ${$inter};
					${$inter}="";
	
					//variables auxiliaires
					$fieldvar_="fieldvar_0_".$search[0];
					global ${$fieldvar_};
					${$fieldvar_}="";
					$fieldvar=${$fieldvar_};
	
					$es=new search("search_simple_fields");
					$serialized = $es->serialize_search();
					break;
				case 'module':
					global $search;
					if(empty($search)) {
						$search=array();
					}
					switch($_SESSION["notice_view".$index_history]["search_mod"]) {
						case 'authperso_see':
							$search[0]="f_30";
							break;
						case 'concept_see':
							$search[0]="f_29";
							break;
						case 'serie_see':
							$search[0]="f_28";
							break;
						case 'publisher_see':
							$search[0]="f_3";
							break;
						case "titre_uniforme_see" :
							$search[0]="f_27";
							break;
						case "subcoll_see" :
							$search[0]="f_5";
							break;
						case "coll_see" :
							$search[0]="f_4";
							break;
						case 'author_see':
							$search[0]="f_8";
							break;
						case 'categ_see':
							$xml_file='';
							$search[0]="s_6";
							break;
						case 'indexint_see':
							$search[0]="f_2";
							break;
						case 'etagere_see':
							$search[0]="f_14";
							break;
						case 'section_see':
							$xml_file='';
							$search[0]="s_5";
							global $search_localisation;
							$search_localisation=$_SESSION["notice_view".$index_history]["search_location"];
							break;
					}
	
					$op_="EQ";
					$valeur_champ=$_SESSION["notice_view".$index_history]["search_id"];
	
					//op�rateur
					$op="op_0_".$search[0];
					global ${$op};
					${$op}=$op_;
	
					//contenu de la recherche
					$field="field_0_".$search[0];
					$field_=array();
					$field_[0]=$valeur_champ;
					global ${$field};
					${$field}=$field_;
	
					//op�rateur inter-champ
					$inter="inter_0_".$search[0];
					global ${$inter};
					${$inter}="";
	
					//variables auxiliaires
					$fieldvar_="fieldvar_0_".$search[0];
					global ${$fieldvar_};
					//fieldvar attention pour la section
					${$fieldvar_}="";
					$fieldvar=${$fieldvar_};
	
					$es=new search($xml_file);
					$serialized = $es->serialize_search();
					break;
			}
		}
		return array(
				'serialized_search' => $serialized,
				'search_type' => (isset($search_type) && $search_type?$search_type:"search_simple_fields"),
				'search_instance' => $es
		);
	}
	
	public static function etagere2mc($id) {
		global $search;
		
		$id = intval($id);
		if(empty($search)) {
			$search=array();
		}
		$search[0]="f_14";
		
		//op�rateur
		$op="op_0_".$search[0];
		global ${$op};
		${$op}="EQ";
		
		//contenu de la recherche
		$field="field_0_".$search[0];
		$field_=array();
		$field_[0]=$id;
		global ${$field};
		${$field}=$field_;
		
		$es=new search('search_simple_fields');
		return array(
				'serialized_search' => $es->serialize_search(),
				'search_type' => 'search_simple_fields',
				'search_instance' => $es
		);
	}
			
	public static function simple_search_to_mc($user_query = '', $to_json = false, $type = TYPE_NOTICE, &$search_instance = null) {
	    global $opac_indexation_docnum_allfields;
	    global $opac_search_other_function;
	    global $search;
	    
	    if (!isset($search) || !is_array($search)) {
	        $search = array();
	    }
	    if (!is_object($search_instance)) {
    	    if ($type == TYPE_NOTICE) {
    	        //$xml_file="search_simple_fields";
    	        $xml_file="search_fields";
    	        $search_instance = new search($xml_file);
    	    } else if($type == TYPE_ANIMATION) {
    	        $xml_file="search_fields_animations";
    	        $search_instance = new search($xml_file);
    	    } else if($type == TYPE_CMS_ARTICLE) {
    	        $xml_file="search_fields_cms_article";
    	        $search_instance = new search($xml_file);
    	    } else if($type == TYPE_CMS_SECTION) {
    	        $xml_file="search_fields_cms_section";
    	        $search_instance = new search($xml_file);
    	    } else if($type > 10000) {
    	        $xml_file="search_fields_ontology";
    	        $class_id = ((int) $type - 10000);
    	        $ontology = new ontology(ontologies::get_ontology_id_from_class_uri(onto_common_uri::get_uri($class_id)));
    	        $search_instance = new search_ontology($xml_file,$ontology->get_handler()->get_ontology());
    	    } else {
    	        $xml_file="search_fields_authorities";
    	        $search_instance = new search_authorities($xml_file);
    	    }
	    }
	    
		$valeur_champ = '';
		$op_="BOOLEAN";
		
		//on calcule le nouvel index de la recherche
		$n = count($search);
		switch($type){
			case TYPE_NOTICE:
				$search[$n]="f_42";
				break;
			case TYPE_AUTHOR:
				$search[$n]="f_1102";
				break;
			case TYPE_CATEGORY:
				$search[$n]="f_2102";
				break;
			case TYPE_PUBLISHER:
				$search[$n]="f_3102";
				break;
			case TYPE_COLLECTION:
				$search[$n]="f_4102";
				break;
			case TYPE_SUBCOLLECTION:
				$search[$n]="f_5102";
				break;
			case TYPE_SERIE:
				$search[$n]="f_6102";
				break;
			case TYPE_TITRE_UNIFORME:
				$search[$n]="f_7102";
				break;
			case TYPE_INDEXINT:
				$search[$n]="f_8102";
				break;
			case TYPE_CONCEPT:
			    $search[$n] = "f_11102";
			    break;
			case TYPE_EXTERNAL:
			    $search[$n]="f_42";
			    break;
			case TYPE_AUTHPERSO:
			    if ((int) $type > 1000) {
			        $id_authperso = ((int) $type - 1000);
			        $search[$n] = "authperso_$id_authperso";
			    }
				break;
			case TYPE_ANIMATION:
			    $search[$n]="f_1";
				break;
			case TYPE_CMS_ARTICLE:
			    $search[$n]="f_1";
				break;
			case TYPE_CMS_SECTION:
			    $search[$n]="f_1";
				break;
			default :
			    // DONT PUSH
			    if((int) $type > 10000){
			        $class_id = ((int) $type - 10000);
			        $ontology = new ontology(ontologies::get_ontology_id_from_class_uri(onto_common_uri::get_uri($class_id)));
			        $onto_ontology =$ontology->get_handler()->get_ontology();
			        foreach ($onto_ontology->get_classes() as $c) {
			            $uri  = onto_common_uri::get_uri($class_id);
			            if($uri == $c->uri) {
                            $class = $onto_ontology->get_class($c->uri);
			                $field = $class->field;
			                break;
			            }
			        }
			        $search[$n] = 'f_'.$field.'s42'; 
			    }
			    break;
		}
        $valeur_champ = $user_query;
        $t["is_num"][$n]= $opac_indexation_docnum_allfields;
        $t["ck_affiche"][$n]=$opac_indexation_docnum_allfields;

	    //op�rateur
	    $op="op_".$n."_".$search[$n];
	    global ${$op};
	    ${$op}=$op_;
	    //contenu de la recherche
	    $field="field_".$n."_".$search[$n];
	    $field_=array();
	    $field_[0]=(isset($valeur_champ) ? $valeur_champ : '');
	    global ${$field};
	    ${$field}=$field_;
	    
	    //op�rateur inter-champ
	    $inter="inter_".$n."_".$search[$n];
	    global ${$inter};
		if ($n == 0) {
	        ${$inter}="";
	    } else {
	        ${$inter}="and";
	    }
	    
	    //variables auxiliaires
	    $fieldvar_="fieldvar_".$n."_".$search[$n];
	    global ${$fieldvar_};
	    if(isset($t)) ${$fieldvar_}=$t;
	    else ${$fieldvar_}="";
	    $fieldvar=${$fieldvar_};
	    if ($to_json) {
	        return $search_instance->json_encode_search();
	    }
	    return $search_instance->serialize_search();
	}
	
	public function get_input_box_responsive() {
	    global $msg;
	    global $charset;
	    global $get_input_box_id;
	    
	    //R�cup�ration de la valeur de saisie
	    $valeur_="field_".$this->n_ligne."_s_".$this->id;
	    global ${$valeur_};
	    $valeur=${$valeur_};
	    
	    $content = "";
	    
	    if ($_SESSION["nb_queries"]!=0) {
	        $selected = "";
	        if (empty($valeur) || (!empty($valeur) && $valeur[0]=='-1')) {
	            $selected = "selected";
	        }
	        $liste = "<option value='-1' $selected>".$msg["default_search_histo"]."</option>";
	        
    	    //parcours de l'historique des recherches
    	    for ($i=$_SESSION["nb_queries"]; $i>=1; $i--) {
    	        if(is_array($_SESSION["notice_view".$i]) && $_SESSION["notice_view".$i]["search_mod"]){
    	            $temp=html_entity_decode(strip_tags(($i).") ".substr(get_human_query_level_two($i),strpos(get_human_query_level_two($i),":")+2,strlen(get_human_query_level_two($i))-(strpos(get_human_query_level_two($i),":")+2))),ENT_QUOTES,$charset);
    	            if($_SESSION["search_type".$i] != "module"){
    	                $selected = "";
    	                if (!empty($valeur) && $valeur[0]==$i) {
    	                    $selected = "selected";
    	                }
    	                $liste.="<option value='$i' $selected>" . htmlentities($temp,ENT_QUOTES,$charset) . "</option>";
    	            }
    	        }
    	    }
    	    $content = "<select class='rmc_special_field' name='field_".$this->n_ligne."_s_".$this->id."[]'>$liste</select>";
	    }else {
	        $content = "<b>".$msg["histo_empty"]."</b>";
	    }
	    
	    return $content;
	}
}
?>