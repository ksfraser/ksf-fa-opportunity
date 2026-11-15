<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Controller;

use ksfraser\FrontAccounting\Common\App\AbstractTabController;
use ksfraser\FrontAccounting\Opportunity\Service\OpportunityService;
use ksfraser\RelatedTo\DTO\RelatedQueryDto;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
use ksfraser\RelatedTo\UI\DefaultRelatedRecordsRenderer;

/**
 * OpportunityTabController — controller SRP for the Opportunity tab.
 *
 * Replaces pages/opportunities.php. Reads go through OpportunityService::listAll();
 * writes go through the service. No SQL in this class.
 *
 * PHP 7.3 compatible.
 *
 * @package ksf_FA_Opportunity
 * @since   2.0.0
 *
 * @UML Note: APP_TAB_ARCHITECTURE.md §11 (tab controller SRP)
 * @BABOK Related: FR-RT-001 (opportunity tab)
 */
class OpportunityTabController extends AbstractTabController
{
    /** @var OpportunityService */
    private $service;

    /**
     * @param mixed                $context DI request state
     * @param array<string, mixed> $options
     *
     * @since 2.0.0
     */
    public function __construct($context = null, array $options = [])
    {
        parent::__construct($context, $options);
        $this->service = new OpportunityService();
    }

    /** {@inheritDoc} */
    protected function getPkField(): string
    {
        return 'id';
    }

    /** {@inheritDoc} */
    protected function getFieldMetadata(): array
    {
        return [
            'entity'      => 'opportunity',
            'table'       => '0_ksf_crm_opportunities',
            'label'       => 'Opportunity',
            'labelPlural' => 'Opportunities',
            'hookPrefix'  => 'opportunity',
            'pk'          => 'id',
            'fields'      => [
                'id' => [
                    'label' => 'ID', 'type' => 'text',
                    'showInForm' => false, 'showInTable' => false,
                ],
                'opportunity_name' => [
                    'label' => 'Opportunity Name', 'type' => 'text',
                    'showInTable' => true, 'showInForm' => true,
                ],
                'stage' => [
                    'label' => 'Stage', 'type' => 'text',
                    'showInTable' => true, 'showInForm' => true,
                ],
                'amount' => [
                    'label' => 'Amount', 'type' => 'number',
                    'showInTable' => true, 'showInForm' => true,
                ],
                'probability' => [
                    'label' => 'Probability', 'type' => 'number',
                    'showInTable' => true, 'showInForm' => true,
                ],
                'expected_close' => [
                    'label' => 'Expected Close', 'type' => 'date',
                    'showInTable' => true, 'showInForm' => true,
                ],
            ],
            'fk_ddls'   => [],
            'ddlHooks'  => [],
            'tableSettings' => [],
        ];
    }

    /** {@inheritDoc} */
    protected function listRows(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return array_slice($this->service->listAll(), $offset, $perPage);
    }

    /** {@inheritDoc} */
    protected function countRows(): int
    {
        return count($this->service->listAll());
    }

    /** {@inheritDoc} */
    protected function findRecord(string $pk): ?array
    {
        return $this->service->resolveById((int) $pk);
    }

    /** {@inheritDoc} */
    protected function createRecord(array $data): int
    {
        return 0; // Not implemented — read-only view
    }

    /** {@inheritDoc} */
    protected function updateRecord(string $pk, array $data): void
    {
        // Not implemented — read-only view
    }

    /** {@inheritDoc} */
    protected function deleteRecord(string $pk): void
    {
        // Not implemented — read-only view
    }

    /** {@inheritDoc} */
    protected function fkOptions(): array
    {
        return [];
    }

    /** {@inheritDoc} */
    protected function renderSummaryTable(): void
    {
        if (!function_exists('start_table')) {
            return;
        }

        global $path_to_root;

        $page = max(1, $this->page());
        $perPage = $this->perPage;
        $rows = $this->listRows($page, $perPage);
        $total = $this->countRows();
        $pageCount = max(1, (int) ceil($total / $perPage));

        start_table(TABLESTYLE2, "width='95%'");
        $th = function (string $label): void {
            echo '<th>' . htmlspecialchars($this->localise($label), ENT_QUOTES) . '</th>';
        };
        echo '<tr>';
        $th(_('Opportunity'));
        $th(_('Stage'));
        $th(_('Amount'));
        $th(_('Probability'));
        $th(_('Expected Close'));
        $th(_('Actions'));
        echo '</tr>';

        if (empty($rows)) {
            echo '<tr><td colspan="6"><em>'
                . htmlspecialchars($this->localise('No opportunities yet.'), ENT_QUOTES)
                . '</em></td></tr>';
        }

        foreach ($rows as $i => $row) {
            $dto = $row['dto'] ?? null;
            $label = $dto ? $dto->getDisplayLabel() : $row['label'] ?? '';
            $url = $dto ? $dto->url : '#';

            echo '<tr class="' . ($i % 2 ? 'evenrow' : 'oddrow') . '">';
            echo '<td>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['stage'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['amount'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['probability'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['expected_close'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">'
                . htmlspecialchars($this->localise('View'), ENT_QUOTES, 'UTF-8') . '</a></td>'
            . '</tr>';
        }

        // Pager
        echo '<tr class="navibar"><td colspan="6"><div style="float:right;">';
        echo $this->pagerLink(1, _('First'), $page > 1, $page);
        echo $this->pagerLink($page - 1, _('Prev'), $page > 1, $page);
        echo $this->pagerLink($page + 1, _('Next'), $page < $pageCount, $page);
        echo $this->pagerLink($pageCount, _('Last'), $page < $pageCount, $page);
        echo '</div></td></tr>';

        end_table(0);
    }

    /** {@inheritDoc} */
    protected function renderNotes(): void
    {
        // Render related records via RelatedTo hook for the context record
        $lhsId = isset($_GET['id']) ? (string) $_GET['id'] : '';
        if ($lhsId === '' || !function_exists('hook_invoke')) {
            return;
        }

        $queryDto = new RelatedQueryDto('opportunity', $lhsId);
        $queryResult = $queryDto;
        hook_invoke('ksf_FA_RelatedTo', 'RELATED_TO', $queryResult, null);

        if (!is_array($queryResult) || !isset($queryResult['related']) || !is_array($queryResult['related'])) {
            return;
        }

        $renderer = new DefaultRelatedRecordsRenderer(array(
            'css_class' => 'related-records',
            'header_class' => 'related-header',
            'table_class' => 'related-table',
            'table_border' => true,
            'type_labels' => array('opportunity' => _('Opportunities')),
        ));
        echo $renderer->renderRelatedRecords('opportunity', $lhsId, $queryResult['related'], array(
            'css_class' => 'related-records',
            'header_class' => 'related-header',
            'table_class' => 'related-table',
            'table_border' => true,
        ));
    }
}
