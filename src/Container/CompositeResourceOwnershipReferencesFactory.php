<?php

declare(strict_types=1);

namespace Kumwe\Access\Container;

use InvalidArgumentException;
use Kumwe\Access\CompositeResourceOwnershipReferences;
use Kumwe\Access\ResourceOwnershipReferences;
use Psr\Container\ContainerInterface;

/**
 * Compose explicit host inspector services without activation or service discovery.
 *
 * @since 0.1.0
 */
final class CompositeResourceOwnershipReferencesFactory
{
    /**
     * Resolve the required kumwe.access.reference_inspectors list in its declared order.
     *
     * @param ContainerInterface $container Host container exposing config and each inspector service.
     * @return CompositeResourceOwnershipReferences Bounded reference union service.
     * @throws InvalidArgumentException On absent/oversized list, recursive or wrongly typed services.
     * @since 0.1.0
     */
    public function __invoke(ContainerInterface $container): CompositeResourceOwnershipReferences
    {
        $configuration = $container->get('config');
        $kumwe = is_array($configuration) ? ($configuration['kumwe'] ?? null) : null;
        $access = is_array($kumwe) ? ($kumwe['access'] ?? null) : null;
        $names = is_array($access) ? ($access['reference_inspectors'] ?? null) : null;
        if (!is_array($names) || !array_is_list($names) || count($names) > 64) {
            throw new InvalidArgumentException('Explicit bounded reference_inspectors list configuration is required.');
        }
        $inspectors = [];
        foreach ($names as $name) {
            if (!is_string($name) || $name === CompositeResourceOwnershipReferences::class) {
                throw new InvalidArgumentException('Inspector names must be explicit and nonrecursive.');
            }
            $inspector = $container->get($name);
            if (!$inspector instanceof ResourceOwnershipReferences) {
                throw new InvalidArgumentException('Inspectors must implement ResourceOwnershipReferences.');
            }
            $inspectors[] = $inspector;
        }
        return new CompositeResourceOwnershipReferences($inspectors);
    }
}
