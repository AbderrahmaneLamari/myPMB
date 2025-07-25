<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: external.inc.php,v 1.26 2022/10/28 08:18:43 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $search;
global $es;
global $pmb_logs_activate, $ex_env, $user_query;

require_once($class_path."/facettes_external.class.php");

//Réinitialisation des facettes externes
facettes_external::destroy_global_env();

$nb_result_external=0;
$flag=false;

//Recherche multi-critère classique
if ($_SESSION["ext_type"]=="multi") {
    //Vérification des champs vides
    //Y-a-t-il des champs ?
    if (count($search)==0) {
        $search_error_message=$msg["extended_use_at_least_one"];
        $flag=true;
    } else {
        //Vérification des champs vides
        for ($i=0; $i<count($search); $i++) {
            $field_="field_".$i."_".$search[$i];
            global ${$field_};
            $field=${$field_};
            $s=explode("_",$search[$i]);
            if ($s[0]=="f") {
                $champ=$es->fixedfields[$s[1]]["TITLE"];
            } elseif ($s[0]=="s") {
                $champ=$es->specialfields[$s[1]]["TITLE"];
            } else {
                $champ=$es->pp->t_fields[$s[1]]["TITRE"];
            }
            if ((string)$field[0]=="") {
                $search_error_message=sprintf($msg["extended_empty_field"],$champ);
                $flag=true;
                break;
            }
        }
    }
    if (!$flag) {
        $es->remove_forbidden_fields();
        $table=$es->make_search();
        $requete="select count(1) from $table";
        $resultat=pmb_mysql_query($requete);
        $nb_result_external=@pmb_mysql_result($resultat,0,0);
        if ($nb_result_external) {
            print pmb_bidi("<strong>".$es->make_human_query()."</strong> ".$nb_result_external." $msg[results] ");
            print $es->make_hidden_search_form('./index.php?lvl=more_results&mode=external','external_extended_form',"",false);
            print "<input type='hidden' name='count' value='".$nb_result_external."'>";
            print "</form>"; //Fermeture formulaire make_hidden_search_form
            print "<a href=\"#\" onclick=\"document.external_extended_form.submit(); return false;\">$msg[suite]&nbsp;<img src='".get_url_icon('search.gif')."' style='border:0px' align='absmiddle'/></a><br /><br />";
        }
    }
} else {
    //Recherche "simple"
    //Pour chaque case cochée,  on construit et on lance la recherche multicritère correspondante
    global $external_sources, $source, $selected_sources;
    $selected_sources = implode(',', $source);
    $look_array=array("TITLE","AUTHOR","PUBLISHER","COLLECTION","SUBCOLLECTION","CATEGORY","INDEXINT","KEYWORDS","ABSTRACT","ALL");
    $look_id=array(6,8,3,4,5,1,2,12,13,7);
    $look_msg=array("titles","authors","publishers","collections","subcollections","categories","indexint","keywords","abstract","tous");
    
    if (count($source)==0) {
        $flag=true;
        $search_error_message=$msg["connecteurs_no_source"];
    }
    
    if (!$flag) {
        if(empty($search)) {
            $search=array();
        }
        $search[0]="s_2";
        global $op_0_s_2;
        $op_0_s_2="EQ";
        global $field_0_s_2;
        $field_0_s_2=$source;
        
        for ($i=0; $i<count($look_array); $i++) {
            $look="look_".$look_array[$i];
            if (isset(${$look})) {
                $ex_env[$look]=${$look};
            }
        }
        $ex_env["look_FIRSTACCESS"]=1;
        $ex_env["source"]=$source;
        $ex_env["user_query"]=stripslashes($user_query);
        $external_env=serialize($ex_env);
        
        $nb_result_external =0;
        for ($k=0; $k<count($look_array); $k++) {
            $look="look_".$look_array[$k];
            global ${$look};
            if (${$look}) {
                //Construction de la multi-critère
                $search[1]="f_".$look_id[$k];
                
                //opérateur
                $op_="BOOLEAN";
                $op="op_1_".$search[1];
                global ${$op};
                ${$op}=$op_;
                
                //contenu de la recherche
                $field="field_1_".$search[1];
                $field_=array();
                $field_[0]=stripslashes($user_query);
                global ${$field};
                ${$field}=$field_;
                
                //opérateur inter-champ
                $inter="inter_1_".$search[1];
                global ${$inter};
                ${$inter}="and";
                
                //variables auxiliaires
                $fieldvar_="fieldvar_1_".$search[1];
                global ${$fieldvar_};
                ${$fieldvar_}="";
                $fieldvar=${$fieldvar_};
                
                $es=new search("search_simple_fields_unimarc");
                $table=$es->make_search("f_".$look_id[$k]);
                
                $requete="select count(1) from $table";
                $resultat=pmb_mysql_query($requete);
                $nb_result_partial=@pmb_mysql_result($resultat,0,0);
                if ($nb_result_partial) {
                    $nb_result_external+=$nb_result_partial;
                    print "<form name='form_".$look_id[$k]."' action='./index.php?lvl=more_results&mode=external' method='post'>\n";
                    print "<input type='hidden' name='external_env' value='".htmlentities($external_env,ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='search[0]' value='".htmlentities("s_2",ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='op_0_s_2' value='".htmlentities("EQ",ENT_QUOTES,$charset)."'/>\n";
                    for ($j=0; $j<count($source); $j++) {
                        print "<input type='hidden' name='field_0_s_2[".$j."]' value='".htmlentities($source[$j],ENT_QUOTES,$charset)."'/>\n";
                    }
                    print "
					<input type='hidden' name='search[1]' value='".htmlentities($search[1],ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='".$op."' value='".htmlentities($op_,ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='".$field."[0]' value='".htmlentities($field_[0],ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='".$inter."' value='".htmlentities("and",ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='count' value='".$nb_result_external."'>";
                    print pmb_bidi("<strong>".$msg[$look_msg[$k]]."</strong> ".$nb_result_partial." $msg[results] ");
                    print "<a href=\"javascript:document.form_".$look_id[$k].".submit()\">$msg[suite]&nbsp;<img src='".get_url_icon('search.gif')."' style='border:0px' align='absmiddle'/></a><br />";
                    print "</form>\n";
                }
            }
        }
        
        //Enregistrement des stats
        if($pmb_logs_activate){
            global $nb_results_tab;
            $nb_results_tab['external'] = $nb_result_external;
        }
    }
}

?>