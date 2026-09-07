# Integration and future adoption

Register the ConfigProvider only in the host composition root. Supply all three kumwe.access keys explicitly:
membership_resource_types (list), reserved_ownership_rules (category to stable enum wire string), reference_inspectors
(ordered service IDs). Factories create shared bootstrap registries and a union inspector. No aliases are installed
for AuthorizationGateway, recorder, membership freshness, ownership writer/reader or site-group ports: the host
implements those. Values and stateless DecisionCombiner are directly constructed.

App DenyByDefaultAuthorizationGateway, principal provenance/issuance, role grants, membership live lookup, audit
fail-closed handling and transaction coupling stay App. Supply the exact existing seven membership-sensitive targets
and 44 reserved ownership categories in App code during adoption; package defaults must never substitute for that
configuration. Preserve direct-service, HTTP, CLI, MCP, worker and scheduler enforcement parity.

The first successor task is a verified dependency correction in this repository. Current exact Context 0.1.0 is
executable development provenance only. After this package has a passing external immutable-release attestation, a
**separate SDK successor** replaces Spi\Identity\Domain\Capability with Kumwe\Access\Capability and composes canonical
definition invariants in ManifestContributions without moving manifest graph/trust activation here. SDK
route/navigation definitions and conformance/generation fixtures migrate together. Coordinate Contribution
owner/definition adoption in that successor.

After the SDK successor is independently verified, a separate App PR pins both releases, reconciles the extraction
baseline, replaces imports using docs/source-map.json and docs/consumer-inventory.json, removes old portable
implementations and their duplicate tests together, wires explicit policy configuration and regenerates
capability/migration evidence. Map both old AuthorizationDecision constructors into DecisionState; never keep a
boolean shadow decision class or namespace alias. Convert App SystemIdentity enum to its value only at the host
boundary, where issuance authority is retained.

Retain the host-owned gateway, SystemIdentity/SystemPrincipal, authorization audit/logging implementation,
ResourceOwnershipScopeService, SiteGroupAdministration and Doctrine adapters. Keep extension runtime
registries/trust/lifecycle wiring; compose portable definitions instead of duplicating their invariants. Source
changes since App960ce8ec00cf724a7cae03e5ba09c4852c9ab54e or SDKd0484b8733eaa57d076f567ffa5e997b9564b5fa need semantic
reconciliation and a new package release before adoption. No App edits are part of this draft.
