<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// | creator : Eric ROBERT                                                    |
// | modified : ...                                                           |
// +-------------------------------------------------+
// $Id: func_rameau_categ_integral.inc.php,v 1.19.6.1 2023/10/11 10:11:28 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// enregistrement de la notices dans les cat�gories
require_once($class_path."/thesaurus.class.php");
require_once($class_path."/categories.class.php");
global $thes, $thesaurus_defaut;

//Attention, dans le multithesaurus, le thesaurus dans lequel on importe est le thesaurus par defaut
$thes = new thesaurus($thesaurus_defaut);
$rac = $thes->num_noeud_racine;

function traite_categories_enreg($notice_retour, $categories, $thesaurus_traite = 0) {
	z3950_notice::traite_categories_enreg($notice_retour, $categories, $thesaurus_traite);
}

function traite_categories_for_form($tableau_600 = array(), $tableau_601 = array(), $tableau_602 = array(), $tableau_605 = array(), $tableau_606 = array(), $tableau_607 = array(), $tableau_608 = array()) {
	
	global $charset, $pmb_keyword_sep, $rameau;
	$info_606_a = $tableau_606["info_606_a"] ;
	$info_606_j = $tableau_606["info_606_j"] ;
	$info_606_x = $tableau_606["info_606_x"] ;
	$info_606_y = $tableau_606["info_606_y"] ;
	$info_606_z = $tableau_606["info_606_z"] ;
	
	$champ_rameau="";
	for ($a=0; $a<count($info_606_a); $a++) {
		$libelle_final="";
		$libelle_j="";
		for ($j=0; $j<count($info_606_j[$a]); $j++) {
			if (!$libelle_j) $libelle_j .= trim($info_606_j[$a][$j]) ;
				else $libelle_j .= " $pmb_keyword_sep ".trim($info_606_j[$a][$j]) ;
		}
		if (!$libelle_j) $libelle_final = trim($info_606_a[$a][0]) ; else $libelle_final = trim($info_606_a[$a][0])." $pmb_keyword_sep ".$libelle_j ;
		if (!$libelle_final) break ;
		for ($j=0; $j<count($info_606_x[$a]); $j++) {
			$libelle_final .= " $pmb_keyword_sep ".trim($info_606_x[$a][$j]) ;
		}
		for ($j=0; $j<count($info_606_y[$a]); $j++) {
			$libelle_final .= " $pmb_keyword_sep ".trim($info_606_y[$a][$j]) ;
		}
		for ($j=0; $j<count($info_606_z[$a]); $j++) {
			$libelle_final .= " $pmb_keyword_sep ".trim($info_606_z[$a][$j]) ;
		}
		if ($champ_rameau) $champ_rameau.=" $pmb_keyword_sep ";
		$champ_rameau.=$libelle_final;
	} 

	$rameau_form = serialize($tableau_606) ;
	
	// $rameau est la variable trait�e par la fonction traite_categories_from_form, 
	// $rameau est normalement POST�e, afin de pouvoir �tre trait�e en lot, donc hors 
	// formulaire, il faut l'affecter.
	$rameau = addslashes(serialize($tableau_606)) ;

	return array(
		"form" => "<input type='hidden' name='rameau' value='".htmlentities($rameau_form,ENT_QUOTES,$charset)."' />",
		"message" => "Rameau sera int&eacute;gr&eacute; sous forme d'arborescence unique \$a \$x \$y \$z deviennent TG > TS > TS > TS : <b>".htmlentities($champ_rameau,ENT_QUOTES,$charset)."</b>"
	);
}


function traite_categories_from_form() {
		
	global $rameau ;
	global $dbh;
	global $thes;
	
	$id_rech_theme = $thes->num_noeud_racine;
	
	$tableau_606 = unserialize(stripslashes($rameau)) ;
	
	$info_606_a = $tableau_606["info_606_a"] ;
	$info_606_j = $tableau_606["info_606_j"] ;
	$info_606_x = $tableau_606["info_606_x"] ;
	$info_606_y = $tableau_606["info_606_y"] ;
	$info_606_z = $tableau_606["info_606_z"] ;
	
	// ici r�cup�ration du code de admin/import/func_cnl.inc.php puis modif pour cr�ation du tableau des cat�gories, ce qui doit �tre retourn� par la fonction
	$libelle_j = "" ;
	for ($a=0; $a<count($info_606_a); $a++) {
		for ($j=0; $j<count($info_606_j[$a]); $j++) {
			if (!$libelle_j) $libelle_j .= trim($info_606_j[$a][$j]) ;
				else $libelle_j .= " ** ".trim($info_606_j[$a][$j]) ;
			}
		if (!$libelle_j) $libelle_final = trim($info_606_a[$a][0]) ;
			else $libelle_final = trim($info_606_a[$a][0])." ** ".$libelle_j ;
		if (!$libelle_final) break ; 
		$res_a = categories::searchLibelle(addslashes($libelle_final), $thes->id_thesaurus, 'fr_FR', $id_rech_theme);
		if ($res_a) {
			$categid_a = $res_a;
		} else {
			$categid_a = create_categ_z3950($id_rech_theme, $libelle_final, strip_empty_words($libelle_final));
		}
		// r�cup des sous-categ en cascade sous $a
		$categ_parent =  $categid_a ;
		for ($x=0 ; $x < count($info_606_x[$a]) ; $x++) {
			$res_x = categories::searchLibelle(addslashes(trim($info_606_x[$a][$x])), $thes->id_thesaurus, 'fr_FR', $categ_parent);
			if ($res_x) {
				$categ_parent = $res_x;
			} else {
				$categ_parent = create_categ_z3950($categ_parent, trim($info_606_x[$a][$x]), strip_empty_words($info_606_x[$a][$x]));
			}
		} // fin r�cup des $x en cascade sous l'id de la cat�gorie 606$a
		
		if ($categ_parent != $id_rech_theme) {
			$categ_retour[]['categ_id'] = $categ_parent ;
			}
		
		// r�cup TOUT EN CASCADE
		$id_rech_geo = $categ_parent ;
		// r�cup des categ g�o � loger sous la categ g�o principale
		$categ_parent =  $id_rech_geo ;
		for ($y=0 ; $y < count($info_606_y[$a]) ; $y++) {
			$res_y = categories::searchLibelle(addslashes(trim($info_606_y[$a][$y])), $thes->id_thesaurus, 'fr_FR', $categ_parent);
			if ($res_y) {
				$categ_parent = $res_y;		
			} else {
				$categ_parent = create_categ_z3950($categ_parent, trim($info_606_y[$a][$y]), strip_empty_words($info_606_y[$a][$y]));
			}
		} // fin r�cup des $y en cascade sous l'id de la cat�gorie principale th�me g�o
		
		if ($categ_parent != $id_rech_geo) {
			$categ_retour[]['categ_id'] = $categ_parent ;
			}
		
		// r�cup TOUT EN CASCADE
		$id_rech_chrono = $categ_parent ;
		// r�cup des categ chrono � loger sous la categ chrono principale
		$categ_parent =  $id_rech_chrono ;
		for ($z=0 ; $z < count($info_606_z[$a]) ; $z++) {
			$res_z = categories::searchLibelle(addslashes(trim($info_606_z[$a][$z])), $thes->id_thesaurus, 'fr_FR', $categ_parent);
			if ($res_z) {
				$categ_parent = $res_z;
			} else {
				$categ_parent = create_categ_z3950($categ_parent, trim($info_606_z[$a][$z]), strip_empty_words($info_606_z[$a][$z]));
			}
		} // fin r�cup des $z en cascade sous l'id de la cat�gorie principale th�me chrono
		
		if ($categ_parent != $id_rech_chrono) {
			$categ_retour[]['categ_id'] = $categ_parent ;
		}
	}
// DEBUG echo "<pre>"; print_r($categ_retour) ; echo "</pre>"; exit ;
return $categ_retour ;
}


function create_categ_z3950($num_parent, $libelle, $index) {
    return z3950_notice::create_categ_z3950($num_parent, $libelle, $index);
}	
