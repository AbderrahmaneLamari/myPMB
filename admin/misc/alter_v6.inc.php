<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alter_v6.inc.php,v 1.16.2.4 2024/01/05 11:19:57 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

settype ($action,"string");

pmb_mysql_query("set names latin1 ");

switch ($action) {
    case "lancement":
        switch ($version_pmb_bdd) {
            case "v5.36":
                $maj_a_faire = "v6.00";
                echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
                echo form_relance ($maj_a_faire);
                break;
            case "v6.00":
                echo "<strong><font color='#FF0000'>".$msg[1805].$version_pmb_bdd." !</font></strong><br />";
                break;
            default:
                echo "<strong><font color='#FF0000'>".$msg[1806].$version_pmb_bdd." !</font></strong><br />";
                break;
        }
        break;

    case "v6.00":
        echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
        // Equipe DEV Refonte gestion des vignettes
        $rqt = "CREATE TABLE IF NOT EXISTS thumbnail_sources (
        			id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        			class varchar(255) NOT NULL DEFAULT '',
        			settings mediumblob NOT NULL,
        			active tinyint(1) NOT NULL DEFAULT 0
        		)";
        echo traite_rqt($rqt,"CREATE TABLE thumbnail_sources");

        $query = "SELECT 1 FROM thumbnail_sources WHERE id = 1";
        $result = pmb_mysql_query($query);
        if (!pmb_mysql_num_rows($result)) {
            $rqt = "INSERT INTO thumbnail_sources (class, settings, active) VALUES
                ('Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Noimage\\\\NoImageThumbnailSource', '[{\"typedoc\":\"\",\"nivbiblio\":\"\",\"value\":\"no_image.png\"}]', 1);";
            echo traite_rqt($rqt,"INSERT NoImageThumbnailSource INTO thumbnail_sources ");
        }

        $rqt = "CREATE TABLE IF NOT EXISTS thumbnail_sources_entities (
        			id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        			source_class varchar(255) NOT NULL DEFAULT '',
        			pivot_class varchar(255) NOT NULL DEFAULT '',
        			type int(11) NOT NULL DEFAULT 0,
        			pivot LONGTEXT,
        			ranking int(10) NOT NULL DEFAULT 0
        		)";
        echo traite_rqt($rqt,"CREATE TABLE thumbnail_sources_entities");

        $rqt = "ALTER TABLE thumbnail_sources_entities CHANGE pivot pivot LONGTEXT;";
        echo traite_rqt($rqt,"pivot CHANGE in thumbnail_sources_entities");

        $query = "SELECT 1 FROM thumbnail_sources_entities WHERE
    		        source_class in ('Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Url\\\\RecordUrlThumbnailSource',
    		                          'Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Amazon\\\\RecordAmazonThumbnailSource',
    		                          'Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Docnum\\\\RecordDocnumThumbnailSource',
    		                          'Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Noimage\\\\NoImageThumbnailSource')
    		        AND pivot_class = 'Pmb\\\\Thumbnail\\\\Models\\\\Pivots\\\\Entities\\\\Record\\\\RecordBasicPivot\\\\RecordBasicPivot' LIMIT 1";
        $result = pmb_mysql_query($query);
        if (!pmb_mysql_num_rows($result)) {
            $rqt = "INSERT INTO thumbnail_sources_entities (source_class, pivot_class, type, pivot, ranking) VALUES
                ('Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Url\\\\RecordUrlThumbnailSource', 'Pmb\\\\Thumbnail\\\\Models\\\\Pivots\\\\Entities\\\\Record\\\\RecordBasicPivot\\\\RecordBasicPivot', 1, '{\"typedoc\":\"\",\"nivbiblio\":\"\"}', 0),
                ('Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Amazon\\\\RecordAmazonThumbnailSource', 'Pmb\\\\Thumbnail\\\\Models\\\\Pivots\\\\Entities\\\\Record\\\\RecordBasicPivot\\\\RecordBasicPivot', 1, '{\"typedoc\":\"\",\"nivbiblio\":\"\"}', 1),
                ('Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Docnum\\\\RecordDocnumThumbnailSource', 'Pmb\\\\Thumbnail\\\\Models\\\\Pivots\\\\Entities\\\\Record\\\\RecordBasicPivot\\\\RecordBasicPivot', 1, '{\"typedoc\":\"\",\"nivbiblio\":\"\"}', 2),
                ('Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Noimage\\\\NoImageThumbnailSource', 'Pmb\\\\Thumbnail\\\\Models\\\\Pivots\\\\Entities\\\\Record\\\\RecordBasicPivot\\\\RecordBasicPivot', 1, '{\"typedoc\":\"\",\"nivbiblio\":\"\"}', 3);";
            echo traite_rqt($rqt,"INSERT RecordUrlThumbnailSource, RecordAmazonThumbnailSource, RecordDocnumThumbnailSource AND NoImageThumbnailSource INTO thumbnail_sources_entities ");
        }

        // GN - Ajout d'une colonne "logo" pour une animation
        $rqt = "ALTER TABLE anim_animations ADD logo blob default NULL";
        echo traite_rqt($rqt,"alter table anim_animations add logo");

        // GN - Ajout d'une colonne "anim_events" pour un event
        $rqt = "ALTER TABLE anim_events ADD during_day integer default 0";
        echo traite_rqt($rqt,"alter table anim_events add during_day");

        // DG - Ajout du paramétrage lié au type d'authentification
        $rqt = "ALTER TABLE mails_configuration ADD mail_configuration_authentification_type_settings mediumtext AFTER mail_configuration_authentification_type" ;
        echo traite_rqt($rqt,"ALTER TABLE mails_configuration ADD mail_configuration_authentification_type_settings");

        // DG - Configuration des mails - configuration validée ?
        $rqt = "ALTER TABLE mails_configuration ADD mail_configuration_validated INT(1) NOT NULL DEFAULT 0";
        echo traite_rqt($rqt,"ALTER TABLE mails_configuration add mail_configuration_validated");

        // DG - Configuration des mails - informations sur la configuration
        $rqt = "ALTER TABLE mails_configuration ADD mail_configuration_informations text NOT NULL";
        echo traite_rqt($rqt,"ALTER TABLE mails_configuration add mail_configuration_informations");

        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'selfservice' and sstype_param='resa_ici_todo_valid'")) == 0) {
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
        			VALUES (0, 'selfservice', 'resa_ici_todo_valid', '0', '1', 'Permet d\'ignorer la validité de la réservation') ";
            echo traite_rqt($rqt, "INSERT selfservice_resa_ici_todo_valid='0' INTO parametres") ;
        }

        // GN - Ajout d'une colonne dans "anim_animation" pour enregistrer qu'une personne a la fois a une animation
        $rqt = "ALTER TABLE anim_animations ADD unique_registration tinyint default 0";
        echo traite_rqt($rqt,"alter table anim_animations add unique_registration");

        // GN - Ajout d'un paramètre utilisateur pour les animations (Autoriser l'inscription en liste d'attente)
        $rqt = "ALTER TABLE users ADD deflt_animation_waiting_list TINYINT UNSIGNED DEFAULT 0 NOT NULL ";
        echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_animation_waiting_list");

        // GN - Ajout d'un paramètre utilisateur pour les animations (Valider l'inscription automatiquement à l'OPAC)
        $rqt = "ALTER TABLE users ADD deflt_animation_automatic_registration TINYINT UNSIGNED DEFAULT 0 NOT NULL ";
        echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_animation_automatic_registration");

        // GN - Ajout d'un paramètre utilisateur pour les animations (Type de communication)
        $rqt = "ALTER TABLE users ADD deflt_animation_communication_type TINYINT UNSIGNED DEFAULT 1 NOT NULL ";
        echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_animation_communication_type");

        // GN - Ajout d'un paramètre utilisateur pour les animations (Inscription unique a une animation)
        $rqt = "ALTER TABLE users ADD deflt_animation_unique_registration TINYINT UNSIGNED DEFAULT 0 NOT NULL ";
        echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_animation_unique_registration");

        // Equipe DEV refonte D.S.I
        $rqt = "CREATE TABLE IF NOT EXISTS dsi_channel (
				id_channel int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				settings mediumblob NOT NULL,
				type int(11) NOT NULL DEFAULT 0,
				model tinyint(1) NOT NULL DEFAULT 0,
				num_model int(10) UNSIGNED DEFAULT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_channel");


        $rqt = "CREATE TABLE IF NOT EXISTS dsi_content_history (
				id_content_history int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				type int(11) NOT NULL DEFAULT 0,
				content longblob NOT NULL,
				num_diffusion_history int(10) UNSIGNED NOT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_content_history");


        $rqt = "CREATE TABLE IF NOT EXISTS dsi_diffusion (
				id_diffusion int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				settings mediumblob NOT NULL,
				num_status int(10) UNSIGNED NOT NULL DEFAULT 1,
				num_subscriber_list int(10) UNSIGNED DEFAULT NULL,
				num_item int(10) UNSIGNED NOT NULL,
				num_view int(10) UNSIGNED NOT NULL,
				num_channel int(10) UNSIGNED NOT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_diffusion");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_diffusion_history (
				id_diffusion_history int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				total_recipients int(10) UNSIGNED NOT NULL DEFAULT 0,
				num_diffusion int(10) UNSIGNED NOT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_diffusion_history");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_diffusion_product (
				num_diffusion int(10) UNSIGNED NOT NULL,
				num_product int(10) UNSIGNED NOT NULL,
				active tinyint(1) NOT NULL DEFAULT 0,
				last_diffusion datetime DEFAULT NULL,
				PRIMARY KEY (num_diffusion, num_product)
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_diffusion_product");


        $rqt = "CREATE TABLE IF NOT EXISTS dsi_diffusion_status (
				id_diffusion_status int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				active tinyint(1) NOT NULL DEFAULT 0
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_diffusion_status");

        $rqt = "REPLACE INTO dsi_diffusion_status (id_diffusion_status, name, active) VALUES (1, 'Statut par défaut', '1')";
        echo traite_rqt($rqt,"INSERT default status into dsi_diffusion_status ");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_event (
				id_event int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				model tinyint(1) NOT NULL DEFAULT 0,
				settings mediumblob NOT NULL,
				type int(11) NOT NULL DEFAULT 0,
				num_model int(10) UNSIGNED DEFAULT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_event");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_event_diffusion (
				num_event int(10) UNSIGNED NOT NULL,
				num_diffusion int(10) UNSIGNED NOT NULL,
				PRIMARY KEY (num_event, num_diffusion)
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_event_diffusion");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_event_product (
				num_event int(10) UNSIGNED NOT NULL,
				num_product int(10) UNSIGNED NOT NULL,
				PRIMARY KEY (num_event, num_product)
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_event_diffusion");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_item (
				id_item int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				model tinyint(1) NOT NULL DEFAULT 0,
				settings mediumblob NOT NULL,
				type int(11) NOT NULL DEFAULT 0,
				num_model int(10) UNSIGNED DEFAULT NULL,
				num_parent int(10) UNSIGNED DEFAULT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_item");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_product (
				id_product int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				settings mediumblob NOT NULL,
				num_subscriber_list int(10) UNSIGNED DEFAULT NULL,
				num_status int(10) UNSIGNED NOT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_product");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_product_status (
				id_product_status int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				active tinyint(1) NOT NULL DEFAULT 0
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_product_status");

        $rqt = "REPLACE INTO dsi_product_status (id_product_status, name, active) VALUES (1, 'Statut par défaut', '1')";
        echo traite_rqt($rqt,"INSERT default status into dsi_product_status");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_subscribers (
				id_subscriber int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL,
				settings mediumblob NOT NULL,
				type int(11) NOT NULL DEFAULT 0,
				update_type int(11) NOT NULL DEFAULT 0
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_subscribers");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_subscribers_diffusion (
				id_subscriber_diffusion int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				settings mediumblob NOT NULL,
				type int(11) NOT NULL DEFAULT 0,
				update_type int(11) NOT NULL DEFAULT 0,
				num_diffusion int(10) UNSIGNED NOT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_subscribers_diffusion");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_subscribers_product (
				id_subscriber_product int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				settings mediumblob NOT NULL,
				type int(11) NOT NULL DEFAULT 0,
				update_type int(11) NOT NULL DEFAULT 0,
				num_product int(10) UNSIGNED NOT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_subscribers_product");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_subscriber_list (
				id_subscriber_list int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				model tinyint(1) NOT NULL DEFAULT 0,
				settings mediumblob NOT NULL,
				num_parent int(10) UNSIGNED DEFAULT NULL,
				num_model int(10) UNSIGNED DEFAULT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_subscriber_list");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_subscriber_list_content (
				num_subscriber int(10) UNSIGNED NOT NULL,
				num_subscriber_list int(10) UNSIGNED NOT NULL,
				PRIMARY KEY (num_subscriber, num_subscriber_list)
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_subscriber_list_content");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_tag (
				id_tag int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT ''
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_tag");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_view (
				id_view int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name varchar(255) NOT NULL DEFAULT '',
				model tinyint(1) NOT NULL DEFAULT 0,
				settings mediumblob NOT NULL,
				type int(11) NOT NULL DEFAULT 0,
				num_model int(10) UNSIGNED DEFAULT NULL,
				num_parent int(10) UNSIGNED DEFAULT NULL
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_view");

        $rqt = "CREATE TABLE IF NOT EXISTS dsi_entities_tags (
  				num_tag int(11) UNSIGNED NOT NULL,
  				num_entity int(11) UNSIGNED NOT NULL,
  				type int(11) UNSIGNED NOT NULL,
  				PRIMARY KEY (num_tag, num_entity, type)
			)";
        echo traite_rqt($rqt,"CREATE TABLE dsi_entities_tags");


        // Equipe DEV - Ajout d'un paramètre pour le RGAA
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'opac' and sstype_param='rgaa_active'")) == 0) {
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
        			VALUES (0, 'opac', 'rgaa_active', '0', '0', 'Activer la transformation HTML pour compatibilité RGAA\n0 : non\n1 : oui') ";
            echo traite_rqt($rqt, "INSERT opac_rgaa_active='0' INTO parametres") ;
        }

        // code here ...
        // +-------------------------------------------------+
        echo "</table>";
        $rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' ";
        $res = pmb_mysql_query($rqt) ;
        $rqt = "update parametres set valeur_param='0' where type_param='pmb' and sstype_param='bdd_subversion' ";
        $res = pmb_mysql_query($rqt) ;
        echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
        break;

    default:
        include("$include_path/messages/help/$lang/alter.txt");
        break;
}

/*         A METTRE EN 6.00
*/
