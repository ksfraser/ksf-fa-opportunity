# Business Requirements — ksf_FA_Opportunity

**Package:** `ksfraser/ksf-fa-opportunity`
**Version:** `1.0.0`
**Namespace:** `Ksfraser\FrontAccounting\Opportunity\*`
**Module sequence:** OP (Opportunity)

---

## BR-OP-001 — Opportunity resolution ownership

**Status:** IMPLEMENTED

The system SHALL provide `ksf_FA_Opportunity` as the owning module for
opportunity/pipeline data spun off from CRM Opportunities tab. CRM defers to
Opportunity for opportunity display rather than owning opportunity info itself.

1. Opportunity SHALL own the `opportunity` type in the RelatedTo registry.
2. Opportunity SHALL answer `RELATED_TO_FIELDS` for the `opportunity` type.
3. Opportunity SHALL answer `OPPORTUNITY_RESOLVE` by building resolved DTOs from
   `0_ksf_crm_opportunities` data.
4. Consuming modules MAY override by registering their own `OPPORTUNITY_RESOLVE`
   listener.

---

## BR-OP-002 — Opportunity display default

**Status:** IMPLEMENTED

The opportunity display default SHALL show Opportunity Name — Stage — Amount.

1. `OpportunityDTO::getDisplayLabel()` SHALL return non-empty fields joined with ` — `.
2. When all display fields are empty, return `Opportunity #<id>`.
3. All values SHALL be HTML-escaped at render time.

---

## BR-OP-003 — Resolved DTO for RelatedTo

**Status:** IMPLEMENTED

Opportunity SHALL build resolved display data (DTO + fields + labels + url) for RelatedTo display.

1. Resolved data wraps a `RelatedRecordDto` with resolved fields,
   overridable labels, and a URL.
2. `fieldMap()` returns the field name => display label mapping.
3. `listAll()` returns resolved opportunity data for summary table display.

---

## BR-OP-004 — Opportunity pipeline fields

**Status:** IMPLEMENTED

Opportunity SHALL store pipeline fields: name, stage, amount, probability,
expected close date, customer, contact, description.

1. All fields stored in `0_ksf_crm_opportunities`.
2. Default stage is `prospecting`.
3. Default probability is 0.
