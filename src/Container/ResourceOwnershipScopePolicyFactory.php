<?php

declare(strict_types=1);

namespace Kumwe\Access\Container;

use InvalidArgumentException;
use Kumwe\Access\OwnershipScopeRule;
use Kumwe\Access\ResourceOwnershipScopePolicy;
use Psr\Container\ContainerInterface;

/**
 * Build ownership rules from an explicit host-owned reserved table.
 *
 * @since 0.1.0
 */
final class ResourceOwnershipScopePolicyFactory
{
    /**
     * Resolve kumwe.access.reserved_ownership_rules using stable OwnershipScopeRule values.
     *
     * @param ContainerInterface $container Host container exposing config.
     * @return ResourceOwnershipScopePolicy Snapshot with unknown-isolation fallback.
     * @throws InvalidArgumentException On absent or malformed table.
     * @since 0.1.0
     */
    public function __invoke(ContainerInterface $container): ResourceOwnershipScopePolicy
    {
        $configuration = $container->get('config');
        $rules = is_array($configuration) ? ($configuration['kumwe']['access']['reserved_ownership_rules'] ?? null) : null;
        if (!is_array($rules)) {
            throw new InvalidArgumentException('Explicit reserved_ownership_rules configuration is required.');
        }
        $typed = [];
        foreach ($rules as $category => $rule) {
            if (!is_string($category) || !is_string($rule) || OwnershipScopeRule::tryFrom($rule) === null) {
                throw new InvalidArgumentException('Reserved ownership rules require named categories and valid rules.');
            }
            $typed[$category] = OwnershipScopeRule::from($rule);
        }
        return new ResourceOwnershipScopePolicy($typed);
    }
}
