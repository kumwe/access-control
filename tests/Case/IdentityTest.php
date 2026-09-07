<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use InvalidArgumentException;
use Kumwe\Access\AuthorizationResource;
use Kumwe\Access\Capability;
use Kumwe\Access\GrantScope;
use Kumwe\Access\ResourcePolicyTarget;
use Kumwe\Access\Tests\TestCase;

/**
 * Canonical identity grammar, matching and raw-byte boundaries.
 * @since 0.1.0
 */
final class IdentityTest extends TestCase
{
    /**
     * Capability normalization preserves its historic approved grammar.
     * @return void
     * @since 0.1.0
     */
    public function testCapabilityIdentity(): void
    {
        $value = Capability::fromString(' Content.Publish ');
        $this->assertSame('content.publish', $value->value());
        $this->assertSame('content.publish', (string) $value);
        $this->assertTrue($value->equals(Capability::fromString('CONTENT.PUBLISH')));
        $this->assertFalse($value->equals(Capability::fromString('content.read')));
        foreach (['', '1name', 'a..b', 'a_', 'a/b', 'a b', str_repeat('a', 192)] as $bad) {
            $this->assertThrows(fn () => Capability::fromString($bad), InvalidArgumentException::class, $bad);
        }
        $this->assertSame(str_repeat('a', 191), Capability::fromString(str_repeat('a', 191))->value());
    }

    /**
     * Exact asymmetric grant coverage cannot widen a named grant.
     * @return void
     * @since 0.1.0
     */
    public function testGrantScopeCoverage(): void
    {
        $global = GrantScope::global();
        $scope = GrantScope::named(' SITE ', ' 123 ');
        $this->assertTrue($global->isGlobal());
        $this->assertNull($global->identifier());
        $this->assertSame('global', $global->type());
        $this->assertSame('site', $scope->type());
        $this->assertSame('123', $scope->identifier());
        $this->assertTrue($global->covers($scope));
        $this->assertFalse($scope->covers($global));
        $this->assertFalse($scope->equals($global));
        $this->assertTrue($scope->equals(GrantScope::named('site', '123')));
        $this->assertFalse($scope->covers(GrantScope::named('site', '124')));
        $this->assertFalse($scope->covers(GrantScope::named('record', '123')));
        foreach (
            [
            ['global', 'x'], ['', 'x'], ['1site', 'x'], ['site', ''], ['site', str_repeat('x', 192)]
            ] as [$t, $id]
        ) {
            $this->assertThrows(fn () => GrantScope::named($t, $id), InvalidArgumentException::class, 'scope');
        }
    }

    /**
     * All forbidden controls fail before trim can erase them.
     * @return void
     * @since 0.1.0
     */
    public function testRawControlRefusal(): void
    {
        foreach ([...range(0, 31), 127] as $byte) {
            foreach ([chr($byte) . 'abc', 'abc' . chr($byte), 'a' . chr($byte) . 'bc'] as $bad) {
                $this->assertThrows(
                    fn () => Capability::fromString($bad),
                    InvalidArgumentException::class,
                    'capability'
                );
                $this->assertThrows(
                    fn () => GrantScope::named('site', $bad),
                    InvalidArgumentException::class,
                    'scope id'
                );
                $this->assertThrows(
                    fn () => GrantScope::named($bad, 'id'),
                    InvalidArgumentException::class,
                    'scope type'
                );
                $this->assertThrows(
                    fn () => AuthorizationResource::item('record', $bad),
                    InvalidArgumentException::class,
                    'resource'
                );
            }
        }
    }

    /**
     * Numeric selectors remain strings; wildcard and overlap are explicit.
     * @return void
     * @since 0.1.0
     */
    public function testTargetIdentityAndOverlap(): void
    {
        $target = new ResourcePolicyTarget('record', ['123', '01', '0', '123']);
        $this->assertSame(['0', '01', '123'], $target->identifiers);
        $this->assertSame(['type' => 'record', 'identifiers' => ['0', '01', '123']], $target->toArray());
        $this->assertTrue($target->matches(AuthorizationResource::item('record', '123')));
        $this->assertFalse($target->matches(AuthorizationResource::item('record', '1')));
        $this->assertFalse($target->matches(AuthorizationResource::item('other', '123')));
        $this->assertFalse($target->matches(AuthorizationResource::collection('record')));
        $this->assertTrue((new ResourcePolicyTarget('record'))->matches(AuthorizationResource::collection('record')));
        $this->assertSame('*', AuthorizationResource::item('record', '*')->identifier());
        $this->assertTrue($target->overlaps(new ResourcePolicyTarget('record')));
        $this->assertTrue($target->overlaps(new ResourcePolicyTarget('record', ['123'])));
        $this->assertFalse($target->overlaps(new ResourcePolicyTarget('record', ['9'])));
        $this->assertFalse($target->overlaps(new ResourcePolicyTarget('other')));
    }

    /**
     * Selectors reject explicit wildcard, bad types and excessive duplicate work.
     * @return void
     * @since 0.1.0
     */
    public function testTargetBoundaries(): void
    {
        foreach (['', 'Record', '1record', str_repeat('r', 64)] as $type) {
            $this->assertThrows(fn () => new ResourcePolicyTarget($type), InvalidArgumentException::class, 'type');
        }
        $this->assertThrows(
            fn () => new ResourcePolicyTarget('record', ['*']),
            InvalidArgumentException::class,
            'wildcard'
        );
        $this->assertSame(['x'], (new ResourcePolicyTarget('record', array_fill(0, 128, 'x')))->identifiers);
        $this->assertThrows(
            fn () => new ResourcePolicyTarget('record', array_fill(0, 129, 'x')),
            InvalidArgumentException::class,
            'bound counts consumed entries'
        );
    }
}
