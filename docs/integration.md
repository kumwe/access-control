# Host integration

Register ConfigProvider only in the host composition root. Supply all three `kumwe.access` keys explicitly:
`membership_resource_types` (list), `reserved_ownership_rules` (category to stable enum wire string), and
`reference_inspectors` (ordered service IDs). Factories create shared bootstrap registries and a union inspector.
No aliases are installed for AuthorizationGateway, recorder, membership freshness, ownership writer/reader or
site-group ports: the host implements those. Values and stateless DecisionCombiner are directly constructed.

Core's DenyByDefaultAuthorizationGateway, principal provenance/issuance, role grants, membership live lookup, audit
fail-closed handling and transaction coupling remain Core-owned. Supply Core's actual membership-sensitive targets
and reserved ownership categories. Package defaults must never substitute for that configuration. Preserve
direct-service, HTTP, CLI, MCP, worker and scheduler enforcement parity.

The [Core contract](core-contract.md) defines authority, service lifetime, SDK responsibilities and test ownership.
Use the recorded source and consumer inventories to reconcile current imports and policy composition against the
selected exact dependency graph. Both old AuthorizationDecision representations map to DecisionState; do not retain
a boolean shadow decision class or namespace alias. Convert Core SystemIdentity enums at the trusted host boundary.

SDK composes the canonical Capability and definition invariants while retaining manifest graph/trust validation,
concrete route/navigation definitions, lifecycle wiring and conformance/generation fixtures. Core retains its gateway,
audit/logging implementation, ResourceOwnershipScopeService, SiteGroupAdministration and persistence adapters.

## Verified Laminas service resolution

The consuming host installs `laminas/laminas-servicemanager:^4.0` and registers
`(new Kumwe\Access\ConfigProvider())()['dependencies']` in its ServiceManager,
with its explicit policy array registered as the `config` service. ServiceManager
is a host integration choice and a development dependency of this package; portable
runtime code only requires PSR-11. The archive consumer declares the host dependency
itself and invokes `resources/toolchain/service-manager-smoke.php` through its own
no-dev authoritative Composer autoloader. This gate resolves all three advertised
services, verifies shared lifetimes and empty capability registries, proves missing
configuration fails, and ensures no host authorization gateway is registered.
