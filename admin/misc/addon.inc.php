<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: addon.inc.php,v 1.6.6.34 2024/01/08 16:14:33 touraine37 Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if( !function_exists('traite_rqt') ) {
	function traite_rqt($requete="", $message="") {

		global $charset;
		$retour="";
		if($charset == "utf-8"){
			$requete=utf8_encode($requete);
		}
		pmb_mysql_query($requete) ;
		$erreur_no = pmb_mysql_errno();
		if (!$erreur_no) {
			$retour = "Successful";
		} else {
			switch ($erreur_no) {
				case "1060":
					$retour = "Field already exists, no problem.";
					break;
				case "1061":
					$retour = "Key already exists, no problem.";
					break;
				case "1091":
					$retour = "Object already deleted, no problem.";
					break;
				default:
					$retour = "<font color=\"#FF0000\">Error may be fatal : <i>".pmb_mysql_error()."<i></font>";
					break;
			}
		}
		return "<tr><td><font size='1'>".($charset == "utf-8" ? utf8_encode($message) : $message)."</font></td><td><font size='1'>".$retour."</font></td></tr>";
	}
}
echo "<table>";

/******************** AJOUTER ICI LES MODIFICATIONS *******************************/

switch ($pmb_bdd_subversion) {
	case 0:
		// DG - Ajout d'une classification sur les listes
		$rqt = "ALTER TABLE lists ADD list_num_ranking int not null default 0 AFTER list_default_selected" ;
		echo traite_rqt($rqt,"ALTER TABLE lists ADD list_num_ranking");
	case 1:
		// DG - Ajout dans les bannettes la possibilité d'historiser les diffusions
		$rqt = "ALTER TABLE bannettes ADD bannette_diffusions_history INT(1) UNSIGNED NOT NULL default 0";
		echo traite_rqt($rqt,"ALTER TABLE bannettes ADD bannette_diffusions_history");

		// DG - Log des diffusions de bannettes
		$rqt = "CREATE TABLE IF NOT EXISTS bannettes_diffusions (
					id_diffusion int unsigned not null auto_increment primary key,
        			diffusion_num_bannette int(9) unsigned not null default 0,
        			diffusion_mail_object text,
					diffusion_mail_content mediumtext,
					diffusion_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					diffusion_records text,
					diffusion_deleted_records text,
					diffusion_recipients text,
					diffusion_failed_recipients text
        		)";
		echo traite_rqt($rqt,"create table bannettes_diffusions");
	case 2:
		// TS-RT-JP - Ajout de la table dsi_content_buffer
		$rqt = "CREATE TABLE IF NOT EXISTS dsi_content_buffer (
		  id_content_buffer int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  type int(11) NOT NULL DEFAULT 0,
		  content longblob NOT NULL,
		  num_diffusion_history int(10) UNSIGNED NOT NULL DEFAULT 0
		)";
		echo traite_rqt($rqt,"CREATE TABLE dsi_content_buffer");

		// TS-RT-JP - Ajout du champ automatic sur une diffusion
		$rqt = "ALTER TABLE dsi_diffusion ADD automatic tinyint(1) NOT NULL DEFAULT 0 AFTER settings" ;
		echo traite_rqt($rqt,"ALTER dsi_diffusion ADD automatic");

		// TS-RT-JP - Ajout d'un état sur l'historique de diffusion
		$rqt = "ALTER TABLE dsi_diffusion_history ADD state tinyint(1) NOT NULL DEFAULT 0 AFTER total_recipients" ;
		echo traite_rqt($rqt,"ALTER dsi_diffusion_history ADD state");
	case 3:
		//DG - Tâches : changement du champ msg_statut en texte
		$rqt = "ALTER TABLE taches MODIFY msg_statut TEXT";
		echo traite_rqt($rqt,"ALTER TABLE taches MODIFY msg_statut IN TEXT");

		//DG - Ajout d'un paramètre caché permettant de définir si une indexation via le gestionnaire de tâches est en cours
		if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='scheduler_indexation_in_progress' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
				VALUES (NULL, 'pmb', 'scheduler_indexation_in_progress', '0', 'Paramètre caché permettant de définir si une indexation via le gestionnaire de tâches est en cours', '', '1')" ;
			echo traite_rqt($rqt,"insert hidden pmb_scheduler_indexation_in_progress=0 into parametres") ;
		}
	case 4:
		//DG - Tâches : (correction du float) changement du champ indicat_progress en nombre flotant
		$rqt = "ALTER TABLE taches MODIFY indicat_progress FLOAT(5,2) NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE taches MODIFY indicat_progress IN FLOAT");

	case 5:
	    //NG - Ajout d'un parametre pour la prise en compte de la gestion des animations des lecteurs
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='gestion_animation' "))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) VALUES (0, 'pmb', 'gestion_animation', '0', 'Utiliser la gestion des animations des lecteurs ? \n 0 : Non\n 1 : Oui, gestion simple, \n 2 : Oui, gestion avancée') " ;
	        echo traite_rqt($rqt,"insert pmb_gestion_animation = 0 into parametres");
	    }

	case 6:
	    // GN - Ajout d'un paramètre utilisateur (import Z3950 en catalogue automatique/manuel)
	    $rqt = "ALTER TABLE users ADD deflt_notice_catalog_categories_auto INT(1) UNSIGNED DEFAULT 1 NOT NULL ";
	    echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_notice_catalog_categories_auto");

	case 7:
		// Equipe DEV Plugins
		$rqt = "CREATE TABLE IF NOT EXISTS plugins (
        			id_plugin int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        			plugin_name varchar(255) NOT NULL DEFAULT '',
        			plugin_settings text NOT NULL
			)";
		echo traite_rqt($rqt,"CREATE TABLE plugins");

	case 8 :
	    // DB - Info de modification des fichiers db_param
	    $rqt = " select 1 " ;
        echo traite_rqt($rqt, encoding_normalize::charset_normalize("<b class='erreur'>
            LES FICHIERS DE CONNEXION A LA BASE DE DONNEES ( pmb/includes/db_param.inc.php et pmb/opac_css/includes/opac_db_param.inc.php ONT ETE MODIFIES.<br />
            Un modèle de référence est défini dans le répertoire pmb/tables pour chacun de ces fichiers.<br />
            VERIFIEZ CES FICHIERS SI VOUS VENEZ DE FAIRE UNE MISE A JOUR DE VOTRE INSTALLATION.
            </b>", 'iso-8859-1'));

	case 9 :
	    // TS - mise à jour du paramètre pmb_book_pics_url
	    $rqt = "UPDATE parametres SET comment_param = CONCAT(comment_param,'\n Ce paramètre n\'est plus utilisé. Merci de reporter les valeurs personnalisées dans le paramétrage des vignettes (admin/vignettes/sources/liens externes).') WHERE sstype_param = 'book_pics_url'" ;
            echo traite_rqt($rqt, encoding_normalize::charset_normalize("<b class='erreur'>
            Les paramètres book_pics_url ne sont plus utilisés. Merci de reporter les valeurs personnalisées dans le paramétrage de vignettes (admin/vignettes/sources/liens externes.
            </b> ", 'iso-8859-1'));

	    // TS - modification du nom de la source de vinettes
	    $rqt = "UPDATE thumbnail_sources_entities SET source_class = 'Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Externallinks\\\\RecordExternallinksThumbnailSource' WHERE source_class = 'Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Amazon\\\\RecordAmazonThumbnailSource'";
	    echo traite_rqt($rqt, "UPDATE thumbnail_sources_entitie WHERE source_class = 'Pmb\\Thumbnail\\Models\\Sources\\Entities\\Record\\Amazon\\RecordAmazonThumbnailSource'");

	    //TS - changement du champ search_universe_description en text
	    $rqt = "ALTER TABLE search_universes MODIFY search_universe_description TEXT";
	    echo traite_rqt($rqt,"ALTER TABLE search_universes MODIFY search_universe_description IN TEXT");

	    //TS - changement du champ search_segment_description en text
	    $rqt = "ALTER TABLE search_segments MODIFY search_segment_description TEXT";
	    echo traite_rqt($rqt,"ALTER TABLE search_segments MODIFY search_segment_description IN TEXT");
	case 10 :
	    //GN - Ajout d'un champ search_segment_data pour stocker des données
	    $rqt = "ALTER TABLE search_segments ADD search_segment_data varchar(255)";
	    echo traite_rqt($rqt,"ALTER TABLE search_segments ADD search_segment_data");
	case 11 :
		//DG - Modification de la taille du champ watch_boolean_expression en text
		$rqt = "ALTER TABLE docwatch_watches MODIFY watch_boolean_expression TEXT";
		echo traite_rqt($rqt,"ALTER TABLE docwatch_watches MODIFY watch_boolean_expression IN TEXT");

		//DG - Modification de la taille du champ datasource_boolean_expression en text
		$rqt = "ALTER TABLE docwatch_datasources MODIFY datasource_boolean_expression TEXT";
		echo traite_rqt($rqt,"ALTER TABLE docwatch_datasources MODIFY datasource_boolean_expression IN TEXT");
	case 12 :
	    //QV - Refonte DSI ajout des descripteurs
	    $rqt = "CREATE TABLE IF NOT EXISTS dsi_diffusion_descriptors (
                num_diffusion int(11) NOT NULL DEFAULT 0,
                num_noeud int(11) NOT NULL DEFAULT 0,
                diffusion_descriptor_order int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (num_diffusion, num_noeud)
            )";
	    echo traite_rqt($rqt, "CREATE TABLE dsi_diffusion_descriptors");

	    //QV - Refonte DSI correction du commentaire dsi_active
	    $rqt = "UPDATE parametres SET comment_param = 'D.S.I activée ? \r\n 0: Non \r\n 1: Oui \r\n 2: Oui (refonte)' WHERE type_param = 'dsi' AND sstype_param = 'active';";
	    echo traite_rqt($rqt, "UPDATE parametres SET comment_param for dsi_active");

	    //QV - Refonte Portail correction du commentaire cms_active
	    $rqt = "UPDATE parametres SET comment_param = 'Module \'Portail\' activé.\r\n 0 : Non.\r\n 1 : Oui.\r\n 2 : Oui (refonte).' WHERE type_param = 'cms' AND sstype_param = 'active';";
	    echo traite_rqt($rqt, "UPDATE parametres SET comment_param for cms_active");
	case 13 :
		// DG - Table de cache des ISBD d'entités
		$rqt = "CREATE TABLE IF NOT EXISTS entities (
				num_entity int(10) UNSIGNED NOT NULL DEFAULT 0,
				type_entity int(3) UNSIGNED NOT NULL DEFAULT 0,
				entity_isbd text NOT NULL,
				PRIMARY KEY(num_entity, type_entity)
			)";
		echo traite_rqt($rqt,"CREATE TABLE entities");
	case 14 :
		//RT - Modification commentaire accessibility
		$rqt = "UPDATE parametres SET comment_param = 'Accessibilité activée.\n0 : Non.\n1 : Oui.\n2 : Oui + compatibilité REM (unité CSS)' WHERE type_param = 'opac' AND sstype_param = 'accessibility'";
		echo traite_rqt($rqt,"UPDATE parametres SET comment_param for accessibility");
	case 15 :
	    //RT - TS Ajout paramètre d'activation de l'autocomplétion en recherche simple
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'opac' and sstype_param='search_autocomplete'")) == 0) {
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param, section_param)
        			VALUES (0, 'opac', 'search_autocomplete', '0', '0', 'Autocomplétion en recherche simple activée.\r\n 0 : Non.\r\n 1 : Oui.', 'c_recherche')";
	        echo traite_rqt($rqt, "INSERT opac_search_autocomplete INTO parametres") ;
	    }
	case 16 :
		// DG - Log des diffusions de bannettes - détails des équations exécutées au remplissage
		$rqt = "ALTER TABLE bannettes_diffusions ADD diffusion_equations text";
		echo traite_rqt($rqt,"ALTER TABLE bannettes_diffusions ADD diffusion_equations");
	case 17 :
	    // JP - Ajout du champ modified sur un content_buffer
	    $rqt = "ALTER TABLE dsi_content_buffer ADD modified tinyint(1) NOT NULL DEFAULT 0 AFTER content" ;
	    echo traite_rqt($rqt,"ALTER dsi_content_buffer ADD modified");

	case 18 :
	    // DB / QV : Compatibilité MySQL 8
	    // Utilisation des back quotes (`) pour Mysql 8. NE PAS LES SUPPRIMER
	    $rqt = "ALTER TABLE thumbnail_sources_entities CHANGE `rank` ranking int(10) NOT NULL DEFAULT 0";
	    echo traite_rqt($rqt,"ALTER TABLE thumbnail_sources_entities CHANGE rank ranking");

	    $rqt = "ALTER TABLE notices_relations CHANGE `rank` ranking int(11)  NOT NULL DEFAULT 0";
	    echo traite_rqt($rqt,"ALTER TABLE notices_relations CHANGE rank ranking");
	case 19 :
	    // DG - Modification de la date de création d'un document du portfolio en datetime
	    $rqt = "ALTER TABLE cms_documents MODIFY document_create_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	    echo traite_rqt($rqt,"ALTER TABLE cms_documents MODIFY document_create_date DATETIME");
	case 20 :
	    // DG - TS - Parametre pour définir la taille maximale du cache des images en gestion
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='img_cache_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion)
					VALUES (NULL, 'pmb', 'img_cache_size', '100', 'Taille maximale du cache des images en Mo. Paramètre modifiable uniquement via l\'application.', 1)";
	        echo traite_rqt($rqt,"insert pmb_img_cache_size = '100' into parametres ");
	    }
	    // DG - TS - Parametre pour définir la taille maximale du cache des images en opac
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='img_cache_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion, section_param)
					VALUES (NULL, 'opac', 'img_cache_size', '100', 'Taille maximale du cache des images en Mo. Paramètre modifiable uniquement via l\'application.', 1, 'a_general')";
	        echo traite_rqt($rqt,"insert opac_img_cache_size = '100' into parametres ");
	    }
	    // DG - TS - Parametre pour définir la volumétrie d'images à supprimer lors de la saturation du cache en gestion
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='img_cache_clean_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion)
					VALUES (NULL, 'pmb', 'img_cache_clean_size', '20', 'Pourcentage du nombre d\'images à supprimer lors de la saturation du cache.  Paramètre modifiable uniquement via l\'application.', 1)";
	        echo traite_rqt($rqt,"insert pmb_img_cache_clean_size = '20' into parametres ");
	    }
	    // DG - TS - Parametre pour définir la volumétrie d'images à supprimer lors de la saturation du cache en opac
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='img_cache_clean_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion, section_param)
					VALUES (NULL, 'opac', 'img_cache_clean_size', '20', 'Pourcentage du nombre d\'images à supprimer lors de la saturation du cache.  Paramètre modifiable uniquement via l\'application.', 1, 'a_general')";
	        echo traite_rqt($rqt,"insert opac_img_cache_clean_size = '20' into parametres ");
	    }
	    // DG - TS - Parametre pour définir le type des images stockees dans le cache opac
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='img_cache_type' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion, section_param)
					VALUES (NULL, 'opac', 'img_cache_type', 'webp', 'Type d\'image à stocker dans le cache. Paramètre modifiable uniquement via l\'application.', 1, 'a_general')";
	        echo traite_rqt($rqt,"insert opac_img_cache_type = 'webp' into parametres ");
	    }
	case 21 :
	    // DG - Ajout du champ cache_cadre_header sur la table cms_cache_cadres
	    $rqt = "ALTER TABLE cms_cache_cadres ADD cache_cadre_header MEDIUMTEXT NOT NULL" ;
	    echo traite_rqt($rqt,"ALTER cms_cache_cadres ADD cache_cadre_header");
	case 22 :
	    //GN - Alerter l'utilisateur par mail des nouvelles inscriptions aux animations proposees ?
	    $rqt = "ALTER TABLE users ADD user_alert_animation_mail INT(1) UNSIGNED NOT NULL DEFAULT 0 after deflt_animation_unique_registration";
	    echo traite_rqt($rqt,"ALTER TABLE users add user_alert_animation_mail default 0");

	    //GN - Ajout d'un mail pour reception. L'autre mail sert a l'envoi et n'est pas toujours consultable
	    $rqt = "ALTER TABLE users ADD user_email_recipient VARCHAR(255) default '' after user_alert_animation_mail";
	    echo traite_rqt($rqt,"ALTER TABLE users add user_email_recipient default ''");
	case 23 :
	    //GN - Ajout d'une table pour enregistrer les transactions de paiement
	    $rqt = "CREATE TABLE transaction_payments (
                id INT(11) unsigned auto_increment,
                order_number INT NOT NULL,
                payment_date DATETIME NOT NULL,
                payment_status INT(1) NOT NULL,
                payment_organization_status VARCHAR(10) NULL,
                num_user INT NOT NULL,
                num_organization INT(1)NOT NULL,
                PRIMARY KEY (id),
                UNIQUE order_number (order_number)
                ) ";
	    echo traite_rqt($rqt,"create table transaction_payments");

	    //GN - Ajout d'une table pour enregistrer les organismes de paiement
	    $rqt = "CREATE TABLE payment_organization (
                id INT(11) unsigned auto_increment,
                name VARCHAR(255) NOT NULL,
                data mediumblob NULL,
                PRIMARY KEY (id)
                ) ";
	    echo traite_rqt($rqt,"create table payment_organization");

	    //GN - Ajout d'une table d'une table de liaison entre les payments et les comptes
	    $rqt = "CREATE TABLE transaction_compte_payments (
                id INT(11) unsigned auto_increment,
                transaction_num INT NOT NULL,
                compte_num INT NOT NULL,
                amount INT NOT NULL,
                PRIMARY KEY (id)
                )";
	    echo traite_rqt($rqt,"create table transaction_compte_payments");
	case 24 :
		// DB - Modification des tables récolteur
		$rqt = "ALTER TABLE harvest_field ADD harvest_field_ufield varchar(100) DEFAULT NULL AFTER harvest_field_xml_id";
		echo traite_rqt($rqt,"ALTER TABLE harvest_field ADD harvest_field_ufields");

		$rqt = "ALTER TABLE harvest_search_field CHANGE num_field num_field VARCHAR(25) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"ALTER TABLE harvest_search_field CHANGE num_field VARCHAR(25)");

		$rqt = "ALTER TABLE harvest_src DROP harvest_src_pmb_unimacfield, DROP harvest_src_pmb_unimacsubfield, DROP harvest_src_unimacsubfield";
		echo traite_rqt($rqt,"ALTER TABLE harvest_src DROP harvest_src_pmb_unimacfield, harvest_src_pmb_unimacsubfield, harvest_src_unimacsubfield");

		$rqt = "ALTER TABLE harvest_src CHANGE harvest_src_unimacfield harvest_src_ufield VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"ALTER TABLE harvest_src CHANGE harvest_src_unimacfield harvest_src_ufield ");
}



/******************** JUSQU'ICI **************************************************/
/* PENSER à faire +1 au paramètre $pmb_subversion_database_as_it_shouldbe dans includes/config.inc.php */
/* COMMITER les deux fichiers addon.inc.php ET config.inc.php en même temps */

echo traite_rqt("update parametres set valeur_param='".$pmb_subversion_database_as_it_shouldbe."' where type_param='pmb' and sstype_param='bdd_subversion'","Update to $pmb_subversion_database_as_it_shouldbe database subversion.");
echo "<table>";