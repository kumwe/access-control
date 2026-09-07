<?php

declare(strict_types=1);

namespace Kumwe\Access;

use Kumwe\Context\Value\SiteContext;

use InvalidArgumentException;

/**
 * A named, declared set of sites that may jointly own a resource.
 *
 * A group is declared administrative state, never inferred from a hierarchy: sites A, B and D may share
 * clients while A and C share products, so groups overlap freely and have no inheritance between them.
 * Membership is what an ownership scope resolves against, which is why the constructor refuses an empty
 * set — a group nobody belongs to would own resources nobody could reach, and the registry must fail
 * closed rather than publish one. Identifiers are validated against the same alphabet as `SiteContext`,
 * so a group identifier and a site identifier can be compared and bound into a query interchangeably.
 *
 * @since  0.1.0
 */
final readonly class SiteGroup
{
    /** Normalized display name, at most 191 bytes. @var string @since 0.1.0 */
    public string $name;

    /**
     * Normalised identifier the ownership registry stores and the gateway resolves membership by.
     *
     * @var    string
     * @since  0.1.0
     */
    public string $identifier;

    /**
     * Member site identifiers, de-duplicated and sorted so two equal groups compare equal.
     *
     * @var    list<string>
     * @since  0.1.0
     */
    public array $members;

    /**
     * Validate and hold one declared group.
     *
     * @param   string            $identifier  Raw group identifier to normalise and validate.
     * @param   string            $name        Operator-facing label shown in administration and denials.
     * @param   iterable<string>  $members     Site identifiers belonging to the group; at least one.
     *
     * @throws  InvalidArgumentException  When the identifier is not a valid group identifier, the name is
     *          empty or longer than 191 characters, a member is not a valid site identifier, or the
     *          resolved membership is empty.
     *
     * @since  0.1.0
     */
    public function __construct(string $identifier, string $name, iterable $members)
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $identifier) === 1) {
            throw new InvalidArgumentException('Raw identifiers cannot contain control bytes.');
        }
        $identifier = strtolower(trim($identifier));
        if (preg_match('/^[a-z0-9][a-z0-9._:-]{0,190}$/D', $identifier) !== 1) {
            throw new InvalidArgumentException('A site group must be a valid non-empty identifier.');
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $name) === 1) {
            throw new InvalidArgumentException('Raw identifiers cannot contain control bytes.');
        }
        $label = trim($name);
        if ($label === '' || strlen($label) > 191) {
            throw new InvalidArgumentException('A site group name must be between 1 and 191 characters.');
        }

        $this->name = $label;
        $sites = [];
        $consumed = 0;
        foreach ($members as $member) {
            if (++$consumed > 4096) {
                throw new InvalidArgumentException('A site group accepts at most 4096 member entries.');
            }
            $value = SiteContext::fromString($member)->identifier();
            $sites['id:' . $value] = $value;
        }
        if ($sites === []) {
            throw new InvalidArgumentException('A site group must declare at least one member site.');
        }
        ksort($sites, SORT_STRING);

        $this->identifier = $identifier;
        $this->members = array_values($sites);
    }

    /**
     * Whether a site belongs to this group.
     *
     * @param   SiteContext  $site  Site being tested for membership, normally the caller's own.
     *
     * @return  bool  True only when the site was declared a member; membership is never inferred.
     *
     * @since  0.1.0
     */
    public function contains(SiteContext $site): bool
    {
        return in_array($site->identifier(), $this->members, true);
    }
}
