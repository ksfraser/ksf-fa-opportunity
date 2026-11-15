<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\App;

use ksfraser\FrontAccounting\Common\App\AbstractFrontController;

/**
 * OpportunityFrontController — single front controller for the Opportunity module.
 *
 * Extends AbstractFrontController with FA module URL options (`view` canonical,
 * `tab` alias, no $_GET normalisation, ui.inc already loaded).
 *
 * @package ksf_FA_Opportunity
 * @since   2.0.0
 *
 * @UML Note: APP_TAB_ARCHITECTURE.md §11 (host page = thin router)
 * @BABOK Related: FR-RT-001 (app shell)
 */
class OpportunityFrontController extends AbstractFrontController
{
    public function __construct(
        OpportunityAppShell $appShell,
        string $fallbackSecurityArea,
        string $path_to_root
    ) {
        parent::__construct($appShell, $fallbackSecurityArea, $path_to_root);
    }
}
