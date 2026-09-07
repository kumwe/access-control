<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use InvalidArgumentException;
use Kumwe\Access\AuthorizationDefinitionLifecycle;
use Kumwe\Access\Capability;
use Kumwe\Access\CompositeResourceOwnershipReferences;
use Kumwe\Access\DecisionCombiner;
use Kumwe\Access\OwnershipScopeRule;
use Kumwe\Access\ResourceOwnershipScopePolicy;
use Kumwe\Access\ResourcePolicyDefinition;
use Kumwe\Access\Tests\TestCase;

/**
 * Regressions for hostile objects that PHP iterable annotations alone cannot reject.
 * @since 0.1.0
 */
final class HostileDefinitionTest extends TestCase
{
    /**
     * A forged selector cannot execute callbacks or widen resource matching.
     * @return void
     * @since 0.1.0
     */
    public function testRejectsExecutableDuckTypedTargets(): void
    {
        $fake = new class {
            /**
             * Retain explicit fixture state.
             * @var bool
             * @since 0.1.0
             */
            public bool $called = false;
            /**
             * @return array{type: string, identifiers: list<string>} Forged shape.
             * @since 0.1.0
             */
            public function toArray(): array
            {
                $this->called = true;
                return ['type' => 'record', 'identifiers' => []];
            }
        };
        $hostile = [$fake];
        $this->assertThrows(
            fn () => (new \ReflectionClass(ResourcePolicyDefinition::class))->newInstanceArgs([
                'core.read', 'core', Capability::fromString('record.read'), $hostile, false, [],
                AuthorizationDefinitionLifecycle::Active, 1,
            ]),
            InvalidArgumentException::class,
            'target must be exact final value'
        );
        $this->assertFalse($fake->called);
    }

    /**
     * Invalid reserved rules cannot masquerade as missing keys and be widened later.
     * @return void
     * @since 0.1.0
     */
    public function testRejectsUntypedReservedRules(): void
    {
        foreach ([null, 'site_only', false, new \stdClass()] as $bad) {
            $hostile = ['reserved' => $bad];
            $this->assertThrows(
                fn () => (new \ReflectionClass(ResourceOwnershipScopePolicy::class))->newInstance($hostile),
                InvalidArgumentException::class,
                'reserved value must be typed'
            );
        }
    }

    /**
     * Iterable metadata is not a substitute for runtime element validation.
     * @return void
     * @since 0.1.0
     */
    public function testRejectsWrongDecisionAndInspectorTypes(): void
    {
        $decisions = [new \stdClass()];
        $method = new \ReflectionMethod(DecisionCombiner::class, 'combine');
        $this->assertThrows(
            fn () => $method->invoke(new DecisionCombiner(), $decisions),
            InvalidArgumentException::class,
            'decision'
        );
        $inspectors = [new \stdClass()];
        $this->assertThrows(
            fn () => (new \ReflectionClass(CompositeResourceOwnershipReferences::class))->newInstance($inspectors),
            InvalidArgumentException::class,
            'inspector'
        );
    }
    /**
     * Invalid numeric adapter output cannot be mistaken for no live references.
     * @return void
     * @since 0.1.0
     */
    public function testRejectsUntypedReferenceIdentifiers(): void
    {
        $inspector = require __DIR__ . '/../Fixture/invalid-reference-port.fixture';
        if (!$inspector instanceof \Kumwe\Access\ResourceOwnershipReferences) {
            throw new \RuntimeException('Invalid fixture did not implement the port.');
        }
        $references = new CompositeResourceOwnershipReferences([$inspector]);
        $resource = \Kumwe\Access\AuthorizationResource::item('record', '1');
        $this->assertThrows(
            fn () => $references->sitesReferencing($resource, ['123']),
            InvalidArgumentException::class,
            'numeric adapter output must refuse'
        );
        $method = new \ReflectionMethod($references, 'sitesReferencing');
        $this->assertThrows(
            fn () => $method->invoke($references, $resource, [123]),
            InvalidArgumentException::class,
            'numeric candidate must refuse'
        );
    }
}
