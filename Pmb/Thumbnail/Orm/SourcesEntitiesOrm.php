<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SourcesEntitiesOrm.php,v 1.2.4.1 2023/08/31 12:56:45 qvarin Exp $
namespace Pmb\Thumbnail\Orm;

use Pmb\Common\Orm\Orm;

class SourcesEntitiesOrm extends Orm
{

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $tableName = "thumbnail_sources_entities";

	/**
	 * Primary Key
	 *
	 * @var string
	 */
	public static $idTableName = "id";

	/**
	 * 
	 * @var integer
	 */
	protected $id = 0;

	/**
	 * 
	 * @var string
	 */
	protected $source_class = "";

	/**
	 * 
	 * @var string
	 */
	protected $pivot_class = "";

	/**
	 * 
	 * @var integer
	 */
	protected $type = 0;

	/**
	 * 
	 * @var string
	 */
	protected $pivot = "";

	/**
	 * 
	 * @var integer
	 */
	protected $ranking = 0;
	
}