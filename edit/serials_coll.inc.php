<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serials_coll.inc.php,v 1.29 2020/11/05 10:22:14 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$list_records_bulletins_collstate_edition_ui = new list_records_bulletins_collstate_edition_ui(array('niveau_biblio' => 's', 'niveau_hierar' => '1'), array(), array());
print $list_records_bulletins_collstate_edition_ui->get_display_list();