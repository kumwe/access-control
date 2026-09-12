# Core contract

Access Control owns deterministic authorization decisions, capability and resource-policy definitions, owner-bound
registries, scopes, membership and ownership ports. Core authenticates, issues principals and system identities,
resolves current membership, evaluates policy, enforces every decision and persists/audits state changes.

## Decision and authority boundary

Only `DecisionState::Allow` permits access. Deny overrides StepUp, StepUp overrides Allow, and NotApplicable grants
nothing. Equal-state decisions use deterministic byte-order tie-breaking. A step-up outcome does not authenticate
or authorize an action. Core enforces provenance, current membership, role grants, token audience, disabled users,
trust revocation and fail-closed audit behavior at the actual operation boundary.

Core's gateway, SystemIdentity/SystemPrincipal, concrete membership directory, authorization recorder,
ResourceOwnershipScopeService, SiteGroupAdministration and Doctrine adapters remain Core-owned. Approval consumes
`Kumwe\Access\MembershipDirectory`; the package does not provide a live membership store.

## Container configuration and lifetime

Core registers `Kumwe\Access\ConfigProvider` at its composition root. Its `kumwe.access` configuration explicitly
supplies `membership_resource_types`, `reserved_ownership_rules` and `reference_inspectors`. These are host policy
inputs; missing configuration fails. The package supplies no fallback security table or authorization gateway alias.

Factories create AuthorizationPolicyRegistry, ResourceOwnershipScopePolicy and CompositeResourceOwnershipReferences
as shared services for one trusted container/runtime generation. Rebuild that generation on lifecycle changes.
In-memory registries provide no cross-process synchronization or transaction guarantee. Context values are explicit
operation inputs, never shared current-user or current-tenant services. Values and DecisionCombiner are constructed
directly. [Integration](integration.md) covers PSR-11 and the tested Laminas host composition.

## SDK and Core compatibility

SDK and Core use the canonical `Kumwe\Access\Capability` and four-state AuthorizationDecision models. SDK retains
manifest graph validation, concrete route/navigation definitions, trust admission and extension lifecycle activation.
Core converts its SystemIdentity enum at the trusted boundary and supplies its actual membership-sensitive targets
and reserved ownership categories. Do not duplicate portable invariants or introduce historical namespace aliases.

Exact-pin compatible independently verified pre-1.0 versions and commit consumer lockfiles. The
[release record](release-record.md), [source map](source-map.json), [consumer inventory](consumer-inventory.json) and
[membership source map](membership-source-map.json) preserve exact baseline mappings for compatibility review.
Reconcile them against current consumers before import changes or duplicate implementation removal.

## Test ownership and operations

The package owns decision algebra, identifier grammar, scope/delegation, owner collisions, lifecycle removal,
registry replacement, hostile iterables, API and configured-service conformance. Core retains authorization parity,
vertical/horizontal escalation, query/count/relation/report disclosure, membership/role staleness, token audience,
trust revocation, direct invocation, database ownership/CAS, audit failures, lifecycle and recovery suites.
SDK retains manifest graph and contribution activation tests. Remove duplicate portable unit tests only when their
production implementation is replaced by the verified package. See [testing](testing.md).

Core rollback restores its previously tested dependency and composition tuple. Publication status does not prove
Core integration or independent release verification; [release guidance](releasing.md) defines that evidence.
