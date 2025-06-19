<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_patterns_users_ui.class.php,v 1.5.2.2 2023/07/18 09:14:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_patterns_users_ui extends list_patterns_ui {
	
	/**
	 *Instance utilisateur
	 * @var user
	 */
	public static $user;
	
	public static function get_available_patterns() {
		$patterns = [
				'user_group_user' => [
						'user_name',
						'user_first_name',
						'user_login',
						'user_email',
						
				],
				'user_group_loc' => [
						'user_loc_name',
						'user_loc_adr1',
						'user_loc_adr2',
						'user_loc_cp',
						'user_loc_town',
						'user_loc_phone',
						'user_loc_email',
						'user_loc_website',
				],
				'user_group_misc' => [
						'user_day_date',
				]
		];
		
		
		
		return $patterns;
	}
	
	public static function get_patterns($text='') {
		
		$user = static::$user;

		$loc_name = '';
		$loc_adr1 = '';
		$loc_adr2 = '';
		$loc_cp = '';
		$loc_town = '';
		$loc_phone = '';
		$loc_email = '';
		$loc_website = '';
		if ($user->deflt_docs_location) {
			$result = pmb_mysql_query("SELECT * FROM docs_location WHERE idlocation=".$user->deflt_docs_location);
			if (pmb_mysql_num_rows($result)) {
				$user_loc = pmb_mysql_fetch_object($result);
				$loc_name = $user_loc->name;
				$loc_adr1 = $user_loc->adr1;
				$loc_adr2 = $user_loc->adr2;
				$loc_cp = $user_loc->cp;
				$loc_town = $user_loc->town;
				$loc_phone = $user_loc->phone;
				$loc_email = $user_loc->email;
				$loc_website = $user_loc->website;
			}
		}
		$search = array(
				"!!user_name!!",
				"!!user_first_name!!",
				"!!user_login!!",
				"!!user_email!!",
				"!!user_loc_name!!",
				"!!user_loc_adr1!!",
				"!!user_loc_adr2!!",
				"!!user_loc_cp!!",
				"!!user_loc_town!!",
				"!!user_loc_phone!!",
				"!!user_loc_email!!",
				"!!user_loc_website!!",
				"!!user_day_date!!"
		);
		$replace = array(
				$user->get_nom(),
				$user->get_prenom(),
				$user->get_username(),
				$user->get_user_email(),
				$loc_name,
				$loc_adr1,
				$loc_adr2,
				$loc_cp,
				$loc_town,
				$loc_phone,
				$loc_email,
				$loc_website,
				format_date(date('Y-m-d'))
		);
		return array(
				'search' => $search,
				'replace' => $replace
		);
	}
	
	public static function set_user($user) {
		static::$user = $user;
	}
}