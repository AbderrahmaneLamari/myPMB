<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facette_search_compare.class.php,v 1.22.2.1 2023/07/05 15:50:03 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($include_path."/templates/facette_search_compare.tpl.php");
require_once("$class_path/notice_tpl_gen.class.php");
require_once("$class_path/mono_display.class.php");
require_once("$class_path/encoding_normalize.class.php");

class facette_search_compare {
	
	public $facette_compare=array();
	public $facette_groupby=array();
	public $result=array();
	public $headers=array();
	
	public $notice_tpl;
	public $notice_nb;
	public $max_display=1000;
	public $first_collumn_size=15;
	
	public static $temporary_table_name = '';
	
	public function __construct() {
		global $pmb_compare_notice_template;
		global $pmb_compare_notice_nb;
		
		$this->notice_tpl=$pmb_compare_notice_template;
		$this->notice_nb=$pmb_compare_notice_nb;
		$this->facette_compare = static::get_compare_checked_session();
		$this->facette_groupby = static::get_groupby_checked_session();
	}
	
	public function get_display_parameters(){
		global $tpl_display_compare,$msg,$charset;
		
		$tpl_display_compare = str_replace('!!notice_nb_libelle!!', htmlentities($this->notice_nb,ENT_QUOTES,$charset), $tpl_display_compare);
		if($this->notice_tpl) {
			$tpl = notice_tpl_gen::get_instance($this->notice_tpl);
			$tpl_display_compare = str_replace('!!notice_tpl_libelle!!',htmlentities($tpl->name,ENT_QUOTES,$charset) , $tpl_display_compare);
		} else {
			$tpl_display_compare = str_replace('!!notice_tpl_libelle!!',htmlentities($msg['notice_tpl_list_default'],ENT_QUOTES,$charset) , $tpl_display_compare);
		}
		return $tpl_display_compare;
	}
	
	public function get_content_form() {
		$interface_content_form = new interface_content_form(static::class);
		$sel_notice_tpl=notice_tpl_gen::gen_tpl_select("notice_tpl",$this->notice_tpl,'');
		$interface_content_form->add_element('notice_tpl', 'notice_tpl_label')
		->add_html_node($sel_notice_tpl);
		$interface_content_form->add_element('notice_nb', 'notice_nb_label')
		->add_input_node('integer', $this->notice_nb);
		return $interface_content_form->get_display();
	}
	
	public function get_form(){
		$interface_form = new interface_admin_form('form_compare');
		$interface_form->set_content_form($this->get_content_form());
		return $interface_form->get_display_parameters();
	}
	
	public function save_form(){
		global $pmb_compare_notice_template,$pmb_compare_notice_nb;
		global $notice_tpl;
		global $notice_nb;
		
		$this->notice_tpl = intval($notice_tpl);
		$this->notice_nb = intval($notice_nb);
		
		$query="UPDATE parametres SET valeur_param='".$this->notice_tpl."' WHERE type_param='pmb' AND sstype_param='compare_notice_template'";
		pmb_mysql_query($query);
		
		$query="UPDATE parametres SET valeur_param='".$this->notice_nb."' WHERE type_param='pmb' AND sstype_param='compare_notice_nb'";
		pmb_mysql_query($query);
		
		$pmb_compare_notice_template=$this->notice_tpl;
		$pmb_compare_notice_nb=$this->notice_nb;
		return true;
	}
	
	/**
	 * G�n�re un nom de table temporaire
	 * @param string $prefix
	 */
	public static function gen_temporary_table_name($prefix='compare_table'){
		static::$temporary_table_name = $prefix.md5(microtime(true));
	}
	
	/**
	 * Tableau d'identifiants d'objets
	 */
	protected function get_objects_compare($facette_compare) {
		$objects_ids = array();
		$query="SELECT DISTINCT(notice_id) as object_id FROM notices_fields_global_index
			JOIN ".static::$temporary_table_name." ON notices_fields_global_index.id_notice=".static::$temporary_table_name.".notice_id
			WHERE notices_fields_global_index.code_champ='$facette_compare[2]'
			AND notices_fields_global_index.code_ss_champ='$facette_compare[3]'
			AND notices_fields_global_index.value='".addslashes($facette_compare[1])."'";
		$result=pmb_mysql_query($query);
		while($row=pmb_mysql_fetch_object($result)){
			$objects_ids[]=$row->object_id;
		}
		return $objects_ids;
	}
	
	protected function get_query_groupby($facette_groupby, $tmpArray) {
		global $lang;
		$query = "SELECT value,id_notice FROM notices_fields_global_index
			WHERE id_notice IN (".implode(",", $tmpArray).")
			AND notices_fields_global_index.code_champ='$facette_groupby[1]'
			AND notices_fields_global_index.code_ss_champ='$facette_groupby[2]'
			AND (notices_fields_global_index.lang='$lang' OR notices_fields_global_index.lang='')";
		return $query;
	}
	
	protected function add_result($group_label, $value, $pos, $key, $groupby_key, $tmpArray=array()) {
		$this->result[$group_label][$pos] = array(
			'count' => count($tmpArray),
			'value' => $value,
			'pos' => $pos,
			'key' => $key,
			'groupby_key' => $groupby_key,
			'notices_ids' => implode(",", $tmpArray)
		);
	}
	
	protected function build_result() {
		global $msg;
		
		$pos=0;
		foreach($this->facette_compare as $key=>$facette_compare){
			//on remonte les ID de notices, dans la liste des ID de notices d�j� prente dans le recherche et qui ont une correspondance avec la valeur de la facette
			$tmpArray = $this->get_objects_compare($facette_compare);
			//on construit les entete du tableau
			if(!in_array($facette_compare[1], $this->headers)){
				$pos++;
				$this->headers[$pos]=$facette_compare[1];
			}
			if (!empty($tmpArray)) {
				//et on organise par crit�re de regroupement.
				if (!empty($this->facette_groupby)) {
					foreach ($this->facette_groupby as $key_groupby => $facette_groupby) {
						// je regroupe sous les valeurs de la facette choisie pour le regroupement
						$query = $this->get_query_groupby($facette_groupby, $tmpArray);
						$result=pmb_mysql_query($query);
						$tmpArray=array_flip($tmpArray);
						while($line=pmb_mysql_fetch_object($result)){
							if(!$this->result[$line->value][$pos]['count']){
								$this->add_result($line->value, $facette_compare[1], $pos, $key, $key_groupby);
								$this->result[$line->value][$pos]['count']=1;
							} else {
								$this->result[$line->value][$pos]['count']++;
							}
							if($this->result[$line->value][$pos]['notices_ids']){
								$this->result[$line->value][$pos]['notices_ids'].=",";
							}
							$this->result[$line->value][$pos]['notices_ids'].=$line->id_notice;
							unset($tmpArray[$line->id_notice]);
						}
						if (!empty($tmpArray)) {
							$tmpArray = array_flip($tmpArray);
							//pas de valeur de regroupement, je regroupe sous le message facettes_not_grouped
							$this->add_result($msg['facettes_not_grouped'], $facette_compare[1], $pos, $key, $key_groupby, $tmpArray);
						}
					}
				} else {
					//pas de valeur de regroupement, je regroupe sous le message facettes_not_grouped
					$this->add_result($msg['facettes_not_grouped'], $facette_compare[1], $pos, $key, 0, $tmpArray);
				}
			}
			//le tri du r�sultat
			if (!empty($this->result)) {
				self::sort_compare($this->result);
			}
		}
	}
	
	/**
	 * On lance la comparaison � partir d'un r�sultat de recherche
	 * Rempli la variables result
	 * @param object searcher $searcher
	 * @return true si succ�s message d'erreur sinon
	 */
	public function compare($searcher){
		self::session_facette_compare($this);
	
		if (!empty($this->facette_compare)) {
			$listeNotices = "0";
			if($searcher->get_nb_results()){
				$listeNotices = $searcher->get_result();
			}
			//on insert les notices du searcher en table memoire
			self::gen_temporary_table_name();
			$query = "CREATE TEMPORARY TABLE ".static::$temporary_table_name." engine=memory SELECT notice_id FROM notices WHERE notice_id IN (".$listeNotices.")";
			pmb_mysql_query($query);
			$query = "ALTER TABLE ".static::$temporary_table_name." engine=memory ADD INDEX notice_id_index BTREE (notice_id)";
			pmb_mysql_query($query);
				
			//pour toutes les facettes choisies en comparaison
			$this->build_result();
				
			//Si trop de r�sultat, la g�n�ration du tableau html sera trop longue = on coupe.
			if ((count($this->result) * count($this->facette_compare)) > $this->max_display) {
				return 'facette_compare_too_more_result';
			}
			return true;
		}else{
			//pas de r�sultat
			return 'facette_compare_no_result';
		}
	}
	
	/**
	 * La fonction d'affichage du comparateur de notices
	 * @return string affichage en mode comparateur
	 */
	public function display_compare(){
		global $facette_search_compare_wrapper;
		global $facette_search_compare_header;
		global $facette_search_compare_line;
		global $facette_search_compare_element;
		global $facette_search_compare_hidden_line;
		global $facette_search_compare_hidden_element;
		
		//script
		$compare_wrapper_script="";
		$facette_search_compare_wrapper=str_replace("!!compare_wrapper_script!!", $compare_wrapper_script, $facette_search_compare_wrapper);
		
		$body="";
		$header="";
		
		if (!empty($this->result)) {
			//Les entetes
			foreach ($this->headers as $compareHeader) {
				$header.= $facette_search_compare_header;
				$header = str_replace("!!compare_hearder_libelle!!", $compareHeader, $header);
			}
			
			//les tailles CSS
			$facette_search_compare_wrapper = str_replace("!!first_collumn_size!!", $this->first_collumn_size, $facette_search_compare_wrapper);
			$cullumn_size = round((100 - $this->first_collumn_size) / count($this->headers));
			$facette_search_compare_wrapper = str_replace("!!cullumn_size!!", $cullumn_size, $facette_search_compare_wrapper);
			
			//les lignes
			$even_odd='even';
			foreach($this->result as $groupedby=>$comparedElements){
				
				//une ligne
				$line=$facette_search_compare_line;
				$line=str_replace("!!even_odd!!",$even_odd , $line);
				if($even_odd=='even'){
					$even_odd='odd';
				}else{
					$even_odd='even';
				}
				$line=str_replace("!!groupedby_libelle!!",$groupedby , $line);
				$line=str_replace("!!compare_line_onclick!!",'toggle_hidden_line(this,"compare_hidden_line_'.$groupedby.'")' , $line);
				//et la ligne cach�e
				$hidden_line=$facette_search_compare_hidden_line;
				$hidden_line=str_replace("!!compare_hidden_line_id!!", 'compare_hidden_line_'.$groupedby, $hidden_line);
				
				//chacun des elements et elements cach�s d'une ligne
				$elements="";
				$hidden_elements="";
				$nb_headers = count($this->headers);
				for ($i = 1; $i < $nb_headers + 1; $i++) {
					//un element d'une ligne
					$element = $facette_search_compare_element;
					$hidden_element = $facette_search_compare_hidden_element;
					
					if($comparedElements[$i]['notices_ids']){
						$element=str_replace("!!compare_element_libelle!!", $comparedElements[$i]['count'], $element);
						
						$notices=static::call_notice_display($comparedElements[$i]['notices_ids'],$this->notice_nb,$this->notice_tpl);
						
						$hidden_element=str_replace("!!compare_hidden_element_libelle!!", $notices, $hidden_element);
					}else{
						$element=str_replace("!!compare_element_libelle!!", '', $element);
						
						$hidden_element=str_replace("!!compare_hidden_element_libelle!!", '', $hidden_element);
					}
					
					//on renseigne le boutton "..." si besoin
					if($comparedElements[$i]['notices_ids']){
						$hidden_element=str_replace("!!compare_hidden_line_see_more!!", self::get_compare_see_more($comparedElements[$i]['notices_ids']), $hidden_element);
					}else{
						$hidden_element=str_replace("!!compare_hidden_line_see_more!!", '', $hidden_element);
					}
					
					$elements.=$element;
					$hidden_elements.=$hidden_element;
				}
				
				$hidden_line=str_replace("!!compare_hidden_line_elements!!", $hidden_elements, $hidden_line);
				$line=str_replace("!!compare_line_elements!!", $elements, $line);
				
				//on ajoute la ligne visible et invisible
				$body.=$line.$hidden_line;
			}
		}
		
		//et on injecte le HTML
		$facette_search_compare_wrapper=str_replace("!!compare_header!!", $header, $facette_search_compare_wrapper);
		$facette_search_compare_wrapper=str_replace("!!compare_body!!", $body, $facette_search_compare_wrapper);
		
		//construction du lien AJAX
		if(static::class == 'facettes_external_search_compare') {
			$facette_search_compare_wrapper=str_replace("!!categ!!", "facettes_external", $facette_search_compare_wrapper);
		} else {
			$facette_search_compare_wrapper=str_replace("!!categ!!", "facettes", $facette_search_compare_wrapper);
		}
		
		return $facette_search_compare_wrapper;
	}
	
	/**
	 * On cr�� le tableau des �l�ments � comparer, au dessus du menu des facettes
	 * @return string le tableau de selection des valeurs de comparaisons
	 */
	public function gen_table_compare() {
		global $charset,$msg;
		
		$table_compare='';
		if(is_array($this->facette_compare)) {
			foreach($this->facette_compare as $key=>$facette_compare){
				if(!$facette_compare['available']){
					$balise_start="<del>";
					$balise_stop="</del>";
				}else{
					$balise_start="<p>";
					$balise_stop="</p>";
				}
				$table_compare.='<tr>';
				$table_compare.='<td style="width:90%;">';
				$table_compare.=$balise_start.htmlentities($facette_compare[0],ENT_QUOTES,$charset).' : '.htmlentities(static::get_formatted_value($facette_compare[2], $facette_compare[3], $facette_compare[1]),ENT_QUOTES,$charset).$balise_stop;
				$table_compare.='<input id="compare_facette_'.$key.'" type="hidden" value="'.htmlentities($facette_compare['value'],ENT_QUOTES,$charset).'" name="check_facette_compare[]"/>';
				$table_compare.='</td>';
				$table_compare.='<td>';
				$table_compare.='<span class="facette_compare">';
				$table_compare.='<img  width="18px" height="18px" title="'.$msg['facette_compare_remove'].'" alt="'.$msg['facette_compare_remove'].'" class="facette_compare_close" onclick="remove_compare_facette(\''.htmlentities(addslashes($facette_compare['value']),ENT_QUOTES,$charset).'\');"  src="'.get_url_icon('cross.png').'"/>';
				$table_compare.='</span>';
				$table_compare.='</td>';
				$table_compare.='</tr>';
			}
		}
		return $table_compare;
	}
	
	/**
	 * @return string le tableau de selection des valeurs de groupement
	 */
	public function gen_table_groupby(){
		global $charset,$msg;
		
		$table_groupby='';
		if(is_array($this->facette_groupby)) {
			foreach($this->facette_groupby as $facette_groupby){
				if(!$facette_groupby['available']){
					$balise_start="<del>";
					$balise_stop="</del>";
				}else{
					$balise_start="<p>";
					$balise_stop="</p>";
				}
				$table_groupby.='
					<tr>
						<td style="width:90%;">'.$balise_start.$facette_groupby[0].$balise_stop.'</td>
						<td><img height="18px" width="18px" title="'.$msg['facette_compare_remove'].'" alt="'.$msg['facette_compare_remove'].'" class="facette_compare_close" src="'.get_url_icon('cross.png').'" onclick="group_by(\''.htmlentities(addslashes($facette_groupby['value']),ENT_QUOTES,$charset).'\');valid_facettes_compare();"/></td>
					</tr>';
			}
		}
		return $table_groupby;
	}
	
	/**
	 * si une des facette n'est pas d�j� choisie pour comparer et n'est pas utilis� en recherche, on la rend active pour pouvoir etre utilis� en comparaison
	 * @param string $id l'id de la facette concern�e 
	 * @param bool $available 
	 */
	public function set_available_compare($id,$available=true){
 		$this->facette_compare[$id]['available']=$available;
		$_SESSION['check_facette_compare'][$id]['available']=$available;
	}
	
	/**
	 * Si un groupe n'est pas d�j� choisi et dont un �lement au moins est disponible pour la recherche, on le rend actif pour pouvoir etre utilis� en groupement
	 * @param integer $id l'id du groupe
	 * @param bool $available
	 */
	public function set_available_groupby($id,$available=true){
 		$this->facette_groupby[$id]['available']=$available;
		$_SESSION['check_facette_groupby'][$id]['available']=$available;
	}
	
	public static function sort_compare(&$array) {
		global $msg;
		
		$tmp = array();
		if (!empty($array[$msg['facettes_not_grouped']])) {
			$tmp = $array[$msg['facettes_not_grouped']];
			unset($array[$msg['facettes_not_grouped']]);
		}
		
		krsort($array);
		
		if (!empty($tmp)) {
			$array[$msg['facettes_not_grouped']] = $tmp;
		}
		return $array;
	}
	
	/**
	 * Classe permettant d'appeler l'affichage des notices
	 * Retire de la liste envoy�e en r�f�rence les notices d�j� affich�es
	 * @param string $notices_ids la liste des notices, s�par�es par ,
	 * @param integer $notice_nb le nombre de notices � afficher par passe
	 * @param integer $notice_tpl l'identifiant du template d'affichage, si null, affiche le header de la classe d'affichage
	 */
	public static function call_notice_display(&$notices_ids, $notice_nb, $notice_tpl) {
		$notices_ids = explode(",", $notices_ids);
		
		$notices = '';
		for ($i_notice_nb = 0; $i_notice_nb < $notice_nb; $i_notice_nb++) {
			if (!empty($notices_ids[$i_notice_nb])) {
				$notices .= '<li>';
				
				// notice de monographie
				$nt = new mono_display($notices_ids[$i_notice_nb], 0);
				$notices .= $nt->header;
				
				unset($notices_ids[$i_notice_nb]);
				$notices .= '</a>';
				$notices .= '</li>';
			}
		}
		
		if (!empty($notices_ids)) {
			$notices_ids = implode(',', $notices_ids);
		}
		return $notices;
	}
	
	/**
	 * Passage en session des valeurs du comparateur
	 * Ou revalidation des variables de classe courrante � partir des variables de session
	 * @param facette_search_compare $facette_search_compare
	 */
	public static function session_facette_compare($facette_search_compare=null,$reinit_compare=false){
		global $check_facette_compare;
		global $check_facette_groupby;
		global $charset;
		
		if($facette_search_compare){
			$facette_search_compare->facette_compare=static::get_compare_checked_session();
			$facette_search_compare->facette_groupby=static::get_groupby_checked_session();
		}elseif($reinit_compare){
			if($facette_search_compare){
				$facette_search_compare->facette_compare=array();
			}
			static::set_compare_checked_session(array());
		} else {
			if (!empty($check_facette_compare)) {
				static::set_compare_checked_session(array());
				foreach ($check_facette_compare as $f_c) {
					$f_c=stripslashes($f_c);
					$f_c = encoding_normalize::utf8_normalize($f_c);
					$f_c_tab=pmb_utf8_array_decode(json_decode($f_c));
					if($charset!='utf-8'){
						$f_c=utf8_decode($f_c);
					}
					if($f_c!=''){
						$facettes_compare_checked = static::get_compare_checked_session();
						$facettes_compare_checked[$f_c_tab[4]]=$f_c_tab;
						$facettes_compare_checked[$f_c_tab[4]]['value']=$f_c;
						static::set_compare_checked_session($facettes_compare_checked);
					}else{
					    if (static::class == 'facettes_external_search_compare') {
							unset($_SESSION['check_facettes_external_compare'][$f_c_tab[4]]);
						} else {
							unset($_SESSION['check_facette_compare'][$f_c_tab[4]]);
						}
					}
				}
			}else{
				static::unset_compare_checked_session();
			}
		
			if (!empty($check_facette_groupby)) {
				static::set_groupby_checked_session(array());
				foreach ($check_facette_groupby as $f_gb) {
					$f_gb=stripslashes($f_gb);
					$f_gb = encoding_normalize::utf8_normalize($f_gb);
					$f_gb_tab=pmb_utf8_array_decode(json_decode($f_gb));
					if($charset!='utf-8'){
						$f_gb=utf8_decode($f_gb);
					}
					if($f_gb!=''){
						$facettes_groupby_checked = static::get_groupby_checked_session();
						$facettes_groupby_checked[$f_gb_tab[3]]=$f_gb_tab;
						$facettes_groupby_checked[$f_gb_tab[3]]['value']=$f_gb;
						static::set_groupby_checked_session($facettes_groupby_checked);
					}else{
					    if (static::class == 'facettes_external_search_compare') {
							unset($_SESSION['check_facettes_external_groupby'][$f_gb_tab[3]]);
						} else {
							unset($_SESSION['check_facette_groupby'][$f_gb_tab[3]]);
						}
					}
				}
			}else{
				static::unset_groupby_checked_session();
			}
		}
	}
	
	/**
	 * On renvoi un id de facette en fonction de ses �l�ments
	 * @param String $name
	 * @param String $libelle
	 * @param String $code_champ
	 * @param String $code_ss_champ
	 */
	public static function gen_compare_id($name,$libelle,$code_champ,$code_ss_champ){
		$id=$name."_".$libelle."_".$code_champ."_".$code_ss_champ;
		$id=convert_diacrit($id).md5($id);
		$id=str_replace("'","",$id);
		return $id;
	}
	
	/**
	 * On renvoi un id de groupement en fonction de ses �l�ments
	 * @param String $name
	 * @param String $code_champ
	 * @param String $code_ss_champ
	 */
	public static function gen_groupby_id($name,$code_champ,$code_ss_champ){
		$id=$name."_".$code_champ."_".$code_ss_champ;
		$id=convert_diacrit($id).md5($id);
		$id=str_replace("'","",$id);
		return $id;
	}
	
	/**
	 * @param String $name
	 * @param String $code_champ
	 * @param String $code_ss_champ
	 * @param String $idGroupBy
	 */
	public static function gen_groupby($name,$code_champ,$code_ss_champ,$idGroupBy){
		return '["'.addslashes($name).'",'.$code_champ.','.$code_ss_champ.',"'.$idGroupBy.'"]';
	}
	
	/**
	 * On conserve dans les formulaires de recherche les informations, pour les faire �voluer au cours de la session.
	 * @return string le bloc dans le formulaire
	 */
	public static function form_write_facette_compare(){
		global $charset;
		
		$form = '';
		$facettes_compare_checked = static::get_compare_checked_session();
		if (!empty($facettes_compare_checked)) {
			foreach($facettes_compare_checked as $facette_compare) {
				$form .= "<input type=\"hidden\" name=\"check_facette_compare[]\" value=\"".htmlentities($facette_compare['value'], ENT_QUOTES, $charset)."\">\n";
			}
		}
		$facettes_groupby_checked = static::get_groupby_checked_session();
		if(is_countable($facettes_groupby_checked) && sizeof($facettes_groupby_checked)){
			foreach($facettes_groupby_checked as $facette_groupby){
				$form .= "<input type=\"hidden\" name=\"check_facette_groupby[]\" value=\"".htmlentities($facette_groupby['value'],ENT_QUOTES,$charset)."\">\n";
			}
		}
		$form .= "<input type='hidden' value='' id='filtre_compare_form_values' name='filtre_compare'>";
		return $form;
	}
	
	public static function get_compare_see_more($objects_ids){
		global $msg;
		return "<a onclick='compare_see_more(this,[".$objects_ids."]);'>".$msg["facette_plus_link"]."</a>";
	}
	
	/**
	 * @return string le bouton d'ajout du crit�re de groupage dans le tableau HTML des facettes
	 */
	public static function get_groupby_row($facette_compare, $groupBy, $idGroupBy) {
		global $msg;
		global $charset;
		$script = "";
		if (!empty($facette_compare->facette_groupby[$idGroupBy])) {
			$script = "
				<th class='groupby_button' onclick=\"group_by('".htmlentities($groupBy, ENT_QUOTES, $charset)."');\"><img title='".$msg['facette_compare_groupby']."' class='facette_compare_grp' alt='".$msg['facette_compare_groupby']."' src='".get_url_icon('group_by.png')."'/></th>
				<input type='hidden' id='facette_groupby_$idGroupBy' name='check_facette_groupby[]' value='".htmlentities($groupBy, ENT_QUOTES, $charset)."'/>";
		} else {
			$script = "
				<th class='groupby_button' onclick=\"group_by('".htmlentities($groupBy, ENT_QUOTES, $charset)."');\"><img title='".$msg['facette_compare_groupby']."' class='facette_compare_grp' alt='".$msg['facette_compare_groupby']."' src='".get_url_icon('group_by_grey.png')."'/></th>
				<input type='hidden' id='facette_groupby_$idGroupBy' name='check_facette_groupby[]' value=''/>";
			}	
		return $script;
	}
	
	public static function get_begin_result_list(){
		return "<a href='javascript:expandAll_compare();'>
					<img class='img_plusplus' src='".get_url_icon('expand_all.gif')."' border='0' id='expandall'>
				</a>
				&nbsp;
				<a href='javascript:collapseAll_compare()'>
					<img class='img_moinsmoins' src='".get_url_icon('collapse_all.gif')."' border='0' id='collapseall'>
				</a>
		";
	}
	
	/**
	 * @return string les script js utile pour le comparateur
	 */
	public static function get_compare_wrapper(){
		global $msg;
		$script="
			function valid_facettes_compare(){
				
				var form = document.facettes_multi;
				if(form.elements.length>0){
					
					var form_values_compare_input_array=new Array();
					
					for(var i=0; i<form.elements.length;i++){
						
						if(form.elements[i].name=='check_facette[]' && form.elements[i].checked){
							//on transforme les case � coch� en element du tableau des facettes	
							//on ajoute dans le tableau des facettes
							var value=form.elements[i].value;
							var jsonArray=JSON.parse(value);
							
							//On ajoute dans le formulaire de postage g�n�ral
							var form_values_compare_input=document.createElement('input');
							form_values_compare_input.setAttribute('name','check_facette_compare[]');
							form_values_compare_input.setAttribute('type','hidden');
							form_values_compare_input.setAttribute('value',value);
							form_values_compare_input_array.push(form_values_compare_input);
						}
					}
					
					var post=false;
					var form_values=document.search_form;
					for(var i=0;i<form_values_compare_input_array.length;i++) {
						form_values.appendChild(form_values_compare_input_array[i]);
					}
					
					for(var i=0; i<form_values.elements.length;i++){
						if(form_values.elements[i].name=='check_facette_compare[]' && form_values.elements[i].value!=''){
							if(document.getElementById('filtre_compare_facette')) {
								document.getElementById('filtre_compare_facette').value='compare';
							}
							if(document.getElementById('filtre_compare_form_values')) {
								document.getElementById('filtre_compare_form_values').value='compare';
							}
							form_values.submit();
							post=true;
						}
					}
					if(post == false){
						alert('".$msg['facette_compare_not_selected']."');
					}
				}else{
					alert('".$msg['facette_compare_not_selected']."');
				}
			}
			
			function valid_compare(){
				var form_values=document.search_form;
				var post=false;
				
				for(var i=0; i<form_values.elements.length;i++){
					if(form_values.elements[i].name=='check_facette_compare[]' && form_values.elements[i].value!=''){
						if(document.getElementById('filtre_compare_facette')) {
							document.getElementById('filtre_compare_facette').value='compare';
						}
						if(document.getElementById('filtre_compare_form_values')) {
							document.getElementById('filtre_compare_form_values').value='compare';
						}
						form_values.submit();
						post=true;
					}
				}
				if(post == false){
					alert('".$msg['facette_compare_not_selected']."');
				}
			}
			
			function remove_compare_facette(value){
				
				var jsonArray = JSON.parse(value);
				
				//on supprime l'�lement du tableau des facettes
				elem=document.getElementById('compare_facette_'+jsonArray[4]);
				elem.parentNode.removeChild(elem);
				
				//on supprime l'�l�ment du formulaire g�n�ral aussi
				var form_values=document.search_form;
				for(var i in form_values.elements){
					if(form_values.elements[i] && form_values.elements[i].value && form_values.elements[i].name=='check_facette_compare[]'){
						form_values_json_array=JSON.parse(form_values.elements[i].value);
						
						if(form_values_json_array[4]==jsonArray[4]){
							elem=form_values.elements[i];
							elem.parentNode.removeChild(elem);
						}
					}
				}
				var post=true;
				for(var i in form_values.elements){
					if(form_values.elements[i] && form_values.elements[i].value && form_values.elements[i].name=='check_facette_compare[]'){
						valid_facettes_compare();
						post=false;
					}
				}

				if(post){
 					var input_form_values = document.createElement('input');
					input_form_values.setAttribute('type', 'hidden');
					input_form_values.setAttribute('name', 'reinit_compare');
					input_form_values.setAttribute('value', '1');
					document.forms['search_form'].appendChild(input_form_values);
					document.search_form.submit();
				}
			}
			
			function group_by(groupBy){
				
				var jsonArray=JSON.parse(groupBy);
			
				//on vide les elements group_by
				var group_by_elements=document.getElementsByName('check_facette_groupby[]');
				
				var nodes_to_remove;
				
				for(var i in group_by_elements){
					if(group_by_elements[i].nodeName=='INPUT'){
						if(group_by_elements[i].getAttribute('id')!='facette_groupby_'+jsonArray[3]){
							
							if(group_by_elements[i].parentNode.getAttribute('name')!='search_form' && group_by_elements[i].parentNode.getAttribute('name')!='cart_values'){
								
								var group_by_elements_img=group_by_elements[i].previousElementSibling.firstChild;
									
								group_by_elements_img.setAttribute('src','".get_url_icon('group_by_grey.png')."');
								group_by_elements[i].setAttribute('value','');
								
							}else{
								nodes_to_remove=[i=[group_by_elements[i],group_by_elements[i].parentNode]];
							}
						}
					}
				}
				
				if(nodes_to_remove && nodes_to_remove.length>0){
					for(var i in nodes_to_remove){
						nodes_to_remove[i][1].removeChild(nodes_to_remove[i][0]);
					}
				}
				
				element=document.getElementById('facette_groupby_'+jsonArray[3]);
				var img=element.previousElementSibling.firstChild;
				
				var table_groupby=document.getElementById('facette_groupby');
				
				if(element.getAttribute('value')==''){
					element.setAttribute('value',JSON.stringify(groupBy));
					
					//On ajoute dans le formulaire de postage g�n�ral
					var form_values=document.search_form;
					var form_values_groupby_input=document.createElement('input');
					form_values_groupby_input.setAttribute('name','check_facette_groupby[]');
					form_values_groupby_input.setAttribute('type','hidden');
					form_values_groupby_input.setAttribute('value',groupBy);
					form_values.appendChild(form_values_groupby_input);
					
					
				}
				valid_facettes_compare();
			}
		";
		
		return $script;
	}
	
	public static function get_compare_checked_session() {
		if(!isset($_SESSION['check_facette_compare'])) $_SESSION['check_facette_compare'] = array();
		return $_SESSION['check_facette_compare'];
	}
	
	public static function set_compare_checked_session($facettes_compare) {
		$_SESSION['check_facette_compare'] = $facettes_compare;
	}
	
	public static function unset_compare_checked_session() {
		unset($_SESSION['check_facette_compare']);
	}
	
	public static function get_groupby_checked_session() {
		if(!isset($_SESSION['check_facette_groupby'])) $_SESSION['check_facette_groupby'] = array();
		return $_SESSION['check_facette_groupby'];
	}
	
	public static function set_groupby_checked_session($facettes_groupby) {
		$_SESSION['check_facette_groupby'] = $facettes_groupby;
	}
	
	public static function unset_groupby_checked_session() {
		unset($_SESSION['check_facette_groupby']);
	}
	
	public static function get_formatted_value($id_critere, $id_ss_critere, $value) {
		return facettes::get_formatted_value($id_critere, $id_ss_critere, $value);
	}
}
