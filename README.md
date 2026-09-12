# Kumwe Access Control

[![Packagist version][version-badge]][package]
[![Access CI][ci-badge]][ci]
[![PHP requirement][php-badge]][package]
[![License][license-badge]](LICENSE)

[version-badge]: https://img.shields.io/packagist/v/kumwe/access-control
[package]: https://packagist.org/packages/kumwe/access-control
[ci-badge]: https://github.com/kumwe/access-control/actions/workflows/ci.yml/badge.svg?branch=main
[ci]: https://github.com/kumwe/access-control/actions/workflows/ci.yml
[php-badge]: https://img.shields.io/packagist/dependency-v/kumwe/access-control/php
[license-badge]: https://img.shields.io/packagist/l/kumwe/access-control

Portable authorization decisions, owner-bound capability/resource-policy registries, scope models and explicit host
authority ports. Canonical PHP namespace: `Kumwe\Access\`. Requires PHP 8.5, exact Access Context 0.1.2 and
PSR Container 2. No native extension is required.

## Installation and use

Install the published package with an exact pre-1.0 pin:

```sh
composer require kumwe/access-control:0.1.2
```

```php
<?php

require 'vendor/autoload.php';

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

## Core integration

Register ConfigProvider in Core's composition root with explicit membership targets, reserved ownership rules and
reference inspectors. Core supplies authority, membership resolution, persistence, transactions and audit adapters.
See the [Core contract](docs/core-contract.md) and [integration guide](docs/integration.md) for service lifetimes,
configuration and retained integration tests.

[Public API](docs/public-api.md), [architecture](docs/architecture.md), [charter](CHARTER.md),
[release record](docs/release-record.md) and [security policy](docs/security.md) describe supported boundaries.
Published versions and CI status are linked above; Core integration is validated in the consuming repository.

## Development and releases

```sh
composer install
composer check
composer examples
```

CI installs the built ZIP as a no-development, authoritative-classmap dependency and exercises every export,
example and configured service. [Testing](docs/testing.md) explains package and Core ownership.
[Release guidance](docs/releasing.md) and [dependency verification](docs/dependency-release-gate.md) distinguish
publication identity checks from independent consumer evidence. Licensed under [Apache-2.0](LICENSE).
