<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Service;

use Ksfraser\FrontAccounting\Opportunity\DTO\OpportunityDTO;
use Ksfraser\FrontAccounting\Opportunity\Repository\OpportunityRepository;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;

final class OpportunityService
{
    private $repo;

    public function __construct(?OpportunityRepository $repo = null)
    {
        $this->repo = $repo ?? new OpportunityRepository();
    }

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
