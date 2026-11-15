<?php

declare(strict_types=1);

use Ksfraser\FrontAccounting\Opportunity\Service\OpportunityService;
use ksfraser\RelatedTo\DTO\RelatedQueryDto;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
use ksfraser\RelatedTo\UI\DefaultRelatedRecordsRenderer;

$path_to_root = '../../..';

include_once($path_to_root . '/includes/session.inc');
add_access_extensions();

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    display_error(_('Opportunity module dependencies are not installed'));
    exit;
}
require_once $autoload;

$service = new OpportunityService();
$action = isset($_GET['action']) ? (string) $_GET['action'] : 'list';

if ($action === 'list') {
    page(_('Opportunities'));
    $opps = $service->listAll();
    echo '<p>' . _('Opportunities') . '</p>';
    echo '<table class="selection">';
    echo '<thead><tr>'
        . '<th>' . _('Opportunity') . '</th>'
        . '<th>' . _('Stage') . '</th>'
        . '<th>' . _('Amount') . '</th>'
        . '<th>' . _('Probability') . '</th>'
        . '<th>' . _('Expected Close') . '</th>'
        . '<th>' . _('Actions') . '</th>'
        . '</tr></thead><tbody>';
    foreach ($opps as $opp) {
        echo '<tr>'
            . '<td>' . htmlspecialchars($opp['label'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . htmlspecialchars($opp['fields']['stage'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . htmlspecialchars($opp['fields']['amount'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . htmlspecialchars($opp['fields']['probability'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . htmlspecialchars($opp['fields']['expectedClose'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td><a href="' . htmlspecialchars($opp['url'], ENT_QUOTES, 'UTF-8') . '" target="_blank">'
            . _('Edit') . '</a></td>'
            . '</tr>';
    }
    echo '</tbody></table>';

    // Related records rendered via RelatedTo hook
    $lhsId = isset($_GET['id']) ? (string) $_GET['id'] : '';
    if (function_exists('hook_invoke') && class_exists(DefaultRelatedRecordsRenderer::class) && $lhsId !== '') {
        $queryDto = new RelatedQueryDto('opportunity', $lhsId);
        $queryResult = $queryDto;
        hook_invoke('ksf_FA_RelatedTo', 'RELATED_TO', $queryResult, null);
        if (is_array($queryResult) && isset($queryResult['related']) && is_array($queryResult['related'])) {
            $renderer = new DefaultRelatedRecordsRenderer(array(
                'label_resolver' => function ($type, $id) { return (string) $id; },
                'url_resolver'   => function ($type, $id) { return '#'; },
                'type_labels'    => array('opportunity' => _('Opportunities')),
            ));
            echo $renderer->renderRelatedRecords('opportunity', $lhsId, $queryResult['related'], array(
                'css_class' => 'related-records',
                'header_class' => 'related-header',
                'table_class' => 'related-table',
                'table_border' => true,
            ));
        }
    }

    end_page();
    exit;
}

display_error(_('Unknown action'));
exit;
