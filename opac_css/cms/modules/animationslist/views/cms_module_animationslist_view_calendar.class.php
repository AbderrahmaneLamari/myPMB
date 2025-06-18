<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_animationslist_view_calendar.class.php,v 1.1 2021/04/01 15:37:10 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

use Pmb\Animations\Models\AnimationModel;
use Pmb\Animations\Models\AnimationStatusModel;

class cms_module_animationslist_view_calendar extends cms_module_common_view {
	
	public function __construct($id = 0) {
		$this->use_dojo = true;
		parent::__construct($id);
	}

	public function get_form() {
		$form = "
        <div class='row'>
			<div class='colonne3'>
				<label for='cms_module_animationslist_view_calendar_nb_displayed_animations_under'>".$this->format_text($this->msg['cms_module_animationslist_view_calendar_nb_displayed_animations_under'])."</label>
			</div>
			<div class='colonne-suite'>
				<input type='text' name='cms_module_animationslist_view_calendar_nb_displayed_animations_under' value='".$this->format_text($this->parameters['nb_displayed_animations_under'])."'/>
			</div>
		</div>";
		
		return $form;
	}
	
	public function save_form() {
	    global $cms_module_animationslist_view_calendar_nb_displayed_animations_under;
		
		$this->save_constructor_link_form('animation');
		$this->save_constructor_link_form('animationslist');
		$this->parameters['nb_displayed_animations_under'] = (int) $cms_module_animationslist_view_calendar_nb_displayed_animations_under;
		
		return parent::save_form();
	}
	
	public function get_headers($datas = []) {
		$headers = parent::get_headers($datas);
		$headers[] = "
		<script>
			require(['dijit/dijit']);
		</script>";		
		$headers[] = "
		<script>
			require(['dijit/Calendar']);
		</script>";
		$headers[] = "<link rel='stylesheet' type='text/css' href='" . $this->get_ajax_link(array('do' => 'get_css'), 'css') . "'/>";
		
		return $headers;
	}
	
	public function render($datas) {
		$html_to_display = "<div id='cms_module_calendar_$this->id'></div>";
		$legend = '';
		$styles = [];
		$animations = [];
		$animations_list = '';
		
		if ($this->parameters > 0 && !empty($datas['animations'])) {
			$legend = "<div class='row'>";
			$animations_list = "<ul class='cms_module_animationslist_view_calendar_animationslist'>";
			$nb_displayed = 0;
			$date_time = mktime(0, 0, 0);
			$calendar = [];
			foreach ($datas['animations'] as $id_animation) {
			    $animation = new AnimationModel($id_animation);
			    $animation->fetchEvent();
			    $title = $animation->name;
			    if (!empty($animation->event->startDate)) {
 					if (!in_array($title, $calendar)) {
 					    $calendar[] = $title;
 					    $status = new AnimationStatusModel($animation->numStatus);
 					    $color = $status->color;
						$legend .= "
							<div style='float:left;'>
								<div style='float:left;width:1em;height:1em;background-color:$color'></div>
								<div style='float:left;'>&nbsp;".$this->format_text($title)."&nbsp;&nbsp;</div>
							</div>";
					}
					$styles[$animation->numStatus] = $color;
					$start_time = new DateTime($animation->event->startDate);
					$end_time = new DateTime($animation->event->endDate);
					if ($nb_displayed < $this->parameters['nb_displayed_animations_under'] && ($start_time->getTimestamp() >= $date_time || $end_time->getTimestamp() >= $date_time)) {
					    $animations_list .= "<li><a href='".$this->get_constructed_link('animation', $id_animation)."' title='".$this->format_text($title)."' alt='".$this->format_text($title)."'><span class='cms_module_animationslist_animation_".$id_animation."'>".$this->get_date_to_display($start_time->format('d/m/Y'), $end_time->format('d/m/Y'))."</span> : ".$this->format_text($title)."</a></li>";
						$nb_displayed++;
					}
					$animation->event->startDateTime = $start_time->getTimestamp();
					$animation->event->endDateTime = $end_time->getTimestamp();
 					$animations[] = $animation;
				}
			}
			$animations_list .= "</ul>";
			$legend .= "</div><div class='row'></div>";
		}
		
		$html_to_display .= "<style>";

		if (!empty($styles)) {
			foreach ($styles as $id => $color) {
				$html_to_display .= "
					#".$this->get_module_dom_id()." td.cms_module_animationslist_animation_$id {
						background : $color;
					}
					#".$this->get_module_dom_id()." .cms_module_animationslist_view_calendar_animationslist .cms_module_animationslist_animation_$id {
						color : $color;
					}";
			}
		}
		$html_to_display .= "</style>";
		$html_to_display .= $legend . $animations_list;
		
		$json_animations = encoding_normalize::json_encode($this->utf8_encode($animations));
		if (empty($json_animations)) {
		    $json_animations = [];
		}
		
		$link_single_animation = $this->get_constructed_link('animation', '!!id!!');
		if (empty($link_single_animation)) {
		    $link_single_animation = "";
		}
		
		$link_animations = $this->get_constructed_link('animationslist', '!!date!!');
		if (empty($link_animations)) {
		    $link_animations = "";
		}
		
		$html_to_display .= "
		<script type='text/javascript'>
            try {
                require([
                    'apps/pmb/cms/CmsAnimationsCalendar',
                    'dojo/ready',
                    'dojo/domReady!'
                ], function(Calendar, ready){
                    ready(function() {
                        var calendar = new Calendar({
                            animations : $json_animations,
                            singleAnimationLink : '$link_single_animation',
                            animationsLink : '$link_animations'
                        }, 'cms_module_calendar_$this->id')
                        calendar.startup();
                    });
                });
            } catch (e) {
                console.log(e);
            }
		</script>";
		
		return $html_to_display;
	}
	
	public function execute_ajax() {
		global $do;
		
		$response = array();
		switch ($do) {
			case 'get_css':
				$response['content-type'] = 'text/css';
				$response['content'] = "
#".$this->get_module_dom_id()." td.cms_module_animationslist_animation_day {
	background : green;		
}
#".$this->get_module_dom_id()." ul.cms_module_animationslist_view_calendar_animationslist li {
	display : block;
}

#".$this->get_module_dom_id()." ul.cms_module_animationslist_view_calendar_animationslist li a {
	display : inline;
	background : none;
	border : none;
	color : inherit !important;
}";
				
				break;			
			case 'get_js':
				$response['content-type'] = 'application/javascript';
				$response['content'] = '';
				break;		
		}
		
		return $response;
	}
	
	protected function get_date_to_display($start, $end) {
		$display = "";
		if ($start) {
			if ($end && $start != $end) {
				$display .= "du $start au $end";
			} else {
				$display .= $start;
			}
		}
		
		return $display;
	}
}
