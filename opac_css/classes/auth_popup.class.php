<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: auth_popup.class.php,v 1.9 2022/08/01 13:45:18 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
// authentification via "popup" à l'OPAC

global $base_path, $include_path, $msg, $charset;
global $empty_pwd, $ext_auth;
global $action;
global $callback_func;
global $callback_url, $new_tab;
global $popup_header;
global $opac_websubscribe_show, $opac_password_forgotten_show;

require_once $include_path."/empr.inc.php";
require_once $include_path."/empr_func.inc.php";


class auth_popup {
	
	protected $callback_func = "";
	protected $callback_url = "";
	protected $new_tab = false;
	protected $handle_ext_auth = false;


	public function __construct()
	{
        global $base_path;
	    if ( file_exists($base_path.'/includes/ext_auth.inc.php') ) {
	        $this->handle_ext_auth = true;
	    }
	}
	
	
	public function process()
	{
		global $base_path,$msg;
		global $empty_pwd, $ext_auth;
		
		global $action;
		global $callback_func;
		global $callback_url,$new_tab;
		
		global $popup_header;
		
		$this->callback_func = $callback_func;
		$this->callback_url = $callback_url;
		$this->new_tab = $new_tab;
		
		switch($action){
			case 'check_auth' :
				//On tente la connexion
				// si paramétrage authentification particulière
				$empty_pwd=true;
				$ext_auth=false;
				if ( $this->handle_ext_auth ) {
				    require_once $base_path.'/includes/ext_auth.inc.php'; 
				}
				$log_ok = connexion_empr();
				print $popup_header;
				if($log_ok){
					//réussie, on poursuit le tout...		
					$this->success_callback();
				}else{
					print $this->get_form($msg['auth_failed']);
				}
				break;
			case 'get_form' :
			default :
				print $popup_header;
				if(!$_SESSION['user_code']){
					print $this->get_form();
				}else{
					$this->success_callback();
				}
				break;
		}
	}
	
	
	protected function success_callback()
	{
		$html = "
		<script type='text/javascript'>";
		if($this->callback_func){
			$html.= "window.parent.".$this->callback_func."('".$_SESSION['id_empr_session']."');";
		}else if ($this->callback_url){
			if($this->new_tab){
				$html.= "window.open('".$this->callback_url."');";
			}else{
				$html.= "window.parent.document.location='".$this->callback_url."';";
			}
		}
		$html.="
			var frame = window.parent.document.getElementById('auth_popup');
			frame.parentNode.removeChild(frame);
		</script>";	
		print $html;	
	}
	
	
    protected function get_form($message="")
    {
        global $base_path, $include_path, $charset;
		global $opac_websubscribe_show,$opac_password_forgotten_show,$msg;
		
		if(!$message){
			$message = $msg["need_auth"];
		}
		$template_path = $include_path.'/templates/auth_popup.tpl.html';
		if( file_exists($include_path.'/templates/auth_popup_subst.tpl.html') ) {
		    $template_path = $include_path.'/templates/auth_popup_subst.tpl.html';
		}
		try {
		    $H2o = H2o_collection::get_instance($template_path);
		    $form = $H2o->render([
		        'message' => $message,
		        'callback_func' => $this->callback_func,
		        'callback_url' => $this->callback_url,
		        'new_tab' => $this->new_tab,
		    ]);
		} catch(Exception $e) {
		    $form = '<blockquote id="askmdp">
		    <!-- '.$e->getMessage().' -->
		    <div class="error_on_template" title="' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '">'
		    .$msg["error_template"].
		    '</div>
            </blockquote>';
		}

		return $form;
	}
}


