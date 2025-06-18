<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: addon.inc.php,v 1.6.2.41 2022/01/25 08:14:19 gneveu Exp $

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
        //MO && QV - Ajout d'un paramètre pour activer l'historique de navigation graphique.
        if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='nav_history_activated' "))==0){
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param)
                    VALUES (0, 'opac', 'nav_history_activated','0','Activer l\'historique de navigation graphique.\n 0: Non \n 1: Oui','a_general')";
            echo traite_rqt($rqt,"insert opac_nav_history_activated=0 into parametres");
        }
    case 1:
        // DG - Envoi par mail de l'impression
        if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param='opac' and sstype_param='print_email' "))==0){
            $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
	  			VALUES (0, 'opac', 'print_email', '1', 'Autoriser l\'envoi par mail sur le formulaire d\'impression de recherche ?\n 0 : Non \n 1 : Oui \n 2 : Seulement pour les lecteurs connectés', 'a_general', '0')";
            echo traite_rqt($rqt,"insert opac_print_email=1 into parametres ");
        }
        
        // DG - Afficher le champ de saisie de l'adresse mail expéditrice sur le formulaire d'impression de recherche ?
        if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param='opac' and sstype_param='print_email_sender' "))==0){
            $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
	  			VALUES (0, 'opac', 'print_email_sender', '1', 'Afficher le champ de saisie de l\'adresse mail expéditrice sur le formulaire d\'impression de recherche ?\n 0 : Non \n 1 : Oui', 'a_general', '0')";
            echo traite_rqt($rqt,"insert opac_print_email_sender=1 into parametres ");
        }
        
        // DG - Autoriser l'envoi du formulaire d'impression de recherche à d'autres destinataires ?
        if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param='opac' and sstype_param='print_email_recipients' "))==0){
            $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
	  			VALUES (0, 'opac', 'print_email_recipients', '1', 'Autoriser l\'envoi du formulaire d\'impression de recherche à d\'autres destinataires ?\n 0 : Non \n 1 : Oui \n 2 : Seulement pour les lecteurs connectés', 'a_general', '0')";
            echo traite_rqt($rqt,"insert opac_print_email_recipients=1 into parametres ");
        }
    case 2:
        // DG - Grilles de saisie de notices : Modification commentaire du paramètre form_editables pour la gestion avancée
        $rqt = "update ignore parametres set comment_param='Grilles de notices éditables ? \n 0 non \n 1 oui, gestion simple \n 2 oui, gestion avancée \nAttention : ce sont deux gestions de grilles différentes, vous pouvez passez de l\'une à l\'autre mais les données restent bien distinctes.' where type_param='pmb' and sstype_param='form_editables'";
        echo traite_rqt($rqt, "update parametres change comment for pmb_form_editables");
        
        // DG - Grilles de saisie de lecteurs : Modification commentaire du paramètre form_empr_editables pour la gestion avancée
        $rqt = "update ignore parametres set comment_param='Grilles de lecteurs éditables ? \n 0 non \n 1 oui, gestion simple \n 2 oui, gestion avancée \nAttention : ce sont deux gestions de grilles différentes, vous pouvez passez de l\'une à l\'autre mais les données restent bien distinctes.' where type_param='pmb' and sstype_param='form_empr_editables'";
        echo traite_rqt($rqt, "update parametres change comment for pmb_form_empr_editables");
    case 3:
        // DG - Autoriser ou non la prolongation par le responsable du groupe
        if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param='opac' and sstype_param='pret_groupe_prolongation' "))==0){
            global $opac_pret_prolongation;
            $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
	  			VALUES (0, 'opac', 'pret_groupe_prolongation', '".intval($opac_pret_prolongation)."', 'Autoriser le responsable à prolonger les prêts de son groupe ?\n 0 : Non \n 1 : Oui', 'a_general', '0')";
            echo traite_rqt($rqt,"insert opac_pret_groupe_prolongation=opac_pret_prolongation into parametres ");
        }
    case 4:
    	//DG - Propriétaire par défaut en création de document numérique
    	if (!pmb_mysql_num_rows(pmb_mysql_query("SHOW COLUMNS FROM users LIKE 'deflt_explnum_lenders'"))){
    		$rqt = "ALTER TABLE users ADD deflt_explnum_lenders INT(6) UNSIGNED DEFAULT 1 NOT NULL AFTER deflt_explnum_statut " ;
    		echo traite_rqt($rqt,"ALTER users ADD deflt_explnum_lenders ");
    		
    		//Mise à jour des valeurs selon le paramétrage existant
    		$rqt = "UPDATE users SET deflt_explnum_lenders=deflt_lenders";
    		echo traite_rqt($rqt,"UPDATE users SET deflt_explnum_lenders=deflt_lenders");
    	}
    	
    	//DG - Localisation par défaut en création de document numérique
    	if (!pmb_mysql_num_rows(pmb_mysql_query("SHOW COLUMNS FROM users LIKE 'deflt_explnum_location'"))){
    		$rqt = "ALTER TABLE users ADD deflt_explnum_location INT(6) UNSIGNED DEFAULT 1 NOT NULL AFTER deflt_explnum_statut " ;
    		echo traite_rqt($rqt,"ALTER users ADD deflt_explnum_location ");
    		
    		//Mise à jour des valeurs selon le paramétrage existant
    		$rqt = "UPDATE users SET deflt_explnum_location=deflt_docs_location";
    		echo traite_rqt($rqt,"UPDATE users SET deflt_explnum_location=deflt_docs_location");
    	}
    case 5:
        //DG - On stocke la provenance du mail
        if (!pmb_mysql_num_rows(pmb_mysql_query("SHOW COLUMNS FROM mails LIKE 'mail_from_uri'"))){
            $rqt = "ALTER TABLE mails ADD mail_from_uri VARCHAR(255) NOT NULL DEFAULT ''" ;
            echo traite_rqt($rqt,"ALTER mails ADD mail_from_uri ");
        }
        
        //DG - On stocke la provenance du mail en attente
        if (!pmb_mysql_num_rows(pmb_mysql_query("SHOW COLUMNS FROM mails_waiting LIKE 'mail_waiting_from_uri'"))){
            $rqt = "ALTER TABLE mails_waiting ADD mail_waiting_from_uri VARCHAR(255) NOT NULL DEFAULT ''" ;
            echo traite_rqt($rqt,"ALTER mails_waiting ADD mail_waiting_from_uri ");
        }
    case 6:
        //DG - Paramètre pour l'orientation du bandeau d'acceptation des cookies
        if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='cookies_consent_orientation' "))==0){
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'opac', 'cookies_consent_orientation', 'bottom', 'Emplacement du bandeau d\'acceptation des cookies et des traceurs ? \ntop : en haut \nmiddle : au milieu \nbottom : en bas \npopup : fenêtre','a_general',0)";
            echo traite_rqt($rqt,"insert opac_cookies_consent_orientation into parametres");
        }
        
        //DG - Paramètre pour afficher ou non l'icône de modification des paramètres
        if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='cookies_consent_show_icon' "))==0){
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'opac', 'cookies_consent_show_icon', '0', 'Afficher l\'icône de modification des cookies et des traceurs ? \n0 : non \n1 : oui','a_general',0)";
            echo traite_rqt($rqt,"insert opac_cookies_consent_show_icon into parametres");
        }
    case 7:
        // DG - Création de la table de planches de codes-barres
        // id_barcodes_sheet : Identifiant
        // barcodes_sheet_label : Libellé
        // barcodes_sheet_data : Structure JSON des données de génération
        // barcodes_sheet_order : Ordre
        $rqt = "create table if not exists barcodes_sheets(
				id_barcodes_sheet int unsigned not null auto_increment primary key,
				barcodes_sheet_label varchar(255) not null default '',
				barcodes_sheet_data text not null,
				barcodes_sheet_order int(11) NOT NULL default 0) ";
        echo traite_rqt($rqt,"create table barcodes_sheets");
    case 8:
        // DG - Création de la table pour gérer les cookies et les traceurs
        // id_analytics_service : Identifiant
        // analytics_service_name : Nom du service
        // analytics_service_active : Activé Oui/Non
        // analytics_service_parameters : Paramètres
        // analytics_service_template : Template calculé
        // analytics_service_consent_template : Template de consentement calculé
        $rqt = "create table if not exists analytics_services(
				id_analytics_service int unsigned not null auto_increment primary key,
				analytics_service_name varchar(255) not null default '',
				analytics_service_active int(1) NOT NULL DEFAULT 0,
				analytics_service_parameters mediumtext not null,
				analytics_service_template text not null,
				analytics_service_consent_template text not null)";
        echo traite_rqt($rqt,"create table analytics_services");
    case 9:
        // BT - Ajout d'un paramètre permettant de gérer l'affichage OPAC des options de groupement sur les bannettes / alertes
        // Le paramètre contenant une faute d'orthographe (banette au lieu de bannette), le case est supprimé et corrigé en dessous (98)
	case 10:
	   // AR - Ajout d'un paramètre pour la gestion des troncatures dans Sphinx
	   if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'sphinx' AND sstype_param = 'troncat_min_length'")) == 0) {
	       $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			 VALUES (0, 'sphinx', 'troncat_min_length', '3', 'Nombre de caractères minimum du mot cherché pour déclencher la troncature. \n La modification de ce paramètre nécessite la regénération complète des index Sphinx. (via les lignes de commandes)', '', 0)";
            echo traite_rqt($rqt, "insert sphinx_troncat_min_length into parametres");
        }
	case 11:
	    // BT - Correction du nom du paramètre show_bannettes_groupement (case 96)
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'opac' AND sstype_param = 'show_banettes_groupement'"))) {
	        $rqt = "DELETE FROM parametres WHERE type_param = 'opac' AND sstype_param = 'show_banettes_groupement'";
	        echo traite_rqt($rqt, "delete opac_show_banettes_groupement");
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'opac', 'show_bannettes_groupement', '0', 'Affichage des options de groupement dans les alertes ? \n0 : non \n1 : oui', 'f_modules', 0)";
	        echo traite_rqt($rqt, "insert opac_show_bannettes_groupement into parametres");
	    } elseif (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'opac' AND sstype_param = 'show_bannettes_groupement'")) == 0) {
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'opac', 'show_bannettes_groupement', '0', 'Affichage des options de groupement dans les alertes ? \n0 : non \n1 : oui', 'f_modules', 0)";
	        echo traite_rqt($rqt, "insert opac_show_bannettes_groupement into parametres");
	    }
	case 12:
	    // QV/GN/JP - Correction du champ date dans la table anim_registrations
	    $rqt = "CREATE TABLE IF NOT EXISTS anim_registrations (
			id_registration INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
			nb_registered_persons INT(11),
            name VARCHAR(255),
            email VARCHAR(255),
            phone_number VARCHAR(255),
            num_animation INT(11),
            num_registration_status INT(11),
            num_empr INT(11),
            num_origin INT(11),
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            hash VARCHAR(255) NULL DEFAULT ''
        )";
	    echo traite_rqt($rqt, 'CREATE TABLE IF NOT EXISTS anim_registrations');
	    
	    if (pmb_mysql_num_rows(pmb_mysql_query("SHOW COLUMNS FROM anim_registrations WHERE FIELD = 'date' and Type = 'datetime'")) == 1) {
            $rqt = "ALTER TABLE anim_registrations CHANGE date date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
            echo traite_rqt($rqt,"ALTER TABLE anim_registrations CHANGE date");
	    }
	    
	    // QV/GN/JP - Ajout du champ hash dans la table anim_registrations
	    if (pmb_mysql_num_rows(pmb_mysql_query('SHOW COLUMNS FROM anim_registrations WHERE Field = "hash"')) == 0) {
	        $rqt = "ALTER TABLE anim_registrations ADD hash VARCHAR(255) NULL DEFAULT '' AFTER date";
	        echo traite_rqt($rqt,"alter table anim_registrations add hash");
	    }
	    
	    // QV/GN - renommage de la colonne nb_registred_persons -> nb_registered_persons
	    if (pmb_mysql_num_rows(pmb_mysql_query('SHOW COLUMNS FROM anim_registrations WHERE Field = "nb_registred_persons"')) == 1) {
	        $rqt = "ALTER TABLE anim_registrations CHANGE nb_registred_persons nb_registered_persons INT(11)";
	        echo traite_rqt($rqt,"ALTER TABLE anim_registrations CHANGE nb_registred_persons nb_registered_persons");
	    }
	case 13:
		// DG - Ajout de la possibilité de joindre les images dans le mail ( pmb_mail_html_format=2 )
		$rqt = "update parametres set comment_param = 'Format d\'envoi des mails : \n 0: Texte brut\n 1: HTML \n 2: HTML, images incluses\nAttention, ne fonctionne qu\'en mode d\'envoi smtp !' where type_param='pmb' and sstype_param='mail_html_format'";
		echo traite_rqt($rqt,"update parametre pmb_mail_html_format");
	case 14:
	    // QV/JP - Passage du champ "chat_message_date" en timestamp s'il est en datetime
        $rqt = "ALTER TABLE chat_messages CHANGE chat_message_date chat_message_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
        echo traite_rqt($rqt,"ALTER TABLE chat_messages CHANGE chat_message_date");
	case 15 :
	    //DB - Modification du libellé des paramètres pmb_mail_methode / opac_mail_methode suite ajout mode d'authentification SMTP
	    $rqt = "update parametres set comment_param = 'Méthode d\'envoi des mails : \n php : fonction mail() de php\n smtp,hote:port,auth,user,pass,(ssl|tls),auth_type\n\nen smtp,\nauth (authentification) = 0 ou 1\nauth_type (type d\'authentification) = Non renseigné, CRAM-MD5, LOGIN, PLAIN ou XOAUTH2' where sstype_param = 'mail_methode'";
	    echo traite_rqt($rqt,"update parametre pmb_mail_methode, opac_mail_methode");
	case 16 :
		// DG - Table de logs
		$rqt = "CREATE TABLE IF NOT EXISTS logs (
					id_log int unsigned not null auto_increment primary key,
					log_service varchar(255) not null default '',
					log_type varchar(255) not null default '',
					log_module varchar(255) not null default '',
					log_label varchar(255) not null default '',
					log_message mediumtext,
					log_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					log_url varchar(255) not null default '',
					log_num_user int(10) unsigned not null default 0,
					log_type_user int(1) unsigned not null default 0,
					log_data mediumtext
        		)";
		echo traite_rqt($rqt,"create table logs");
	case 17 :
	    //GN QV JP - Paramètres pour afficher le champ de recherche sur l'identifiant d'une authorité
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='show_authority_id' "))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                    VALUES (0, 'pmb', 'show_authority_id', '0', 'Afficher le champ de recherche sur l\'identifiant d\'une autorité \n 0 : non \n 1 : oui', '',0) ";
	        echo traite_rqt($rqt, "insert pmb_show_authority_id = 0 into parameters");
	    }
	case 18 :
		// DG - Adresse mail du destinataire pour la demande de désinscription à une liste de diffusion.
		if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='mail_list_unsubscribe_mailto' "))==0){
			global $opac_biblio_email;
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                    VALUES(0,'pmb','mail_list_unsubscribe_mailto','".$opac_biblio_email."','Adresse mail du destinataire pour la demande de désinscription à une liste de diffusion.','',0)" ;
			echo traite_rqt($rqt,"insert pmb_mail_list_unsubscribe_mailto into parametres");
		}
	case 19 :
		// DG - On modifie la clé primaire en retirant l'auto incrément
		$req="SHOW COLUMNS from logs like 'id_log'";
		$res=pmb_mysql_query($req);
		if($res && pmb_mysql_num_rows($res)){
			$rqt = "ALTER TABLE logs change id_log uniqid_log varchar(13) NOT NULL default ''";
			echo traite_rqt($rqt,"ALTER TABLE logs change id_log to uniqid_log varchar(13)") ;
		}
	case 20 :
	    //MO - RT Ajout d'un parametre pour activer le pipe comme caratere d'opérateur OU en recherche
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='search_fixed_and_operator' "))==0) {
	        $rqt = "insert into parametres (valeur_param, type_param, sstype_param, section_param, comment_param) values ('0', 'pmb', 'search_fixed_and_operator', 'search', 'Empêcher le caractère plus (+) d\'être interprété comme un opérateur OU dans les recherches.\nL\'opérateur OU par défaut est la ligne verticale ou pipe \'|\'. \n0 : non \n1 : oui')" ;
	        echo traite_rqt($rqt, "Add general setting search_fixed_and_operator");
	    }
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='opac_search_fixed_and_operator' "))==0) {
	        $rqt = "insert into parametres (valeur_param, type_param, sstype_param, section_param, comment_param) values ('0', 'opac', 'opac_search_fixed_and_operator', 'c_recherche', 'Empêcher le caractère plus (+) d\'être interprété comme un opérateur OU dans les recherches opac.\nL\'opérateur OU par défaut est la ligne verticale ou pipe \'|\'.\n0 : non \n1 : oui')" ;
	        echo traite_rqt($rqt, "Add opac parametre opac_search_fixed_and_operator");
	    }
	case 21 :
		//DG - Nettoyage des entrées de traductions à partir des paramètres
		// Les données peuvent être tronquées, il faut donc que les traductions soient dans le champ trans_text
		$rqt = "DELETE FROM translation WHERE trans_table='parametres' AND trans_field='valeur_param' AND trans_small_text != ''";
		echo traite_rqt($rqt,"delete values translations in small_text");
		
		// DG - Table de définition/personnalisation des sélecteurs
		$rqt = "CREATE TABLE IF NOT EXISTS selectors (
					selector_name varchar(255) not null default '',
					selector_parameters_tabs mediumtext,
					primary key(selector_name)
        		)";
		echo traite_rqt($rqt,"create table selectors");
	case 22:
	    // JP-QV - Table de liaisons d'un segment vers des univers
	    $rqt = "CREATE TABLE IF NOT EXISTS search_segments_associated_universes (
                   num_segment int(10) NOT NULL,
                   num_universe int(10) NOT NULL
                )";
	    echo traite_rqt($rqt,"create table search_segments_associated_universes");
	case 23:
	    // DB - Ajout champs d'activation RMC dans tables search_universes et search_segments
	    $req="SHOW COLUMNS from search_universes like 'search_universe_rmc_enabled'";
	    $res=pmb_mysql_query($req);
	    if($res && pmb_mysql_num_rows($res)==0){
	        $rqt = "ALTER TABLE search_universes ADD search_universe_rmc_enabled INT(1) NOT NULL DEFAULT '0' ";
	        echo traite_rqt($rqt,"ALTER TABLE search_universes add search_universe_rmc_enabled") ;
	    }
	    $req="SHOW COLUMNS from search_segments like 'search_segment_rmc_enabled'";
	    $res=pmb_mysql_query($req);
	    if($res && pmb_mysql_num_rows($res)==0){
	        $rqt = "ALTER TABLE search_segments ADD search_segment_rmc_enabled INT(1) NOT NULL DEFAULT '0' ";
	        echo traite_rqt($rqt,"ALTER TABLE search_segments add search_segment_rmc_enabled") ;
	    }
	case 24:
	    $req = "SHOW KEYS FROM search_segments_associated_universes";
	    $res = pmb_mysql_query($req);
	    if ($res && pmb_mysql_num_rows($res) == 0) {
	        $rqt = "ALTER TABLE search_segments_associated_universes ADD PRIMARY KEY (num_segment, num_universe); ";
	        echo traite_rqt($rqt,"ALTER TABLE search_segments_associated_universes ADD PRIMARY KEY") ;
	    }
	case 25:
		//DG - Personnalisation du mode d'affichage dans les sélecteurs
		if (!pmb_mysql_num_rows(pmb_mysql_query("SHOW COLUMNS FROM selectors LIKE 'selector_display_modes'"))){
			$rqt = "ALTER TABLE selectors ADD selector_display_modes mediumtext" ;
			echo traite_rqt($rqt,"ALTER selectors ADD selector_display_modes ");
		}
	case 26:
		//DG - Paramètre pour la supervision des logs
		if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'supervision' and sstype_param='logs_active' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'supervision', 'logs_active', '0', 'Supervision des logs activée.\n 0 : Non\n 1 : Oui','', 1)";
			echo traite_rqt($rqt,"insert supervision_logs_active into parametres");
		}
		
		//DG - Paramètre pour la supervision des mails
		if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'supervision' and sstype_param='mails_active' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'supervision', 'mails_active', '0', 'Supervision des mails activée.\n 0 : Non\n 1 : Oui','', 1)";
			echo traite_rqt($rqt,"insert supervision_mails_active into parametres");
		}
	case 27:
		//GN-TS - Paramètre pour afficher la RMC responsive
		if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='rmc_responsive' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'opac', 'rmc_responsive', '0', 'Affichage de la recherche multi-critères responsive .\n 0 : Non\n 1 : Oui','', 0)";
			echo traite_rqt($rqt,"insert opac_rmc_responsive into parametres");
		}
	case 28:
	    //QV JP - Paramètre pour utiliser la vignette d'un document numérique si pas de vignette pour la notice
	    $query_old = "select 1 from parametres where type_param= 'opac' and sstype_param='record_use_thumbnail_docnum'";
	    $query_new = "select 1 from parametres where type_param= 'opac' and sstype_param='book_pics_use_thumbnail_docnum'";
	    if (pmb_mysql_num_rows(pmb_mysql_query($query_old))==0 && pmb_mysql_num_rows(pmb_mysql_query($query_new))==0) {
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'opac', 'record_use_thumbnail_docnum', '0', 'Utiliser la vignette du premier document numérique, si la notice n\'en possède pas.\n 0 : Non\n 1 : Oui','', 0)";
			echo traite_rqt($rqt,"insert opac_record_use_thumbnail_docnum=0 into parametres");
		}
	case 29:
	    //QV JP - UPDATE du paramètre pour utiliser la vignette d'un document numérique si pas de vignette pour la notice
	    $query_old = "select 1 from parametres where type_param= 'opac' and sstype_param='record_use_thumbnail_docnum'";
	    $query_new = "select 1 from parametres where type_param= 'opac' and sstype_param='book_pics_use_thumbnail_docnum'";
	    if (pmb_mysql_num_rows(pmb_mysql_query($query_old))==1 && pmb_mysql_num_rows(pmb_mysql_query($query_new))==0) {
	        $rqt = "UPDATE parametres SET sstype_param='book_pics_use_thumbnail_docnum', section_param='e_aff_notice' WHERE type_param= 'opac' and sstype_param='record_use_thumbnail_docnum'";
	        echo traite_rqt($rqt,"Parametre opac_record_use_thumbnail_docnum switch to opac_book_pics_use_thumbnail_docnum");
	    }
	case 30:
	    //TS GN - Paramètre pour sotcker la vignette d'un document numérique dans un repertoire defini
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='docnum_img_folder_id' "))==0) {
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'pmb', 'docnum_img_folder_id', '0', 'Identifiant du répertoire d\'upload des vignettes de documents numériques.','', 0)";
	        echo traite_rqt($rqt,"insert pmb_docnum_img_folder_id=0 into parametres");
	    }
	case 31:
	    //QV JP - Paramètre pour limiter le nombre de noeud dans les graphes
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 from parametres where type_param='pmb' and sstype_param='entity_graph_limit'"))==0) {
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'pmb', 'entity_graph_limit', '20', 'Valeur numérique, permettant de limiter le nombre de points sur chaque branche du graphe :\n 0 pour illimité','', 0)";
	        echo traite_rqt($rqt,"insert pmb_entity_graph_limit=20 into parametres");
	    }
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 from parametres where type_param='opac' and sstype_param='entity_graph_limit'"))==0) {
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'opac', 'entity_graph_limit', '20', 'Valeur numérique, permettant de limiter le nombre de points sur chaque branche du graphe :\n 0 pour illimité', '', 0)";
	        echo traite_rqt($rqt,"insert opac_entity_graph_limit=20 into parametres");
	    }
	case 32 :
	    // QV JP - Script de vérification de saisie d'un exemplaire
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'pmb' AND sstype_param='expl_verif_js' "))==0){
	        $rqt = "INSERT INTO parametres ( type_param, sstype_param, valeur_param, comment_param,section_param,gestion)
					VALUES ( 'pmb', 'expl_verif_js', '', 'Script de vérification de saisie d\'exemplaire', '', 0)";
	        echo traite_rqt($rqt,"INSERT pmb_expl_verif_js='' INTO parametres");
	    }
	case 33 :
	    //QV JP - Creation de la table pour les types d'animations et jointure avec la table des animations
	    $rqt = "CREATE TABLE IF NOT EXISTS anim_types (
            id_type INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
            label VARCHAR(255) NOT NULL DEFAULT ''
        )";
	    echo traite_rqt($rqt,"CREATE TABLE anim_types");
	    
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM anim_types WHERE id_type='1'"))==0) {
	        $rqt = "INSERT INTO anim_types (id_type, label) VALUES (1, 'Type par défaut')";
	        echo traite_rqt($rqt, 'INSERT DEFAULT VALUES INTO anim_types');
	    }
	    
	    $req="SHOW COLUMNS FROM anim_animations LIKE 'num_type'";
	    $res = pmb_mysql_query($req);
	    if($res && pmb_mysql_num_rows($res)==0){
	        $rqt = "ALTER TABLE anim_animations ADD num_type INT(11) NOT NULL DEFAULT 1 ";
	        echo traite_rqt($rqt,"ALTER TABLE anim_animations ADD num_type=1") ;
	    }
	case 34 :
	    //GN - Ajout d'un template pour envoyer un mail de demande de mot de passe (Objet du mail et corps du mail)
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'empr' and sstype_param='send_pwd_mail_obj' "))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion)
					VALUES (0, 'empr', 'send_pwd_mail_obj', 'Demande de changement de mot de passe', 'Objet du mail envoyé lors de la demande de mot de passe.', 0)";
	        echo traite_rqt($rqt,"insert empr_send_pwd_mail_obj into parametres");
	    }
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'empr' and sstype_param='send_pwd_mail_text' "))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion)
					VALUES (0, 'empr', 'send_pwd_mail_text', 'Bonjour, \nAfin de vous connecter à notre site, un mot de passe temporaire a été généré : !!pwd!! \nVous pouvez le modifier en accédant à votre compte depuis l\'adresse !!url!!\nCordialement,\nLe responsable de la bibliothèque.', 'Texte du mail envoyé lors de la demande de mot de passe.\nNe pas oublier de mettre !!pwd!! pour insérer le mot de passe et !!url!! pour insérer le lien de connexion à l\'opac', 0)";
	        echo traite_rqt($rqt,"insert empr_send_pwd_mail_text into parametres");
	    }
	case 35 :
	    //GN - Ajout d'un parametre pour mettre par defaut l'envoi du mot de passe lors de la creation d'un nouvel utilisateur
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'empr' and sstype_param='send_pwd_by_mail' "))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion)
                    VALUES (0, 'empr', 'send_pwd_by_mail', 0, 'Paramétrer par défaut l\'envoi du mot de passe de l\'emprunteur par mail en création de compte : 0 : non 1 : oui', 0)";
	        echo traite_rqt($rqt,"insert empr_send_pwd_by_mail into parametres");
	    }
}






/******************** JUSQU'ICI **************************************************/
/* PENSER à faire +1 au paramètre $pmb_subversion_database_as_it_shouldbe dans includes/config.inc.php */
/* COMMITER les deux fichiers addon.inc.php ET config.inc.php en même temps */

echo traite_rqt("update parametres set valeur_param='".$pmb_subversion_database_as_it_shouldbe."' where type_param='pmb' and sstype_param='bdd_subversion'","Update to $pmb_subversion_database_as_it_shouldbe database subversion.");
echo "<table>";