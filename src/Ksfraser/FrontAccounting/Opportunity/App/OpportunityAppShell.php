<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\App;

use ksfraser\FrontAccounting\Common\App\AbstractAppShell;
use ksfraser\FrontAccounting\Common\App\TabRegistration;
use ksfraser\FrontAccounting\Common\Menu\FAModuleMenu;
use ksfraser\FrontAccounting\Opportunity\Controller\OpportunityTabController;

/**
 * OpportunityAppShell — application shell SRP for the Opportunity module.
 *
 * Registers the Opportunity core tab (opportunities list view), then fires
 * the `opportunity_register_tabs` register-with-me hook on boot() so other
 * modules can register their own tabs into the Opportunity app.
 *
 * @package ksf_FA_Opportunity
 * @since   2.0.0
 *
 * @UML Note: APP_TAB_ARCHITECTURE.md §7/§11 (app host)
 * @BABOK Related: FR-RT-001 (app shell)
 */
class OpportunityAppShell extends AbstractAppShell
{
    public function __construct()
    {
        parent::__construct("Opportunity", _("Opportunity"));

        $this->add_module(_("Opportunity"));

        $menu = new FAModuleMenu(
            'modules/ksf_FA_Opportunity/opportunity.php',
            'view',
            ''
        );

        $menu->addItem('opportunities', _("Opportunities"), MENU_MAIN)
             ->registerWithApp($this, 'SA_OPPORTUNITY_VIEW');

        $this->add_extensions();
    }
}
