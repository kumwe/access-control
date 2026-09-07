<?php

declare(strict_types=1);

namespace Kumwe\Access;

use Kumwe\Access\Container\AuthorizationPolicyRegistryFactory as RegistryFactory;
use Kumwe\Access\Container\CompositeResourceOwnershipReferencesFactory as ReferencesFactory;
use Kumwe\Access\Container\ResourceOwnershipScopePolicyFactory as ScopeFactory;

/**
 * Deterministic service declarations with explicit host policy and no active capabilities.
 *
 * @since 0.1.0
 */
final class ConfigProvider
{
    /**
     * Declare shared bootstrap registries and reference composition for one host container lifetime.
     *
     * @return array{dependencies: array{factories: array<class-string,
     *         class-string<RegistryFactory|ReferencesFactory|ScopeFactory>>,
     *         shared: array<class-string, bool>}} Factories; no authority ports or values are registered.
     * @since 0.1.0
     */
    public function __invoke(): array
    {
        return ['dependencies' => [
            'factories' => [
                AuthorizationPolicyRegistry::class => RegistryFactory::class,
                ResourceOwnershipScopePolicy::class => ScopeFactory::class,
                CompositeResourceOwnershipReferences::class => ReferencesFactory::class,
            ],
            'shared' => [AuthorizationPolicyRegistry::class => true, ResourceOwnershipScopePolicy::class => true,
                CompositeResourceOwnershipReferences::class => true],
        ]];
    }
}
