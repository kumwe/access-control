<?php

declare(strict_types=1);

namespace Kumwe\Access;

use InvalidArgumentException;

/**
 * Stateless bounded aggregation with deny, step-up, allow, abstain precedence.
 *
 * @since 0.1.0
 */
final class DecisionCombiner
{
    /**
     * Return the strongest result; equal outcomes choose policy then reason in byte order.
     *
     * Every entry is consumed (at most 1024), even after a denial, to reject oversized/hostile input.
     * Empty input returns not_applicable with access.aggregate.v1/no_applicable_policy metadata.
     * The caller owns rule evaluation, authentication, context authority and audit.
     *
     * @param iterable<AuthorizationDecision> $decisions Already evaluated immutable outcomes.
     * @return AuthorizationDecision Deterministic selected decision, or explicit abstention.
     * @throws InvalidArgumentException On more than 1024 consumed decisions.
     * @since 0.1.0
     */
    public function combine(iterable $decisions): AuthorizationDecision
    {
        $selected = null;
        $consumed = 0;
        $ranks = ['deny' => 0, 'step_up' => 1, 'allow' => 2, 'not_applicable' => 3];
        foreach ($decisions as $decision) {
            if (!$decision instanceof AuthorizationDecision) {
                throw new InvalidArgumentException('Every decision must be an AuthorizationDecision.');
            }
            if (++$consumed > 1024) {
                throw new InvalidArgumentException('At most 1024 decisions may be combined.');
            }
            if (
                $selected === null
                || $ranks[$decision->state->value] < $ranks[$selected->state->value]
                || ($decision->state === $selected->state
                    && strcmp(
                        $decision->policy . "\0" . $decision->reason,
                        $selected->policy . "\0" . $selected->reason
                    ) < 0)
            ) {
                $selected = $decision;
            }
        }
        return $selected ?? new AuthorizationDecision(
            DecisionState::NotApplicable,
            'access.aggregate.v1',
            'no_applicable_policy',
        );
    }
}
