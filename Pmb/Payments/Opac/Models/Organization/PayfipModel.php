<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: PayfipModel.php,v 1.1.2.1 2024/01/03 08:44:29 gneveu Exp $

namespace Pmb\Payments\Opac\Models;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

use Pmb\Common\Models\Model;

class PayfipModel extends Model
{
}
