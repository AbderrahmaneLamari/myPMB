<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SearchModel.php,v 1.7 2022/12/23 09:24:50 gneveu Exp $

namespace Pmb\Common\Models;

class SearchModel extends Model
{
    /**
     * Exemple de tableau
     *
     * $globalsSearch = [
     *      'f_1' => [
     *          'BOOLEAN' => 'afri*'
     *      ]
     * ];
     *
     */
    
    public function __construct()
    {}
    
    private function setGlobalsSearch(array $globalsSearch)
    {
        global $search;
        
        $i = 0;
        foreach ($globalsSearch as $searchCode => $searchValues) {
            $search[] = $searchCode;
            foreach ($searchValues as $searchOperator => $searchValue) {
                $op = "op_" . $i . "_" . $searchCode;
                global ${$op};
                ${$op} = $searchOperator;
                if (is_array($searchValue) && $searchOperator == 'BETWEEN') {
                    // Cas ou on a besoin de !!p!! et !!p1!!
                    $field_ = "field_" . $i . "_" . $searchCode;
                    global ${$field_};
                    ${$field_} = [$searchValue[0]];
                    
                    $field1_ = "field_" . $i . "_" . $searchCode . "_1";
                    global ${$field1_};
                    ${$field1_} = [$searchValue[1]];
                } else {
                    // Cas classique
                    $field_ = "field_" . $i . "_" . $searchCode;
                    global ${$field_};
                    if (!is_array($searchValue)) {
                        $searchValue = [$searchValue];
                    }
                    ${$field_} = $searchValue;
                }
            }
            $i++;
        }
    }
    
    public function makeSearch(array $globalsSearch, string $labelId, string $search_fields = 'search_fields')
    {
        $this->setGlobalsSearch($globalsSearch);
        $searcher = new \search(true, $search_fields);
        $table = $searcher->make_search();
        $res = pmb_mysql_query("SELECT * FROM $table");
        
        $ids = [];
        while ($row = pmb_mysql_fetch_object($res)) {
            $ids[] = $row->{$labelId};
        }
        return $ids;
    }
}