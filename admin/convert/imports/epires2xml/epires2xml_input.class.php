<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: epires2xml_input.class.php,v 1.1.12.1 2023/03/22 09:19:28 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once ($base_path."/admin/convert/convert_input.class.php");

class epires2xml_input extends convert_input {
	
	public function _get_n_notices_($fi,$file_in,$input_params,$origine) {
		global $base_path,$charset;
		//pmb_mysql_query("delete from import_marc");
		
		$first=true;
		$stop=false;
		$content="";
		$index=array();
		$n=1;
		//Lecture du fichier d'entr�e
		while (!$stop) {
			
			//Recherche de +++
			$pos_deb=strpos($content,"+++");
			while (($pos_deb===false)&&(!feof($fi))) {
				$tmp_content=fread($fi,4096);
				if($_SESSION["encodage_fic_source"]){//On a forc� l'encodage
					if(($charset == "utf-8") && ($_SESSION["encodage_fic_source"] == "iso8859")){
						$tmp_content=utf8_encode($tmp_content);
					}elseif(($charset == "iso-8859-1" && ($_SESSION["encodage_fic_source"] == "utf8"))){
						$tmp_content=utf8_decode($tmp_content);
					}
				}
				$content.=$tmp_content;
				$content=str_replace("!\r\n ","",$content);
				$content=str_replace("!\r ","",$content);
				$content=str_replace("!\n ","",$content);
				$pos_deb=strpos($content,"+++");
			}
			//D�but accroch�
			if ($pos_deb!==false) {
				//Notice = d�but jusqu'au +++
				$notice=substr($content,0,$pos_deb);
				$content=substr($content,$pos_deb+3);
			} else {
				//Pas de notice suivante, c'est la fin du fichier
				$notice=$content;
				$stop=true;
			}
			
			//Si c'est la premi�re notice, c'est la ligne d'intitul�s !!
			if ($first) {
				$cols=explode(";;",$notice);
				$fcols=fopen("$base_path/temp/".$origine."_cols.txt","w+");
				if ($fcols) {
					fwrite($fcols,serialize($cols));
					fclose($fcols);
				}
				$notice="";
				$first=false;
			} 
			if ($notice) {
				$requete="insert into import_marc (no_notice, notice, origine) values($n,'".addslashes($notice)."','$origine')";
				pmb_mysql_query($requete);
				$n++;
				$t=array();
				$t["POS"]=$n;
				$t["LENGHT"]=1;
				$index[]=$t;
			}
		}
		return $index;
	}
}

?>