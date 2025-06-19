<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SubscribersProductOrm.php,v 1.4.2.1 2023/03/24 09:34:04 rtigero Exp $
namespace Pmb\DSI\Orm;

use Pmb\Common\Orm\Orm;

class SubscribersProductOrm extends Orm
{

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "dsi_subscribers_product";

	/**
	 * Primary Key
	 *
	 * @var string
	 */
	public static $idTableName = "id_subscribers_product";
	
	/**
	 *
	 * @var integer
	 */
	protected $id_subscribers_product = 0;
	
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
	protected $num_product = 0;

	/**
	 *
	 * @Relation 0n
	 * @Orm Pmb\DSI\Orm\ProductOrm
	 * @RelatedKey num_product
	 */
	protected $product = null;

	/**
	 *
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass = null;
}