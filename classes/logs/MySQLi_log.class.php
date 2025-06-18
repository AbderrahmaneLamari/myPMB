<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: MySQLi_log.class.php,v 1.1.2.3 2021/10/18 13:02:55 dgoron Exp $

global $class_path;
require_once ($class_path."/log.class.php");

class MySQLi_log extends log {

	protected static $slow_log_time = 1;
	
	public function __construct($id=0) {
		parent::__construct($id);
		$this->service = 'MySQLi';
	}
	
	public static function get_error() {
		return pmb_mysql_error(static::$dbh);
	}
}

