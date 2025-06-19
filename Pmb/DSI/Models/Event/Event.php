<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Event.php,v 1.2.4.1 2023/03/15 13:54:56 jparis Exp $

namespace Pmb\DSI\Models\Event;

interface Event
{
	public function trigger();
}

