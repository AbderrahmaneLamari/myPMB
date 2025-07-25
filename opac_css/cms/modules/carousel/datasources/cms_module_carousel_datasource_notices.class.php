<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_carousel_datasource_notices.class.php,v 1.10.14.2 2023/06/09 08:21:08 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

use Pmb\Thumbnail\Models\ThumbnailSourcesHandler;

class cms_module_carousel_datasource_notices extends cms_module_common_datasource_records{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		global $opac_url_base;
		global $opac_show_book_pics;
		global $opac_book_pics_url;
		
		$datas = parent::get_datas();
		$notices = $datas['records'];
		$query = "select notice_id,tit1,thumbnail_url,code from notices where notice_id in ('".implode("','",$notices)."')";
		$result = pmb_mysql_query($query);
		$notices = array();
		if(pmb_mysql_num_rows($result)){
		    $thumbnailSourcesHandler = new ThumbnailSourcesHandler();
			while($row = pmb_mysql_fetch_object($result)){
			    $url_vign = "";
			    if ($opac_show_book_pics=='1') {
			        $url_vign = $thumbnailSourcesHandler->generateUrl(TYPE_NOTICE, $row->notice_id);
			    }
				$notices[] = array(
					'title' => $row->tit1,
					'link' => $opac_url_base."?lvl=notice_display&id=".$row->notice_id,
					'vign' => $url_vign
				);
			}
		}
		return array('records' => $notices);
	}
	
	public function get_format_data_structure(){
		return array(
			array(
				'var' => "records",
				'desc' => $this->msg['cms_module_carousel_datasource_notices_records_desc'],
				'children' => array(
					array(
						'var' => "records[i].title",
						'desc'=> $this->msg['cms_module_carousel_datasource_notices_record_title_desc'] 
					),
					array(
						'var' => "records[i].vign",
						'desc'=> $this->msg['cms_module_carousel_datasource_notices_record_vign_desc'] 
					),
					array(
						'var' => "records[i].link",
						'desc'=> $this->msg['cms_module_carousel_datasource_notices_record_link_desc'] 
					)
				)
			)
		);
	}
}