<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: lettre_reader_loans_late_PDF.class.php,v 1.16.2.1 2023/04/12 15:38:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/pdf/reader/loans/lettre_reader_loans_PDF.class.php");

class lettre_reader_loans_late_PDF extends lettre_reader_loans_PDF {
    
    protected static $initialized_PDF;
    
    protected static $niveau_relance;
    
    protected $expl_list;
    
    protected function _init_PDF() {
        if(empty(static::$initialized_PDF)) {
            parent::_init_PDF();
            static::$initialized_PDF = $this->PDF;
        } else {
            $this->PDF = static::$initialized_PDF;
        }
    }
    
    protected static function get_parameter_prefix() {
        return "pdflettreretard";
    }
    
    protected function get_parameter_value($name) {
        if(isset(static::$niveau_relance)) {
            $parameter_name = static::get_parameter_prefix().'_'.static::$niveau_relance.$name;
            if($this->is_exist_parameter($parameter_name)) {
                return $this->get_evaluated_parameter($parameter_name);
            }
        }
        $parameter_name = static::get_parameter_prefix().'_1'.$name;
        return $this->get_evaluated_parameter($parameter_name);
    }
    
    protected function _init_parameter_value($name, $value) {
        $parameter_name = static::get_parameter_prefix().'_'.static::$niveau_relance.$name;
        global $$parameter_name;
        if(empty(${$parameter_name}) && ${$parameter_name} != "0") {
            ${$parameter_name} = $value;
        }
    }
    
    protected function _init_default_positions() {
        $this->_init_position_values('date_jour', array($this->w/2,98,0,0,10));
        $this->_init_position_values('biblio_info', array($this->get_parameter_value('marge_page_gauche'),15));
        $this->_init_position_values('lecteur_adresse', array($this->get_parameter_value('marge_page_gauche'),45));
        if(!empty($this->get_parameter_value('objet'))) {
            $this->_init_position_values('objet', array($this->get_parameter_value('marge_page_gauche'),105,0,0,10));
            $this->_init_position_values('madame_monsieur', array($this->get_parameter_value('marge_page_gauche'),112,0,0,10));
        } else {
            $this->_init_position_values('madame_monsieur', array($this->get_parameter_value('marge_page_gauche'),105,0,0,10));
        }
        $this->_init_position_values('after_sign', array($this->get_parameter_value('marge_page_gauche'),0,0,0,10));
    }
    
    protected function get_query_list($id_empr) {
        return $this->get_query_list_base()." where pret_idempr='".$id_empr."' and pret_retour < curdate() ".$this->get_query_list_order();
    }
    
    protected function get_expl_list($id_empr) {
        
        if(empty($this->expl_list[$id_empr])) {
            $expl_list = array();
            if( 3 != static::$niveau_relance ) {
                $rqt = $this->get_query_list($id_empr);
                $req = pmb_mysql_query($rqt);
                while ($data = pmb_mysql_fetch_array($req)) {
                    $expl_list[] = $data;
                }
            } else {
                $expl_list['r3'] = array();
                $requete="select expl_cb from exemplaires, pret where pret_idempr=$id_empr and pret_idexpl=expl_id and niveau_relance=3";
                $res_recouvre=pmb_mysql_query($requete);
                while ($rrc=pmb_mysql_fetch_object($res_recouvre)) {
                    $expl_list['r3'][]=$rrc->expl_cb;
                }
                $rqt = $this->get_query_list($id_empr);
                $req = pmb_mysql_query($rqt);
                $expl_list['r'] = array();
                while ($data = pmb_mysql_fetch_object($req)) {
                    // Pas répéter les retard si déjà en niveau 3
                    if(isset($expl_list['r3'])){
                        if(in_array($data->expl_cb,$expl_list['r3'])===false){
                            $expl_list['r'][] = $data->expl_cb;
                        }
                    }
                }
            }
            $this->expl_list[$id_empr] = $expl_list;
        }
        return $this->expl_list[$id_empr];
    }
    
    protected function get_frais_relance($id_empr) {
        global $pmb_gestion_financiere, $pmb_gestion_amende;
        
        $frais_relance = 0;
        if (($pmb_gestion_financiere)&&($pmb_gestion_amende)) {
            $id_compte=comptes::get_compte_id_from_empr($id_empr,2);
            if ($id_compte) {
                $cpte=new comptes($id_compte);
                $frais_relance=$cpte->summarize_transactions("","",0,-1);
                if ($frais_relance<0) $frais_relance=-$frais_relance; else $frais_relance=0;
            }
        }
        return $frais_relance;
    }
    
    protected function display_parameter_multiCell($name) {
    	switch ($name) {
    		case 'fdp':
    			$this->display_multiCell($this->w, 5, $this->get_parameter_value($name), 0, 'R');
    			break;
    		default:
    			$this->display_multiCell($this->w, 5, $this->get_parameter_value($name));
    			break;
    	}
    }
    
    public function doLettre($id_empr) {
        global $pmb_afficher_numero_lecteur_lettres;
        global $mailretard_hide_fine;
        //Pour les amendes
        $valeur=0;
        $this->PDF->addPage();
        
        $this->display_date_jour();
        $this->display_biblio_info() ;
        $this->display_lecteur_adresse($id_empr, 90, 0, !$pmb_afficher_numero_lecteur_lettres, true,true);
        
        $this->display_objet();
        $this->display_madame_monsieur($id_empr);
        
        $this->PDF->SetXY ($this->get_parameter_value('marge_page_gauche'),$this->PDF->GetY()+4);
        $this->display_parameter_multiCell('before_list');
        
        //Récupération des exemplaires
        $expl_list = $this->get_expl_list($id_empr);
        
        //Calcul des frais de relance
        $frais_relance = $this->get_frais_relance($id_empr);
        
        $displayed_after_recouvrement = false;
        if( 3 != static::$niveau_relance ) {
            foreach ($expl_list as $data) {
                if (($pos_page=$this->PDF->GetY())>260) {
                    $this->PDF->addPage();
                    $pos_page=$this->get_parameter_value('debut_expl_page');
                }
                $valeur+=$this->display_expl_retard($data['expl_cb'],$pos_page, 10);
            }
            if (($valeur || $frais_relance) && (!$mailretard_hide_fine)) {
                $this->print_amendes($valeur,$frais_relance);
            }
            $this->PDF->SetX ($this->get_parameter_value('marge_page_gauche'));
            $this->PDF->setFont($this->font, '', 10);
        } else {
            $liste_r = $expl_list['r'];
            $liste_r3 = $expl_list['r3'];
            
            //Affichage des retards niveau 1 et 2
            if( !empty($liste_r) ) {
                foreach($liste_r as $cb_expl) {
                    if (($pos_page=$this->PDF->GetY())>260) {
                        $this->PDF->addPage();
                        $pos_page=$this->get_parameter_value('debut_expl_page');
                    }
                    $valeur+=$this->display_expl_retard($cb_expl,$pos_page, 10);
                }
            }
            //Affichage des retards de niveau 3
            if( !empty($liste_r3) ) {
                //Texte avant liste des recouvrements
                $this->PDF->setFont($this->font, '', 10);
                $this->display_parameter_multiCell('before_recouvrement');
                foreach($liste_r3 as $cb_expl) {
                    if (($pos_page=$this->PDF->GetY())>260) {
                        $this->PDF->addPage();
                        $pos_page=$this->get_parameter_value('debut_expl_page');
                    }
                    $valeur+=$this->display_expl_retard($cb_expl,$pos_page, 10);
                }
                if (($valeur || $frais_relance) && (!$mailretard_hide_fine)) {
                    $this->print_amendes($valeur,$frais_relance);
                }
                //Texte après liste des recouvrements si pas de retards niveau 1 ou 2
                if( empty($liste_r) ) {
                    $this->PDF->setFont($this->font, '', 10);
                    $this->display_parameter_multiCell('after_recouvrement');
                    $displayed_after_recouvrement = true;
                }
            }
        }
        if(empty($displayed_after_recouvrement)) {
	        $pos_page=$this->PDF->GetY();//Récupère la position dans la page pour prendre en compte l'ajout ou non des informations d'amendes et éviter la superposition d'informations
	        if (($pos_page+5)>$this->get_parameter_value('limite_after_list')) {
	            $this->PDF->addPage();
	            $pos_after_list = $this->get_parameter_value('debut_expl_page');
	        } else {
	            $pos_after_list = $pos_page+5;
	        }
	        $this->PDF->SetXY ($this->get_parameter_value('marge_page_gauche'),$pos_after_list);
        }
        
        $this->PDF->setFont($this->font, '', 10);
        $this->display_parameter_multiCell('after_list');
        
        $this->PDF->setFont($this->font, 'I', 10);
        $this->display_parameter_multiCell('fdp');
        
        $this->display_after_sign();
        
        return $valeur;
    }
    
    protected function get_query_expl_info($cb_doc) {
        global $msg;
        
        $dates_resa_sql = " date_format(pret_date, '".$msg["format_date"]."') as aff_pret_date, date_format(pret_retour, '".$msg["format_date"]."') as aff_pret_retour " ;
        $query = "SELECT notices_m.notice_id as m_id, notices_s.notice_id as s_id, pret_idempr, expl_id, expl_cb, expl_cote, expl_prix, pret_date, pret_retour, tdoc_libelle, section_libelle, location_libelle, trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if (mention_date!='', concat(' (',mention_date,')') ,''))) as tit, ".$dates_resa_sql.", " ;
        $query.= " notices_m.tparent_id, notices_m.tnvol " ;
        $query.= " FROM (((exemplaires LEFT JOIN notices AS notices_m ON expl_notice = notices_m.notice_id ) LEFT JOIN bulletins ON expl_bulletin = bulletins.bulletin_id) LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), docs_type, docs_section, docs_location, pret ";
        $query.= " WHERE expl_cb='".addslashes($cb_doc)."' and expl_typdoc = idtyp_doc and expl_section = idsection and expl_location = idlocation and pret_idexpl = expl_id  ";
        return $query;
    }
    
    protected function display_expl_info_notice_description($cb_doc, $short=0, $longmax=99999) {
        $expl = $this->get_expl_info($cb_doc);
        $libelle=$expl->tdoc_libelle;
        $responsabilites=get_notice_authors($expl->m_id+$expl->s_id) ;
        
        $as = array_keys ($responsabilites["responsabilites"], "0" ) ;
        $aut1_libelle = array();
        for ($i = 0 ; $i < count($as) ; $i++) {
            $indice = $as[$i] ;
            $auteur_1 = $responsabilites["auteurs"][$indice] ;
            $auteur = new auteur($auteur_1["id"]);
            $aut1_libelle[]= $auteur->get_isbd();
            
        }
        if (count($aut1_libelle)) {
            $auteurs_liste = implode ("; ",$aut1_libelle) ;
            if ($auteurs_liste) $libelle .= ' / '. $auteurs_liste;
            
        }
        $libelle=$expl->tit." (".$libelle.")" ;
        while( $this->PDF->GetStringWidth($libelle) > 178) {
            $libelle=substr($libelle,0,strlen($libelle)-2);
        }
        $this->PDF->multiCell($this->w, 8, $libelle, 0, 'L', 0);
    }
    
    protected function display_expl_retard_info_dates($cb_doc, $retrait) {
        global $msg;
        
        $expl = $this->get_expl_info($cb_doc);
        $this->PDF->SetXY ($this->x_expl_info+$retrait,$this->y_expl_info+4);
        $this->PDF->setFont($this->font, '', 10);
        $this->PDF->multiCell(($this->w - $retrait), 8, $msg['fpdf_date_pret']." ".$expl->aff_pret_date, 0, 'L', 0);
        if (ceil($this->PDF->GetStringWidth($msg['fpdf_date_pret']." ".$expl->aff_pret_date)) > 52) {
            $w_string =	ceil($this->PDF->GetStringWidth($msg['fpdf_date_pret']." ".$expl->aff_pret_date));
        } else {
            $w_string = 52;
        }
        $this->PDF->SetXY (($this->x_expl_info+$retrait+$w_string),$this->y_expl_info+4);
        $this->PDF->setFont($this->font, 'B', 10);
        $this->PDF->multiCell(($this->w - $retrait - 52), 8, $msg['fpdf_retour_prevu']." ".$expl->aff_pret_retour, 0, 'L', 0);
    }
    
    protected function display_expl_retard_info_description($cb_doc, $retrait) {
        $expl = $this->get_expl_info($cb_doc);
        
        $this->PDF->SetXY ($this->x_expl_info+$retrait,$this->y_expl_info+8);
        $this->PDF->setFont($this->font, 'I', 8);
        $this->PDF->multiCell(($this->w - $retrait), 8, strip_tags($expl->location_libelle.": ".parseHTML($expl->section_libelle).", ".$expl->expl_cote." (".$expl->expl_cb.")"), 0, 'L', 0);
        
    }
    
    protected function display_expl_retard($cb_doc, $y, $retrait) {
        global $msg;
        global $pmb_gestion_financiere, $pmb_gestion_amende;
        global $mailretard_hide_fine;
        
        $valeur=0;
        $this->x_expl_info = $this->get_parameter_value('marge_page_gauche');
        //Position y calculée avant l'appel
        $this->y_expl_info = $y;
        
        $expl = $this->get_expl_info($cb_doc);
        
        $this->PDF->SetXY ($this->x_expl_info,$this->y_expl_info);
        $this->PDF->setFont($this->font, 'BU', 10);
        $this->display_expl_info_notice_description($cb_doc);
        
        $this->display_expl_retard_info_dates($cb_doc, $retrait);
        $this->display_expl_retard_info_description($cb_doc, $retrait);
        
        if (($pmb_gestion_financiere)&&($pmb_gestion_amende)) {
            $amende=new amende($expl->pret_idempr);
            $amd=$amende->get_amende($expl->expl_id);
            if ($amd["valeur"] && !$mailretard_hide_fine) {
                $this->PDF->SetXY (($this->x_expl_info+$retrait+120),$this->y_expl_info+8);
                $this->PDF->multiCell(($this->w - $retrait - 120), 8, sprintf($msg["relance_lettre_retard_amende"],comptes::format_simple($amd["valeur"])), 0, 'R', 0);
                $valeur=$amd["valeur"];
            }
        }
        return $valeur;
    }
    
    protected function display_expl_retard_empr($id_empr, $cb_doc, $y, $retrait) {
        $requete = "SELECT id_empr, empr_cb, empr_nom, empr_prenom, empr_adr1, empr_adr2, empr_cp, empr_ville, empr_pays, empr_mail, empr_tel1, empr_tel2  FROM empr WHERE id_empr='$id_empr' LIMIT 1 ";
        $res = pmb_mysql_query($requete);
        $empr = pmb_mysql_fetch_object($res);
        $this->PDF->SetXY ($this->get_parameter_value('marge_page_gauche'),$y);
        $this->PDF->setFont($this->font, '', 12);
        $this->PDF->multiCell(100, 8, $empr->empr_prenom." ".$empr->empr_nom, 0, 'L', 0);
        $y=$y+4;
        $this->display_expl_retard($cb_doc, $y, $retrait+10) ;
    }
    
    protected function print_amendes($valeur,$frais_relance) {
        global $msg;
        //Si il y a des amendes
        $this->PDF->SetY ($this->PDF->GetY()+2);
        $this->PDF->setFont($this->font, '', 10);
        $this->PDF->SetWidths(array(70,30));
        
        if ($this->PDF->GetY()>260) {
            $this->PDF->addPage();
            $this->PDF->SetY($this->get_parameter_value('debut_expl_page'));
        }
        if ($valeur) {
            $this->PDF->SetX ($this->get_parameter_value('marge_page_gauche')+40);
            $this->PDF->Row(array($msg["relance_lettre_retard_total_amendes"], comptes::format_simple($valeur) ));
        }
        if ($frais_relance) {
            $this->PDF->SetX ($this->get_parameter_value('marge_page_gauche')+40);
            $this->PDF->Row(array($msg["relance_lettre_retard_frais_relance"], comptes::format_simple($frais_relance) ));
        }
        if (($frais_relance)&&($valeur)) {
            $this->PDF->SetX ($this->get_parameter_value('marge_page_gauche')+40);
            $this->PDF->Row(array($msg["relance_lettre_retard_total_du"], comptes::format_simple($valeur+$frais_relance) ));
        }
        $this->PDF->SetY ($this->PDF->GetY()+4);
    }
    
    public static function set_niveau_relance($niveau_relance) {
        static::$niveau_relance = $niveau_relance;
    }
    
    public static function get_instance($group='') {
        global $msg, $charset;
        global $base_path, $class_path, $include_path;
        
        if(empty(static::$niveau_relance)) {
            return parent::get_instance($group);
        } else {
            $className = static::class;
            if(!isset(static::$instances[$className][static::$niveau_relance])) {
                $print_parameter = static::get_parameter_name(static::$niveau_relance.'print');
                global ${$print_parameter};
                if($group) {
                    if(!empty(${$print_parameter}) && file_exists($class_path."/pdf/".$group."/".${$print_parameter}.".class.php")) {
                        require_once($class_path."/pdf/".$group."/".${$print_parameter}.".class.php");
                        $className = ${$print_parameter};
                    } else {
                        require_once($class_path."/pdf/".$group."/".$className.".class.php");
                    }
                } else {
                    if(!empty(${$print_parameter}) && file_exists($class_path."/pdf/".${$print_parameter}.".class.php")) {
                        require_once($class_path."/pdf/".${$print_parameter}.".class.php");
                        $className = ${$print_parameter};
                    } else {
                        require_once($class_path."/pdf/".$className.".class.php");
                    }
                }
                static::$instances[$className][static::$niveau_relance] = new $className();
            } else {
                //Ré-initialisation des positions pour démarrer une nouvelle page
                static::$instances[$className][static::$niveau_relance]->reset_default_positions();
            }
            return static::$instances[$className][static::$niveau_relance];
        }
    }
}