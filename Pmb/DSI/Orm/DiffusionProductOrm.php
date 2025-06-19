<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DiffusionProductOrm.php,v 1.1 2022/11/09 08:10:04 rtigero Exp $
namespace Pmb\DSI\Orm;

use Pmb\Common\Orm\OrmManyToMany;

class DiffusionProductOrm extends OrmManyToMany
{

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "dsi_diffusion_product";

	/**
	 *
	 * @var integer
	 */
	protected $num_diffusion = 0;

	/**
	 *
	 * @var integer
	 */
	protected $num_product = 0;

	/**
	 *
	 * @var integer
	 */
	protected $active = 0;

	/**
	 *
	 * @var \DateTime
	 */
	protected $last_diffusion = "";

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;
}