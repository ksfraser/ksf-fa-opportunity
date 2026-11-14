<?php

declare(strict_types=1);

/**
 * KSF FrontAccounting Module Hooks — ksf_FA_Opportunity
 *
 * Opportunity is the owning module for opportunity/pipeline data spun off
 * from CRM Opportunities tab.
 *
 * @package KsfFA_ksf_FA_Opportunity
 * @version 1.0.0
 */

require_once __DIR__ . '/ComposerDependencies.php';

\ksfraser\FrontAccounting\Opportunity\Utils\ComposerDependencies::ensure(__DIR__);

$autoload_path = dirname(__FILE__) . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

require_once __DIR__ . '/includes/common.inc';
require_once __DIR__ . '/includes/events.inc';

use ksfraser\RelatedTo\DTO\RelatedQueryDto;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;

define('SS_ksf_FA_Opportunity', 156 << 8);

if (!defined('SA_OPPORTUNITY_VIEW')) {
    define('SA_OPPORTUNITY_VIEW', SS_ksf_FA_Opportunity | 1);
    define('SA_OPPORTUNITY_MANAGE', SS_ksf_FA_Opportunity | 2);
}

class hooks_ksf_FA_Opportunity extends hooks {
    const RESPONDERS = array('RELATED_TO_FIELDS', 'OPPORTUNITY_RESOLVE', 'OPPORTUNITY_FIELDS');

    var $module_name = 'ksf_FA_Opportunity';
    var $version = '1.0.0';

    function install_tabs($app) { }
    function install_options($app) { }

    function install_access() {
        $security_sections[SS_ksf_FA_Opportunity] = _("Opportunity Management");
        $security_areas['SA_OPPORTUNITY_VIEW'] = array(SA_OPPORTUNITY_VIEW, _("View opportunities"));
        $security_areas['SA_OPPORTUNITY_MANAGE'] = array(SA_OPPORTUNITY_MANAGE, _("Manage opportunities"));
        return array($security_areas, $security_sections);
    }

    function activate_extension($company, $check_only = true) {
        if ($check_only) { return true; }
        \ksfraser\FrontAccounting\Opportunity\Utils\ComposerDependencies::ensure(__DIR__);
        $this->update_databases($company, array(
            '0_ksf_crm_opportunities.sql' => array('ksf_crm_opportunities'),
        ), false);
        return true;
    }

    private function ensure_composer_dependencies() {
        \ksfraser\FrontAccounting\Opportunity\Utils\ComposerDependencies::ensure(__DIR__);
    }

    public function getModuleConstants(&$data, $opts = null) {
        $data['constants'] = array(
            'SS_ksf_FA_Opportunity' => SS_ksf_FA_Opportunity,
            'SA_OPPORTUNITY_VIEW' => SA_OPPORTUNITY_VIEW,
            'SA_OPPORTUNITY_MANAGE' => SA_OPPORTUNITY_MANAGE,
        );
        return true;
    }

    public function getModuleCapabilities(&$data, $opts = null) {
        $data['capabilities'] = self::RESPONDERS;
        return true;
    }

    public function hasCapability(&$data, $opts = null) {
        $capability = is_array($opts) && isset($opts['capability']) ? $opts['capability'] : null;
        if ($capability === null && isset($data['capability'])) {
            $capability = $data['capability'];
        }
        $data['has_capability'] = in_array($capability, self::RESPONDERS, true);
        return $data['has_capability'];
    }

    public function respondToCapabilityRequest(&$data, $opts = null) {
        $request = is_array($opts) && isset($opts['request'])
            ? $opts['request']
            : (isset($data['request']) ? $data['request'] : 'capabilities');
        $data['request'] = $request;
        $data['module'] = $this->module_name;
        if (strpos($request, 'has:') === 0) {
            return $this->hasCapability($data, array('capability' => substr($request, 4)));
        }
        switch ($request) {
            case 'capabilities':
                return $this->getModuleCapabilities($data, $opts);
            case 'constants':
                return $this->getModuleConstants($data, $opts);
            default:
                $data['error'] = 'Unknown request type: ' . $request;
                return null;
        }
    }

    public function RELATED_TO_FIELDS(&$data, $opts = null) {
        if (!isset($data['module']) || !is_string($data['module'])) {
            $data['error'] = 'RELATED_TO_FIELDS requires data[module]';
            return null;
        }
        if (!isset($data['type']) || !is_string($data['type'])) {
            $data['error'] = 'RELATED_TO_FIELDS requires data[type]';
            return null;
        }
        if ($data['type'] !== 'opportunity') { return null; }
        if (!isset($data['fields']) || !is_array($data['fields'])) {
            $data['fields'] = array();
        }
        $data['fields'] = array_merge($data['fields'], array(
            'opportunityName' => _('Opportunity Name'),
            'stage'           => _('Stage'),
            'amount'          => _('Amount'),
            'probability'     => _('Probability'),
            'expectedClose'   => _('Expected Close'),
        ));
        return null;
    }

    public function OPPORTUNITY_RESOLVE(&$data, $opts = null) {
        if (!isset($data['opportunity_id']) || !is_numeric($data['opportunity_id'])) {
            $data['error'] = 'OPPORTUNITY_RESOLVE requires data[opportunity_id]';
            return null;
        }
        if (!isset($data['dto']) || !$data['dto'] instanceof RelatedRecordDto) {
            $data['error'] = 'OPPORTUNITY_RESOLVE requires data[dto] as RelatedRecordDto';
            return null;
        }
        $service = new \Ksfraser\FrontAccounting\Opportunity\Service\OpportunityService();
        $data['result'] = $service->resolveById((int) $data['opportunity_id'], $data['dto']);
        return null;
    }

    public function OPPORTUNITY_FIELDS(&$data, $opts = null) {
        $data['fields'] = array(
            'opportunityName' => _('Opportunity Name'),
            'stage'           => _('Stage'),
            'amount'          => _('Amount'),
            'probability'     => _('Probability'),
            'expectedClose'   => _('Expected Close'),
        );
        return null;
    }
}
