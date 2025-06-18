<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: event_list_ui.class.php,v 1.2 2021/03/05 14:16:41 moble Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once $class_path.'/event/event.class.php';

class event_list_ui extends event {
    protected $url_base = "";
    protected $selection_action = [];
    protected $resa_id = [];
    
    public function get_url_base() {
        return $this->url_base;
    }
    
    public function set_url_base($url_base) {
        $this->url_base = $url_base;
        return $this->url_base;
    }
    
    public function get_resa_id() {
        return $this->resa_id;
    }
    
    public function set_resa_id($resa_id) {
        $this->resa_id = $resa_id;
        return $this->resa_id;
    }
    
    public function get_selection_action() {
        return $this->selection_action;
    }
    
    public function set_selection_action($selection_action) {
        $this->selection_action = $selection_action;
        return $this->selection_action;
    }
}