<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Service;

use Ksfraser\FrontAccounting\Opportunity\DTO\OpportunityDTO;
use Ksfraser\FrontAccounting\Opportunity\Repository\OpportunityRepository;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;

/**
 * OpportunityService — use-case layer for opportunity resolution and display.
 *
 * Composes OpportunityRepository and builds resolved DTOs for RelatedTo display.
 * Opportunity is the owning module for opportunity data; consuming modules
 * override by registering their own OPPORTUNITY_RESOLVE listener.
 *
 * @package Ksfraser\FrontAccounting\Opportunity\Service
 * @since   1.0.0
 * @BABOK Related: BR-OP-001
 */
final class OpportunityService
{
    private $repo;

    /**
     * @BABOK Related: BR-OP-001
     */
    public function __construct(?OpportunityRepository $repo = null)
    {
        $this->repo = $repo ?? new OpportunityRepository();
    }

    /**
     * @BABOK Related: BR-OP-001
     */
    public function resolveById(int $id, ?RelatedRecordDto $dto = null): array
    {
        $row = $this->repo->findById($id);
        if ($row === null) {
            return array(
                'dto' => new RelatedRecordDto('opportunity', '0', 'opportunity', '0', null),
                'fields' => $this->fieldMap(),
                'labels' => $this->labelMap(),
                'url' => '#',
                'label' => _('Opportunity #') . (string) $id,
            );
        }
        $opp = OpportunityDTO::fromArray($row);
        $dto = $dto ?? new RelatedRecordDto(
            'opportunity', (string) $opp->id, 'opportunity', (string) $opp->id, null
        );
        return array(
            'dto'    => $dto,
            'fields' => array(
                'opportunityName' => $opp->opportunityName,
                'stage'           => $opp->stage,
                'amount'          => $opp->amount,
                'probability'     => $opp->probability,
                'expectedClose'   => $opp->expectedClose,
            ),
            'labels' => $this->labelMap(),
            'url'    => '/modules/ksf_FA_Opportunity/pages/opportunity_form.php?action=edit&opportunity_id=' . $opp->id,
            'label'  => $opp->getDisplayLabel(),
        );
    }

    /**
     * @BABOK Related: BR-OP-003
     */
    public function listAll(): array
    {
        $rows = $this->repo->findAll();
        $results = array();
        foreach ($rows as $row) {
            $opp = OpportunityDTO::fromArray($row);
            $results[] = array(
                'id'    => $opp->id,
                'dto'   => new RelatedRecordDto('opportunity', (string) $opp->id, 'opportunity', (string) $opp->id, null),
                'fields' => array(
                    'opportunityName' => $opp->opportunityName,
                    'stage'           => $opp->stage,
                    'amount'          => $opp->amount,
                    'probability'     => $opp->probability,
                    'expectedClose'   => $opp->expectedClose,
                ),
                'labels' => $this->labelMap(),
                'url'    => '/modules/ksf_FA_Opportunity/pages/opportunity_form.php?action=edit&opportunity_id=' . $opp->id,
                'label'  => $opp->getDisplayLabel(),
            );
        }
        return $results;
    }

    /**
     * @BABOK Related: BR-OP-003
     */
    public function fieldMap(): array { return $this->labelMap(); }
    public function labelMap(): array
    {
        return array(
            'opportunityName' => _('Opportunity Name'),
            'stage'           => _('Stage'),
            'amount'          => _('Amount'),
            'probability'     => _('Probability'),
            'expectedClose'   => _('Expected Close'),
        );
    }
}
