<?php

declare(strict_types=1);

namespace Kumwe\Access;

use InvalidArgumentException;
use Kumwe\Context\Value\SiteContext;

/**
 * Asks every contributed reference inspector and answers with the union of what they find.
 *
 * References worth protecting live in different bounded contexts, and the scope-change service must not
 * know which. Composing is the union rather than the intersection on purpose: one inspector finding a
 * stranded reference is enough to refuse the narrowing, and an inspector that knows nothing about a
 * resource contributes nothing rather than an implicit approval.
 *
 * @since  0.1.0
 */
final readonly class CompositeResourceOwnershipReferences implements ResourceOwnershipReferences
{
    /**
     * Inspectors consulted for every narrowing, in registration order.
     *
     * @var    list<ResourceOwnershipReferences>
     * @since  0.1.0
     */
    private array $inspectors;

    /**
     * Hold the inspectors this installation contributes.
     *
     * @param  iterable<ResourceOwnershipReferences>  $inspectors  Contributed reference sources.
     *
     * @since  0.1.0
     */
    public function __construct(iterable $inspectors)
    {
        $held = [];
        foreach ($inspectors as $inspector) {
            if (!$inspector instanceof ResourceOwnershipReferences) {
                throw new InvalidArgumentException('Every inspector must implement ResourceOwnershipReferences.');
            }
            if (count($held) >= 64) {
                throw new InvalidArgumentException('At most 64 reference inspectors are accepted.');
            }
            $held[] = $inspector;
        }
        $this->inspectors = $held;
    }

    /**
     * Name every site any inspector reports as still referring to the resource.
     *
     * @param   AuthorizationResource  $resource  Resource whose owning scope is about to narrow.
     * @param   list<string>           $sites     Site identifiers that would lose reach.
     *
     * @return  list<string>  De-duplicated union in site-identifier order.
     *
     * @throws InvalidArgumentException On invalid canonical string lists or bounded-entry overflow.
     *
     * @since  0.1.0
     */
    public function sitesReferencing(AuthorizationResource $resource, array $sites): array
    {
        if (!array_is_list($sites) || count($sites) > 4096) {
            throw new InvalidArgumentException('At most 4096 site candidates are accepted.');
        }
        $candidates = [];
        foreach ($sites as $site) {
            if (!is_string($site) || SiteContext::fromString($site)->identifier() !== $site) {
                throw new InvalidArgumentException('Candidate sites must be canonical string identifiers.');
            }
            $candidates[] = $site;
        }
        $referencing = [];
        foreach ($this->inspectors as $inspector) {
            $returned = $inspector->sitesReferencing($resource, $candidates);
            if (!array_is_list($returned) || count($returned) > 4096) {
                throw new InvalidArgumentException('At most 4096 sites per inspector are accepted.');
            }
            foreach ($returned as $site) {
                if (!is_string($site) || SiteContext::fromString($site)->identifier() !== $site) {
                    throw new InvalidArgumentException('Inspector sites must be canonical string identifiers.');
                }
                if (in_array($site, $candidates, true)) {
                    $referencing['id:' . $site] = $site;
                }
            }
        }
        ksort($referencing, SORT_STRING);

        return array_values($referencing);
    }
}
