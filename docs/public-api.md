# Public API — proposed 0.1.0

This document inventories every public type and member. Parameters and return shapes below are exact PHP contracts;
the corresponding source PHPDoc states invariants, exceptions and side effects. The proposed release is blocked as
described in dependency-gate.md.

Values are final/readonly unless explicitly mutable; no value performs I/O or starts a transaction. Registries retain
per-container bootstrap state and have no process synchronization. Host ports specify responsibilities but ship no
authority/persistence adapter. PHP exceptions inherit their standard Throwable API and do not confer authority. Enum
cases use stable strings. Factory failures never synthesize defaults. Bounds and initial clean-break decisions AC-001
through AC-007 in architecture.md apply to every relevant constructor even when historical source comments describe
normalized logical counts.

## `Kumwe\Access\AuthorizationDecision`

Immutable four-state authorization result with stable machine-readable provenance.

@since 0.1.0

Public properties: `allowed` (bool, readonly); `policy` (string, readonly); `reason` (string, readonly); `state`
(Kumwe\Access\DecisionState, readonly);

### `__construct(Kumwe\Access\DecisionState $state, string $policy, string $reason)`

Construct one result. Codes are exact, bounded lowercase ASCII tokens; no normalization occurs.

@param DecisionState $state Outcome, independent of HTTP or session mechanisms.
@param string $policy Stable policy identifier, 1–127 bytes.
@param string $reason Stable reason identifier, 1–127 bytes.
@throws InvalidArgumentException When either code is malformed.
@since 0.1.0

### `toArray(): array`

Export the exact stable result shape without changing authority.

@return array{state: string, allowed: bool, policy: string, reason: string} Decision data.
@since 0.1.0

## `Kumwe\Access\AuthorizationDecisionRecorder`

Port that receives every authorization decision the gateway reaches, allow or deny alike.

The gateway calls this before it returns a permit or raises a denial, so the sink an implementation
writes to is the authoritative record of what was attempted, not only of what succeeded. Recording is
treated as part of the security boundary: an implementation that throws makes the gateway abandon an
otherwise permitted action with `AuthorizationAuditUnavailable`, so an implementation should raise
only when the record genuinely did not reach its sink. It sits on the hot path of every authorized
operation, including read checks, which is the constraint on how much work it may do per call.

@since  0.1.0

### `record(Kumwe\Context\Value\ExecutionContext $context, Kumwe\Access\Capability $action, Kumwe\Access\AuthorizationResource $resource, Kumwe\Access\AuthorizationDecision $decision): void`

Record one evaluated decision against the actor, action, and resource it concerned.

@param   ExecutionContext       $context   Actor, site, and request correlation the decision was
         made for.
@param   Capability             $action    Capability that was being exercised.
@param   AuthorizationResource  $resource  Resource the action was aimed at.
@param   AuthorizationDecision  $decision  Outcome, with the policy and reason that produced it.

@return  void

@since  0.1.0

## `Kumwe\Access\AuthorizationDefinitionLifecycle`

Lifecycle state shared by capability and resource-policy definitions in the live registry.

Active and deprecated definitions remain enforceable, so an owner can announce a replacement
without breaking grants in the same release. Disabled and retired definitions fail closed even
when a stale stored grant still names them. Removing an extension withdraws its definitions
altogether; these explicit states cover definitions retained for rollout or compatibility.

@since  0.1.0

Public constants/cases: `Active`, `Deprecated`, `Disabled`, `Retired`.

### `enforceable(): bool`

Whether the definition may participate in a live authorization decision.

@return  bool  True for active and deprecated definitions; false otherwise.

@since  0.1.0

## `Kumwe\Access\AuthorizationDenied`

Raised when the authorization gateway refuses an action, carrying the full shape of the refusal.

This is the single failure every application service lets propagate when policy says no, so delivery
code has one thing to catch: `ProblemDetailsMiddleware` answers it with a generic 403 problem
document that reveals none of these fields, and the administrator surfaces refuse the request in the
same way. The properties exist for the operator-facing log and for tests that need to pin *why* a
request was refused rather than merely that it was — the policy and reason are copied straight from
the `AuthorizationDecision` the gateway already recorded.

@since  0.1.0

Public properties: `action` (string, readonly); `policy` (string, readonly); `reason` (string, readonly);
`resourceIdentifier` (string, readonly); `resourceType` (string, readonly); `siteIdentifier` (string, readonly);
`subject` (string, readonly);

### `__construct(string $subject, string $action, string $resourceType, string $resourceIdentifier, string $siteIdentifier, string $policy, string $reason)`

Describe the refusal in the terms the gateway evaluated it in.

@param  string  $subject             Actor identifier the context resolved to, human or system.
@param  string  $action              Capability value that was refused, such as `content.publish`.
@param  string  $resourceType        Resource type the action was aimed at, such as `content`.
@param  string  $resourceIdentifier  Resource identifier, or `*` for a collection-wide attempt.
@param  string  $siteIdentifier      Site the execution context was operating in.
@param  string  $policy              Versioned rule that produced the refusal.
@param  string  $reason              Stable token naming the cause, such as `global_grant_required`.

@since  0.1.0

## `Kumwe\Access\AuthorizationGateway`

The one port application code asks before it changes or reveals anything.

Every service, handler, console command, and MCP tool routes its permission question through this
interface rather than reading grants itself, which is what keeps the delivery adapters at parity: a
capability that is refused over REST cannot be reached through the administrator or the CLI. An
implementation owes three guarantees — it denies by default, it records every decision it reaches
before acting on it, and it answers the delegation question separately from the access question so
that an actor can never grant authority it does not itself hold.

@since  0.1.0

### `assertAllowed(Kumwe\Context\Value\ExecutionContext $context, Kumwe\Access\Capability $action, Kumwe\Access\AuthorizationResource $resource): void`

Require that an actor may perform an action on a resource, and stop the operation otherwise.

Call it before the first side effect of an operation. The decision is recorded before the refusal
is raised, so a denial still leaves a trail even though nothing else happened.

@param   ExecutionContext       $context   Actor, site, and provenance the action runs under.
@param   Capability             $action    Capability being exercised.
@param   AuthorizationResource  $resource  Resource the action is aimed at.

@return  void

@throws  AuthorizationDenied  When policy refuses the actor this action on this resource.

@since  0.1.0

### `assertCanDelegate(Kumwe\Context\Value\ExecutionContext $context, Kumwe\Access\Capability $action, Kumwe\Access\GrantScope $scope): void`

Require that an actor may hand an action onward to others within a scope.

Granting a capability to a role, or minting a token that carries one, is authority transfer
rather than use, so it is checked against the actor's own ceiling: nobody may delegate wider than
they hold, and some actions are not delegatable at any scope. The sole bootstrap exception is an
explicitly trusted extension capability: a global `extensions.manage` holder may make its first
human grant after activation, because no principal can hold an owner-new capability before that
grant exists. Core and system-only capabilities never enter that exception. Callers issuing a
grant assert this before and inside the write transaction.

@param   ExecutionContext  $context  Actor, site, and provenance the delegation runs under.
@param   Capability        $action   Capability the actor proposes to grant onward.
@param   GrantScope        $scope    Scope the grant would be written at, global or named.

@return  void

@throws  AuthorizationDenied  When the delegation would exceed the actor's effective authority.

@since  0.1.0

### `decide(Kumwe\Context\Value\ExecutionContext $context, Kumwe\Access\Capability $action, Kumwe\Access\AuthorizationResource $resource): Kumwe\Access\AuthorizationDecision`

Evaluate whether an actor may perform an action on a resource, without raising on refusal.

Use this where a denial is an expected branch — hiding a menu entry, filtering a listing — and
`assertAllowed()` where it must stop the operation. The decision is audited either way.

@param   ExecutionContext       $context   Actor, site, and provenance the action runs under.
@param   Capability             $action    Capability being exercised.
@param   AuthorizationResource  $resource  Resource the action is aimed at.

@return  AuthorizationDecision  The outcome, plus the policy and reason behind it.

@since  0.1.0

## `Kumwe\Access\AuthorizationPolicyRegistry`

Canonical live registry of capability metadata and owner-bound resource policies.

Core and extensions populate this same mutable bootstrap registry through their owner-bound
contribution registrars. The authorization gateway only reads it once request handling begins;
unknown, disabled, retired, unbound, or ambiguously registered capabilities therefore fail closed
without a second hard-coded action/resource or system-authority catalog.

@since  0.1.0

### `__construct(Kumwe\Access\MembershipRequirement $membershipRequirement)`

Create an empty registry with explicit host membership-resource policy.

The composition root must share this instance with both the contribution registry set and the
gateway. Keeping construction empty prevents core from taking a registration path extensions do not.

@param MembershipRequirement $membershipRequirement Explicit host-selected sensitive resource types.
@since  0.1.0

### `allowsHumanGrant(Kumwe\Access\Capability $action): bool`

Whether a human principal may exercise this registered capability through stored grants.

@param   Capability  $action  Capability being evaluated.

@return  bool  False for unknown, unenforceable, or system-only definitions.

@since  0.1.0

### `allowsSystemIdentity(Kumwe\Access\Capability $action, Kumwe\Access\AuthorizationResource $resource, string $identity): bool`

Whether the matching policy grants an unattended identity this exact action/resource pair.

@param   Capability             $action    Capability being exercised.
@param   AuthorizationResource  $resource  Resource the action is aimed at.
@param   string                 $identity  Purpose-built unattended actor.

@return  bool  True only when the enforceable resource policy names that identity.

@since  0.1.0

### `capability(Kumwe\Access\Capability $capability): ?Kumwe\Access\CapabilityDefinition`

Resolve a capability's operational metadata, including owner and lifecycle.

@param   Capability  $capability  Permission code being inspected.

@return  ?CapabilityDefinition  Registered metadata, or null when no owner currently publishes it.

@since  0.1.0

### `capabilityDefinitions(): Kumwe\Access\CapabilityDefinitionRegistry`

Reach the capability-definition catalog for persistence and diagnostic adapters.

@return  CapabilityDefinitionRegistry  The same live catalog this registry evaluates.

@since  0.1.0

### `registerCapability(Kumwe\Access\CapabilityDefinition $definition): void`

Add one owner-bound capability definition.

@param   CapabilityDefinition  $definition  Typed metadata contributed by core or an extension.

@return  void

@since  0.1.0

### `registerResourcePolicy(Kumwe\Access\ResourcePolicyDefinition $definition): void`

Add one owner-bound action/resource policy after its capability has been registered.

@param   ResourcePolicyDefinition  $definition  Typed binding contributed by the same capability owner.

@return  void

@since  0.1.0

### `removeOwner(string $owner): void`

Withdraw every policy and capability belonging to an owner, policies first.

@param   string  $owner  Core or extension owner whose live authority is being removed.

@return  void

@since  0.1.0

### `requiresGlobalGrant(Kumwe\Access\Capability $action, Kumwe\Access\AuthorizationResource $resource): bool`

Decide whether only an installation-wide human grant can authorize this request.

@param   Capability             $action    Capability being exercised.
@param   AuthorizationResource  $resource  Resource the action is aimed at.

@return  bool  The matching policy's explicit installation-global classification.

@since  0.1.0

### `requiresMembershipContext(Kumwe\Access\Capability $action): bool`

Whether a delegated credential for this capability must carry a live organization membership.

The decision follows enforceable typed resource targets, not capability namespaces. An extension
capability bound to business records is therefore constrained exactly like a core capability,
while a similarly named capability targeting only site resources is not accidentally constrained.

@param   Capability  $action  Capability being considered for a delegated credential.

@return  bool  True when any live binding reaches organization-sensitive resources.

@since  0.1.0

### `resourcePolicies(): Kumwe\Access\ResourcePolicyRegistry`

Reach the resource-policy catalog for persistence and diagnostic adapters.

@return  ResourcePolicyRegistry  The same live catalog this registry evaluates.

@since  0.1.0

### `resourcePolicy(Kumwe\Access\Capability $action, Kumwe\Access\AuthorizationResource $resource): ?Kumwe\Access\ResourcePolicyDefinition`

Resolve the typed base policy for one action/resource request.

@param   Capability             $action    Capability being exercised.
@param   AuthorizationResource  $resource  Resource the action is aimed at.

@return  ?ResourcePolicyDefinition  Matching enforceable binding, or null when unsupported.

@since  0.1.0

### `supports(Kumwe\Access\Capability $action, Kumwe\Access\AuthorizationResource $resource): bool`

Decide whether an action is meaningful against a resource at all.

@param   Capability             $action    Capability being exercised.
@param   AuthorizationResource  $resource  Resource the action is aimed at.

@return  bool  True only when enforceable owner-bound capability and policy definitions match.

@since  0.1.0

### `supportsDelegation(Kumwe\Access\Capability $action, Kumwe\Access\GrantScope $scope): bool`

Decide whether a capability may be granted onward at the proposed scope.

Global and site scopes are governed directly by capability metadata. A narrower scope must also
name a resource that an enforceable policy binds to the capability, preventing arbitrary scope
type strings from being stored merely because the capability lists a similar name.

@param   Capability  $action  Capability the caller proposes to grant onward.
@param   GrantScope  $scope   Exact reach of the proposed grant.

@return  bool  True when metadata permits delegation and the requested scope is meaningful.

@since  0.1.0

## `Kumwe\Access\AuthorizationResource`

Subject of an authorization decision: a resource family paired with the identity being acted on.

Every call into `AuthorizationGateway` names its target with one of these, so the policy registry, the
site-ownership resolver and the decision audit all speak one vocabulary. Both halves are validated once,
at construction, which lets consumers concatenate them into log lines and query parameters without
re-checking them. Reach for `collection()` when the operation covers a whole family — a listing, or a
create where no identifier exists yet — and `item()` when it targets one addressable resource.

@since  0.1.0

### `collection(string $type): Kumwe\Access\AuthorizationResource`

Name a whole family of resources rather than one member of it.

A collection carries `*` as its identifier, which `DenyByDefaultAuthorizationGateway` treats as owned
by the calling site instead of asking the ownership registry — there is no single row to own.

@param   string  $type  Resource family being listed or added to.

@return  self  Target whose identifier is `*`.

@throws  InvalidArgumentException  When the type is not a short lowercase identifier.

@since  0.1.0

### `identifier(): string`

Report which resource within the family is being acted on.

@return  string  The trimmed identifier, or `*` when the target stands for the whole collection.

@since  0.1.0

### `item(string $type, string $identifier): Kumwe\Access\AuthorizationResource`

Name one addressable resource within a family.

Surrounding whitespace is stripped before validation, so a raw route segment or request field can be
handed over as it arrived.

@param   string  $type        Resource family the identifier belongs to.
@param   string  $identifier  Identity of the resource, usually its primary key or slug.

@return  self  Target carrying the trimmed identifier.

@throws  InvalidArgumentException  When the type or the trimmed identifier fails validation.

@since  0.1.0

### `type(): string`

Report which resource family the target belongs to.

@return  string  Lowercase identifier the policy and ownership registries key on, such as `content`.

@since  0.1.0

## `Kumwe\Access\AuthorizationResourceOwnershipUnknown`

Raised when nothing authoritative records which site owns a resource.

Site isolation cannot be decided without an owner, and guessing one would let a caller reach across
sites, so the resolver refuses instead. `DenyByDefaultAuthorizationGateway` catches this and fails
closed with the `core.site-ownership.v1` policy and a `resource_site_unknown` reason rather than falling
back to the calling site; `ResourceSiteOwnershipWriter::remove()` raises it when the row it was asked to
delete is absent. Reaching an operator, it means a resource exists without its ownership row — created
outside the transaction that should have recorded it, or already deleted.

@since  0.1.0

### `__construct(Kumwe\Access\AuthorizationResource $resource)`

Name the unowned resource in the operator-facing message.

@param  AuthorizationResource  $resource  Target whose owning site could not be established.

@since  0.1.0

## `Kumwe\Access\Capability`

Name of one thing an actor may be permitted to do, normalised at the point it enters the domain.

Capabilities are the vocabulary grants are written in: `CapabilityGrant` pairs one with a scope,
`PrincipalGrant` carries one for an authenticated token, and extension manifests declare their own
through `CapabilityDefinition`. Wrapping the string in a type is what stops `Content.Publish` and
` content.publish ` from becoming two different permissions, since every value passes through
`fromString()` — the only constructor — which trims, lowercases and enforces the identifier grammar
before anything is compared or stored.

@since  0.1.0

### `__toString(): string`

Render the capability as its code, so it can be interpolated into messages and log lines.

@return  string  The same value `value()` returns.

@since   0.1.0

### `equals(Kumwe\Access\Capability $other): bool`

Whether another capability names the same permission.

Both sides were normalised on construction, so this is a plain identity test rather than a
lenient comparison; nothing further is folded here.

@param   self  $other  Capability to compare against.

@return  bool  True when the two codes are identical.

@since   0.1.0

### `fromString(string $value): Kumwe\Access\Capability`

Normalise and validate a capability code from an operator, a manifest, or a stored row.

Trimming and lowercasing happen before the value is judged, so surrounding whitespace and casing
are corrected rather than refused. The grammar itself is strict: a leading letter, then
alphanumeric groups joined by single `.`, `_`, `:` or `-` separators, with no trailing separator.

@param   string  $value  Capability code as written, in any casing and with any surrounding space.

@return  self  The normalised capability.

@throws  InvalidArgumentException  When the trimmed value is empty, longer than 191 characters,
         or not a lowercase delimiter-separated identifier.

@since   0.1.0

### `value(): string`

The normalised code, for writing to a row or matching against one already there.

@return  string  Lowercase and delimiter-separated, safe to persist exactly as returned.

@since   0.1.0

## `Kumwe\Access\CapabilityDefinition`

Operational metadata for one capability recognised by the authorization gateway.

The definition makes ownership and delegation constraints data rather than branches in the
gateway. An empty allowed-scope list marks a capability as system-only; otherwise a human grant
may exercise it and it may be delegated only when both `delegatable` and the requested scope type
permit it. Lifecycle and version travel with the definition so stale extension grants fail closed
when their owner disables or retires the capability.

@since  0.1.0

Public properties: `allowedScopes` (array, readonly); `capability` (Kumwe\Access\Capability, readonly);
`definitionVersion` (int, readonly); `delegatable` (bool, readonly); `highImpact` (bool, readonly); `lifecycle`
(Kumwe\Access\AuthorizationDefinitionLifecycle, readonly); `owner` (string, readonly);

### `__construct(Kumwe\Access\Capability $capability, string $owner, iterable $allowedScopes, bool $delegatable, bool $highImpact, Kumwe\Access\AuthorizationDefinitionLifecycle $lifecycle, int $definitionVersion)`

Validate and hold one owner-bound capability definition.

@param   Capability                        $capability         Permission code the definition describes.
@param   string                            $owner              `core` or the owning extension's
         `vendor/name` identifier.
@param   iterable<string>                  $allowedScopes      Grant-scope types accepted for this
         capability; an empty set makes it system-only.
@param   bool                              $delegatable        Whether a human may grant the capability
         onward at an allowed scope.
@param   bool                              $highImpact         Whether exercising it requires the
         high-impact controls layered above the base grant check.
@param   AuthorizationDefinitionLifecycle  $lifecycle          Current enforceability state.
@param   int                               $definitionVersion  Positive owner-controlled definition version.

@throws  InvalidArgumentException  When the owner, namespace, scope list, or version is invalid.

@since  0.1.0

### `allowsDelegation(Kumwe\Access\GrantScope $scope): bool`

Whether this capability may be delegated at the requested scope.

@param   GrantScope  $scope  Exact grant reach proposed by the caller.

@return  bool  True only for an enforceable, delegatable definition listing the scope type.

@since  0.1.0

### `allowsHumanGrant(): bool`

Whether a human grant can ever exercise this capability.

@return  bool  False for system-only capabilities whose allowed-scope list is empty.

@since  0.1.0

### `assertOwnedIdentifier(string $owner, string $identifier, string $kind): void`

Refuse an extension-owned identifier outside the extension's dotted namespace.

Core capability identifiers retain their historical vocabulary and therefore need no `core.`
prefix. Every resource-policy identifier, including core's, is namespace checked by the outer
contribution owner before reaching this lower-level definition.

@param   string  $owner       Validated definition owner.
@param   string  $identifier  Capability or policy identifier being claimed.
@param   string  $kind        Kind named in the failure message.

@return  void

@throws  InvalidArgumentException  When an extension claims another owner's identifier.

@since  0.1.0

### `assertOwner(string $owner): void`

Validate the string identity shared by capability and resource-policy owners.

@param   string  $owner  Candidate `core` or `vendor/name` owner identifier.

@return  void

@throws  InvalidArgumentException  When the identifier is neither core nor an extension name.

@since  0.1.0

### `enforceable(): bool`

Whether this definition may currently take part in an authorization decision.

@return  bool  True while the lifecycle is active or deprecated.

@since  0.1.0

### `toArray(): array`

Export the stable metadata shape used by diagnostics and persistence adapters.

@return  array{
             id: string,
             owner: string,
             allowed_scopes: list<string>,
             delegatable: bool,
             high_impact: bool,
             lifecycle: string,
             version: int
         }

@since  0.1.0

## `Kumwe\Access\CapabilityDefinitionRegistry`

Owner-aware operational catalog of every capability the running authorization layer recognises.

Each identifier can be claimed once. Removal is by owner, which lets extension disable and trust
revocation make stored grants dormant without guessing which rows mention that package. The
registry stores the complete typed metadata rather than a second action list in the gateway.

@since  0.1.0

### `definition(Kumwe\Access\Capability $capability): ?Kumwe\Access\CapabilityDefinition`

Resolve a capability's operational definition.

@param   Capability  $capability  Normalized capability being evaluated.

@return  ?CapabilityDefinition  Its definition, or null when no active owner registered it.

@since  0.1.0

### `isOwnedBy(Kumwe\Access\Capability $capability, string $owner): bool`

Whether the named owner currently holds a capability identifier.

@param   Capability  $capability  Capability whose ownership is being checked.
@param   string      $owner       Expected `core` or `vendor/name` owner.

@return  bool  True only when the registered definition belongs to that exact owner.

@since  0.1.0

### `ownedBy(string $owner): array`

List the definitions one owner currently holds, ordered by capability identifier.

@param   string  $owner  Definition owner being inventoried.

@return  list<CapabilityDefinition>  Matching definitions in deterministic identifier order.

@since  0.1.0

### `register(Kumwe\Access\CapabilityDefinition $definition): void`

Claim one capability identifier for its declared owner.

@param   CapabilityDefinition  $definition  Validated operational metadata to add.

@return  void

@throws  InvalidArgumentException  When any owner already registered the identifier.

@since  0.1.0

### `removeOwner(string $owner): void`

Withdraw every capability belonging to one owner.

Resource policies must be removed first by the composite policy registry, so no surviving
policy can reference a capability this method has withdrawn.

@param   string  $owner  Owner being disabled, removed, or made untrusted.

@return  void

@since  0.1.0

## `Kumwe\Access\CompositeResourceOwnershipReferences`

Asks every contributed reference inspector and answers with the union of what they find.

References worth protecting live in different bounded contexts, and the scope-change service must not
know which. Composing is the union rather than the intersection on purpose: one inspector finding a
stranded reference is enough to refuse the narrowing, and an inspector that knows nothing about a
resource contributes nothing rather than an implicit approval.

@since  0.1.0

### `__construct(iterable $inspectors)`

Hold the inspectors this installation contributes.

@param  iterable<ResourceOwnershipReferences>  $inspectors  Contributed reference sources.

@since  0.1.0

### `sitesReferencing(Kumwe\Access\AuthorizationResource $resource, array $sites): array`

Name every site any inspector reports as still referring to the resource.

@param   AuthorizationResource  $resource  Resource whose owning scope is about to narrow.
@param   list<string>           $sites     Site identifiers that would lose reach.

@return  list<string>  De-duplicated union in site-identifier order.

@since  0.1.0

## `Kumwe\Access\ConfigProvider`

Deterministic service declarations with explicit host policy and no active capabilities.

@since 0.1.0

### `__invoke(): array`

Declare shared bootstrap registries and reference composition for one host container lifetime.

@return array{dependencies: array{factories: array<class-string, class-string>,
        shared: array<class-string, bool>}} Factories; no authority ports or values are registered.
@since 0.1.0

## `Kumwe\Access\Container\AuthorizationPolicyRegistryFactory`

Build the registry only after the host explicitly supplies membership policy.

@since 0.1.0

### `__invoke(Psr\Container\ContainerInterface $container): Kumwe\Access\AuthorizationPolicyRegistry`

Read kumwe.access.membership_resource_types; absence or malformed input refuses construction.

@param ContainerInterface $container Host container with the config service.
@return AuthorizationPolicyRegistry Empty, lifecycle-independent registry.
@throws InvalidArgumentException On missing policy or invalid entries.
@since 0.1.0

## `Kumwe\Access\Container\CompositeResourceOwnershipReferencesFactory`

Compose explicit host inspector services without activation or service discovery.

@since 0.1.0

### `__invoke(Psr\Container\ContainerInterface $container): Kumwe\Access\CompositeResourceOwnershipReferences`

Resolve the required kumwe.access.reference_inspectors list in its declared order.

@param ContainerInterface $container Host container exposing config and each inspector service.
@return CompositeResourceOwnershipReferences Bounded reference union service.
@throws InvalidArgumentException On absent/oversized list, recursive or wrongly typed services.
@since 0.1.0

## `Kumwe\Access\Container\ResourceOwnershipScopePolicyFactory`

Build ownership rules from an explicit host-owned reserved table.

@since 0.1.0

### `__invoke(Psr\Container\ContainerInterface $container): Kumwe\Access\ResourceOwnershipScopePolicy`

Resolve kumwe.access.reserved_ownership_rules using stable OwnershipScopeRule values.

@param ContainerInterface $container Host container exposing config.
@return ResourceOwnershipScopePolicy Snapshot with unknown-isolation fallback.
@throws InvalidArgumentException On absent or malformed table.
@since 0.1.0

## `Kumwe\Access\DecisionCombiner`

Stateless bounded aggregation with deny, step-up, allow, abstain precedence.

@since 0.1.0

### `combine(iterable $decisions): Kumwe\Access\AuthorizationDecision`

Return the strongest result; equal outcomes choose policy then reason in byte order.

Every entry is consumed (at most 1024), even after a denial, to reject oversized/hostile input.
Empty input returns not_applicable with access.aggregate.v1/no_applicable_policy metadata.
The caller owns rule evaluation, authentication, context authority and audit.

@param iterable<AuthorizationDecision> $decisions Already evaluated immutable outcomes.
@return AuthorizationDecision Deterministic selected decision, or explicit abstention.
@throws InvalidArgumentException On more than 1024 consumed decisions.
@since 0.1.0

## `Kumwe\Access\DecisionState`

Closed neutral decision vocabulary; only Allow grants permission.

@since 0.1.0

Public constants/cases: `Allow`, `Deny`, `NotApplicable`, `StepUp`.

## `Kumwe\Access\GrantScope`

The reach of a grant: the whole installation, or one named resource within it.

A capability says what may be done; this says where. A scope is either global, which covers every
request, or a type-and-identifier pair such as `site`/`primary`, which covers only a request naming
that exact resource. `covers()` is deliberately asymmetric — the scope authority was granted at is
asked whether it reaches the scope a request is made against — and that one-way test is what keeps a
grant over a single site from being read as authority over the installation. `CapabilityGrant` and
`PrincipalGrant` each carry one, and both states are reachable only through `global()` and
`named()`, so an unvalidated pair cannot exist.

@since  0.1.0

### `covers(Kumwe\Access\GrantScope $requested): bool`

Whether a grant held at this scope reaches a request made at another.

The receiver is the scope authority was granted at and the argument is the scope being asked for,
so the test runs one way: the global scope covers everything, and a named scope covers only an
identical type and identifier — never a different resource, and never the installation at large.

@param   self  $requested  Scope the request is being made against.

@return  bool  True when the grant's reach includes the requested scope.

@since  0.1.0

### `equals(Kumwe\Access\GrantScope $other): bool`

Whether two scopes are the same scope.

Distinct from `covers()` in being symmetric: the global scope equals only the global scope, even
though it covers every other one.

@param   self  $other  Scope to compare against.

@return  bool  True when both the type and the identifier match.

@since  0.1.0

### `global(): Kumwe\Access\GrantScope`

The unrestricted scope, which covers every request whatever its type or identifier.

@return  self  The global scope, whose `identifier()` is null.

@since  0.1.0

### `identifier(): ?string`

The resource this scope is limited to, when it is limited to one.

@return  ?string  Identifier of the named resource; null means unrestricted, not unknown.

@since  0.1.0

### `isGlobal(): bool`

Whether this scope reaches the whole installation rather than a single resource.

@return  bool  True for the scope `global()` builds.

@since  0.1.0

### `named(string $type, string $identifier): Kumwe\Access\GrantScope`

Build a scope restricted to one identified resource.

The type is trimmed and lowercased and the identifier trimmed before either is judged, so values
read back from configuration or a stored row need no cleaning first. `global` is refused as a
type here because the unrestricted scope carries no identifier and must come from `global()`.

@param   string  $type        Kind of resource the grant is limited to, such as `site`.
@param   string  $identifier  Identifier of that resource, as the store spells it.

@return  self  A scope covering that one resource.

@throws  InvalidArgumentException  When the type is `global`, when the type is not a lowercase
         identifier of 1 to 63 characters, or when the identifier is empty, longer than 191
         characters, or carries control characters.

@since  0.1.0

### `type(): string`

The kind of resource this scope is limited to.

@return  string  A resource type such as `site`, or `global` for the unrestricted scope.

@since  0.1.0

## `Kumwe\Access\MembershipContextValidator`

Inward-facing freshness authority for versioned organization membership contexts.

A `MembershipContext` is a credential snapshot, not proof that its organization and optional
workspace still confer authority. The canonical authorization gateway asks this port on every
decision before treating those contextual identifiers as grant scopes. Implementations compare the
exact actor, site, membership row, versions, policy generation, and workspace assignment with live
state and fail closed when any part cannot be verified.

@since  0.1.0

### `current(string $subjectId, Kumwe\Context\Value\SiteContext $site, Kumwe\Context\Value\MembershipContext $membership, bool $lock = optional): bool`

Revalidate one exact membership snapshot against its current authority source.

@param   string             $subjectId   Actor expected to hold the membership.
@param   SiteContext        $site        Exact site the decision executes in.
@param   MembershipContext  $membership  Versioned organization and optional workspace snapshot.
@param   bool               $lock        Whether to hold the live membership for a following mutation.

@return  bool  True only when every identity, lifecycle, time, version, generation, and workspace
         binding is still current; false when stale or unverifiable.

@since  0.1.0

## `Kumwe\Access\MembershipRequirement`

Explicit immutable host declaration of resource types requiring fresh membership.

@since 0.1.0

Public properties: `resourceTypes` (array, readonly); 

### `__construct(iterable $resourceTypes)`

Snapshot a host-owned policy without default sensitive categories.

@param iterable<string> $resourceTypes Exact types selected by the composition root.
@throws InvalidArgumentException On malformed types or more than 4096 consumed entries.
@since 0.1.0

### `requiredFor(string $resourceType): bool`

Inspect configured metadata only; this does not validate a membership or grant authority.

@param string $resourceType Resource type being examined.
@return bool Whether the host explicitly requires fresh membership for this type.
@since 0.1.0

## `Kumwe\Access\OwnershipNarrowingRefused`

Raised when narrowing an owning scope would leave another site's records pointing at nothing.

Widening is cheap because it only adds reach. Narrowing takes reach away, and the sites losing it may
already have built records around the shared resource; completing the change would leave those records
referring to something they can no longer see. The operation therefore refuses and names the sites, so
an operator resolves the references first and repeats the narrowing rather than discovering the damage
afterwards. The asymmetry is deliberate and is stated in the operator documentation.

@since  0.1.0

Public properties: `referencingSites` (array, mutable); 

### `__construct(Kumwe\Access\AuthorizationResource $resource, Kumwe\Access\OwnershipScope $target, array $referencingSites)`

Name the resource, the refused target scope and every site that would be stranded.

@param  AuthorizationResource  $resource          Resource whose owning scope was being narrowed.
@param  OwnershipScope         $target            Scope the caller asked to narrow to.
@param  list<string>           $referencingSites  Sites whose records still refer to the resource.

@since  0.1.0

## `Kumwe\Access\OwnershipNarrowingUnbounded`

Raised when a narrowing cannot name the sites it would take reach away from.

The installation scope means every site there is and every site there will be, so the set losing reach
when a resource leaves it is not a list the registry can enumerate and prove safe. Rather than run the
stranded-reference guard over a set it cannot bound — which would answer "nothing would be stranded"
simply because it had nothing to look at — the operation refuses. An operator who needs the resource at
a narrower owner records a new one at that owner and withdraws the installation-scoped resource
deliberately, which keeps the decision about what happens to the references an explicit one.

@since  0.1.0

### `__construct(Kumwe\Access\AuthorizationResource $resource, Kumwe\Access\OwnershipScope $current)`

Name the resource and the owner it cannot be narrowed out of.

@param  AuthorizationResource  $resource  Resource whose owning scope was being narrowed.
@param  OwnershipScope         $current   Owner whose membership cannot be enumerated.

@since  0.1.0

## `Kumwe\Access\OwnershipScope`

The single owner of a resource, held at a site, a declared group of sites, or the installation.

Every resource keeps exactly one owner; this is that owner, widened from a bare site identifier to a
level plus the membership it resolves to. The membership is carried on the instance rather than looked
up during a decision, so `contains()` is a pure test the authorization gateway can run on the hot path
without a second query — group membership is administrative state resolved once, not transactional
state resolved per call. For a site scope `contains()` reduces to exactly the identifier equality it
replaced, which is the property that makes the widening safe for every resource owned today.

@since  0.1.0

Public properties: `identifier` (string, readonly); `level` (Kumwe\Access\OwnershipScopeLevel, readonly); `sites`
(array, readonly);

### `contains(Kumwe\Context\Value\SiteContext $site): bool`

Whether a site is inside this owning scope.

For a site scope this is the identifier equality it replaced, byte for byte. For a group scope it
is membership of the declared set. For the installation scope it is true, because the installation
owns every site — the gateway still demands a global grant there, so answering true widens nothing.

@param   SiteContext  $site  Site the caller is executing in.

@return  bool  True when the site may reach resources this scope owns.

@since  0.1.0

### `describe(): string`

Render the scope for an audit entry, a denial reason, or an operator-facing message.

@return  string  `site:<identifier>`, `group:<identifier>`, or `installation:*`.

@since  0.1.0

### `equals(Kumwe\Access\OwnershipScope $other): bool`

Whether two scopes name the same owner.

Only the level and the identifier are compared. Two readings of one group taken either side of a
membership change are the same owner, which is what a compare-and-set on ownership must mean.

@param   self  $other  Scope being compared against.

@return  bool  True when both name the same owner.

@since  0.1.0

### `group(Kumwe\Access\SiteGroup $group): Kumwe\Access\OwnershipScope`

Own a resource at a declared group, visible to that group's members and to nobody else.

@param   SiteGroup  $group  Declared group, already resolved to the members it currently has.

@return  self  A group-level scope carrying the group's membership.

@since  0.1.0

### `installation(): Kumwe\Access\OwnershipScope`

Own a resource at the installation, which a human may only reach with a global grant.

The membership list is deliberately empty: an installation scope is not a set of sites an operator
maintains, and `DenyByDefaultAuthorizationGateway` answers it with the global-grant requirement
rather than with a containment test.

@return  self  The installation-level scope.

@since  0.1.0

### `isInstallation(): bool`

Whether this scope is the installation itself.

@return  bool  True only at installation level, where a global human grant is required.

@since  0.1.0

### `requireSite(): Kumwe\Context\Value\SiteContext`

The single owning site, for a caller that can only act on behalf of one.

Durable background work — a job, a schedule — runs as the site that owns it, so a caller building
an execution context needs one site rather than a set. Categories that carry such work are declared
site-only in `ResourceOwnershipScopePolicy`, so this refuses rather than choosing a member.

@return  SiteContext  The owning site.

@throws  OwnershipScopeNotSiteBound  When the scope is a group or the installation.

@since  0.1.0

### `site(Kumwe\Context\Value\SiteContext $site): Kumwe\Access\OwnershipScope`

Own a resource at one site, which is what every ownership row meant before groups existed.

This is the constructor every existing `record()` call site translates to, so a caller that
legitimately means "this site owns it" keeps saying so and keeps the behaviour it had.

@param   SiteContext  $site  Site that owns the resource.

@return  self  A site-level scope whose membership is that one site.

@since  0.1.0

### `siteOrNull(): ?Kumwe\Context\Value\SiteContext`

The single owning site, when there is one.

@return  ?SiteContext  The owning site at site level, or null for a group or the installation.

@since  0.1.0

## `Kumwe\Access\OwnershipScopeChangeRejected`

Raised when a scope change is neither a widening nor a narrowing of the owner it starts from.

`widen()` and `narrow()` are separate operations because they carry different obligations, so each
must be handed a target that actually moves in its direction. A caller asking to "widen" a resource
from one group to an unrelated group of the same size is asking for something that adds and removes
reach at once, which no single guard can make safe; it is refused so the caller performs the two
moves explicitly and each is judged on its own.

@since  0.1.0

### `__construct(Kumwe\Access\AuthorizationResource $resource, Kumwe\Access\OwnershipScope $current, Kumwe\Access\OwnershipScope $target, string $direction)`

Name both scopes and the direction that was asked for.

@param  AuthorizationResource  $resource   Resource whose owning scope was being changed.
@param  OwnershipScope         $current    Owner the resource holds now.
@param  OwnershipScope         $target     Owner the caller asked for.
@param  string                 $direction  Operation that refused: `widen` or `narrow`.

@since  0.1.0

## `Kumwe\Access\OwnershipScopeLevel`

The level an owning scope is held at: one site, a declared group of sites, or the installation.

A resource still has exactly one owner; this names how wide that single owner is. The order the cases
are written in is the order of reach, and `reach()` exposes it so a scope change can be classified as
widening or narrowing without a table of special cases. Storage keeps the backing value verbatim in
`resource_site_ownership.scope_level`, so a new case would be a schema-visible change rather than a
private refactor.

@since  0.1.0

Public constants/cases: `Group`, `Installation`, `Site`.

### `reach(): int`

Report how far this level reaches, so two levels can be ordered without enumerating pairs.

@return  int  1 for a site, 2 for a group, 3 for the installation; larger is wider.

@since  0.1.0

### `widerThan(Kumwe\Access\OwnershipScopeLevel $other): bool`

Whether this level reaches strictly further than another.

@param   self  $other  Level being compared against.

@return  bool  True only when this level is wider; equal levels are neither wider nor narrower.

@since  0.1.0

## `Kumwe\Access\OwnershipScopeNotPermitted`

Raised when a resource category is asked to be owned at a level its rule does not admit.

This is the refusal that keeps a legal entity's books out of joint ownership. It fires at the point an
ownership fact is constructed, in `ResourceOwnership::of()`, rather than at the database, so the
impermissible pairing never reaches storage and no code path exists that could write one. Reaching an
operator, it means someone asked to share a category this build declares isolated; the answer is to
share a different category, never to change the rule.

@since  0.1.0

### `__construct(Kumwe\Access\AuthorizationResource $resource, Kumwe\Access\OwnershipScope $scope, Kumwe\Access\OwnershipScopeRule $rule)`

Name the category, the refused level and the rule that refused it.

@param  AuthorizationResource  $resource  Target whose ownership was being established.
@param  OwnershipScope         $scope     Owner that was asked for.
@param  OwnershipScopeRule     $rule      Rule this build fixes for the resource's category.

@since  0.1.0

## `Kumwe\Access\OwnershipScopeNotSiteBound`

Raised when a caller that can only act for one site is handed a scope that names several.

Durable background work executes as the site that owns it, so the worker and the scheduler need a
single site rather than a set. Every category that carries such work is declared site-only in
`ResourceOwnershipScopePolicy`, which means this exception marks a broken invariant — an ownership row
that was widened past what its category permits — and not an ordinary operator mistake. Refusing here
keeps the failure loud instead of silently electing one member of a group to run as.

@since  0.1.0

### `__construct(Kumwe\Access\OwnershipScope $scope)`

Name the offending scope in the operator-facing message.

@param  OwnershipScope  $scope  Scope that owns the resource but names no single site.

@since  0.1.0

## `Kumwe\Access\OwnershipScopeRule`

The set of ownership levels one resource category may be held at.

A category declares a rule, not a list, so the permitted combinations are closed: there is no way to
write down "group but not site", and no way to assemble a set at runtime from configuration. That is
what makes accounting isolation structural rather than operational — a ledger's category resolves to
`SiteOnly`, and `SiteOnly::permits()` answers false for a group with no branch an operator can reach.
Every rule permits the site level, because a resource that no site may own could never be created.

@since  0.1.0

Public constants/cases: `SiteGroupOrInstallation`, `SiteOnly`, `SiteOrGroup`.

### `levels(): array`

The levels this rule admits, widest last.

@return  list<OwnershipScopeLevel>  Permitted levels in order of reach, for documentation and
         administration screens that show an operator what a category may become.

@since  0.1.0

### `permits(Kumwe\Access\OwnershipScopeLevel $level): bool`

Whether a category under this rule may be owned at a level.

@param   OwnershipScopeLevel  $level  Level an ownership row would be written at.

@return  bool  True only when this rule admits the level.

@since  0.1.0

## `Kumwe\Access\ResourceOwnership`

One resource paired with the scope that owns it, provably permitted for that resource's category.

The constructor is private and `of()` is the only way in, so holding an instance is itself the proof
that the category's rule admits the level: an ownership row that a legal entity's books could not
legitimately carry cannot be assembled, let alone written. Every write path that changes an owner takes
this type rather than a loose resource-and-scope pair, which moves the isolation check from a rule the
registry remembers to apply into a shape the type system will not let a caller skip.

@since  0.1.0

Public properties: `resource` (Kumwe\Access\AuthorizationResource, readonly); `scope` (Kumwe\Access\OwnershipScope,
readonly);

### `of(Kumwe\Access\AuthorizationResource $resource, Kumwe\Access\OwnershipScope $scope, Kumwe\Access\ResourceOwnershipScopePolicy $policy): Kumwe\Access\ResourceOwnership`

Establish who owns a resource, refusing a level the resource's category does not admit.

@param   AuthorizationResource         $resource  Resource being created or reassigned; a collection
         names a family and has no single owner to record.
@param   OwnershipScope                $scope     Owner being proposed for it.
@param   ResourceOwnershipScopePolicy  $policy    Catalogue holding this build's frozen category table.

@return  self  The proven pairing, safe to hand to the ownership registry.

@throws  OwnershipScopeNotPermitted  When the category may not be owned at that level.
@throws  \InvalidArgumentException  When the resource names a whole collection.

@since  0.1.0

## `Kumwe\Access\ResourceOwnershipReferences`

Answers which sites still point at a resource that is about to move out of their reach.

Narrowing an owning scope is not the inverse of widening it. Widening only adds sites; narrowing takes
them away, and a site that has already built records around a shared resource would be left pointing
at something it can no longer see. This port is how the scope-change service finds that out before it
happens, so narrowing can be refused with the affected sites named instead of silently orphaning them.

An implementation reports only sites drawn from the list it is given, and reports nothing when it has
no opinion. Several implementations are composed, because the references worth protecting come from
different bounded contexts: core knows about scoped grants, and an extension that stores its own
cross-references contributes an implementation that knows about those.

@since  0.1.0

### `sitesReferencing(Kumwe\Access\AuthorizationResource $resource, array $sites): array`

Name the sites, among those about to lose reach, whose records still refer to the resource.

@param   AuthorizationResource  $resource  Resource whose owning scope is about to narrow.
@param   list<string>           $sites     Site identifiers that would lose reach; never empty.

@return  list<string>  The subset that still refers to the resource, in site-identifier order;
         empty when narrowing would strand nothing this implementation knows about.

@since  0.1.0

## `Kumwe\Access\ResourceOwnershipScopePolicy`

Neutral ownership-rule registry with an explicit host-owned reserved category table.

No accounting, content, extension or other host category is embedded here. Unknown categories
remain site-only; reserved categories cannot be redeclared; declarations are idempotent only
for the same rule. The host decides which trusted definitions it registers.

@since 0.1.0

### `__construct(array $reserved)`

Snapshot explicit host policy; unknown types remain site-only.

@param array<string, OwnershipScopeRule> $reserved Immutable host category table.
@throws InvalidArgumentException On invalid categories or more than 4096 entries.
@since 0.1.0

### `permits(string $category, Kumwe\Access\OwnershipScopeLevel $level): bool`

Whether a category may be owned at a level.

@param   string               $category  Authorization resource type being asked about.
@param   OwnershipScopeLevel  $level     Level an ownership row would be written at.

@return  bool  True only when the category's rule admits the level.

@since  0.1.0

### `register(string $category, Kumwe\Access\OwnershipScopeRule $rule): void`

Declare the ownership levels a contributed resource category may be held at.

An extension contributing a new resource family calls this once, before the first resource of that
family is created. Redeclaring the same rule is accepted so that a repeated bootstrap is harmless;
anything else is refused, because a category whose rule can change is a category whose isolation
can be negotiated.

@param   string              $category  Authorization resource type the rule applies to.
@param   OwnershipScopeRule  $rule      Levels resources of that category may be owned at.

@return  void

@throws  InvalidArgumentException  When the category is reserved by core, is not a valid resource
         type, or was already declared with a different rule.

@since  0.1.0

### `rule(string $category): Kumwe\Access\OwnershipScopeRule`

The rule governing one resource category.

@param   string  $category  Authorization resource type being asked about.

@return  OwnershipScopeRule  The reserved rule, the declared rule, or `SiteOnly` when neither
         exists, which keeps an unknown category isolated instead of shareable.

@since  0.1.0

### `table(): array`

The complete table this build freezes, for documentation and administration screens.

@return  array<string, OwnershipScopeRule>  Reserved and declared categories in category order.

@since  0.1.0

## `Kumwe\Access\ResourcePolicyDefinition`

Owner-bound action/resource binding used by the canonical authorization gateway.

A definition states which bounded resource selectors one capability may reach, whether those
resources belong to the whole installation, and which purpose-built system identities may use the
binding without a human grant. It deliberately carries no callback, SQL, or expression string;
attribute policies can layer on this base binding without turning registry loading into code execution.

@since  0.1.0

Public properties: `capability` (Kumwe\Access\Capability, readonly); `definitionVersion` (int, readonly); `id`
(string, readonly); `installationGlobal` (bool, readonly); `lifecycle` (Kumwe\Access\AuthorizationDefinitionLifecycle,
readonly); `owner` (string, readonly); `systemIdentities` (array, readonly); `targets` (array, readonly);

### `__construct(string $id, string $owner, Kumwe\Access\Capability $capability, iterable $targets, bool $installationGlobal, iterable $systemIdentities, Kumwe\Access\AuthorizationDefinitionLifecycle $lifecycle, int $definitionVersion)`

Validate and hold one resource-policy definition.

@param   string                            $id                  Dotted policy identifier under its owner.
@param   string                            $owner               `core` or the owning extension's
         `vendor/name` identifier.
@param   Capability                        $capability          Action whose reach this policy defines.
@param   iterable<ResourcePolicyTarget>    $targets             Non-empty bounded resource selectors.
@param   bool                              $installationGlobal  Whether matching resources require a
         global human grant rather than site ownership.
@param   iterable<string>          $systemIdentities    Core system identities permitted to use it.
@param   AuthorizationDefinitionLifecycle  $lifecycle           Current enforceability state.
@param   int                               $definitionVersion   Positive owner-controlled definition version.

@throws  InvalidArgumentException  When the identifier, owner, targets, system identities, or version is invalid.

@since  0.1.0

### `allowsSystemIdentity(string $identity): bool`

Inspect allowlist membership only; the host/registry must separately enforce lifecycle and authority.

@param   string          $identity  Purpose-built identity carried by the execution context.

@return  bool  True when the immutable allowlist names the identity; this alone never grants permission.

@since  0.1.0

### `enforceable(): bool`

Whether this definition may currently take part in a decision.

@return  bool  True while its lifecycle remains active or deprecated.

@since  0.1.0

### `matches(Kumwe\Access\AuthorizationResource $resource): bool`

Whether this policy binds its capability to the requested resource.

@param   AuthorizationResource  $resource  Target being evaluated.

@return  bool  True when an enforceable target selector covers the resource.

@since  0.1.0

### `overlaps(Kumwe\Access\ResourcePolicyDefinition $other): bool`

Whether this definition overlaps another binding for the same capability.

@param   self  $other  Definition being considered for registration.

@return  bool  True when both could settle the same action/resource request.

@since  0.1.0

### `toArray(): array`

Export the stable metadata shape used by contribution reconciliation and diagnostics.

@return  array{
             id: string,
             owner: string,
             capability: string,
             resources: list<array{type: string, identifiers: list<string>}>,
             installation_global: bool,
             system_identities: list<string>,
             lifecycle: string,
             version: int
         }

@since  0.1.0

## `Kumwe\Access\ResourcePolicyRegistry`

Collision-safe owner-aware registry of capability-to-resource policy bindings.

A policy is accepted only after its capability has been registered by the same owner, and no two
definitions may overlap for one capability. Those invariants leave every action/resource request
with at most one typed base binding and prevent an extension from attaching policy to another
package's capability namespace.

@since  0.1.0

### `__construct(Kumwe\Access\CapabilityDefinitionRegistry $capabilities)`

Bind the policy registry to the capability catalog it validates references against.

@param  CapabilityDefinitionRegistry  $capabilities  Canonical live capability definitions.

@since  0.1.0

### `definitionFor(Kumwe\Access\Capability $capability, Kumwe\Access\AuthorizationResource $resource): ?Kumwe\Access\ResourcePolicyDefinition`

Resolve the single enforceable policy binding an action/resource pair matches.

@param   Capability             $capability  Action being exercised.
@param   AuthorizationResource  $resource    Target the action is aimed at.

@return  ?ResourcePolicyDefinition  Matching policy, or null when the pair is unsupported.

@since  0.1.0

### `definitionsFor(Kumwe\Access\Capability $capability): array`

List every enforceable resource binding for one capability.

Callers that derive credential constraints need the complete typed target set, including
extension-owned definitions, rather than namespace conventions or a second resource catalog.

@param   Capability  $capability  Capability whose live bindings are being inspected.

@return  list<ResourcePolicyDefinition>  Enforceable bindings in deterministic policy-id order.

@since  0.1.0

### `ownedBy(string $owner): array`

List the policy definitions one owner currently holds, ordered by policy identifier.

@param   string  $owner  Definition owner being inventoried.

@return  list<ResourcePolicyDefinition>  Matching definitions in deterministic identifier order.

@since  0.1.0

### `register(Kumwe\Access\ResourcePolicyDefinition $definition): void`

Register one base resource-policy binding.

@param   ResourcePolicyDefinition  $definition  Validated owner-bound policy to add.

@return  void

@throws  InvalidArgumentException  When the id is taken, the capability is missing or foreign,
         or another policy already covers any of the same resources for the capability.

@since  0.1.0

### `removeOwner(string $owner): void`

Withdraw every resource-policy definition belonging to one owner.

@param   string  $owner  Owner being disabled, removed, or made untrusted.

@return  void

@since  0.1.0

## `Kumwe\Access\ResourcePolicyTarget`

One bounded resource selector inside a registered resource policy.

A target always names one resource type. An empty identifier list selects every member and the
collection of that type; a non-empty list selects only the exact identifiers it contains. Keeping
the selector typed prevents policies from smuggling regular expressions or executable predicates
into the base action/resource registry.

@since  0.1.0

Public properties: `identifiers` (array, readonly); `type` (string, readonly); 

### `__construct(string $type, iterable $identifiers = optional)`

Validate one resource type and its optional exact identifier allowlist.

@param   string            $type         Resource family the selector covers.
@param   iterable<string>  $identifiers  Exact identifiers, or an empty iterable for the whole family.

@throws  InvalidArgumentException  When the type, an identifier, or the list bound is invalid.

@since  0.1.0

### `matches(Kumwe\Access\AuthorizationResource $resource): bool`

Whether this selector covers the requested authorization resource.

@param   AuthorizationResource  $resource  Resource being evaluated.

@return  bool  True when the type and, when bounded, the identifier match.

@since  0.1.0

### `overlaps(Kumwe\Access\ResourcePolicyTarget $other): bool`

Whether two selectors could match the same resource.

Registration uses this to reject ambiguous action/resource bindings. A whole-family selector
overlaps every selector of its type; two bounded selectors overlap when they share an identifier.

@param   self  $other  Selector to compare with this one.

@return  bool  True when at least one authorization resource would satisfy both selectors.

@since  0.1.0

### `toArray(): array`

Export the deterministic selector shape used by manifests and diagnostics.

@return  array{type: string, identifiers: list<string>}  Type and sorted exact identifiers.

@since  0.1.0

## `Kumwe\Access\ResourceSiteOwnership`

Authoritative answer to which scope owns a given resource.

Site isolation in a multi-site installation rests on this contract: the gateway asks whether the site
the caller is executing in is inside the scope an implementation names, and denies when it is not. An
implementation must therefore be authoritative rather than best-effort — it may never fill a gap with
the caller's own site, because that would hand a caller any resource it happens to name. Records are
kept in step by `ResourceSiteOwnershipWriter`, which writes them alongside the resources themselves.

The returned scope carries its resolved membership, so a decision never issues a second query to find
out which sites a group contains: group membership is administrative state an implementation resolves
once, not transactional state it looks up per call.

@since  0.1.0

### `scopeFor(Kumwe\Access\AuthorizationResource $resource): Kumwe\Access\OwnershipScope`

Resolve the scope that owns a resource.

@param   AuthorizationResource  $resource  Target whose owning scope is being established.

@return  OwnershipScope  The owner, established from durable records rather than from the caller.

@throws  AuthorizationResourceOwnershipUnknown  When no authoritative record names a reachable owner.

@since  0.1.0

## `Kumwe\Access\ResourceSiteOwnershipConflict`

Raised when ownership is changed on behalf of an owner that does not actually hold the resource.

`ResourceSiteOwnershipWriter::remove()` and `reassign()` both match on the resource *and* the owner the
caller expects, so a statement that affects no row while a record still exists means the caller was
wrong about the owner. Failing here instead of matching by resource alone stops one site from severing
another site's ownership — which would leave that resource unreachable to everyone — and turns two
concurrent scope changes into one change and one refusal rather than a last-writer-wins race.

@since  0.1.0

### `__construct(Kumwe\Access\AuthorizationResource $resource, Kumwe\Access\OwnershipScope $expected, Kumwe\Access\OwnershipScope $actual)`

Name the resource and both owners in the operator-facing message.

@param  AuthorizationResource  $resource  Target whose ownership was being changed.
@param  OwnershipScope         $expected  Owner the caller believed held the resource.
@param  OwnershipScope         $actual    Owner the surviving ownership record names.

@since  0.1.0

## `Kumwe\Access\ResourceSiteOwnershipWriter`

Write side of the resource-to-scope registry that `ResourceSiteOwnership` reads.

Ownership is only trustworthy when it appears and disappears together with the resource it describes,
so implementations are called from inside the caller's transaction rather than opening one of their
own. Both failure directions matter: a resource left without a row becomes unreachable, since the
gateway denies it with `resource_site_unknown`, while a row outliving its resource keeps asserting an
owner that no longer exists. Removal is therefore matched against the owner the caller expects, and
says so when the record disagrees rather than deleting whatever it finds.

Creation and removal still speak in sites, because a resource is born owned by the site that made it
and every existing call site legitimately means exactly that. Changing the owner of a living resource
is `reassign()`, which takes a proven `ResourceOwnership` and the owner the caller believes it is
replacing, so a concurrent change loses rather than silently wins.

@since  0.1.0

### `reassign(Kumwe\Access\ResourceOwnership $owner, Kumwe\Access\OwnershipScope $expected): void`

Move a living resource from one owning scope to another.

The resource itself does not move; only the scope that owns it changes, so there is no data
migration and no window in which the resource is unowned. The expected owner is part of the match,
which makes the write a compare-and-set: two operators widening the same resource at once produce
one change and one refusal instead of a last-writer-wins race.

@param   ResourceOwnership  $owner     Proven pairing of the resource with the scope it moves to.
@param   OwnershipScope     $expected  Scope the caller believes owns it now.

@return  void

@throws  ResourceSiteOwnershipConflict  When a record exists but names an owner other than the
         expected one.
@throws  AuthorizationResourceOwnershipUnknown  When no ownership record exists for the resource.

@since  0.1.0

### `record(Kumwe\Access\AuthorizationResource $resource, Kumwe\Context\Value\SiteContext $site): void`

Record ownership in the same transaction that creates the resource.

@param   AuthorizationResource  $resource  Resource being created; a collection has no owner to record.
@param   SiteContext            $site      Site that will own it until an operator widens the scope.

@return  void

@since  0.1.0

### `remove(Kumwe\Access\AuthorizationResource $resource, Kumwe\Context\Value\SiteContext $expectedSite): void`

Remove ownership in the same transaction that physically deletes the resource.

The expected site is part of the match, so a caller acting for the wrong site withdraws nothing and
is told so, rather than orphaning a resource that belongs to another site. A resource whose scope
has been widened past a single site is not removable this way, because the caller's belief about
the owner is already wrong.

@param   AuthorizationResource  $resource      Resource being deleted; a collection has no row to remove.
@param   SiteContext            $expectedSite  Site the caller believes owns the resource.

@return  void

@throws  ResourceSiteOwnershipConflict  When a record exists but names a different owner.
@throws  AuthorizationResourceOwnershipUnknown  When no ownership record exists for the resource.

@since  0.1.0

## `Kumwe\Access\SiteGroup`

A named, declared set of sites that may jointly own a resource.

A group is declared administrative state, never inferred from a hierarchy: sites A, B and D may share
clients while A and C share products, so groups overlap freely and have no inheritance between them.
Membership is what an ownership scope resolves against, which is why the constructor refuses an empty
set — a group nobody belongs to would own resources nobody could reach, and the registry must fail
closed rather than publish one. Identifiers are validated against the same alphabet as `SiteContext`,
so a group identifier and a site identifier can be compared and bound into a query interchangeably.

@since  0.1.0

Public properties: `identifier` (string, readonly); `members` (array, readonly); `name` (string, readonly); 

### `__construct(string $identifier, string $name, iterable $members)`

Validate and hold one declared group.

@param   string            $identifier  Raw group identifier to normalise and validate.
@param   string            $name        Operator-facing label shown in administration and denials.
@param   iterable<string>  $members     Site identifiers belonging to the group; at least one.

@throws  InvalidArgumentException  When the identifier is not a valid group identifier, the name is
         empty or longer than 191 characters, a member is not a valid site identifier, or the
         resolved membership is empty.

@since  0.1.0

### `contains(Kumwe\Context\Value\SiteContext $site): bool`

Whether a site belongs to this group.

@param   SiteContext  $site  Site being tested for membership, normally the caller's own.

@return  bool  True only when the site was declared a member; membership is never inferred.

@since  0.1.0

## `Kumwe\Access\SiteGroupRegistry`

Authoritative answer to which sites a declared group currently contains.

Group membership is administrative state — an operator adds or removes a site, and that is a rare,
audited event — so an implementation is expected to resolve the whole declared set once and answer
from it, never to issue a query per authorization decision. Membership must be reported fail-closed
on the same terms as site ownership: a site an operator has disabled is not a member for the purpose
of a decision, and a group nobody currently belongs to does not resolve at all.

@since  0.1.0

### `all(): array`

List every declared group that currently resolves.

@return  list<SiteGroup>  Groups in identifier order; empty on an installation that declares none.

@since  0.1.0

### `group(string $identifier): Kumwe\Access\SiteGroup`

Resolve one declared group and the sites it currently contains.

@param   string  $identifier  Group identifier stored on an ownership row or named by an operator.

@return  SiteGroup  The declared group, restricted to the sites that are currently enabled.

@throws  SiteGroupUnknown  When no such group is declared, or none of its members is enabled.

@since  0.1.0

## `Kumwe\Access\SiteGroupUnknown`

Raised when a group identifier resolves to no usable declaration.

Both causes deny in the same direction, which is why they share one exception: the group was never
declared, or every site declared in it has been disabled. Neither may be answered with an empty group,
because an empty owning scope would leave the resources it owns visible to whoever asked next. The
ownership resolver converts this into `resource_site_unknown` so the gateway fails closed exactly as
it already does for a resource with no ownership row at all.

@since  0.1.0

### `__construct(string $identifier)`

Name the unresolved group in the operator-facing message.

@param  string  $identifier  Group identifier that resolved to no enabled membership.

@since  0.1.0

## `Kumwe\Access\SiteGroupWriter`

Write side of the declared-group registry that `SiteGroupRegistry` reads.

A group is a declaration an operator makes, so both halves of it are explicit: which sites are in, and
which are out. An implementation writes the declaration and its membership together, inside the
caller's transaction, because a group that exists with no membership owns resources nobody can reach.
Nothing here authorizes or audits — `SiteGroupAdministration` owns both, so every route to a
declaration change passes the same capability and leaves the same trail.

@since  0.1.0

### `addSite(string $group, Kumwe\Context\Value\SiteContext $site): void`

Bring one site into an existing declaration.

@param   string       $group  Identifier of the declared group.
@param   SiteContext  $site   Site being included.

@return  void

@throws  SiteGroupUnknown  When no such group is declared.

@since  0.1.0

### `removeSite(string $group, Kumwe\Context\Value\SiteContext $site): void`

Take one site back out of an existing declaration.

@param   string       $group  Identifier of the declared group.
@param   SiteContext  $site   Site being excluded.

@return  void

@throws  SiteGroupUnknown  When no such group is declared.

@since  0.1.0

### `save(Kumwe\Access\SiteGroup $group): void`

Create or replace one declaration and its complete membership.

@param   SiteGroup  $group  Declaration to store, carrying the exact membership it should end with.

@return  void

@since  0.1.0

