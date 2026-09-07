<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use InvalidArgumentException;
use Kumwe\Access\AuthorizationDecision;
use Kumwe\Access\DecisionCombiner;
use Kumwe\Access\DecisionState;
use Kumwe\Access\Tests\TestCase;

/**
 * Decision algebra conformance and refusal stability.
 * @since 0.1.0
 */
final class DecisionTest extends TestCase
{
    /**
     * Every four-state pair follows the independent normative matrix.
     * @return void
     * @since 0.1.0
     */
    public function testEveryDecisionPairAndOrder(): void
    {
        $bytes = file_get_contents(__DIR__ . '/../../resources/conformance/decisions-v1.json');
        if (!is_string($bytes)) {
            throw new \RuntimeException('Decision corpus is missing.');
        }
        $corpus = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($corpus) || !is_array($corpus['pairs'] ?? null)) {
            throw new \RuntimeException('Decision corpus has no pairs.');
        }
        foreach ($corpus['pairs'] as $pair) {
            if (
                !is_array($pair) || !array_is_list($pair) || count($pair) !== 3
                || !is_string($pair[0]) || !is_string($pair[1]) || !is_string($pair[2])
            ) {
                throw new \RuntimeException('Malformed decision corpus vector.');
            }
            [$left, $right, $expected] = $pair;
            $a = new AuthorizationDecision(DecisionState::from($left), 'rule.a', 'cause_a');
            $b = new AuthorizationDecision(DecisionState::from($right), 'rule.b', 'cause_b');
            foreach ([[$a, $b], [$b, $a]] as $input) {
                $result = (new DecisionCombiner())->combine($input);
                $this->assertSame($expected, $result->state->value);
                $this->assertSame($expected === 'allow', $result->allowed);
                $this->assertSame($result->allowed, $result->toArray()['allowed']);
            }
        }
    }

    /**
     * Empty and equal precedence never depend on registration order.
     * @return void
     * @since 0.1.0
     */
    public function testEmptyAndTiedDecisions(): void
    {
        $combiner = new DecisionCombiner();
        $empty = $combiner->combine([]);
        $this->assertSame(['state' => 'not_applicable', 'allowed' => false,
            'policy' => 'access.aggregate.v1', 'reason' => 'no_applicable_policy'], $empty->toArray());
        $a = new AuthorizationDecision(DecisionState::Deny, 'rule.a', 'z');
        $b = new AuthorizationDecision(DecisionState::Deny, 'rule.a', 'a');
        $this->assertSame($b, $combiner->combine([$a, $b]));
        $this->assertSame($b, $combiner->combine([$b, $a]));
    }

    /**
     * Codes reject hostile raw input and preserve exact accepted bytes.
     * @return void
     * @since 0.1.0
     */
    public function testMachineCodeBoundary(): void
    {
        foreach (['', 'A', ' leading', "code\0", "x\n", str_repeat('a', 128)] as $bad) {
            $this->assertThrows(
                fn () => new AuthorizationDecision(DecisionState::Allow, $bad, 'ok'),
                InvalidArgumentException::class,
                'invalid policy'
            );
            $this->assertThrows(
                fn () => new AuthorizationDecision(DecisionState::Allow, 'ok', $bad),
                InvalidArgumentException::class,
                'invalid reason'
            );
        }
        $code = 'a' . str_repeat('9', 126);
        $this->assertSame($code, (new AuthorizationDecision(DecisionState::Allow, $code, $code))->reason);
    }

    /**
     * The work bound counts duplicates and reads through an early denial.
     * @return void
     * @since 0.1.0
     */
    public function testBoundedDecisionConsumption(): void
    {
        $deny = new AuthorizationDecision(DecisionState::Deny, 'rule.a', 'deny');
        $combiner = new DecisionCombiner();
        $this->assertSame($deny, $combiner->combine(array_fill(0, 1024, $deny)));
        $count = 0;
        $input = (static function () use (&$count, $deny): iterable {
            while (true) {
                $count++;
                yield $deny;
            }
        })();
        $this->assertThrows(fn () => $combiner->combine($input), InvalidArgumentException::class, 'bound');
        $this->assertSame(1025, $count);
    }
}
