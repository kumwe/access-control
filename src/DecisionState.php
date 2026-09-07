<?php

declare(strict_types=1);

namespace Kumwe\Access;

/**
 * Closed neutral decision vocabulary; only Allow grants permission.
 *
 * @since 0.1.0
 */
enum DecisionState: string
{
    /**
 * An applicable rule explicitly permits.
 * @since 0.1.0
 */
    case Allow = 'allow';
    /**
 * An applicable rule refuses and overrides every other outcome.
 * @since 0.1.0
 */
    case Deny = 'deny';
    /**
 * Further assurance is required; this is not permission.
 * @since 0.1.0
 */
    case StepUp = 'step_up';
    /**
 * The rule abstains; the host must fail closed unless another rule allows.
 * @since 0.1.0
 */
    case NotApplicable = 'not_applicable';
}
