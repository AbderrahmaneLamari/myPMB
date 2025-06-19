<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DiffusionStatus.php,v 1.1.4.1 2023/03/08 16:04:59 qvarin Exp $

namespace Pmb\DSI\Models;

class DiffusionStatus extends Status
{
	protected $ormName = "Pmb\DSI\Orm\DiffusionStatusOrm";

	public $idDiffusionStatus;

	public $diffusions;
}

