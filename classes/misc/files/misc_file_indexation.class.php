<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: misc_file_indexation.class.php,v 1.3.6.1 2023/07/26 06:24:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/misc/files/misc_file.class.php");

class misc_file_indexation extends misc_file {
	
	protected $type;
	
	protected static $xml_indexation;
	
	public function __construct($path, $filename) {
		parent::__construct($path, $filename);
		$this->set_type($path);
	}
	
	protected function analyze() {
		if(file_exists($this->path.'/'.$this->filename)) {
			$xml = file_get_contents($this->path.'/'.$this->filename);
			static::$xml_indexation = _parser_text_no_function_($xml,"INDEXATION");
			static::$xml_indexation['FIELD'] = $this->apply_sort(static::$xml_indexation['FIELD']);
		}
	}
	
	protected function get_display_header_list() {
		global $msg, $charset;
		$display = "
		<tr>
			<th>".htmlentities($msg['misc_file_code'], ENT_QUOTES, $charset)."</th>
			<th>".htmlentities($msg['misc_file_label'], ENT_QUOTES, $charset)."</th>
			<th>".htmlentities($msg['misc_file_visible'], ENT_QUOTES, $charset)."</th>
			<th>".htmlentities($msg['misc_file_pond'], ENT_QUOTES, $charset)."</th>
		</tr>";
		return $display;
	}
	
	protected function get_display_pond($code, $label, $pond='') {
// 		global $msg, $charset;
		
		$display = "<span id='subst_file_data_".$code."_pond_span'>";
		if(isset($this->data[$code]['pond']) && $this->data[$code]['pond'] != $pond) {
			$display .= "<strong>".$this->data[$code]['pond']."</strong>";
// 			$display .= "<input type='hidden' name='subst_file_data[".$code."][pond]' id='subst_file_data_".$code."_pond' value='".intval($this->data[$code]['pond'])."' />";
// 			$display .= " <img data-file-code='".$code."' data-file-label='".htmlentities($label, ENT_QUOTES, $charset)."' data-file-pond='".intval($this->data[$code]['pond'])."' data-file-action='edit_pond' ".(!empty($this->type) ? "data-file-type='".$this->type."'" : "")." src='".get_url_icon('b_edit.png')."' alt='".$msg['62']."' title='".$msg['62']."' style='cursor:pointer;'/>";
			
		} elseif($pond !== '') {
			$display .= $pond;
// 			$display .= " <img data-file-code='".$code."' data-file-label='".htmlentities($label, ENT_QUOTES, $charset)."' data-file-pond='".intval($pond)."' data-file-action='edit_pond' ".(!empty($this->type) ? "data-file-type='".$this->type."'" : "")." src='".get_url_icon('b_edit.png')."' alt='".$msg['62']."' title='".$msg['62']."' style='cursor:pointer;'/>";
		}
		
		$display .= "</span>";
		return $display;

		return '';
	}
	
	protected function get_display_content_list() {
		global $msg, $charset;
		
		$display = "";
		foreach (static::$xml_indexation['FIELD'] as $field) {
			$display .= "
			<tr class='center' data-file-element='".$field['ID']."'>
				<td>
					".$field['ID']."
					".$this->get_informations_hidden($field['ID'])."
				</td>
				<td>".htmlentities($msg[$field['NAME']], ENT_QUOTES, $charset)."</td>
				<td>".$this->get_visible_checkbox($field['ID'])."</td>
				<td>".$this->get_display_pond($field['ID'], $msg[$field['NAME']], $field['POND'] ?? '')."</td>
			</tr>";
		}
		return $display;
	}
	
	public function get_display_list() {
		$display = "<table id='misc_file_indexation_list'>";
		$display .= $this->get_display_header_list();
		if(count(static::$xml_indexation['FIELD'])) {
			$display .= $this->get_display_content_list();
		}
		$display .= "</table>";
		
		return $display;
	}
	
	public function set_type($type) {
		$this->type = substr($type, strrpos($type, '/')+1);
	}
	
	public function set_properties_from_form() {
		global $subst_file_data;
		
		parent::set_properties_from_form();
		if(is_array($subst_file_data) && count($subst_file_data)) {
			foreach ($subst_file_data as $code=>$element) {
				if(isset($element['pond'])) {
					$this->data[$code]['pond'] = $element['pond'];
				}
			}
		}
	}
	
	public function get_default_template() {
		$is_subst = strpos($this->filename, '_subst.xml');
		if(file_exists($this->path.'/'.$this->filename)) {
			$contents = file_get_contents($this->path.'/'.$this->filename);
			return utf8_encode($contents);
		} elseif($is_subst) {
			$contents = file_get_contents($this->path.'/'.str_replace('_subst.xml', '.xml', $this->filename));
			return utf8_encode($contents);
		}
	}
	
	protected function field_exists($field_id, $substitution_fields) {
		foreach ($substitution_fields as $key=>$field) {
			if($field['ID'] == $field_id) {
				return $key;
			}
		}
		return false;
	}
	
	protected function apply_sort($substitution_fields) {
		if(!count($this->data)) {
			return $substitution_fields;
		}
		$sorted_substitution = array();
		foreach ($this->data as $field_id=>$field) {
			$field_exists = $this->field_exists($field_id, $substitution_fields);
			if($field_exists !== false) {
				$sorted_substitution[] = $substitution_fields[$field_exists];
				unset($substitution_fields[$field_exists]);
			}
		}
		$sorted_substitution = array_merge($sorted_substitution, $substitution_fields);
		return $sorted_substitution;
	}
	
	public function apply_substitution($fields) {
		if(count($this->data)) {
			$substitution = array();
			foreach ($fields as $field) {
				if(!isset($this->data[$field['ID']]['visible']) || $this->data[$field['ID']]['visible']) {
					if(isset($this->data[$field['ID']]['pond'])) {
						$field['POND'] = $this->data[$field['ID']]['pond'];
					}
					$substitution[] = $field;
				}
			}
			//Ordonnancement
			$substitution = $this->apply_sort($substitution);
		} else {
			$substitution = $fields;
		}
		return $substitution;
	}
}
	
