<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mailtpl.tpl.php,v 1.24 2021/03/08 16:58:52 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $mailtpl_attachments_form_tpl, $msg, $mailtpl_form_resavars, $mailtpl_form_selvars, $mailtpl_form_sel_img, $mailtpl_content_form;
global $pdflettreresa_resa_prolong_email;

$mailtpl_form_resavars = "
	<select name='resavars_id' id='resavars_id'>
		<option value=!!new_date!!>".$msg["scan_request_date"]."</option>
		<option value=!!expl_title!!>".$msg["233"]."</option>
		<option value=!!record_permalink!!>".$msg["cms_editorial_form_permalink"]."</option>
	</select>
	<input type='button' class='bouton' value=\" ".$msg["admin_mailtpl_form_selvars_insert"]." \" onClick=\"insert_vars(document.getElementById('resavars_id'), document.getElementById('f_message')); return false; \" />
		";

$mailtpl_form_selvars="
<select name='selvars_id' id='selvars_id'>
    !!options_selvars!!
</select>
<input type='button' class='bouton' value=\" ".$msg["admin_mailtpl_form_selvars_insert"]." \" onClick=\"insert_vars(document.getElementById('selvars_id'), document.getElementById('f_message')); return false; \" />
<script type='text/javascript'>

	function insert_vars(theselector,dest){	
		var selvars='';
		for (var i=0 ; i< theselector.options.length ; i++){
			if (theselector.options[i].selected){
				selvars=theselector.options[i].value ;
				break;
			}
		}
		if(!selvars) return ;

		if(typeof(tinyMCE)== 'undefined'){			
			var start = dest.selectionStart;		   
		    var start_text = dest.value.substring(0, start);
		    var end_text = dest.value.substring(start);
		    dest.value = start_text+selvars+end_text;
		}else{
			tinyMCE_execCommand('mceInsertContent',false,selvars);
		}
	}
	
	
</script>
";

$mailtpl_form_sel_img="
!!select_file!!
<input type='button' class='bouton' value=\" ".$msg["admin_mailtpl_form_sel_img_insert"]." \" onClick=\"insert_img(document.getElementById('select_file'), document.getElementById('f_message')); return false; \" />
<script type='text/javascript'>
	function insert_img(theselector,dest){	
		var href='';
		for (var i=0 ; i< theselector.options.length ; i++){
			if (theselector.options[i].selected){
				href=theselector.options[i].value ;
				break;
			}
		}
		if(!href) return ;
		
		var sel_img='<img src=\"'+href+'\">';
		if(typeof(tinyMCE)== 'undefined'){			
			var start = dest.selectionStart;		   
		    var start_text = dest.value.substring(0, start);
		    var end_text = dest.value.substring(start);
		    dest.value = start_text+sel_img+end_text;
		}else{
			tinyMCE_execCommand('InsertHTML',false,sel_img);
		}
	}

</script>
";
$mailtpl_content_form="	
<script type='text/javascript'>
	function test_form(form){
		if((form.name.value.length == 0) )		{
			alert('".$msg["admin_mailtpl_name_error"]."');
			return false;
		}
        if (typeof(tinyMCE) != 'undefined') {
            if (tinyMCE_getInstance('f_message')) {
                tinyMCE_execCommand('mceToggleEditor',true,'f_message');
                tinyMCE_execCommand('mceRemoveControl',true,'f_message');
            }
        }
		return true;
	}
</script>
<div class='row'>
	<label class='etiquette' for='name'>".$msg['admin_mailtpl_form_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='name' id='name' value='!!name!!' />
</div>
<div class='row'>
	<label class='etiquette' for='f_objet_mail'>".$msg['empr_mailing_form_obj_mail']."</label>
	<div class='row'>
		<input type='text' class='saisie-80em' id='f_objet_mail'  name='f_objet_mail' value='!!objet!!' />
	</div>
</div>
<div class='row'>
	<label class='etiquette' for='f_message'>".$msg["admin_mailtpl_form_tpl"]."</label>
	<div class='row'>
		<textarea id='f_message' name='f_message' cols='100' rows='20'>!!tpl!!</textarea>
	</div>
</div>
<div class='row'>
	<label class='etiquette'>".$msg["admin_mailtpl_form_selvars"]."</label>
	<div class='row'>
		!!selvars!!
	</div>
</div>";
if($pdflettreresa_resa_prolong_email){
	$mailtpl_content_form.="
	<div class='row'>
		<label class='etiquette'>".$msg["admin_mailtpl_form_resa_prolong_selvars"]."</label>
		<div class='row'>
			!!resavars!!
		</div>
	</div>";
}
$mailtpl_content_form.="!!sel_img!!
<div class='row'>
	<input type='hidden' id='auto_id_list' name='auto_id_list' value='!!id_check_list!!' >
	<label class='etiquette' for='form_comment'>".$msg['procs_autorisations']."</label>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,1);'>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,0);'>
</div>
<div class='row'>
	!!autorisations_users!!
</div>
<input type='hidden' name='id_mailtpl' id='id_mailtpl' value='!!id_mailtpl!!'/>
";
		
$mailtpl_attachments_form_tpl="
<div class='row'>
	<label class='etiquette' >".$msg["empr_mailing_form_message_piece_jointe"]." (".ini_get('upload_max_filesize').")</label>
</div>
<div id='add_pieces'>
	<input type='hidden' id='nb_piece' value='1'/>
	<div class='row' id='piece_1'>
		<input type='file' id='pieces_jointes_mailing_1' name='pieces_jointes_mailing[]' class='saisie-80em' size='60'/><input class='bouton' type='button' value='X' onclick='document.getElementById(\"pieces_jointes_mailing_1\").value=\"\"'/>
		<input class='bouton' type='button' value='+' onClick=\"add_pieces_jointes_mailing();\"/>
	</div>
</div>
<script type='text/javascript'>
	function add_pieces_jointes_mailing(){
		var nb_piece=document.getElementById('nb_piece').value;
		nb_piece= (nb_piece*1) + 1;
		
		var template = document.getElementById('add_pieces');
		
		var divpiece=document.createElement('div');
   		divpiece.className='row';
   		divpiece.setAttribute('id','piece_'+nb_piece);
   		template.appendChild(divpiece);
   		document.getElementById('nb_piece').value=nb_piece;
   		
   		var inputfile=document.createElement('input');
   		inputfile.setAttribute('type','file');
   		inputfile.setAttribute('name','pieces_jointes_mailing[]');
   		inputfile.setAttribute('id','pieces_jointes_mailing_'+nb_piece);
   		inputfile.setAttribute('class','saisie-80em');
   		inputfile.setAttribute('size','60');
   		divpiece.appendChild(inputfile);
   		
   		var inputfile=document.createElement('input');
   		inputfile.setAttribute('type','button');
   		inputfile.setAttribute('value','X');
   		inputfile.setAttribute('onclick','del_pieces_jointes_mailing('+nb_piece+');');
   		inputfile.setAttribute('class','bouton');
   		divpiece.appendChild(inputfile);
	}
	
	function del_pieces_jointes_mailing(nb_piece){
		var parent = document.getElementById('add_pieces');
		var child = document.getElementById('piece_'+nb_piece);
		parent.removeChild(child);
		
		var nb_piece=document.getElementById('nb_piece').value;
		nb_piece= (nb_piece*1) - 1;
		document.getElementById('nb_piece').value=nb_piece;
		
	}
</script>";
