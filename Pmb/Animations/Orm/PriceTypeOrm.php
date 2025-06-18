<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: PriceTypeOrm.php,v 1.8 2020/10/06 08:39:19 qvarin Exp $

namespace Pmb\Animations\Orm;

use Pmb\Common\Orm\Orm;

class PriceTypeOrm extends Orm
{
    /**
     * Table name
     *
     * @var string
     */
    public static $tableName = "anim_price_types";

    /**
     * Primary Key
     *
     * @var string
     */
    public static $idTableName = "id_price_type";
    
    /**
     *
     * @var \ReflectionClass
     */
    protected static $reflectionClass = null;

    /**
     *
     * @var integer
     */
    protected $id_price_type = 0;

    /**
     *
     * @var string
     */
    protected $name = "";

    /**
     *
     * @var float
     */
    protected $default_value = 0.0;
    
    /**
     * Utiliser dans le Front
     * @var boolean
     */
    protected $modeEdition = false;

    /**
     *
     * @Relation n0
     * @Orm Pmb\Animations\Orm\PriceOrm
     * @Table anim_prices
     * @ForeignKey num_price_type
     * @RelatedKey id_price
     */
    protected $prices = null;
}