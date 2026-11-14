<?php
declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Tests\Unit;

use Ksfraser\FrontAccounting\Opportunity\DTO\OpportunityDTO;
use PHPUnit\Framework\TestCase;

class OpportunityDtoTest extends TestCase
{
    public function testGetDisplayLabel(): void
    {
        $dto = new OpportunityDTO(7, 'Big Deal', 'negotiation', '50000', '75', '2026-12-01', 'C001', '12', 'Description', '');
        $this->assertSame('Big Deal — negotiation — 50000', $dto->getDisplayLabel());
    }

    public function testGetDisplayLabelMinimal(): void
    {
        $dto = new OpportunityDTO(3, '', '', '', '', '', '', '', '', '');
        $this->assertSame('Opportunity #3', $dto->getDisplayLabel());
    }

    public function testToArrayShape(): void
    {
        $dto = new OpportunityDTO(7, 'Big Deal', 'negotiation', '50000', '75', '2026-12-01', 'C001', '12', 'Description', '');
        $array = $dto->toArray();
        $this->assertSame(7, $array['id']);
        $this->assertSame('Big Deal', $array['opportunity_name']);
        $this->assertSame('negotiation', $array['stage']);
        $this->assertSame('50000', $array['amount']);
    }

    public function testFromArrayReconstructs(): void
    {
        $dto = OpportunityDTO::fromArray(array(
            'id' => 9, 'opportunity_name' => 'Small Deal', 'stage' => 'prospecting',
            'amount' => '10000', 'probability' => '20', 'expected_close' => '2026-11-01',
            'customer_id' => 'C002', 'contact_id' => '5', 'description' => 'Notes', 'created_at' => '2026-01-01'
        ));
        $this->assertSame(9, $dto->id);
        $this->assertSame('Small Deal', $dto->opportunityName);
        $this->assertSame('prospecting', $dto->stage);
        $this->assertSame('10000', $dto->amount);
        $this->assertSame('20', $dto->probability);
    }
}
