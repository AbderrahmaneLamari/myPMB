<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationStatusOrm.php,v 1.7 2021/04/01 15:13:11 qvarin Exp $

namespace Pmb\Animations\Orm;

use Pmb\Common\Orm\Orm;

class AnimationStatusOrm extends Orm
{

    /**
     * Table name
     *
     * @var string
     */
    public static $tableName = "anim_status";

    /**
     * Primary Key
     *
     * @var string
     */
    public static $idTableName = "id_status";
    
    /**
     *
     * @var \ReflectionClass
     */
    protected static $reflectionClass = null;

    /**
     *
     * @var integer
     */
    protected $id_status = 0;

    /**
     *
     * @var string
     */
    protected $label = "";
    
    /**
     *
     * @var string
     */
    protected $color = "";
    
    /**
     *
     * @Relation n0
     * @Orm Pmb\Animations\Orm\AnimationOrm
     * @Table anim_animations
     * @RelatedKey id_animation
     * @ForeignKey num_status
     */
    protected $animations = null;
}