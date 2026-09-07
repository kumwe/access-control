<?php

declare(strict_types=1);

namespace Kumwe\Access;

use InvalidArgumentException;

/**
 * The frozen table of which ownership levels each resource category may be held at.
 *
 * This is what makes "accounting is isolated by design" a property of the build rather than of an
 * operator's discipline. The core table below is PHP source, not configuration: there is no environment
 * variable, settings row or manifest key that turns a ledger into shared property, and an extension that
 * contributes an accounting category inherits the rule rather than choosing one. Categories core has not
 * reserved may be declared once by whoever contributes them, and a category nobody declares falls back to
 * `SiteOnly`, so a new resource family is isolated until someone deliberately opts it into sharing.
 *
 * The catalogue only answers questions; enforcement happens where an owner is constructed, in
 * `ResourceOwnership::of()`, so an impermissible pairing never reaches the registry to be refused there.
 *
 * @since  0.1.0
 */
final class ResourceOwnershipScopePolicy
{
    /** Host-owned frozen category rules. @var array<string, OwnershipScopeRule> @since 0.1.0 */
    private readonly array $reserved;

    /**
     * Snapshot explicit host policy; unknown types remain site-only.
     *
     * @param array<string, OwnershipScopeRule> $reserved Immutable host category table.
     * @throws InvalidArgumentException On invalid categories or more than 4096 entries.
     * @since 0.1.0
     */
    public function __construct(array $reserved)
    {
        if (count($reserved) > 4096) {
            throw new InvalidArgumentException('At most 4096 reserved categories are accepted.');
        }
        $snapshot = [];
        foreach ($reserved as $category => $rule) {
            AuthorizationResource::collection($category);
            $snapshot[$category] = $rule;
        }
        $this->reserved = $snapshot;
    }

    /**
     * Rules declared for categories core has not reserved, keyed by category.
     *
     * @var    array<string, OwnershipScopeRule>
     * @since  0.1.0
     */
    private array $declared = [];

    /**
     * Declare the ownership levels a contributed resource category may be held at.
     *
     * An extension contributing a new resource family calls this once, before the first resource of that
     * family is created. Redeclaring the same rule is accepted so that a repeated bootstrap is harmless;
     * anything else is refused, because a category whose rule can change is a category whose isolation
     * can be negotiated.
     *
     * @param   string              $category  Authorization resource type the rule applies to.
     * @param   OwnershipScopeRule  $rule      Levels resources of that category may be owned at.
     *
     * @return  void
     *
     * @throws  InvalidArgumentException  When the category is reserved by core, is not a valid resource
     *          type, or was already declared with a different rule.
     *
     * @since  0.1.0
     */
    public function register(string $category, OwnershipScopeRule $rule): void
    {
        if (preg_match('/^[a-z][a-z0-9._-]{0,62}$/D', $category) !== 1) {
            throw new InvalidArgumentException('An ownership-scope category must be a lowercase identifier.');
        }
        if (isset($this->reserved[$category])) {
            throw new InvalidArgumentException(sprintf(
                'Resource category %s has an ownership-scope rule fixed by this build and cannot be redeclared.',
                $category,
            ));
        }
        $existing = $this->declared[$category] ?? null;
        if ($existing !== null && $existing !== $rule) {
            throw new InvalidArgumentException(sprintf(
                'Resource category %s is already declared as %s.',
                $category,
                $existing->value,
            ));
        }

        if ($existing === null && count($this->declared) >= 4096) {
            throw new InvalidArgumentException('At most 4096 declared categories are accepted.');
        }
        $this->declared[$category] = $rule;
    }

    /**
     * The rule governing one resource category.
     *
     * @param   string  $category  Authorization resource type being asked about.
     *
     * @return  OwnershipScopeRule  The reserved rule, the declared rule, or `SiteOnly` when neither
     *          exists, which keeps an unknown category isolated instead of shareable.
     *
     * @since  0.1.0
     */
    public function rule(string $category): OwnershipScopeRule
    {
        return $this->reserved[$category] ?? $this->declared[$category] ?? OwnershipScopeRule::SiteOnly;
    }

    /**
     * Whether a category may be owned at a level.
     *
     * @param   string               $category  Authorization resource type being asked about.
     * @param   OwnershipScopeLevel  $level     Level an ownership row would be written at.
     *
     * @return  bool  True only when the category's rule admits the level.
     *
     * @since  0.1.0
     */
    public function permits(string $category, OwnershipScopeLevel $level): bool
    {
        return $this->rule($category)->permits($level);
    }

    /**
     * The complete table this build freezes, for documentation and administration screens.
     *
     * @return  array<string, OwnershipScopeRule>  Reserved and declared categories in category order.
     *
     * @since  0.1.0
     */
    public function table(): array
    {
        $table = $this->reserved + $this->declared;
        ksort($table, SORT_STRING);

        return $table;
    }
}
