<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\DTO;

/**
 * Opportunity — immutable value object wrapping 0_ksf_crm_opportunities data.
 *
 * @package Ksfraser\FrontAccounting\Opportunity\DTO
 * @since   1.0.0
 * @BABOK Related: BR-OP-002
 */
final class OpportunityDTO
{
    public $id;
    public $opportunityName;
    public $stage;
    public $amount;
    public $probability;
    public $expectedClose;
    public $customerId;
    public $contactId;
    public $description;
    public $createdAt;

    /**
     * @BABOK Related: BR-OP-002
     */
    public function __construct(
        int $id,
        string $opportunityName = '',
        string $stage = 'prospecting',
        string $amount = '0',
        string $probability = '0',
        string $expectedClose = '',
        string $customerId = '',
        string $contactId = '',
        string $description = '',
        string $createdAt = ''
    ) {
        $this->id             = $id;
        $this->opportunityName= $opportunityName;
        $this->stage          = $stage;
        $this->amount         = $amount;
        $this->probability    = $probability;
        $this->expectedClose  = $expectedClose;
        $this->customerId     = $customerId;
        $this->contactId      = $contactId;
        $this->description    = $description;
        $this->createdAt      = $createdAt;
    }

    /**
     * @BABOK Related: BR-OP-002
     */
    public function getDisplayLabel(): string
    {
        $parts = array();
        if ($this->opportunityName !== '') { $parts[] = $this->opportunityName; }
        if ($this->stage !== '') { $parts[] = $this->stage; }
        if ($this->amount !== '' && $this->amount !== '0') { $parts[] = $this->amount; }
        return $parts === [] ? _('Opportunity #') . (string) $this->id : implode(' — ', $parts);
    }

    /**
     * @BABOK Related: BR-OP-002
     */
    public function toArray(): array
    {
        return array(
            'id'              => $this->id,
            'opportunity_name'=> $this->opportunityName,
            'stage'           => $this->stage,
            'amount'          => $this->amount,
            'probability'     => $this->probability,
            'expected_close'  => $this->expectedClose,
            'customer_id'     => $this->customerId,
            'contact_id'      => $this->contactId,
            'description'     => $this->description,
            'created_at'      => $this->createdAt,
        );
    }

    /**
     * @BABOK Related: BR-OP-002
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (string) ($data['opportunity_name'] ?? ''),
            (string) ($data['stage'] ?? 'prospecting'),
            (string) ($data['amount'] ?? '0'),
            (string) ($data['probability'] ?? '0'),
            (string) ($data['expected_close'] ?? ''),
            (string) ($data['customer_id'] ?? ''),
            (string) ($data['contact_id'] ?? ''),
            (string) ($data['description'] ?? ''),
            (string) ($data['created_at'] ?? '')
        );
    }
}
