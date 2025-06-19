<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationTypesOrm.php,v 1.2 2022/01/18 09:44:24 qvarin Exp $

namespace Pmb\Animations\Orm;

use Pmb\Common\Orm\Orm;

class AnimationTypesOrm extends Orm
{
    /**
     * Table name
     *
     * @var string
     */
    public static $tableName = "anim_types";
    
    /**
     * Primary Key
     *
     * @var string
     */
    public static $idTableName = "id_type";
    
    /**
     *
     * @var \ReflectionClass
     */
    protected static $reflectionClass = null;
    
    /**
     *
     * @var integer
     */
    protected $id_type = 0;
    
    /**
     *
     * @var string
     */
    protected $label = "";
    
    /**
     *
     * @Relation n0
     * @Orm Pmb\Animations\Orm\AnimationOrm
     * @Table anim_animations
     * @RelatedKey id_animation
     * @ForeignKey num_type
     */
    protected $animations = null;
}