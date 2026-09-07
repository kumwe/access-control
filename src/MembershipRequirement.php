<?php

declare(strict_types=1);

namespace Kumwe\Access;

use InvalidArgumentException;

/**
 * Explicit immutable host declaration of resource types requiring fresh membership.
 *
 * @since 0.1.0
 */
final readonly class MembershipRequirement
{
    /** Sorted normalized resource types. @var list<string> @since 0.1.0 */
    public array $resourceTypes;

    /**
     * Snapshot a host-owned policy without default sensitive categories.
     *
     * @param iterable<string> $resourceTypes Exact types selected by the composition root.
     * @throws InvalidArgumentException On malformed types or more than 4096 consumed entries.
     * @since 0.1.0
     */
    public function __construct(iterable $resourceTypes)
    {
        $types = [];
        $consumed = 0;
        foreach ($resourceTypes as $type) {
            if (++$consumed > 4096) {
                throw new InvalidArgumentException('At most 4096 membership resource types are accepted.');
            }
            AuthorizationResource::collection($type);
            $types[$type] = true;
        }
        ksort($types, SORT_STRING);
        $this->resourceTypes = array_keys($types);
    }

    /**
     * Inspect configured metadata only; this does not validate a membership or grant authority.
     *
     * @param string $resourceType Resource type being examined.
     * @return bool Whether the host explicitly requires fresh membership for this type.
     * @since 0.1.0
     */
    public function requiredFor(string $resourceType): bool
    {
        return in_array($resourceType, $this->resourceTypes, true);
    }
}
