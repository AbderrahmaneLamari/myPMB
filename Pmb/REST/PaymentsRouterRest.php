<?php

// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: PaymentsRouterRest.php,v 1.1.2.1 2024/01/03 08:44:29 gneveu Exp $

namespace Pmb\REST;

class PaymentsRouterRest extends RouterRest
{
    /**
     *
     * @const string
     */
    protected const CONTROLLER = "\\Pmb\\Payments\\Opac\\Controller\\PaymentsAPIController";

    /**
     *
     * @var string
     */
    public const ALLOW_OPAC = true;

    /**
     *
     * {@inheritdoc}
     * @see \Pmb\REST\RouterRest::generateRoutes()
     */
    protected function generateRoutes()
    {
        $this->get('/{response}', 'returnPayment');
    }
}
