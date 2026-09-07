# Architecture and versioned decisions

The source closure starts with 35 existing portable or separable types: 33 App authorization types, App GrantScope and
SDK Capability. A second Identity AuthorizationDecision merges into one canonical model. Seven additional types make
42 exports: DecisionState, DecisionCombiner, MembershipRequirement, ConfigProvider and three factories. The manifest
freezes every public method and property.

Only Access Context 0.1.0 (unverified development input) and PSR Container 2 are required. The architecture gate
closes the exact 42-type allow-list, rejects App/SDK/framework/persistence dependencies and runtime selection, and
permits PSR Container only in factories. Context values remain explicit inputs; no context is ambient or retained by
service configuration.

## Versioned clean breaks

- **AC-001 / 0.1.0:** one four-state decision replaces both boolean models. Only Allow produces allowed=true. Deny >
  StepUp > Allow > NotApplicable. Ties compare policy then reason as bytes. Empty input abstains with
  access.aggregate.v1/no_applicable_policy. The host must refuse non-Allow. Codes are exact lowercase machine tokens,
  1–127 bytes. A step-up response never authenticates or authorizes on its own.
- **AC-002 / 0.1.0:** host membership categories and all 44 App reserved ownership rules stay App-owned and are
  mandatory explicit construction inputs. Factories refuse absence; deliberate empty tables are possible and must be
  reviewed by host composition. Unknown categories remain site-only.
- **AC-003 / 0.1.0:** numeric identifiers stay strings; raw ASCII controls are refused before trim. Ordinary
  surrounding spaces and approved lowercase normalization remain. SiteGroup stores its trimmed name rather than
  validating a different local string.
- **AC-004 / 0.1.0:** bound consumed entries, including duplicates: scopes64, policy targets64, system identities64,
  target identifiers128, group members4096, membership resource types4096, reference inspectors64, candidate/returned
  sites4096, aggregate decisions1024. Registries cap capability/policy/declared/reserved entries at4096. Refusal
  precedes unbounded iteration and final publication of state.
- **AC-005 / 0.1.0:** system identity metadata is immutable validated system:lowercase-code strings; issuance and the
  closed trusted identity list stay App. Extension policies cannot declare any. Direct allowsSystemIdentity inspects
  membership only; callers must also enforce lifecycle and authority.
- **AC-006 / 0.1.0:** policies bind the exact CapabilityDefinition registration epoch. Withdrawal or replacement
  cannot revive old policies, even for the same owner. Definitions remain visible through diagnostic ownedBy until
  removed; active lookups are inert. Re-register policies explicitly after capability replacement.
- **AC-007 / 0.1.0:** object-valued iterables require actual final target/decision values or declared inspector ports
  before method invocation. Reserved rules require enum values. Invalid inspector site output refuses; numeric adapter
  errors cannot silently erase live references.

Registries mutate only through explicit registration/removal; no trust admission, callbacks in definitions, sessions,
DB transactions, clocks or audit side effects exist. Shared registries belong to one trusted container/runtime
generation. Rebuild that generation on lifecycle changes; no cross-process synchronization or compare-and-set
guarantee is provided by the in-memory registries. Ownership equality intentionally compares level/id, not current
membership snapshot.

## AC-008: Membership directory ownership completion

The v2 catalog assigns MembershipDirectory to Access. Source closure at App master
was rechecked: it extends MembershipContextValidator and refers only to membership
and site Context values. Move the exact port to Kumwe\Access without a default
implementation. This is additive ownership completion, not a new authority model.
DoctrineMembershipDirectory, authentication, current membership storage, lock scope
and credential selection remain App responsibilities. Approval imports this port;
it must not redeclare it. Consumers of the original App port move only after a
verified Access successor is available. The original extraction baseline remains
recorded; this addition has its own source and consumer inventory.
