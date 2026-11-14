<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\Opportunity\Repository;

final class OpportunityRepository
{
    private string $table = 'ksf_crm_opportunities';

    public function findAll(): array
    {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return array(); }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " ORDER BY created_at DESC, id DESC";
        $result = db_query($sql, 'Could not load opportunities');
        if ($result === false) { return array(); }
        $rows = array();
        while (($row = db_fetch_assoc($result)) !== false && $row !== null) { $rows[] = $row; }
        return $rows;
    }

    public function findById(int $id): ?array
    {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return null; }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " WHERE id = " . (int) $id;
        $result = db_query($sql, 'Could not load opportunity');
        if ($result === false) { return null; }
        $row = db_fetch_assoc($result);
        return $row === false ? null : $row;
    }

    public function save(array $data): int
    {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return 0; }
        $sql = "INSERT INTO " . TB_PREF . $this->table . " (
                opportunity_name, stage, amount, probability, expected_close,
                customer_id, contact_id, description)
            VALUES ("
            . $this->escape($data['opportunity_name'] ?? '') . ", "
            . $this->escape($data['stage'] ?? 'prospecting') . ", "
            . $this->escape($data['amount'] ?? '0') . ", "
            . $this->escape($data['probability'] ?? '0') . ", "
            . $this->escape($data['expected_close'] ?? '') . ", "
            . $this->escape($data['customer_id'] ?? '') . ", "
            . $this->escape($data['contact_id'] ?? '') . ", "
            . $this->escape($data['description'] ?? '') . ")";
        db_query($sql, 'Could not save opportunity');
        return db_insert_id();
    }

    public function update(int $id, array $data): void
    {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return; }
        $sets = [];
        foreach (['opportunity_name', 'stage', 'amount', 'probability', 'expected_close',
                  'customer_id', 'contact_id', 'description'] as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = " . $this->escape($data[$col]);
            }
        }
        if (empty($sets)) { return; }
        $sql = "UPDATE " . TB_PREF . $this->table . " SET " . implode(', ', $sets)
            . " WHERE id = " . (int) $id;
        db_query($sql, 'Could not update opportunity');
    }

    public function delete(int $id): void
    {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return; }
        $sql = "DELETE FROM " . TB_PREF . $this->table . " WHERE id = " . (int) $id;
        db_query($sql, 'Could not delete opportunity');
    }

    private function escape($value): string
    {
        return function_exists('db_escape') ? db_escape($value) : addslashes((string) $value);
    }
}
