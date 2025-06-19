<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_node_p.class.php,v 1.1.2.2 2023/06/28 07:57:25 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class interface_node_p extends interface_node {
	public function get_display() {
		global $charset;
		
		$display = "
		<p class='" . $this->class . "'>" 
			. htmlentities($this->value, ENT_QUOTES, $charset) . 
		"</p>";

		return $display;
	}
}