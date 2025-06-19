<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: recouvr_reader.inc.php,v 1.15 2021/11/04 08:28:11 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $PMBuserid, $PMBusername, $id_empr, $act_line;
global $recouvr_id, $libelle, $montant, $recouvr_ligne;

//Affichage des recouvrements pour un lecteur

require_once($class_path."/emprunteur.class.php");
require_once($class_path."/comptes.class.php");
require_once($class_path."/mono_display.class.php");
require_once($class_path."/serial_display.class.php");

print "<script src='./javascript/dynamic_element.js' type='text/javascript'></script>";

function get_empr_informations($id_empr) {
	$empr=new emprunteur($id_empr,'', FALSE, 0);
	return "
	<div class='row'>
		<div class='colonne3'>
			<div class='row'>".$empr->adr1."</div>
			<div class='row'>".$empr->adr2."</div>
			<div class='row'>".$empr->cp." ".$empr->ville."</div>
			<div class='row'>".$empr->mail."</div>
		</div>
		<div class='colonne_suite'>
			<div class='row'>".$empr->tel1."</div>
			<div class='row'>".$empr->tel2."</div>
		</div>
	</div>";
}

function show_lines_list() {
	global $id_empr;
	
	print "<h3><a href='./circ.php?categ=pret&id_empr=$id_empr'>".emprunteur::get_name($id_empr, 1)."</a></h3>";
	print get_empr_informations($id_empr);
	
	//Liste des recouvrements
	print list_recouvr_reader_ui::get_instance(array('id_empr' => $id_empr))->get_display_list();
}

function show_recouvr_form($recouvr_id) {
	global $msg, $charset;
	global $id_empr;
	
	$libelle = '';
	$montant = '';
	if ($recouvr_id) {
		$requete = "select libelle,montant from recouvrements where recouvr_id=$recouvr_id";
		$resultat = pmb_mysql_query($requete);
		if (pmb_mysql_num_rows($resultat)) {
			$r = pmb_mysql_fetch_object($resultat);
			$libelle = $r->libelle;
			$montant = $r->montant;
		}
	}
	print "
	<form class='form-circ' name='recouvr_reader_form' method='post' action='./circ.php?categ=relance&sub=recouvr&act=recouvr_reader&id_empr=$id_empr'>
		<h3><a href='./circ.php?categ=pret&id_empr=$id_empr'>".emprunteur::get_name($id_empr, 1)."</a></h3>
		<div class='form-contenu'>
			".get_empr_informations($id_empr)."
			<input type='hidden' name='act_line' value=''/>
			<input type='hidden' name='recouvr_id' value=''/>
	        <div class='row'></div>
	    	<div class='row'>
	    	    <div class='row'>
	    	        <label for='libelle'>".$msg["relance_recouvrement_libelle"]."</label>
	            </div>
	            <div class='row'>
	        		<textarea rows='5' cols='30' wrap='virtual' name='libelle' id='libelle'>".htmlentities($libelle, ENT_QUOTES, $charset)."</textarea>
	        	</div>
	    	    <div class='row'>
	        		<label for='montant'>".$msg["relance_recouvrement_montant"]."</label>
	        	</div>
	        	<div class='row'>
	        		<input name='montant' value='".$montant."' class='saisie-10em' id='montant'/>
	        	</div>
	        </div>
	        <div class='row'></div>
		</div>
		<!--boutons -->
		<div class='row'>
			<input type='submit' value='".$msg["77"]."' class='bouton' onClick=\"this.form.act_line.value='rec_update_line'; this.form.recouvr_id.value='".$recouvr_id."'\"/>
			<input type='button' value='".$msg["76"]."' class='bouton' onClick=\"this.form.submit();\"/>
		</div>
	</form>";
	
}

switch ($act_line) {
	case "update_line":
		show_recouvr_form($recouvr_id);
		break;
	case "rec_update_line":
		if ($recouvr_id) {
			$requete="update recouvrements set libelle='".$libelle."', montant='".$montant."' where recouvr_id=$recouvr_id";
			pmb_mysql_query($requete);
		} else {
			$requete="insert into recouvrements (empr_id, date_rec, libelle, montant) values($id_empr,now(),'".$libelle."','".$montant."')";
			pmb_mysql_query($requete);
		}
		show_lines_list();
		break;
	case "del_line":
		for ($i=0; $i<count($recouvr_ligne); $i++) {
			$requete="delete from recouvrements where recouvr_id=".$recouvr_ligne[$i];
			pmb_mysql_query($requete);
		}
		//Vérification qu'il reste des lignes
		$requete="select count(*) from recouvrements where empr_id='$id_empr'";
		$resultat=pmb_mysql_query($requete);
		if (pmb_mysql_result($resultat,0,0))
			show_lines_list();
		else
			print "<script>document.location='./circ.php?categ=relance&sub=recouvr&act=recouvr_liste';</script>";
		break;
	case "solde":
		$requete="select sum(montant) from recouvrements where empr_id='$id_empr'";
		$resultat=pmb_mysql_query($requete);
		$solde=@pmb_mysql_result($resultat,0,0);
		if ($solde) {
			//Crédit du compte lecteur
			$compte_id=comptes::get_compte_id_from_empr($id_empr,2);
			if ($compte_id) {
				$cpte=new comptes($compte_id);
				$id_transaction=$cpte->record_transaction("",$solde,1,$msg["relance_recouvrement_solde_recouvr"],0);
				if ($id_transaction) {
					$cpte->validate_transaction($id_transaction);
					
					//Débit du compte bibliothèque
					$requete="insert into transactions (compte_id,user_id,user_name,machine,date_enrgt,date_prevue,date_effective,montant,sens,realisee,commentaire,encaissement) 
					values(
						0,$PMBuserid,'".$PMBusername."','".$_SERVER["REMOTE_ADDR"]."',now(),now(),now(),
						$solde,-1,1,'".sprintf($msg["relance_recouvrement_solde_recouvr_bibli"],$id_empr)."',0)";
				}
			}
		}
		pmb_mysql_query("delete from recouvrements where empr_id='".$id_empr."'");
		print "<script>document.location='./circ.php?categ=relance&sub=recouvr&act=recouvr_liste';</script>";
		break;
	default:
		show_lines_list();
		break;
}
print "
<script type='text/javascript'>parse_dynamic_elts();</script>
";

?>