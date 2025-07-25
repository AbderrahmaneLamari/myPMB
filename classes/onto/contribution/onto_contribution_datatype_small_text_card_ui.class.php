<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_contribution_datatype_small_text_card_ui.class.php,v 1.9 2021/07/02 14:31:45 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


require_once($include_path.'/templates/onto/contribution/onto_contribution_datatype_ui.tpl.php');
/**
 * class onto_contribution_datatype_small_text_card_ui
 * 
 */
class onto_contribution_datatype_small_text_card_ui extends onto_common_datatype_ui {

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/
	private static $default_type="http://www.w3.org/2000/01/rdf-schema#Literal";

	/**
	 * 
	 *
	 * @param property property la propri�t� concern�e
	 * @param onto_restriction $restrictions le tableau des restrictions associ�es � la propri�t� 
	 * @param array datas le tableau des datatypes
	 * @param string instance_name nom de l'instance
	 * @param string flag Flag

	 * @return string
	 * @static
	 * @access public
	 */
	public static function get_form($item_uri,$property, $restrictions,$datas, $instance_name,$flag) {
		global $msg,$charset,$ontology_contribution_tpl;
		
		$max = $restrictions->get_max();
		
		$form=$ontology_contribution_tpl['form_row_card'];
		$form=str_replace("!!onto_row_label!!",htmlentities(encoding_normalize::charset_normalize($property->get_label(), 'utf-8') ,ENT_QUOTES,$charset) , $form);
		$form=str_replace("!!onto_input_type!!",htmlentities(self::$default_type ,ENT_QUOTES,$charset) , $form);
		
		
		$tab_lang = array();
		if ($property->multilingue && $property->is_cp()) {
		    // Champ perso multilingue
		    $lang = new marc_list('lang');
		    if (!empty($lang->table)) {
		        $tab_lang = $lang->table;
		    }
		    // Sans langue
		    $tab_lang[''] = htmlentities($msg['onto_common_datatype_ui_no_lang'], ENT_QUOTES, $charset);
		}
		
		
		$content='';
		$multilingue = "";
		
		if (!empty($datas)) {
			$i=1;
			$first=true;
			$new_element_order=max(array_keys($datas));
			
			$form=str_replace("!!onto_new_order!!",$new_element_order , $form);
			
			foreach($datas as $key=>$data){
				// On d�cr�mente le tableau des langues disponibles
				
				$row=$ontology_contribution_tpl['form_row_content'];
				
				if($data->get_order()){
					$order=$data->get_order();
				}else{
					$order=$key;
				}
				$inside_row=$ontology_contribution_tpl['form_row_content_small_text_card'];
				
				$inside_row=str_replace("!!onto_row_content_small_text_value!!",htmlentities($data->get_formated_value(), ENT_QUOTES, $charset) ,$inside_row);
				if ($property->multilingue) {
				    $multilingue = self::get_combobox_lang($instance_name.'_'.$property->pmb_name.'['.$order.'][lang]',$instance_name.'_'.$property->pmb_name.'_'.$order.'_lang',$data->get_lang(), 1, '', $tab_lang);
				}
				$inside_row=str_replace("!!onto_row_combobox_lang!!", $multilingue, $inside_row);
				$inside_row=str_replace("!!onto_row_content_small_text_range!!",$property->range[0] , $inside_row);
				
				$row=str_replace("!!onto_inside_row!!",$inside_row , $row);
				
				$input=$ontology_contribution_tpl['form_row_content_input_del_card'];
				
				$row=str_replace("!!onto_row_inputs!!",$input , $row);
				
				$row=str_replace("!!onto_row_order!!",$order , $row);
				
				$content.=$row;
				$first=false;
				$i++;
			}
		}else{
			$form=str_replace("!!onto_new_order!!","0" , $form);
			
			// Un champ sans langue par d�faut
			$row=$ontology_contribution_tpl['form_row_content'];
			
			$inside_row=$ontology_contribution_tpl['form_row_content_small_text_card'];
			
			$inside_row=str_replace("!!onto_row_content_small_text_value!!", "", $inside_row);
			if ($property->multilingue) {
			    $multilingue = self::get_combobox_lang($instance_name.'_'.$property->pmb_name.'[0][lang]',$instance_name.'_'.$property->pmb_name.'_'.$order.'_lang', '', 1, '', $tab_lang);
			}
			$inside_row=str_replace("!!onto_row_combobox_lang!!", $multilingue, $inside_row);
			$inside_row=str_replace("!!onto_row_content_small_text_range!!",$property->range[0] , $inside_row);
			
			
			$row=str_replace("!!onto_inside_row!!",$inside_row , $row);
				
			$input=$ontology_contribution_tpl['form_row_content_input_del_card'];
			
			$row=str_replace("!!onto_row_inputs!!",$input , $row);
			
			$row=str_replace("!!onto_row_order!!","0" , $row);
			
			$content.=$row;
			
		}
		$onto_rows = "";
		
		// On regarde quelles langues sont ajoutables
		
		$onto_rows.= $content;
		
		// Gestion du combobox de langues
		$form = str_replace("!!input_add!!", $ontology_contribution_tpl['form_row_content_input_add_card'], $form);
		$form = str_replace("!!onto_row_max_card!!", $max, $form);
		
		$form=str_replace("!!onto_rows!!",$onto_rows ,$form);
		$form=str_replace("!!onto_row_id!!",$instance_name.'_'.$property->pmb_name , $form);
		
		return $form;
	} // end of member function get_form

	/**
	 * 
	 *
	 * @param onto_common_datatype datas Tableau des valeurs � afficher associ�es � la propri�t�
	 * @param property property la propri�t� � utiliser
	 * @param string instance_name nom de l'instance
	 * 
	 * @return string
	 * @access public
	 */
	public function get_display($datas, $property, $instance_name) {
		
		$display='<div id="'.$instance_name.'_'.$property->pmb_name.'">';
		$display.='<p>';
		$display.=$property->get_label().' : ';
		foreach($datas as $data){
			$display.=$data->get_formated_value();
		}
		$display.='</p>';
		$display.='</div>';
		return $display;
		
	} // end of member function get_display
	
	/**
	 *
	 *
	 * @param property property la propri�t� concern�e
	 * @param onto_restriction $restrictions le tableau des restrictions associ�es � la propri�t�
	 * @param array datas le tableau des datatypes
	 * @param string instance_name nom de l'instance
	 * @param string flag Flag
	
	 * @return string
	 * @static
	 * @access public
	 */
	public static function get_validation_js($item_uri,$property, $restrictions,$datas, $instance_name,$flag){
		global $msg;
		return '{
			"message": "'.addslashes($property->get_label()).'",
			"valid" : true,
			"nb_values": 0,
			"error": "",
			"values": new Array(),
			"check": function(){
				this.values = new Array();
				this.nb_values = 0;
				this.valid = true;
				var order = document.getElementById("'.$instance_name.'_'.$property->pmb_name.'_new_order");
                if (order) {
    				for (var i=0; i<=order.value ; i++){
    					var label = document.getElementById("'.$instance_name.'_'.$property->pmb_name.'_"+i+"_value");
    					var lang = document.getElementById("'.$instance_name.'_'.$property->pmb_name.'_"+i+"_lang");
    					if (!lang) {
    						lang = {value : ""};
    					}
    					if(label.value != ""){
    						if(!this.values[lang.value]){
    							this.values[lang.value] = 0;
    						}
    						this.values[lang.value]++;
    						if(this.nb_values < this.values[lang.value]) {
    							this.nb_values = this.values[lang.value];
    						}
    					}
                    }
				}
							
				if(this.nb_values < '.$restrictions->get_min().'){
					this.valid = false;
					this.error = "min";
				}
				if(this.nb_values > '.$restrictions->get_max().'){
					this.valid = false;
					this.error = "max";
				}
				return this.valid;
			},
			"get_error_message": function(){
 				switch(this.error){
 					case "min" :
						this.message = "'.addslashes($msg['onto_error_no_minima']).'";
						break;
					case "max" : 
						this.message = "'.addslashes($msg['onto_error_too_much_values']).'";
						break;
 				}
				this.message = this.message.replace("%s","'.addslashes($property->get_label()).'");			
				return this.message;	
			} 	
		}';	
	}
	
} // end of onto_common_datatype_ui