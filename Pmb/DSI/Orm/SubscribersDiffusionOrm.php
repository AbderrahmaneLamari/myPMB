<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SubscribersDiffusionOrm.php,v 1.5 2023/02/08 10:54:18 rtigero Exp $
namespace Pmb\DSI\Orm;

use Pmb\Common\Orm\Orm;

class SubscribersDiffusionOrm extends Orm
{

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "dsi_subscribers_diffusion";

	/**
	 * Primary Key
	 *
	 * @var string
	 */
	public static $idTableName = "id_subscriber_diffusion";

	/**
	 * 
	 * @var integer
	 */
	protected $id_subscriber_diffusion = 0;
	
	/**
	 *
	 * @var string
	 */
	protected $name = "";

	/**
	 *
	 * @var string
	 */
	protected $settings = "";

	/**
	 *
	 * @var integer
	 */
	protected $type = 0;

	/**
	 *
	 * @var integer
	 */
	protected $update_type = 0;

	/**
	 *
	 * @var integer
	 */
	protected $num_diffusion = 0;

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;
}