<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sphinx_explnums_indexer.class.php,v 1.2 2023/01/02 15:25:51 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path;

require_once "$class_path/sphinx/sphinx_indexer.class.php";

class sphinx_explnums_indexer extends sphinx_indexer {
	
    public function __construct()
    {
        parent::__construct();
        $this->default_index = 'explnums';
    }
    
	public function fillIndex($explnum_id = 0)
	{
	    global $sphinx_indexes_prefix;
	    
	    $explnum_id = (int) $explnum_id;

		$explnums_noti_ids = [];
		$explnums_bull_ids = [];
		$noti_query = 'select explnum_id as id from explnum where explnum_notice != 0 and explnum_bulletin = 0';
		$bull_query = 'select explnum_id as id from explnum join bulletins on bulletin_id =explnum_bulletin where explnum_notice = 0 and explnum_bulletin != 0';
		if (!empty($explnum_id)) {
		    $noti_query .= " and explnum_id = $explnum_id";
		    $bull_query .= " and explnum_id = $explnum_id";
		}
		
		$nb = 0;
		$res = pmb_mysql_query($noti_query);
		$nb_explnums_noti = pmb_mysql_num_rows($res);
		while ($row = pmb_mysql_fetch_array($res)) {
		    $explnums_noti_ids[] = $row[0];
		}
		
		$res = pmb_mysql_query($bull_query);
		$nb_explnums_bull = pmb_mysql_num_rows($res);
		while ($row = pmb_mysql_fetch_array($res)) {
		    $explnums_bull_ids[] = $row[0];
		}
		
		$nb = $nb_explnums_noti + $nb_explnums_bull;
		
	    $noti_query = 'select explnum_id as id, explnum_notice as num_record, explnum_index_wew as content from explnum where explnum_notice != 0 and explnum_bulletin = 0 and explnum_id = ';
		$bull_query = 'select explnum_id as id, if(num_notice,num_notice,bulletin_notice) as num_record, explnum_index_wew as content from explnum join bulletins on bulletin_id =explnum_bulletin where explnum_notice = 0 and explnum_bulletin != 0 and explnum_id = ';
		
		if (empty($explnum_id)) {
		    print ProgressBar::start($nb, "Index ".$this->default_index);
		}
		
		$table = $sphinx_indexes_prefix.'records_explnums';
	    pmb_mysql_query('set session group_concat_max_len = 16777216');
	    
	    if (!empty($explnums_noti_ids)) {
	        for ($i = 0; $i < $nb_explnums_noti; $i++) {
                $query = $noti_query . $explnums_noti_ids[$i];
                $res = pmb_mysql_query($query);
                $object = pmb_mysql_fetch_object($res);
                
                // Purge...
                $dbh = $this->getDBHandler();
                pmb_mysql_query('delete from '.$sphinx_indexes_prefix.'records_explnums where id = '.$object->id, $dbh);
                $query = "insert into $table (id, content, num_record) values ($object->id, '" . addslashes(encoding_normalize::utf8_normalize($object->content)) . "', '$object->num_record')";
                if (!pmb_mysql_query($query, $dbh)) {
                    print "$table : " . pmb_mysql_error($dbh) . "\n $query";
                    die;
                }
                if (empty($explnum_id)) {
                    print ProgressBar::next();
                }
            }
	    }
	    
	    if (!empty($explnums_bull_ids)) {
	        for ($i = 0; $i < $nb_explnums_bull; $i++) {
	            $query = $bull_query . $explnums_bull_ids[$i];
	            $res = pmb_mysql_query($query);
	            $object = pmb_mysql_fetch_object($res);
	            
	            // Purge...
	            $dbh = $this->getDBHandler();
	            pmb_mysql_query('delete from '.$sphinx_indexes_prefix.'records_explnums where id = '.$object->id, $dbh);
	            $query = "insert into $table (id, content, num_record) values ($object->id, '" . addslashes(encoding_normalize::utf8_normalize($object->content)) . "', '$object->num_record')";
	            if (!pmb_mysql_query($query, $dbh)) {
	                print "$table : " . pmb_mysql_error($dbh) . "\n $query";
	                die;
                }
                if (empty($explnum_id)) {
                    print ProgressBar::next();
                }
	        }
	    }
	    
	    if (empty($explnum_id)) {
	        print ProgressBar::finish();
	    }
	}
	


	public function getIndexConfFile()
	{
	    global $sphinx_indexes_path;
	    global $sphinx_indexes_prefix;
	    $index_name = $sphinx_indexes_prefix.'records_explnums';
		$conf = '
########################################
#   PMB AUTOMATIC INDEX CONSTRUCTION   #
########################################';
	
		$conf.= '
index '.$index_name.'
{
	type = rt
	path = '.str_replace('//', '/', $sphinx_indexes_path.'/'.$index_name).'
	min_infix_len = 3
	expand_keywords = 1
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
	
	#fields definition
	rt_field = content
	rt_field = num_record
	#attribute definition
	rt_attr_uint = num_record
}';
		return $conf;
	}
}