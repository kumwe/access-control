# Kumwe Access Control

Portable authorization decisions, owner-bound capability/resource-policy registries, scope models and explicit host
authority ports. Canonical PHP namespace: `Kumwe\Access\`.

**Portable runtime is implemented and 0.1.0 is published.** This successor selects exact published `kumwe/access-context 0.1.1`. Package CI and release identity checks gate publication; independent verification gates App adoption. See [dependency state](docs/dependency-gate.md).

```php
use Kumwe\Access\AuthorizationDecision;
use Kumwe\Access\DecisionCombiner;
use Kumwe\Access\DecisionState;

$result = (new DecisionCombiner())->combine([
    new AuthorizationDecision(DecisionState::Allow, 'example.grants.v1', 'matching_grant'),
    new AuthorizationDecision(DecisionState::StepUp, 'example.assurance.v1', 'fresh_proof_required'),
]);
assert(!$result->allowed);
```

Deny overrides step-up, step-up overrides allow, and abstention grants nothing. Equal-state decisions select policy
then reason in byte order. Empty input explicitly abstains. The host evaluates rules and enforces the resulting
authority; this package has no authentication or audit implementation.

Registries require explicit `MembershipRequirement` and ownership rules require an explicit reserved host table.
Unknown ownership categories default to site-only. Extension capabilities obey their owner namespace, cannot bind
another owner's capability or declare system identities, and collide regardless of lifecycle. Removing capability
ownership makes orphan policy lookups inert.

Requires PHP 8.5, Access Context and PSR Container 2. No native extension. See `CHARTER.md`, `docs/public-api.md`,
`docs/architecture.md`, `docs/integration.md`, `docs/testing.md`, and `MIGRATION-HANDOFF.md` for exact ownership and
future adoption.

Run `composer install`, `composer check` and `composer examples`. CI also installs the built ZIP as a real
no-development, authoritative-classmap dependency and exercises every export and example.
