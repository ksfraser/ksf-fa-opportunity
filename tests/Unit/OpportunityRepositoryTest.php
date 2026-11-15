<?php
declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Tests\Unit;

use Ksfraser\FrontAccounting\Opportunity\Repository\OpportunityRepository;
use PHPUnit\Framework\TestCase;

/**
 * Tests for OpportunityRepository against stubbed FA db_* functions.
 *
 * @package Ksfraser\FrontAccounting\Opportunity\Tests\Unit
 * @since   1.0.0
 */
class OpportunityRepositoryTest extends TestCase
{
    /** @var OpportunityRepository */
    private $repo;

    protected function setUp(): void
    {
        $this->repo = new OpportunityRepository();
        $GLOBALS['KSF_DB_LAST_SQL']   = null;
        $GLOBALS['KSF_DB_RESULT']     = false;
        $GLOBALS['KSF_DB_ROWS']       = array();
        $GLOBALS['KSF_DB_INSERT_ID']  = 1;
    }

    private function row(array $overrides = array()): array
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
            'created_at'      => '2026-01-01',
        ), $overrides);
    }

    public function testFindAllReturnsEmptyWhenDbReturnsFalse(): void
    {
        $this->assertSame(array(), $this->repo->findAll());
    }

    public function testFindAllReturnsRows(): void
    {
        $GLOBALS['KSF_DB_RESULT'] = true;
        $GLOBALS['KSF_DB_ROWS']   = array($this->row(), $this->row(array('id' => 2)));

        $rows = $this->repo->findAll();

        $this->assertCount(2, $rows);
        $this->assertSame(1, $rows[0]['id']);
        $this->assertSame(2, $rows[1]['id']);
        $this->assertStringContainsString(
            'SELECT * FROM ' . TB_PREF . 'ksf_crm_opportunities ORDER BY created_at DESC, id DESC',
            $GLOBALS['KSF_DB_LAST_SQL']
        );
    }

    public function testFindByIdReturnsRow(): void
    {
        $GLOBALS['KSF_DB_RESULT'] = true;
        $GLOBALS['KSF_DB_ROWS']   = array($this->row(array('id' => 7)));

        $row = $this->repo->findById(7);

        $this->assertSame(7, $row['id']);
        $this->assertSame('Widget deal', $row['opportunity_name']);
        $this->assertStringContainsString('WHERE id = 7', $GLOBALS['KSF_DB_LAST_SQL']);
    }

    public function testFindByIdReturnsNullWhenDbReturnsFalse(): void
    {
        $this->assertNull($this->repo->findById(7));
    }

    public function testSaveInsertsAndReturnsInsertId(): void
    {
        $GLOBALS['KSF_DB_RESULT']   = true;
        $GLOBALS['KSF_DB_INSERT_ID'] = 42;

        $data = array(
            'opportunity_name' => 'Widget deal',
            'stage'            => 'negotiation',
            'amount'           => '5000',
            'probability'      => '75',
            'expected_close'   => '2026-03-31',
            'customer_id'      => 'C100',
            'contact_id'       => 'P5',
            'description'      => 'Widgets for Acme',
        );

        $id = $this->repo->save($data);

        $this->assertSame(42, $id);
        $sql = $GLOBALS['KSF_DB_LAST_SQL'];
        $this->assertStringContainsString('INSERT INTO ' . TB_PREF . 'ksf_crm_opportunities', $sql);
        $this->assertStringContainsString("'negotiation'", $sql);
        $this->assertStringContainsString("'5000'", $sql);
    }

    public function testUpdateBuildsUpdateSql(): void
    {
        $this->repo->update(5, array(
            'stage'           => 'closed_won',
            'probability'     => '100',
        ));

        $sql = $GLOBALS['KSF_DB_LAST_SQL'];
        $this->assertStringContainsString('UPDATE ' . TB_PREF . 'ksf_crm_opportunities', $sql);
        $this->assertStringContainsString("stage = 'closed_won'", $sql);
        $this->assertStringContainsString("probability = '100'", $sql);
        $this->assertStringContainsString('WHERE id = 5', $sql);
    }

    public function testDeleteBuildsDeleteSql(): void
    {
        $this->repo->delete(5);

        $sql = $GLOBALS['KSF_DB_LAST_SQL'];
        $this->assertStringContainsString('DELETE FROM ' . TB_PREF . 'ksf_crm_opportunities', $sql);
        $this->assertStringContainsString('WHERE id = 5', $sql);
    }
}
