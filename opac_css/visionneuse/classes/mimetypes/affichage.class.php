<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: affichage.class.php,v 1.15 2022/10/10 12:01:11 tsamson Exp $

class affichage {
	public $doc;				//le document num�rique � afficher
	public $params;			//param�tres �ventuels
	public $driver;			//driver de la visionneuse
	public $allowedFunctions = array();
	public $message;
	
	public function __construct($doc) {
    	$this->doc = $doc; 
    	$this->driver = $doc->driver;
    	$this->params = $doc->params;
    }
    
    public function fetchDisplay(){
     	//le titre
    	$this->toDisplay["titre"] = $this->doc->titre;
    	//le pdf
    	//$this->toDisplay["doc"] = "<iframe src='".$visionneuse_path."/pdf.php?id=".$this->doc->id."' width='".$this->params["maxX"]."' height='".$this->params["maxY"]."'></iframe>";
    	$url_doc = $this->driver->getDocumentUrl($this->doc->id);
    	if (strpos($this->doc->path, "http") === 0) {
    	    $url_doc = $this->doc->path;
    	}
    	$this->toDisplay["doc"] = "<iframe name='docnum' id='docnum' src='".$url_doc."' width='".$this->driver->getParam("maxX")."' height='".$this->driver->getParam("maxY")."'></iframe>";
		$this->toDisplay["doc"] .= 	"
		<script type='text/javascript'>
			window.onload = checkSize;
			function checkSize(){
				var iframe= document.getElementById('docnum');
				if (isNaN(iframe.width) || iframe.width/getFrameWidth() <= 0.9 || iframe.width/getFrameWidth() >= 1){
					iframe.width = '90%';
					iframe.height = ((getFrameHeight()-40-80)*0.9)+'px';
				}				
			}
		</script>";
		//la description
		$this->toDisplay["desc"] = $this->doc->desc;
		//toPost
		return $this->toDisplay;
    }
    
	
    //ex�cution de l'appel AJAX
    public function exec($method){
    	if($this->checkAllowedFunction($method)){
    		$this->{$method}();
    	}else{
    		print "forbidden";
    	}
    	return false;
    }

	public function checkAllowedFunction($method){
    	return in_array($method,$this->allowedFunction);
	}
	
	public function setMessage($message){
		$this->message = $message;
	}
}
?>