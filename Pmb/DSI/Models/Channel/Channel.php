<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Channel.php,v 1.5.2.3 2023/05/25 12:33:14 jparis Exp $

namespace Pmb\DSI\Models\Channel;

interface Channel
{
    public const CHANNEL_REQUIREMENTS = array(
        "subscribers" => array()
    );

	public function send($subscriberList, $renderedView, $diffusion = null);
}

