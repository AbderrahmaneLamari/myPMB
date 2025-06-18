<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: licence.inc.php,v 1.4 2021/04/16 07:59:13 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $class_path, $msg, $id, $action, $rightaction, $profileaction, $force, $what;

require_once($class_path.'/explnum_licence/explnum_licence.class.php');

$id = intval($id);
if (empty($action)) {
	$action = 'list';
}

switch ($action) {
	case 'save' :
		print '<div class="row"><div class="msg-perio">'.$msg['sauv_misc_running'].'</div></div>';
		$explnum_licence = new explnum_licence($id);
		$explnum_licence->get_values_from_form();
		$explnum_licence->save();
		print '<script type ="text/javascript">
					document.location = "./admin.php?categ=docnum&sub=licence&action=list";
			   </script>';
		break;
	case 'add' :
		$explnum_licence = new explnum_licence();
		print $explnum_licence->get_form();
		break;
	case 'edit' :
		$explnum_licence = new explnum_licence($id);
		$explnum_licence->fetch_data();
		print $explnum_licence->get_form();
		break;
	case 'delete' :
		$force = intval($force);
		print '<div class="row"><div class="msg-perio">'.$msg['suppression_en_cours'].'</div></div>';
		$explnum_licence = new explnum_licence($id);
		$return = $explnum_licence->delete($force);
		if ($return) {
			print '<script type ="text/javascript">
					document.location = "./admin.php?categ=docnum&sub=licence&action=list";
			   </script>';
			break;
		}
		print '<script type ="text/javascript">
				if (confirm("'.addslashes($msg['explnum_licence_is_used_confirm_delete']).'")) {
					document.location = "./admin.php?categ=docnum&sub=licence&action=delete&id='.$id.'&force=1";
				} else {
					history.go(-1);
				}
		   </script>';
		break;
	case 'settings' :
		//Assurons-nous de passer un identifiant de licence
		if (empty($id)) {
			print '<script type ="text/javascript">
					document.location = "./admin.php?categ=docnum&sub=licence&action=list";
			   </script>';
			break;
		}
		if (empty($what)) {
			$what = 'profiles';
		}
		$explnum_licence = new explnum_licence($id);
		print $explnum_licence->get_settings_menu();
		switch ($what) {
			case 'rights' :
				if (empty($rightaction)) {
					$rightaction = 'list';
				}
				switch ($rightaction) {
					case 'list':
						print '
							<script type="text/javascript">
								document.title="'.$msg['explnum_licence_rights'].'";
								window.status="'.$msg['explnum_licence_rights'].'";
							</script>';
						print $explnum_licence->get_rights_list();
						break;
					default :
						require_once($base_path.'/admin/upload/licence_rights.inc.php');
						break;
				}
				break;
			case 'profiles' :
			default :
				if (empty($profileaction)) {
					$profileaction = 'list';
				}
				switch ($profileaction) {
					case 'list':
						print '
							<script type="text/javascript">
								document.title="'.$msg['explnum_licence_profiles'].'";
								window.status="'.$msg['explnum_licence_profiles'].'";
							</script>';
						print $explnum_licence->get_profiles_list();
						break;
					default :
						require_once($base_path.'/admin/upload/licence_profiles.inc.php');
						break;
				}
				break;
		}
		break;
	case 'list':
	default :
		print '
		<script type="text/javascript">
			document.title="'.$msg['admin_menu_noti_licence'].'";
			window.status="'.$msg['admin_menu_noti_licence'].'";
		</script>';
		print list_configuration_explnum_licence_ui::get_instance()->get_display_list();
		break;
}

?>