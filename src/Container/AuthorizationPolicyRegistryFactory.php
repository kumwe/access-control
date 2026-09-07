<?php

declare(strict_types=1);

namespace Kumwe\Access\Container;

use InvalidArgumentException;
use Kumwe\Access\AuthorizationPolicyRegistry;
use Kumwe\Access\MembershipRequirement;
use Psr\Container\ContainerInterface;

/**
 * Build the registry only after the host explicitly supplies membership policy.
 *
 * @since 0.1.0
 */
final class AuthorizationPolicyRegistryFactory
{
    /**
     * Read kumwe.access.membership_resource_types; absence or malformed input refuses construction.
     *
     * @param ContainerInterface $container Host container with the config service.
     * @return AuthorizationPolicyRegistry Empty, lifecycle-independent registry.
     * @throws InvalidArgumentException On missing policy or invalid entries.
     * @since 0.1.0
     */
    public function __invoke(ContainerInterface $container): AuthorizationPolicyRegistry
    {
        $configuration = $container->get('config');
        $kumwe = is_array($configuration) ? ($configuration['kumwe'] ?? null) : null;
        $access = is_array($kumwe) ? ($kumwe['access'] ?? null) : null;
        $types = is_array($access) ? ($access['membership_resource_types'] ?? null) : null;
        if (!is_array($types) || !array_is_list($types)) {
            throw new InvalidArgumentException('Explicit membership_resource_types list configuration is required.');
        }
        foreach ($types as $type) {
            if (!is_string($type)) {
                throw new InvalidArgumentException('Every membership resource type must be a string.');
            }
        }
        return new AuthorizationPolicyRegistry(new MembershipRequirement($types));
    }
}
