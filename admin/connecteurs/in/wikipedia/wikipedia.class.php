<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: wikipedia.class.php,v 1.15 2022/02/16 13:33:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/connecteurs.class.php");
require_once($class_path."/curl.class.php");

class wikipedia extends connector {
	
    public function __construct($connector_path="") {
    	parent::__construct($connector_path);
    }
    
    public function get_id() {
    	return "wikipedia";
    }
    
    //Est-ce un entrepot ?
	public function is_repository() {
		return 2;
	}
    
    public function make_serialized_properties() {
    	global $accesskey, $secretkey;
		//Mise en forme des param�tres � partir de variables globales (mettre le r�sultat dans $this->parameters)
		$keys = array();
    	
    	$keys['accesskey']=$accesskey;
		$keys['secretkey']=$secretkey;
		$this->parameters = serialize($keys);
	}

	public function enrichment_is_allow(){
		return true;
	}
	
	public function getEnrichmentHeader(){
		$header= array();
		$header[]= "<!-- Script d'enrichissement pour wikipedia-->";
		$header[]= "<script type='text/javascript'>
			function load_wiki_bio(notice_id,type,label){
				var	wiki= new http_request();
				var content = document.getElementById('div_'+type+notice_id);
				content.innerHTML = '';
				var patience= document.createElement('img');
				patience.setAttribute('src','".get_url_icon('patience.gif')."');
				patience.setAttribute('align','middle');
				patience.setAttribute('id','patience'+notice_id);
				content.appendChild(patience);
				wiki.request('./ajax.php?module=ajax&categ=enrichment&action=enrichment&type='+type+'&id='+notice_id,true,'&enrich_params[label]='+label,true,gotEnrichment);
			}
		</script>";
		return $header;
	}
	
	public function getTypeOfEnrichment($source_id){
		$type = array();
		$type['type'] = array(
			array( 
				'code' => "wiki",
				'label' => $this->msg["wikipedia_label"]
			),
			"bio"
		);		
		$type['source_id'] = $source_id;
		return $type;
	}
	
	public function getEnrichment($notice_id,$source_id,$type="",$enrich_params=array()){
		$enrichment= array();
		//on renvoi ce qui est demand�... si on demande rien, on renvoi tout..
		switch ($type){
			case "bio" :
				$enrichment['bio']['content'] = $this->get_author_page($notice_id,$enrich_params);	
				break;
			case "wiki" :
			default :
				$enrichment['wiki']['content'] = $this->noticeInfos($notice_id);
				break;
		}		
		$enrichment['source_label']=$this->msg['wikipedia_enrichment_source'];
		return $enrichment;
	}
	
	public function get_author_page($notice_id,$enrich_params){
		global $lang;
		global $charset;
		
		if(isset($enrich_params['label']) && $enrich_params['label']!=""){
			$author = $enrich_params['label'];
		}else{
			//on va chercher l'auteur principal...
			$query = "select responsability_author from responsability where responsability_notice =".$notice_id." and responsability_type=0";
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)){
				$author_id = pmb_mysql_result($result,0,0);
				$author_class = new auteur($author_id);
				$author =  $author_class->get_isbd();
			}
		}
		$curl = new Curl();
		//on fait un premier appel pour regarder si on a quelque chose chez Wikip�dia...
		$url = "http://".substr($lang,0,2).".wikipedia.org/w/api.php?format=json&action=opensearch&search=".rawurlencode($author)."&limit=20";
		$json = $curl->get($url);
		$search = json_decode($json);
		if(count($search[1])==1 || $enrich_params['label']!=""){
			$url = "http://".substr($lang,0,2).".wikipedia.org/w/api.php?format=json&action=query&titles=".rawurlencode($search[1][0])."&prop=revisions&rvprop=content&rvparse=1";
			$json = $curl->get($url);
			$response = json_decode($json);
			$html_to_return="";
			foreach($response->query->pages as $page){
				foreach($page->revisions[0] as $rev){
					$html_to_return .= utf8_decode(encoding_normalize::clean_cp1252($rev, 'utf-8'));
				}
			}
			$html_to_return = str_replace("href=\"/","target='_blank' href=\"http://".substr($lang,0,2).".wikipedia.org/",$html_to_return);		
			$dom = new domDocument();
			$dom->loadHTML($html_to_return);
			$spans = $dom->getElementsByTagName("span");
			for($i=0; $i<$spans->length ; $i++){
				for($j=0 ; $j<$spans->item($i)->attributes->length ; $j++){
					if($spans->item($i)->attributes->item($j)->name == "class" && $spans->item($i)->attributes->item($j)->nodeValue == "editsection"){
						$spans->item($i)->parentNode->removeChild($spans->item($i));
					}
				}
			}
			$html_to_return = $dom->saveHTML();
		}else if(count($search[1])>1){
			//si plus d'un r�sultat on propose le choix...
			$html_to_return = "
			<div id='wiki_bio_".$notice_id."'>
				<table>";
			for($i=0 ; $i<count($search[1]) ; $i++){
				if($i%4 == 0){
					$html_to_return.= "
					<tr>";
				}
				$html_to_return.="
						<td>
							<a href='#' onclick='load_wiki_bio(\"".$notice_id."\",\"bio\",\"".htmlentities(utf8_decode($search[1][$i]),ENT_QUOTES,$charset)."\");return false;' >".utf8_decode($search[1][$i])."</a>
						</td>";
				if($i%4 == 3){
					$html_to_return.= "
					</tr>";
				}
			}
			$html_to_return.= "
				</table>
			</div>";
			
			
		}else{
			$html_to_return = $this->msg['wikipedia_no_informations'];
		}

		return $html_to_return; 
	}
	
	public function noticeInfos($notice_id){
		global $lang;
		
		$rqt = "select tit1 from notices where notice_id='$notice_id'";
		$res =pmb_mysql_query($rqt);
		if(pmb_mysql_num_rows($res)){
			$titre = pmb_mysql_result($res,0,0);
			$curl = new Curl();
			//on fait un premier appel pour regarder si on a quelque chose chez Wikip�dia...
			$url = "http://".substr($lang,0,2).".wikipedia.org/w/api.php?format=json&action=opensearch&search=".rawurlencode($titre);
			$json = $curl->get($url);
			$search = json_decode($json);
			if(count($search[1])>0){
				$url = "http://".substr($lang,0,2).".wikipedia.org/w/api.php?format=json&action=query&titles=".rawurlencode($search[1][0])."&prop=revisions&rvprop=content&rvparse=1";
				$json = $curl->get($url);
				$response = json_decode($json);
				$html_to_return="";
				foreach($response->query->pages as $page){
					foreach($page->revisions[0] as $rev){
						$html_to_return .= utf8_decode(encoding_normalize::clean_cp1252($rev, 'utf-8'));
					}
				}
				$html_to_return = str_replace("href=\"/","target='_blank' href=\"http://".substr($lang,0,2).".wikipedia.org/",$html_to_return);		
				$dom = new domDocument();
				$dom->loadHTML($html_to_return);
				$spans = $dom->getElementsByTagName("span");
				for($i=0; $i<$spans->length ; $i++){
					for($j=0 ; $j<$spans->item($i)->attributes->length ; $j++){
						if($spans->item($i)->attributes->item($j)->name == "class" && $spans->item($i)->attributes->item($j)->nodeValue == "editsection"){
							$spans->item($i)->parentNode->removeChild($spans->item($i));
						}
					}
				}
				$html_to_return = $dom->saveHTML();
			}else{
				$html_to_return = $this->msg['wikipedia_no_informations'];
			}
		}
		return $html_to_return; 
	}
}
?>