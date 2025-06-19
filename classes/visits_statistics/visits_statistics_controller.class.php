<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: visits_statistics_controller.class.php,v 1.1 2021/07/16 07:26:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class visits_statistics_controller extends lists_controller {
	
	protected static $model_class_name = '';
	
	protected static $list_ui_class_name = 'list_visits_statistics_ui';
	
}