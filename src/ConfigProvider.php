<?php

declare(strict_types=1);

namespace Kumwe\Access;

use Kumwe\Access\Container\AuthorizationPolicyRegistryFactory;
use Kumwe\Access\Container\CompositeResourceOwnershipReferencesFactory;
use Kumwe\Access\Container\ResourceOwnershipScopePolicyFactory;

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
     * @return array{dependencies: array{factories: array<class-string, class-string>,
     *         shared: array<class-string, bool>}} Factories; no authority ports or values are registered.
     * @since 0.1.0
     */
    public function __invoke(): array
    {
        return ['dependencies' => [
            'factories' => [
                AuthorizationPolicyRegistry::class => AuthorizationPolicyRegistryFactory::class,
                ResourceOwnershipScopePolicy::class => ResourceOwnershipScopePolicyFactory::class,
                CompositeResourceOwnershipReferences::class => CompositeResourceOwnershipReferencesFactory::class,
            ],
            'shared' => [AuthorizationPolicyRegistry::class => true, ResourceOwnershipScopePolicy::class => true,
                CompositeResourceOwnershipReferences::class => true],
        ]];
    }
}
