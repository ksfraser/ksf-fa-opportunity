<?php

declare(strict_types=1);

/**
 * ksf_FA_Opportunity — pages/opportunities.php. Deprecated forwarder shim.
 *
 * This page moved into the single front controller ../opportunity.php on the
 * controller/refactor. The page body now lives in
 * src/Ksfraser/FrontAccounting/Opportunity/Controller/OpportunityTabController.php
 * and is reached as:
 *
 *     ../opportunity.php?view=opportunities
 *
 * This shim exists for one release so existing bookmarks and any hard-coded
 * links keep resolving. It forwards internally rather than redirecting, so an
 * in-flight POST keeps its body. Remove it in the next major version.
 *
 * Security: SA_OPPORTUNITY_VIEW — now enforced by opportunity.php from the tab's
 * registered security area (see ../hooks.php::install_access).
 *
 * @package ksf_FA_Opportunity
 * @since   2.0.0
 * @deprecated 2.0.0 Forwarder to ../opportunity.php?view=opportunities
 */

$_GET['view'] = 'opportunities';

// Point self/uri at the canonical controller so the rendered links and form
// actions converge on opportunity.php instead of bouncing through this shim.
if (isset($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = preg_replace('#/pages/opportunities\.php$#', '/opportunity.php', $_SERVER['PHP_SELF']);
}
if (isset($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = preg_replace('#/pages/opportunities\.php$#', '/opportunity.php', $_SERVER['SCRIPT_NAME']);
}
if (isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = preg_replace(
        '#/pages/opportunities\.php(\?|$)#',
        '/opportunity.php$1',
        $_SERVER['REQUEST_URI']
    );
}

// opportunity.php bootstraps FA with $path_to_root = '../../..', which PHP resolves
// against the MAIN script's directory. This shim is one level deeper (pages/),
// so move to the module root first or the include of
// ../../includes/session.inc lands above the FA root and fatals.
chdir(__DIR__ . '/..');

require __DIR__ . '/../opportunity.php';
