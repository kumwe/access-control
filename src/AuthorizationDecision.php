<?php

declare(strict_types=1);

namespace Kumwe\Access;

use InvalidArgumentException;

/**
 * Immutable four-state authorization result with stable machine-readable provenance.
 *
 * @since 0.1.0
 */
final readonly class AuthorizationDecision
{
    /**
 * True exclusively for Allow; abstention and step-up confer no authority.
 * @var bool
 * @since 0.1.0
 */
    public bool $allowed;

    /**
     * Construct one result. Codes are exact, bounded lowercase ASCII tokens; no normalization occurs.
     *
     * @param DecisionState $state Outcome, independent of HTTP or session mechanisms.
     * @param string $policy Stable policy identifier, 1–127 bytes.
     * @param string $reason Stable reason identifier, 1–127 bytes.
     * @throws InvalidArgumentException When either code is malformed.
     * @since 0.1.0
     */
    public function __construct(public DecisionState $state, public string $policy, public string $reason)
    {
        foreach ([$policy, $reason] as $code) {
            if (preg_match('/^[a-z][a-z0-9._:-]{0,126}$/D', $code) !== 1) {
                throw new InvalidArgumentException('Decision policy and reason must be bounded machine codes.');
            }
        }
        $this->allowed = $state === DecisionState::Allow;
    }

    /**
     * Export the exact stable result shape without changing authority.
     *
     * @return array{state: string, allowed: bool, policy: string, reason: string} Decision data.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['state' => $this->state->value, 'allowed' => $this->allowed,
            'policy' => $this->policy, 'reason' => $this->reason];
    }
}
