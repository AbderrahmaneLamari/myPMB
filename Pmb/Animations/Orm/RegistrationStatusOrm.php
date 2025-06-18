<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RegistrationStatusOrm.php,v 1.5 2020/09/03 08:09:02 gneveu Exp $

namespace Pmb\Animations\Orm;

use Pmb\Common\Orm\Orm;

class RegistrationStatusOrm extends Orm
{
    /**
     * Table name
     *
     * @var string
     */
    public static $tableName = "anim_registration_status";

    /**
     * Primary Key
     *
     * @var string
     */
    public static $idTableName = "id_registration_status";
    
    /**
     *
     * @var \ReflectionClass
     */
    protected static $reflectionClass = null;

    /**
     *
     * @var integer
     */
    protected $id_registration_status = 0;

    /**
     *
     * @var string
     */
    protected $name = "";

    /**
     * @Relation 0n
     * @Orm Pmb\Animations\Orm\RegistrationOrm
     * @ForeignKey num_registration_status
     * @RelatedKey id_registration_status
     */
    protected $registration = null;
}