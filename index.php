<?php
/**
 * ksf_FA_Opportunity — single front controller for the Opportunity module.
 *
 * This file is the whole preamble. Everything it used to do by hand — resolving
 * the tab from `view`, resolving the per-tab security area, booting the shell
 * so `opportunity_register_tabs` can contribute tabs, rendering the sub-menu,
 * enforcing access and dispatching to the tab controller — now lives in
 * `ksfraser\FrontAccounting\Common\App\AbstractFrontController` (package
 * `ksfraser/ksf-fa-common`), driven by
 * `ksfraser\FrontAccounting\Opportunity\App\OpportunityFrontController`.
 *
 * URL shape
 * ---------
 *   opportunity.php                                default tab (opportunity list)
 *   opportunity.php?view=opportunities             read a tab (canonical)
 *   opportunity.php?tab=opportunities              the same tab, house-standard alias
 *   opportunity.php?view=opportunities&page=3      tab + pager position
 *
 * `view` stays the canonical parameter for Opportunity.
 *
 * This file is TWO levels below the FA root, which is why $path_to_root is
 * '../..' — unlike pages/*.php, which are three levels down.
 *
 * @package ksf_FA_Opportunity
 * @since   2.0.0
 *
 * @UML Note: APP_TAB_ARCHITECTURE.md §11 (host page = thin router)
 * @BABOK Related: FR-RT-001 (app shell)
 */

chdir(__DIR__);

if (file_exists(__DIR__ . '/bootstrap.php')) {
    require_once __DIR__ . '/bootstrap.php';
}

$path_to_root = "../..";

// Default view when none specified — show the opportunity list
if (!isset($_GET['view']) && !isset($_GET['tab'])) {
    $_GET['view'] = 'opportunities';
}

// The fallback security area is a REQUIRED constructor argument and is never
// empty: it is what makes access enforcement fail closed for a tab contributed
// by another module that registers without an area of its own.
$frontController = new \Ksfraser\FrontAccounting\Opportunity\App\OpportunityFrontController(
    new \Ksfraser\FrontAccounting\Opportunity\App\OpportunityAppShell(),
    'SA_OPPORTUNITY_VIEW',
    $path_to_root
);

// Phase A (global scope): the area must be known BEFORE session.inc, and it can
// only come from the tabs registered at shell-construction time.
$page_security = $frontController->getSecurityArea();
include_once($path_to_root . "/includes/session.inc");
add_access_extensions();

// FA's bootstrap loads only ui_view/ui_controls/ui_msgs. ui_input.inc and
// ui_lists.inc must be pulled in by the page, or every helper the tab
// controllers use — text_row(), amount_row(), date_row(),
// array_selector_row(), submit_row(), the customer_list_row filter combo — is
// an undefined function.
//
// This include has to happen HERE, at global scope, and OpportunityFrontController
// therefore turns the base class's own load_ui_includes step off. includes/ui.inc
// reads `$path_to_root` as a global in its including scope; it is included here,
// at global scope, where the value is correct.
include_once($path_to_root . "/includes/ui.inc");

// Phase B: boot() + re-resolve, page and menu render, the single access check,
// then dispatch.
$frontController->run();
