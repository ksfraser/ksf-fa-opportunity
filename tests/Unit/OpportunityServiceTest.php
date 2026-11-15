<?php
declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Tests\Unit;

use Ksfraser\FrontAccounting\Opportunity\Repository\OpportunityRepository;
use Ksfraser\FrontAccounting\Opportunity\Service\OpportunityService;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
use PHPUnit\Framework\TestCase;

/**
 * Tests for OpportunityService backed by the real repository and stubbed
 * FA db_* functions.
 *
 * @package Ksfraser\FrontAccounting\Opportunity\Tests\Unit
 * @since   1.0.0
 */
class OpportunityServiceTest extends TestCase
{
    /** @var OpportunityService */
    private $service;

    protected function setUp(): void
    {
        $this->service = new OpportunityService(new OpportunityRepository());
        $GLOBALS['KSF_DB_RESULT']   = false;
        $GLOBALS['KSF_DB_ROWS']     = array();
    }

    private function oppRow(array $overrides = array()): array
    {
        return array_merge(array(
            'id'              => 1,
            'opportunity_name'=> 'Widget deal',
            'stage'           => 'negotiation',
            'amount'          => '5000',
            'probability'     => '75',
            'expected_close'  => '2026-03-31',
            'customer_id'     => 'C100',
            'contact_id'      => 'P5',
            'description'     => 'Widgets for Acme',
            'created_at'      => '',
        ), $overrides);
    }

    public function testResolveByIdNotFoundReturnsEmptyStructure(): void
    {
        $result = $this->service->resolveById(999);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('dto', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertInstanceOf(RelatedRecordDto::class, $result['dto']);
        $this->assertSame('opportunity', $result['dto']->lhsType);
        $this->assertSame('0', $result['dto']->lhsId);
        $this->assertSame('#', $result['url']);
        $this->assertSame('Opportunity #999', $result['label']);
        $this->assertSame(array(
            'opportunityName' => 'Opportunity Name',
            'stage'           => 'Stage',
            'amount'          => 'Amount',
            'probability'     => 'Probability',
            'expectedClose'   => 'Expected Close',
        ), $result['fields']);
        $this->assertSame($result['fields'], $result['labels']);
    }

    public function testResolveByIdFoundReturnsPopulatedStructure(): void
    {
        $GLOBALS['KSF_DB_RESULT'] = true;
        $GLOBALS['KSF_DB_ROWS']   = array($this->oppRow(array(
            'opportunity_name'=> 'Widget deal',
            'stage'           => 'closed_won',
            'amount'          => '5000',
            'probability'     => '100',
            'expected_close'  => '2026-03-31',
        )));

        $result = $this->service->resolveById(1);

        $this->assertInstanceOf(RelatedRecordDto::class, $result['dto']);
        $this->assertSame('1', $result['dto']->lhsId);
        $this->assertSame('Widget deal', $result['fields']['opportunityName']);
        $this->assertSame('closed_won', $result['fields']['stage']);
        $this->assertSame('5000', $result['fields']['amount']);
        $this->assertSame('100', $result['fields']['probability']);
        $this->assertSame('2026-03-31', $result['fields']['expectedClose']);
        $this->assertStringContainsString('opportunity_id=1', $result['url']);
        $this->assertSame('Widget deal — closed_won — 5000', $result['label']);
    }

    public function testListAllReturnsEmptyOutsideFA(): void
    {
        $this->assertSame(array(), $this->service->listAll());
    }

    public function testListAllReturnsRows(): void
    {
        $GLOBALS['KSF_DB_RESULT'] = true;
        $GLOBALS['KSF_DB_ROWS']   = array(
            $this->oppRow(array('id' => 1, 'opportunity_name' => 'Alpha')),
            $this->oppRow(array('id' => 2, 'opportunity_name' => 'Beta')),
        );

        $results = $this->service->listAll();

        $this->assertCount(2, $results);
        $this->assertSame('1', $results[0]['dto']->lhsId);
        $this->assertSame('Alpha', $results[0]['fields']['opportunityName']);
        $this->assertSame('2', $results[1]['dto']->lhsId);
        $this->assertSame('Beta', $results[1]['fields']['opportunityName']);
    }

    public function testFieldMapAndLabelMapReturnExpectedLabels(): void
    {
        $expected = array(
            'opportunityName' => 'Opportunity Name',
            'stage'           => 'Stage',
            'amount'          => 'Amount',
            'probability'     => 'Probability',
            'expectedClose'   => 'Expected Close',
        );
        $this->assertSame($expected, $this->service->fieldMap());
        $this->assertSame($expected, $this->service->labelMap());
    }
}
