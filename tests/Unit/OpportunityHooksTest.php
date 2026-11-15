<?php
declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Tests\Unit;

use ksfraser\RelatedTo\DTO\RelatedRecordDto;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../hooks.php';

/**
 * Tests for \hooks_ksf_FA_Opportunity responders (RELATED_TO_FIELDS,
 * OPPORTUNITY_RESOLVE, OPPORTUNITY_FIELDS).
 *
 * @package Ksfraser\FrontAccounting\Opportunity\Tests\Unit
 * @since   1.0.0
 */
class OpportunityHooksTest extends TestCase
{
    /** @var \hooks_ksf_FA_Opportunity */
    private $hooks;

    protected function setUp(): void
    {
        $this->hooks = new \hooks_ksf_FA_Opportunity();
        $GLOBALS['KSF_HOOK_HANDLER'] = null;
        $GLOBALS['KSF_DB_RESULT']   = false;
        $GLOBALS['KSF_DB_ROWS']     = array();
    }

    private function data(array $overrides = array()): array
    {
        return array_merge(array(
            'module' => 'ksf_FA_Lead',
            'type'   => 'opportunity',
            'fields' => array(),
        ), $overrides);
    }

    public function testRespondersDeclared(): void
    {
        $this->assertContains('RELATED_TO_FIELDS', \hooks_ksf_FA_Opportunity::RESPONDERS);
        $this->assertContains('OPPORTUNITY_RESOLVE', \hooks_ksf_FA_Opportunity::RESPONDERS);
        $this->assertContains('OPPORTUNITY_FIELDS', \hooks_ksf_FA_Opportunity::RESPONDERS);
    }

    public function testRelatedToFieldsSetsOpportunityFields(): void
    {
        $data = $this->data(array('type' => 'opportunity'));
        $this->hooks->RELATED_TO_FIELDS($data);

        $this->assertSame(array(
            'opportunityName' => 'Opportunity Name',
            'stage'           => 'Stage',
            'amount'          => 'Amount',
            'probability'     => 'Probability',
            'expectedClose'   => 'Expected Close',
        ), $data['fields']);
    }

    public function testRelatedToFieldsIgnoresNonOpportunityType(): void
    {
        $data = $this->data(array('type' => 'lead'));
        $this->hooks->RELATED_TO_FIELDS($data);

        $this->assertSame(array(), $data['fields']);
    }

    public function testOpportunityFieldsReturnsFieldMap(): void
    {
        $data = array();
        $this->hooks->OPPORTUNITY_FIELDS($data);

        $this->assertSame(array(
            'opportunityName' => 'Opportunity Name',
            'stage'           => 'Stage',
            'amount'          => 'Amount',
            'probability'     => 'Probability',
            'expectedClose'   => 'Expected Close',
        ), $data['fields']);
    }

    public function testOpportunityResolveNotFoundSetsResult(): void
    {
        $dto = new RelatedRecordDto('opportunity', '3', 'opportunity', '3', null);
        $data = array('opportunity_id' => 3, 'dto' => $dto);

        $this->hooks->OPPORTUNITY_RESOLVE($data);

        $this->assertArrayHasKey('result', $data);
        $result = $data['result'];
        $this->assertIsArray($result);
        $this->assertInstanceOf(RelatedRecordDto::class, $result['dto']);
        $this->assertSame('opportunity', $result['dto']->lhsType);
        $this->assertSame('0', $result['dto']->lhsId);
        $this->assertSame('#', $result['url']);
        $this->assertSame('Opportunity #3', $result['label']);
        $this->assertSame(array(
            'opportunityName' => 'Opportunity Name',
            'stage'           => 'Stage',
            'amount'          => 'Amount',
            'probability'     => 'Probability',
            'expectedClose'   => 'Expected Close',
        ), $result['fields']);
    }

    public function testOpportunityResolveFoundSetsPopulatedResult(): void
    {
        $GLOBALS['KSF_DB_RESULT'] = true;
        $GLOBALS['KSF_DB_ROWS']   = array(array(
            'id'              => 8,
            'opportunity_name'=> 'Widget deal',
            'stage'           => 'negotiation',
            'amount'          => '5000',
            'probability'     => '75',
            'expected_close'  => '2026-03-31',
            'customer_id'     => 'C100',
            'contact_id'      => 'P5',
            'description'     => 'Widgets for Acme',
            'created_at'      => '',
        ));

        $dto = new RelatedRecordDto('opportunity', '8', 'opportunity', '8', null);
        $data = array('opportunity_id' => 8, 'dto' => $dto);

        $this->hooks->OPPORTUNITY_RESOLVE($data);

        $result = $data['result'];
        $this->assertSame('8', $result['dto']->lhsId);
        $this->assertSame('Widget deal', $result['fields']['opportunityName']);
        $this->assertSame('negotiation', $result['fields']['stage']);
        $this->assertSame('5000', $result['fields']['amount']);
        $this->assertSame('75', $result['fields']['probability']);
        $this->assertStringContainsString('opportunity_id=8', $result['url']);
    }

    public function testOpportunityResolveRequiresOpportunityIdAndDto(): void
    {
        $data = array('dto' => new RelatedRecordDto('opportunity', '1', 'opportunity', '1', null));
        $this->hooks->OPPORTUNITY_RESOLVE($data);
        $this->assertArrayHasKey('error', $data);

        $data = array('opportunity_id' => 1);
        $this->hooks->OPPORTUNITY_RESOLVE($data);
        $this->assertArrayHasKey('error', $data);
    }
}
