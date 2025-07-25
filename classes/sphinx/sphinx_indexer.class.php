<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sphinx_indexer.class.php,v 1.19 2023/01/02 15:25:50 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once $class_path.'/sphinx/sphinx_base.class.php';
require_once($base_path."/devel/sphinx/progress_bar.php");

class sphinx_indexer extends sphinx_base {
    
    /**
     * Nom de la cl� dans la table (� revoir si necessaire � terme)
     * @var string
     */
    protected $object_key= 'id_notice';
    /**
     * Nom de la cl� a retourner (a revoir si n�cessaire � terme)
     * @var string
     */
    protected $object_id= 'notice_id';
    
    /**
     * Tableau contenant les donn�es sources (doit disparaitre � terme)
     * @var string
     */
    protected $object_table = 'notices';
    /**
     * Tableau contenant les donn�es d'index (doit disparaitre � terme)
     * @var string
     */
    protected $object_index_table = 'notices_fields_global_index';
    
    protected $already_checked = false;
    
    protected $specificsAttributes = array();
    
    
    public function __construct(){
        parent::__construct();
    }
    public function fillIndex($object_id=0)
    {
        global $sphinx_indexes_prefix;
        
        //$options['size'] = 80;
        $this->parse_file();
        $object_id+=0;
        //Remplissage des indexs...
        $rq='select '.$this->object_key.' from '.$this->object_table.' '.($object_id!= 0 ? 'where '.$this->object_key.'='.$object_id : '').' order by 1';
        $res=pmb_mysql_query($rq);
        if ($res) {
            pmb_mysql_query('set session group_concat_max_len = 16777216');
            if( $object_id == 0) print ProgressBar::start(pmb_mysql_num_rows($res), "Index ".$this->default_index, $options);
            while ($object=pmb_mysql_fetch_object($res)) {
                //purge...
                $langs = $this->getAvailableLanguages();
                for($i=0 ; $i<count($langs) ; $i++){
                    foreach($this->indexes as $index_name => $infos){
                        pmb_mysql_query('delete from '.$sphinx_indexes_prefix.$index_name.($langs[$i] != '' ? '_'.$langs[$i] :'').' where id = '.$object->{$this->object_key},$this->getDBHandler());
                    }
                }
                //Construction de l'index
                $rq='select code_champ,code_ss_champ,lang,group_concat(value SEPARATOR "'.$this->getSeparator().'") as value from '.$this->object_index_table.' where id_notice= '.$object->{$this->object_key}.' and lang in ("'.implode('","',$this->getAvailableLanguages()).'") group by code_champ,code_ss_champ,lang';
                $inserts = array();
                $res_notice=pmb_mysql_query($rq);
                while ($champ=pmb_mysql_fetch_object($res_notice)) {
                    if(in_array($champ->lang,$langs)){
                        $code_champ=str_pad($champ->code_champ, 3,"0",STR_PAD_LEFT);
                        $code_ss_champ=str_pad($champ->code_ss_champ, 2,"0",STR_PAD_LEFT);
                        $field='f_'.$code_champ.'_'.$code_ss_champ;
                        
                        if($this->insert_index[$field]){
                            $inserts[$this->insert_index[$field].($champ->lang ? '_'.$champ->lang : '')][$field] = addslashes(encoding_normalize::utf8_normalize($champ->value));
                        }
                    }
                }
                $inserts = $this->getSpecificsFiltersValues($object->{$this->object_key},$inserts);
                
                foreach($inserts as $table => $fields){
                    $keys = $values =  "";
                    foreach($fields as $key => $value){
                        if($keys){
                            $keys.=",";
                            $values.=",";
                        }
                        $keys.=$key;
                        if (substr($key, 0, 2) !== "f_") {
                            if (!empty($value)) {
                                $values .= $value;
                            } else {
                                $values .= "''";
                            }
                        } else {
                            $values .= '\''.$value.'\'';
                        }
                    }
                    $query = 'insert into '.$sphinx_indexes_prefix.$table.' (id,'.$keys.') values('.$object->{$this->object_key}.','.$values.')';
                    if(!pmb_mysql_query($query,$this->getDBHandler())){
                        print $table. ' : '.pmb_mysql_error($this->getDBHandler()). "\n";
                    }
                }
                if( $object_id == 0) print ProgressBar::next();
            }
            if( $object_id == 0) print ProgressBar::finish();
        }
    }
    
    public function deleteIndex($object_id=0)
    {
        global $sphinx_indexes_prefix;
        
        $object_id+=0;
        $langs = $this->getAvailableLanguages();
        for($i=0 ; $i<count($langs) ; $i++){
            foreach($this->indexes as $index_name => $infos){
                pmb_mysql_query('delete from '.$sphinx_indexes_prefix.$index_name.($langs[$i] != '' ? '_'.$langs[$i] :'').' where id = '.$object_id ,$this->getDBHandler());
            }
        }
    }
    
    public function getIndexConfFile()
    {
        global $sphinx_indexes_path;
        global $sphinx_indexes_prefix;
        global $sphinx_troncat_min_length;
        
        $this->parse_file();
        $conf = '
########################################
#   PMB AUTOMATIC INDEX CONSTRUCTION   #
########################################';
        $langs = $this->getAvailableLanguages();
        for($i=0 ; $i<count($langs) ; $i++){
            foreach($this->indexes as $index_name => $infos){
                if(count($infos['fields'])){
                    $index_name = $sphinx_indexes_prefix.$index_name.($langs[$i] != '' ? '_'.$langs[$i] : '');
                    $conf.='
                        
index '.$index_name.'
{
	type = rt
	path = '.str_replace('//', '/', $sphinx_indexes_path.'/'.$index_name).'
    expand_keywords = 0;

    min_infix_len = '.$sphinx_troncat_min_length.'
                    
	charset_table = 0..9, a..z, _, A..Z->a..z, U+00C0->a, U+00C1->a, \
        U+00C2->a, U+00C3->a, U+00C4->a, U+00C5->a, U+00C7->c, U+00C8->e, \
        U+00C9->e, U+00CA->e, U+00CB->e, U+00CC->i, U+00CD->i, U+00CE->i, \
        U+00CF->i, U+00D1->n, U+00D2->o, U+00D3->o, U+00D4->o, U+00D5->o, \
        U+00D6->o, U+00D8->o, U+00D9->u, U+00DA->u, U+00DB->u, U+00DC->u, \
        U+00DD->y, U+00E0->a, U+00E1->a, U+00E2->a, U+00E3->a, U+00E4->a, \
        U+00E5->a, U+00E7->c, U+00E8->e, U+00E9->e, U+00EA->e, U+00EB->e, \
        U+00EC->i, U+00ED->i, U+00EE->i, U+00EF->i, U+00F1->n, U+00F2->o, \
        U+00F3->o, U+00F4->o, U+00F5->o, U+00F6->o, U+00F8->o, U+00F9->u, \
        U+00FA->u, U+00FB->u, U+00FC->u, U+00FD->y, U+00FF->y, U+0100->a, \
        U+0101->a, U+0102->a, U+0103->a, U+0104->a, U+0105->a, U+0106->c, \
        U+0107->c, U+0108->c, U+0109->c, U+0110->d, U+0111->d, U+010A->c, \
        U+010B->c, U+010C->c, U+010D->c, U+010E->d, U+010F->d, U+0112->e, \
        U+0113->e, U+0114->e, U+0115->e, U+0116->e, U+0117->e, U+0118->e, \
        U+0119->e, U+011A->e, U+011B->e, U+011C->g, U+011D->g, U+011E->g, \
        U+011F->g, U+0120->g, U+0121->g, U+0122->g, U+0123->g, U+0124->h, \
        U+0125->h, U+0128->i, U+0129->i, U+0131->i, U+012A->i, U+012B->i, \
        U+012C->i, U+012D->i, U+012E->i, U+012F->i, U+0130->i, U+0134->j, \
        U+0135->j, U+0136->k, U+0137->k, U+0139->l, U+013A->l, U+013B->l, \
        U+013C->l, U+013D->l, U+013E->l, U+0141->l, U+0142->l, U+0143->n, \
        U+0144->n, U+0145->n, U+0146->n, U+0147->n, U+0148->n, U+014C->o, \
        U+014D->o, U+014E->o, U+014F->o, U+0150->o, U+0151->o, U+0154->r, \
        U+0155->r, U+0156->r, U+0157->r, U+0158->r, U+0159->r, U+015A->s, \
        U+015B->s, U+015C->s, U+015D->s, U+015E->s, U+015F->s, U+0160->s, \
        U+0161->s, U+0162->t, U+0163->t, U+0164->t, U+0165->t, U+0168->u, \
        U+0169->u, U+016A->u, U+016B->u, U+016C->u, U+016D->u, U+016E->u, \
        U+016F->u, U+0170->u, U+0171->u, U+0172->u, U+0173->u, U+0174->w, \
        U+0175->w, U+0176->y, U+0177->y, U+0178->y, U+0179->z, U+017A->z, \
        U+017B->z, U+017C->z, U+017D->z, U+017E->z, U+01A0->o, U+01A1->o, \
        U+01AF->u, U+01B0->u, U+01CD->a, U+01CE->a, U+01CF->i, U+01D0->i, \
        U+01D1->o, U+01D2->o, U+01D3->u, U+01D4->u, U+01D5->u, U+01D6->u, \
        U+01D7->u, U+01D8->u, U+01D9->u, U+01DA->u, U+01DB->u, U+01DC->u, \
        U+01DE->a, U+01DF->a, U+01E0->a, U+01E1->a, U+01E6->g, U+01E7->g, \
        U+01E8->k, U+01E9->k, U+01EA->o, U+01EB->o, U+01EC->o, U+01ED->o, \
        U+01F0->j, U+01F4->g, U+01F5->g, U+01F8->n, U+01F9->n, U+01FA->a, \
        U+01FB->a, U+0200->a, U+0201->a, U+0202->a, U+0203->a, U+0204->e, \
        U+0205->e, U+0206->e, U+0207->e, U+0208->i, U+0209->i, U+020A->i, \
        U+020B->i, U+020C->o, U+020D->o, U+020E->o, U+020F->o, U+0210->r, \
        U+0211->r, U+0212->r, U+0213->r, U+0214->u, U+0215->u, U+0216->u, \
        U+0217->u, U+0218->s, U+0219->s, U+021A->t, U+021B->t, U+021E->h, \
        U+021F->h, U+0226->a, U+0227->a, U+0228->e, U+0229->e, U+022A->o, \
        U+022B->o, U+022C->o, U+022D->o, U+022E->o, U+022F->o, U+0230->o, \
        U+0231->o, U+0232->y, U+0233->y, U+1E00->a, U+1E01->a, U+1E02->b, \
        U+1E03->b, U+1E04->b, U+1E05->b, U+1E06->b, U+1E07->b, U+1E08->c, \
        U+1E09->c, U+1E0A->d, U+1E0B->d, U+1E0C->d, U+1E0D->d, U+1E0E->d, \
        U+1E0F->d, U+1E10->d, U+1E11->d, U+1E12->d, U+1E13->d, U+1E14->e, \
        U+1E15->e, U+1E16->e, U+1E17->e, U+1E18->e, U+1E19->e, U+1E1A->e, \
        U+1E1B->e, U+1E1C->e, U+1E1D->e, U+1E1E->f, U+1E1F->f, U+1E20->g, \
        U+1E21->g, U+1E22->h, U+1E23->h, U+1E24->h, U+1E25->h, U+1E26->h, \
        U+1E27->h, U+1E28->h, U+1E29->h, U+1E2A->h, U+1E2B->h, U+1E2C->i, \
        U+1E2D->i, U+1E2E->i, U+1E2F->i, U+1E30->k, U+1E31->k, U+1E32->k, \
        U+1E33->k, U+1E34->k, U+1E35->k, U+1E36->l, U+1E37->l, U+1E38->l, \
        U+1E39->l, U+1E3A->l, U+1E3B->l, U+1E3C->l, U+1E3D->l, U+1E3E->m, \
        U+1E3F->m, U+1E40->m, U+1E41->m, U+1E42->m, U+1E43->m, U+1E44->n, \
        U+1E45->n, U+1E46->n, U+1E47->n, U+1E48->n, U+1E49->n, U+1E4A->n, \
        U+1E4B->n, U+1E4C->o, U+1E4D->o, U+1E4E->o, U+1E4F->o, U+1E50->o, \
        U+1E51->o, U+1E52->o, U+1E53->o, U+1E54->p, U+1E55->p, U+1E56->p, \
        U+1E57->p, U+1E58->r, U+1E59->r, U+1E5A->r, U+1E5B->r, U+1E5C->r, \
        U+1E5D->r, U+1E5E->r, U+1E5F->r, U+1E60->s, U+1E61->s, U+1E62->s, \
        U+1E63->s, U+1E64->s, U+1E65->s, U+1E66->s, U+1E67->s, U+1E68->s, \
        U+1E69->s, U+1E6A->t, U+1E6B->t, U+1E6C->t, U+1E6D->t, U+1E6E->t, \
        U+1E6F->t, U+1E70->t, U+1E71->t, U+1E72->u, U+1E73->u, U+1E74->u, \
        U+1E75->u, U+1E76->u, U+1E77->u, U+1E78->u, U+1E79->u, U+1E7A->u, \
        U+1E7B->u, U+1E7C->v, U+1E7D->v, U+1E7E->v, U+1E7F->v, U+1E80->w, \
        U+1E81->w, U+1E82->w, U+1E83->w, U+1E84->w, U+1E85->w, U+1E86->w, \
        U+1E87->w, U+1E88->w, U+1E89->w, U+1E8A->x, U+1E8B->x, U+1E8C->x, \
        U+1E8D->x, U+1E8E->y, U+1E8F->y, U+1E96->h, U+1E97->t, U+1E98->w, \
        U+1E99->y, U+1EA0->a, U+1EA1->a, U+1EA2->a, U+1EA3->a, U+1EA4->a, \
        U+1EA5->a, U+1EA6->a, U+1EA7->a, U+1EA8->a, U+1EA9->a, U+1EAA->a, \
        U+1EAB->a, U+1EAC->a, U+1EAD->a, U+1EAE->a, U+1EAF->a, U+1EB0->a, \
        U+1EB1->a, U+1EB2->a, U+1EB3->a, U+1EB4->a, U+1EB5->a, U+1EB6->a, \
        U+1EB7->a, U+1EB8->e, U+1EB9->e, U+1EBA->e, U+1EBB->e, U+1EBC->e, \
        U+1EBD->e, U+1EBE->e, U+1EBF->e, U+1EC0->e, U+1EC1->e, U+1EC2->e, \
        U+1EC3->e, U+1EC4->e, U+1EC5->e, U+1EC6->e, U+1EC7->e, U+1EC8->i, \
        U+1EC9->i, U+1ECA->i, U+1ECB->i, U+1ECC->o, U+1ECD->o, U+1ECE->o, \
        U+1ECF->o, U+1ED0->o, U+1ED1->o, U+1ED2->o, U+1ED3->o, U+1ED4->o, \
        U+1ED5->o, U+1ED6->o, U+1ED7->o, U+1ED8->o, U+1ED9->o, U+1EDA->o, \
        U+1EDB->o, U+1EDC->o, U+1EDD->o, U+1EDE->o, U+1EDF->o, U+1EE0->o, \
        U+1EE1->o, U+1EE2->o, U+1EE3->o, U+1EE4->u, U+1EE5->u, U+1EE6->u, \
        U+1EE7->u, U+1EE8->u, U+1EE9->u, U+1EEA->u, U+1EEB->u, U+1EEC->u, \
        U+1EED->u, U+1EEE->u, U+1EEF->u, U+1EF0->u, U+1EF1->u, U+1EF2->y, \
        U+1EF3->y, U+1EF4->y, U+1EF5->y, U+1EF6->y, U+1EF7->y, U+1EF8->y, \
        U+1EF9->y
	    
	#fields definition';
                    for($j=0 ; $j<count($infos['fields']) ; $j++){
                        $conf.='
	rt_field = '.$infos['fields'][$j];
                    }
                }
                if (count($infos['attributes'])) {
                    $conf .= '
	#attribute definition';
                    foreach ($infos['attributes'] as $type => $attributes) {
                        for ($j = 0; $j < count($attributes); $j++) {
                            if ($type == 'bigint') {
                                $conf .= '
	rt_attr_bigint	= ' . $attributes[$j];
                            } else {
                                if ($type == 'string' || substr($attributes[$j], 0, 2) == "f_") {
                                    $conf .= '
	rt_attr_string = ' . $attributes[$j];
                                } else {
                                    $conf .= '
	rt_attr_multi = ' . $attributes[$j];
                                }
                            }
                        }
                    }
                }
                $conf.='
}';
            }
        }
        return $conf;
    }
    
    protected function getSpecificsFiltersValues($id, $inserts) {
        $filters = $this->addSpecificsFilters($id);
        foreach ($filters as $type => $filter) {
            foreach ($filter as $name => $values) {
                if (!is_array($values)) {
                    $values = array($values);
                }
                $nb_values = count($values);
                if ($type != 'int' && $type != 'bigint') {
                    for ($i = 0; $i < $nb_values; $i++) {
                        $values[$i] = crc32($values[$i]);
                    }
                }
                if ($type == 'multi') {
                    $filters[$type][$name] = '(' . implode(',', $values) . ')';
                } else {
                    $filters[$type][$name] = implode(',', $values);
                }
            }
            foreach ($inserts as $index => $fields) {
                $inserts[$index] = array_merge($inserts[$index], $filters[$type]);
            }
        }
        return $inserts;
    }
    
    protected function addSpecificsFilters($id,$filters=array()){
        return $filters;
    }
    
    public function editSphinxTables($pmb_table_name, $action, $field_name, $field_perso_id = '', $field_type = '') {
        $dbh = $this->getDBHandler();
        $pperso_field = $this->get_pperso_name($pmb_table_name);
        $sphinx_tables_name = $this->getSphinxTablesName($pmb_table_name);
        $sphinx_type = $this->getSphinxType($field_type);
        
        if (!empty($field_perso_id)) {
            $field_name = $pperso_field . '_' . str_pad($field_perso_id, 2, '0', STR_PAD_LEFT);
        }
        
        foreach ($sphinx_tables_name as $sphinx_table_name) {
            switch ($action) {
                case 'update':
                    if ($this->checkSphinxFieldExists($field_name, $sphinx_table_name)) {
                        pmb_mysql_query("ALTER TABLE $sphinx_table_name DROP COLUMN $field_name", $dbh);
                    }
                    pmb_mysql_query("ALTER TABLE $sphinx_table_name ADD COLUMN $field_name $sphinx_type", $dbh);
                    break;
                case 'delete':
                    if ($this->checkSphinxFieldExists($field_name, $sphinx_table_name)) {
                        pmb_mysql_query("ALTER TABLE $sphinx_table_name DROP COLUMN $field_name", $dbh);
                    }
                    break;
                case 'create':
                default:
                    if (!$this->checkSphinxFieldExists($field_name, $sphinx_table_name)) {
                        pmb_mysql_query("ALTER TABLE $sphinx_table_name ADD COLUMN $field_name $sphinx_type", $dbh);
                    }
                    break;
            }
        }
    }
    
    private function getSphinxTablesName($table_name) {
        global $sphinx_indexes_prefix;
        
        $sphinx_tables = [];
        $languages = $this->getAvailableLanguages();
        $sphinx_table_name = $sphinx_indexes_prefix;
        
        switch ($table_name) {
            case 'notices':
                $sphinx_table_name .= 'records';
                break;
            case 'author':
                $sphinx_table_name .= 'authors';
                break;
            case 'categ':
                $sphinx_table_name .= 'categories';
                break;
            case 'collection':
                $sphinx_table_name .= 'collections';
                break;
            case 'skos':
                $sphinx_table_name .= 'concepts';
                break;
            case 'publisher':
                $sphinx_table_name .= 'publishers';
                break;
            case 'serie':
                $sphinx_table_name .= 'series';
                break;
            case 'subcollection':
                $sphinx_table_name .= 'subcollections';
                break;
            case 'tu':
                $sphinx_table_name .= 'titres_uniformes';
                break;
            default:
                $sphinx_table_name .= $table_name;
                break;
        }
        
        foreach ($languages as $language) {
            $temp = $sphinx_table_name;
            if (!empty($language)) {
                $temp .= "_$language";
            }
            $sphinx_tables[] = $temp;
        }
        
        return $sphinx_tables;
    }
    
    private function getSphinxType($field_type) {
        switch ($field_type) {
            case 'integer':
                $sphinx_type = 'INTEGER';
                break;
            case 'date':
                $sphinx_type = 'BIGINT';
                break;
            case 'float':
                $sphinx_type = 'FLOAT';
                break;
            case 'small_text':
            case 'text':
            default:
                $sphinx_type = 'STRING';
                break;
        }
        return $sphinx_type;
    }
    
    private function checkSphinxFieldExists($field_name, $table) {
        $res = pmb_mysql_query("DESC $table", $this->getDBHandler());
        while ($row = pmb_mysql_fetch_assoc($res)) {
            if ($row['Field'] == strtolower($field_name)) {
                return true;
            }
        }
        return false;
    }
    
    /*
     * $sphinx_fields : Variable contenant les champs d�j� contenus en base
     * $this->indexes : Variable contenant les champs cens�s �tre en base (/indexation/entity/champs_base.xml)
     */
    public function checkExistingIndexes() {
        global $sphinx_indexes_prefix;
        
        if ($this->already_checked) {
            return;
        }
        $langs = $this->getAvailableLanguages();
        $nb_langs = count($langs);
        $dbh = $this->getDBHandler();
        
        $current_tables = [];
        $res = pmb_mysql_query("SHOW TABLES", $dbh);
        while ($row = pmb_mysql_fetch_assoc($res)) {
            $current_tables[] = $row['Index'];
        }
        
        for ($i = 0; $i < $nb_langs; $i++) {
            foreach ($this->indexes as $type => $values) {
                $sphinx_table_name = $sphinx_indexes_prefix . $type . ($langs[$i] != '' ? '_' . $langs[$i] : '');
                if (!empty($values['fields'])) {
                    if (!in_array($sphinx_table_name, $current_tables)) {
                        // On tombe sur des tables que l'on avait pas en base
                        // Il faut donc les ajouter
                        // Seul hic, il faut Sphinx 3.0 minimum (on est en 2.2 pour le moment)
                        continue;
                    }
                    $res = pmb_mysql_query("DESC $sphinx_table_name", $dbh);
                    $indexes = $values;
                    $sphinx_fields = [];
                    while ($row = pmb_mysql_fetch_assoc($res)) {
                        if ($row['Field'] != 'id') {
                            $sphinx_fields[$row['Type']][] = $row['Field'];
                        }
                    }
                    foreach ($sphinx_fields as $type_field => $fields) {
                        $nb_fields = count($fields);
                        for ($j = 0; $j < $nb_fields; $j++) {
                            if (in_array($fields[$j], $indexes['fields'])) {
                                $index = array_search($fields[$j], $indexes['fields']);
                                unset($indexes['fields'][$index]);
                                unset($sphinx_fields[$type_field][$j]);
                                if (empty($sphinx_fields[$type_field])) {
                                    unset($sphinx_fields[$type_field]);
                                }
                            } else {
                                foreach ($indexes['attributes'] as $type_attr => $values_attr) {
                                    if (in_array($fields[$j], $values_attr)) {
                                        if ($type_attr == $type_field || ($type_attr == 'multi' && $type_field == 'mva')) {
                                            unset($indexes['attributes'][$type_attr][$j]);
                                            if (empty($indexes['attributes'][$type_attr])) {
                                                unset($indexes['attributes'][$type_attr]);
                                            }
                                            unset($sphinx_fields[$type_field][$j]);
                                            if (empty($sphinx_fields[$type_field])) {
                                                unset($sphinx_fields[$type_field]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($sphinx_fields)) {
                    foreach ($sphinx_fields as $type => $values) {
                        foreach ($values as $value) {
                            pmb_mysql_query("ALTER TABLE $sphinx_table_name DROP COLUMN $value", $dbh);
                        }
                    }
                }
                if (!empty($indexes['fields'])) {
                    foreach ($indexes['fields'] as $field) {
                        pmb_mysql_query("ALTER TABLE $sphinx_table_name ADD COLUMN $field STRING", $dbh);
                    }
                }
                if (!empty($indexes['attributes'])) {
                    foreach ($indexes['attributes'] as $type_attr => $values_attr) {
                        foreach ($values_attr as $value) {
                            pmb_mysql_query("ALTER TABLE $sphinx_table_name ADD COLUMN $value $type_attr", $dbh);
                        }
                    }
                }
            }
        }
        $this->already_checked = true;
    }
    
    private function get_pperso_name($pmb_table_name) {
        $authperso_id = 0;
        
        $class_name = $this->getIndexerClassName($pmb_table_name);
        $sphinx_class = new $class_name();
        
        if ($class_name == "sphinx_authperso_indexer") {
            $authperso_id = explode("_", $pmb_table_name)[1];
        }
        
        return $sphinx_class->get_pperso_field($authperso_id);
    }
    
    private function getIndexerClassName($pmb_table_name) {
        $class_name = 'sphinx_';
        switch ($pmb_table_name) {
            case 'notices':
                $class_name .= 'records';
                break;
            case 'author':
                $class_name .= 'authors';
                break;
            case 'categ':
                $class_name .= 'categories';
                break;
            case 'collection':
                $class_name .= 'collections';
                break;
            case 'skos':
                $class_name .= 'concepts';
                break;
            case 'publisher':
                $class_name .= 'publishers';
                break;
            case 'serie':
                $class_name .= 'series';
                break;
            case 'subcollection':
                $class_name .= 'subcollections';
                break;
            case 'tu':
                $class_name .= 'titres_uniformes';
                break;
            default:
                $class_name .= 'authperso';
                break;
        }
        $class_name .= '_indexer';
        return $class_name;
    }
    
    public function checkSphinxTables() {
        global $sphinx_indexes_prefix;
        
        $langs = $this->getAvailableLanguages();
        $nb_langs = count($langs);
        $dbh = $this->getDBHandler();
        
        $current_tables = [];
        $res = pmb_mysql_query("SHOW TABLES", $dbh);
        while ($row = pmb_mysql_fetch_assoc($res)) {
            $current_tables[] = $row['Index'];
        }
        
        for ($i = 0; $i < $nb_langs; $i++) {
            foreach ($this->indexes as $type => $values) {
                $sphinx_table_name = $sphinx_indexes_prefix . $type . ($langs[$i] != '' ? '_' . $langs[$i] : '');
                if (!empty($values['fields'])) {
                    if (!in_array($sphinx_table_name, $current_tables)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}