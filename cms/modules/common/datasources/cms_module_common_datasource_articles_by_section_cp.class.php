<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_articles_by_section_cp.class.php,v 1.5.4.2 2023/05/30 07:20:31 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_articles_by_section_cp extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
        parent::__construct($id);
        $this->sortable = true;
        $this->limitable = true;
        $this->paging = true;
    }

    /*
     * On d�fini les s�lecteurs utilisable pour cette source de donn�e
     */
	public function get_available_selectors(){
        return array(
            "cms_module_common_selector_type_section_generic",
		    "cms_module_common_selector_type_section",
        );
    }

    /*
     * On d�fini les crit�res de tri utilisable pour cette source de donn�e
     */
	protected function get_sort_criterias() {
        return array(
            "publication_date",
            "id_article",
            "article_title",
            "article_order",
            "rand()",
            "cp_event_date"
        );
    }

    /*
     * R�cup�ration des donn�es de la source...
     */
	public function get_datas(){
        $selector = $this->get_selected_selector();
        if ($selector) {
            $value = $selector->get_value();
            if (! is_array($value)) {
		        $value = [$value];
            }
            $return = $this->filter_datas("articles", $value);
            if (count($return)) {
                $query = "select id_article,if(article_start_date != '0000-00-00 00:00:00',article_start_date,article_creation_date) as publication_date from cms_articles where id_article in ('" . implode("','", $return) . "')";
                if ($this->parameters["sort_by"] != "") {
                    if ($this->parameters["sort_by"] == "cp_event_date") {
                        $queryAgenda = "SELECT managed_module_box FROM cms_managed_modules WHERE managed_module_name = 'cms_module_agenda'";
                        $result = pmb_mysql_query($queryAgenda);
                        if (pmb_mysql_num_rows($result)) {
                            $data = unserialize(pmb_mysql_result($result, 0, 0));
                            $idsType = array();
                            $idsCp = array();
                            foreach ($data["module"]["calendars"] as $calendar) {
                                $idsType[] = $calendar["type"];
                                $idsCp[] = $calendar["start_date"];
                            }

                            $query .= "
                                JOIN cms_editorial_custom_values ON cms_editorial_custom_values.cms_editorial_custom_champ in (" . implode(',', $idsCp) . ")
                                AND cms_editorial_custom_values.cms_editorial_custom_origine = cms_articles.id_article
                                WHERE article_num_type in (" . implode(',', $idsType) . ")
                                ORDER BY cms_editorial_custom_date
                            ";
                        }
                    } else {
                        $query .= " order by " . $this->parameters["sort_by"];
                    }

                    if ($this->parameters["sort_order"] != "") {
                        $query .= " " . $this->parameters["sort_order"];
                    }
                }
                $result = pmb_mysql_query($query);
                if (pmb_mysql_num_rows($result)) {
                    $return = array();
                    while ($row = pmb_mysql_fetch_object($result)) {
                        $return['articles'][] = $row->id_article;
                    }
                }
            }

            // Pagination
            if ($this->paging && isset($this->parameters['paging_activate']) && $this->parameters['paging_activate'] == "on") {
                $return["paging"] = $this->inject_paginator($return['articles']);
                $return['articles'] = $this->cut_paging_list($return['articles'], $return["paging"]);
            } else if ($this->parameters["nb_max_elements"] > 0) {
                $return = array_slice($return['articles'], 0, $this->parameters["nb_max_elements"]);
            }

            return $return;
        }
        return false;
    }
}