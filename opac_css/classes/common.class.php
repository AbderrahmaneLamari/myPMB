<?php
// +-------------------------------------------------+
// © 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: common.class.php,v 1.1.2.4 2021/10/06 12:32:34 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class common {

	public static function get_metadata() {
		global $msg, $charset, $lvl, $opac_biblio_name;
		global $opac_meta_author, $opac_meta_keywords, $opac_meta_description;
		
		$metadata = "
			<meta charset=\"".$charset."\" />
			<meta name=\"author\" content=\"".($opac_meta_author?htmlentities($opac_meta_author,ENT_QUOTES,$charset):"PMB Group")."\" />
					
			<meta name=\"keywords\" content=\"".($opac_meta_keywords?htmlentities($opac_meta_keywords,ENT_QUOTES,$charset):$msg['opac_keywords'])."\" />
			<meta name=\"description\" content=\"".($opac_meta_description?htmlentities($opac_meta_description,ENT_QUOTES,$charset):$msg['opac_title']." $opac_biblio_name.")."\" />";
		
		switch ($lvl) {
			case 'contribution_area':
				$metadata .= "<meta name='robots' content='all' />";
				break;
			case 'show_cart':
				$metadata .= "<meta name='robots' content='noindex, nofollow' />";
				break;
			default :
				$metadata .= "<meta name='robots' content='all' />";
				break;
		}
		
		$metadata.="
			<!--IE et son enfer de compatibilité-->
			<meta http-equiv='X-UA-Compatible' content='IE=Edge' />
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\" />";
		return $metadata;
	}
	
	public static function get_html_title() {
		global $msg, $lvl, $opac_biblio_name;
		
		switch ($lvl) {
			case 'contribution_area':
				return $msg['empr_menu_contribution_area'].' '.$opac_biblio_name;
			case 'show_cart':
				return $msg['opac_title'].' '.$opac_biblio_name;
			default :
				return $msg['opac_title'].' '.$opac_biblio_name;
		}
	}
	
	public static function get_dojo_configuration() {
		global $javascript_path, $lang;
		
		return "
		<link rel='stylesheet' type='text/css' href='".$javascript_path."/dojo/dijit/themes/tundra/tundra.css' />
		<script type='text/javascript'>
			var dojoConfig = {
				parseOnLoad: true,
				locale: '".str_replace("_","-",strtolower($lang))."',
				isDebug: false,
				usePlainJson: true,
				packages: [{
						name: 'pmbBase',
						location:'../../../..'
					},{
						name: 'd3',
						location:'../../d3'
					}],
				deps: ['apps/pmb/MessagesStore', 'dgrowl/dGrowl', 'dojo/ready', 'apps/pmb/ImagesStore'],
				callback:function(MessagesStore, dGrowl, ready, ImagesStore){
					window.pmbDojo = {};
					pmbDojo.messages = new MessagesStore({url:'./ajax.php?module=ajax&categ=messages', directInit:false});
					pmbDojo.images = new ImagesStore({url:'./ajax.php?module=ajax&categ=images', directInit:false});
					ready(function(){
						new dGrowl({'channels':[{'name':'info','pos':2},{'name':'error', 'pos':1}]});
					});
						
				},
			};
		</script>
		<script type='text/javascript' src='".$javascript_path."/dojo/dojo/dojo.js'></script>
		<script type='text/javascript'>
		dojo.addOnLoad(function () {
			// Ajout du theme Dojo
			dojo.addClass(dojo.body(),'tundra');
		})
		</script>
		";
	}
	 	
	public static function get_script_analytics() {
		global $msg, $include_path;
		global $opac_script_analytics, $opac_cookies_consent, $opac_url_more_about_cookies, $opac_show_social_network, $pmb_logs_activate;
		
		$script_analytics = '';
		if($opac_cookies_consent == 2) {
			//On reste sur l'ancien fonctionnement
			if (!isset($_COOKIE['PhpMyBibli-COOKIECONSENT']) || !$_COOKIE['PhpMyBibli-COOKIECONSENT']) {
				if ($opac_cookies_consent && ($opac_script_analytics || $opac_show_social_network || $pmb_logs_activate)) {
					$script_analytics .= "
					<script type='text/javascript'>
						var msg_script_analytics_content = '".addslashes($msg["script_analytics_content"])."';
						var msg_script_analytics_inform_ask_opposite = '".addslashes($msg["script_analytics_inform_ask_opposite"])."';
						var msg_script_analytics_inform_ask_accept = '".addslashes($msg["script_analytics_inform_ask_accept"])."';
			            var msg_script_analytics_button_dnt_confirm = '".addslashes($msg["script_analytics_button_dnt_confirm"])."';
					";
					if ($opac_url_more_about_cookies) {
						$script_analytics .= "	
						var script_analytics_content_link_more = '".$opac_url_more_about_cookies."';
						var script_analytics_content_link_more_msg = '".addslashes($msg["script_analytics_content_link_more"])."';";
					} else {
						$script_analytics .= "	var script_analytics_content_link_more = '';
						var script_analytics_content_link_more_msg = '';";
					}
					$script_analytics .= "
					</script>
					<script type='text/javascript' src='".$include_path."/javascript/script_analytics.js'></script>
					<script type='text/javascript'>
						scriptAnalytics.CookieConsent.start();
					</script>
					";
				}
			}
			if (!empty($_COOKIE['PhpMyBibli-COOKIECONSENT']) && $_COOKIE['PhpMyBibli-COOKIECONSENT'] != "false") {
				if ($opac_script_analytics) {
					eval("\$opac_script_analytics=\"".str_replace("\"","\\\"",$opac_script_analytics)."\";");
					$script_analytics .= $opac_script_analytics;
				}
			}
		} elseif ($opac_cookies_consent) {
			//nouvelle gestion des cookies
			//on commence par la surcharge des messages
			//utile pour la substitution et pour le charset ISO-8859-1
			$script_analytics .= '
			<script type="text/javascript">
				var tarteaucitron_messages = pmbDojo.messages.getMessages("tarteaucitron");
				if(tarteaucitron_messages.length) {
					tarteaucitronCustomText = {};
					tarteaucitron_messages.forEach(function(message) {
						if(parseInt(message.code.indexOf(":")) !== -1) {
							let tarteaucitron_messages_group = message.code.split(":");
							if(typeof tarteaucitronCustomText[tarteaucitron_messages_group[0]] == "undefined") {
								tarteaucitronCustomText[tarteaucitron_messages_group[0]] = {};
							}
							tarteaucitronCustomText[tarteaucitron_messages_group[0]][tarteaucitron_messages_group[1]] = message.message; 
						} else {
							tarteaucitronCustomText[message.code] = message.message;
						}
					});
				}
			</script>
			';
			$script_analytics .= '<script type="text/javascript" src="'.$include_path.'/javascript/tarteaucitron/tarteaucitron.js"></script>';
			$script_analytics .= cookies_consent::get_initialization();
			$script_analytics .= cookies_consent::get_display_custom_services();
			$script_analytics .= cookies_consent::get_display_services();
			if ($opac_script_analytics) {
				eval("\$opac_script_analytics=\"".str_replace("\"","\\\"",$opac_script_analytics)."\";");
				$script_analytics .= $opac_script_analytics;
			}
		}
		return $script_analytics;
	}
	
	public static function get_js_function_encode_url() {
		return "<script type='text/javascript'>
			// Fonction a utiliser pour l'encodage des URLs en javascript
			function encode_URL(data){
				var docCharSet = document.characterSet ? document.characterSet : document.charset;
				if(docCharSet == \"UTF-8\"){
	    			return encodeURIComponent(data);
	    		}else{
	    			return escape(data);
	    		}
	    	}
	    </script>";
	}
	
	public static function get_js_script_social_network() {
		global $opac_cookies_consent, $opac_show_social_network, $opac_param_social_network;
		
		$js_script = '';
		
		//Opposition à l'utilisation des cookies, désactivation des partages sur les réseaux sociaux
		if ($opac_cookies_consent && cookies_consent::is_opposed_addthis_service()) {
			$opac_show_social_network = 0;
		}
		
		$js_script .= "<script type='text/javascript'>
			var opac_show_social_network =$opac_show_social_network;
		</script>";
		
		if (!empty($opac_show_social_network) && !empty($opac_param_social_network)) {
			$addThisParams = json_decode($opac_param_social_network);
			//ra-4d9b1e202c30dea1
			if (!empty($addThisParams->addthis_share)) {
				$js_script .= "<script type='text/javascript'>var addthis_share = ".json_encode($addThisParams->addthis_share).";</script>";
			}
			$js_script .= "<script type='text/javascript'>var addthis_config = ".json_encode($addThisParams->addthis_config).";</script>
				<script type='text/javascript' src='https://s7.addthis.com/js/$addThisParams->version/addthis_widget.js#pubid=$addThisParams->token'></script>";
		}
		return $js_script;
	}
	
	public static function get_js_function_record_display() {
		return "
		<script type='text/javascript'>
		function findNoticeElement(id){
			var ul=null;
			//cas des notices classiques
			var domNotice = document.getElementById('el'+id+'Child');
			//notice_display
			if(!domNotice) domNotice = document.getElementById('notice');
			if(domNotice){
				var uls = domNotice.getElementsByTagName('ul');
				for (var i=0 ; i<uls.length ; i++){
					if(uls[i].getAttribute('id') == 'onglets_isbd_public'+id){
						var ul = uls[i];
						break;
					}
				}
			} else{
				var li = document.getElementById('onglet_isbd'+id);
				if(!li) var li = document.getElementById('onglet_public'+id);
				if(!li) var li = document.getElementById('onglet_detail'+id);
				if(li) var ul = li.parentNode;
			}
			return ul;
		}
		function show_what(quoi, id) {
			switch(quoi){
				case 'EXPL_LOC' :
					document.getElementById('div_expl_loc' + id).style.display = 'block';
					document.getElementById('div_expl' + id).style.display = 'none';
					document.getElementById('onglet_expl' + id).className = 'isbd_public_inactive';
					document.getElementById('onglet_expl_loc' + id).className = 'isbd_public_active';
					break;
				case 'EXPL' :
					document.getElementById('div_expl_loc' + id).style.display = 'none';
					document.getElementById('div_expl' + id).style.display = 'block';
					document.getElementById('onglet_expl' + id).className = 'isbd_public_active';
					document.getElementById('onglet_expl_loc' + id).className = 'isbd_public_inactive';
					break;
				default :
					quoi= quoi.toLowerCase();
					var ul = findNoticeElement(id);
					if (ul) {
						var items  = ul.getElementsByTagName('li');
						for (var i=0 ; i<items.length ; i++){
							if(items[i].getAttribute('id')){
								if(items[i].getAttribute('id') == 'onglet_'+quoi+id){
									items[i].className = 'isbd_public_active';
									document.getElementById('div_'+quoi+id).style.display = 'block';
								}else{
									if(items[i].className != 'onglet_tags' && items[i].className != 'onglet_avis' && items[i].className != 'onglet_sugg' && items[i].className != 'onglet_basket' && items[i].className != 'onglet_liste_lecture'){
										items[i].className = 'isbd_public_inactive';
										document.getElementById(items[i].getAttribute('id').replace('onglet','div')).style.display = 'none';
									}
								}
							}
						}
					}
					break;
			}
		}
		</script>";
	}

} // end of class