# RTM — ksf_FA_Opportunity

**Module:** `ksf_FA_Opportunity`
**Version:** `1.0.0`
**Requirement definitions:** `ProjectDocuments/Requirements.md`

| BR | Requirement | Implementation | Tests |
|----|-------------|----------------|-------|
| BR-OP-001 | Opportunity resolution ownership — owns `opportunity` type, answers RELATED_TO_FIELDS + OPPORTUNITY_RESOLVE | `hooks.php` (`hooks_ksf_FA_Opportunity::RELATED_TO_FIELDS`, `::OPPORTUNITY_RESOLVE`, `::OPPORTUNITY_FIELDS`), `OpportunityService::resolveById` | (FA runtime only — hooks) |
| BR-OP-002 | Opportunity display default — Name — Stage — Amount, fallback `Opportunity #<id>` | `OpportunityDTO::getDisplayLabel()` | `OpportunityDtoTest` |
| BR-OP-003 | Resolved DTO for RelatedTo — field map + listAll summary data | `OpportunityService::fieldMap()`, `OpportunityService::listAll()`, `hooks.php` `RELATED_TO_FIELDS` | (via related-to-core) |
| BR-OP-004 | Opportunity pipeline fields — name, stage, amount, probability, expected close, … stored in `0_ksf_crm_opportunities` | `OpportunityRepository` (`save`, `update`, `findAll`, `findById`), `OpportunityDTO` | (FA runtime only — repository) |
