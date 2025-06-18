<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: visit_statistics.class.php,v 1.1 2021/05/11 14:31:25 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/**
 * Classe de gestion d'une statistique de fréquentation
 */
class visit_statistics {
	
	/**
	 * Identifiant
	 * @var integer
	 */
	protected $id;
	
	/**
	 * Date ou date de début
	 * @var string
	 */
	protected $date;
	
	/**
	 * Localisation
	 * @var integer
	 */
	protected $location;
	
	/**
	 * Type de visite
	 * @var string
	 */
	protected $type;
	
	/**
	 * Constructeur
	 */
	public function __construct($id = 0) {
		$this->id = intval($id);
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		$query = "SELECT * FROM visits_statistics where visits_statistics_id = ".$this->id;
		$result = pmb_mysql_query($query) ;
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		$row = pmb_mysql_fetch_object($result);
		$this->date = $row->visits_statistics_date;
		$this->location = $row->visits_statistics_location;
		$this->type = $row->visits_statistics_type;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_date() {
		return $this->date;
	}
	
	public function get_location() {
		return $this->location;
	}
	
	public function get_type() {
		return $this->type;
	}
}