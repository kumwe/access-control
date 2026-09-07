<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use Kumwe\Access\AuthorizationDecision;
use Kumwe\Access\AuthorizationDecisionRecorder;
use Kumwe\Access\AuthorizationGateway;
use Kumwe\Access\AuthorizationResource;
use Kumwe\Access\AuthorizationResourceOwnershipUnknown;
use Kumwe\Access\Capability;
use Kumwe\Access\GrantScope;
use Kumwe\Access\MembershipContextValidator;
use Kumwe\Access\OwnershipScope;
use Kumwe\Access\ResourceOwnership;
use Kumwe\Access\ResourceOwnershipScopePolicy;
use Kumwe\Access\ResourceSiteOwnership;
use Kumwe\Access\ResourceSiteOwnershipConflict;
use Kumwe\Access\ResourceSiteOwnershipWriter;
use Kumwe\Access\SiteGroup;
use Kumwe\Access\SiteGroupRegistry;
use Kumwe\Access\SiteGroupUnknown;
use Kumwe\Access\SiteGroupWriter;
use Kumwe\Access\Tests\TestCase;
use Kumwe\Context\Value\ExecutionContext;
use Kumwe\Context\Value\MembershipContext;
use Kumwe\Context\Value\OrganizationContext;
use Kumwe\Context\Value\SiteContext;
use ReflectionClass;
use ReflectionNamedType;
use TypeError;

/**
 * Package-owned port protocol checks; database/authority adapters remain host tests.
 * @since 0.1.0
 */
final class PortTest extends TestCase
{
    /**
     * Writer/read protocol preserves expected-owner compare-and-set refusal.
     * @return void
     * @since 0.1.0
     */
    public function testOwnershipPortConformance(): void
    {
        $port = new class implements ResourceSiteOwnership, ResourceSiteOwnershipWriter {
            /**
             * Retain explicit fixture state.
             * @var ?OwnershipScope
             * @since 0.1.0
             */
            private ?OwnershipScope $owner = null;
            /**
             * Provide the explicit record fixture contract.
             * @param AuthorizationResource $resource Explicit fixture input.
             * @param SiteContext $site Explicit fixture input.
             * @return void Fixture result.
             * @since 0.1.0
             */
            public function record(AuthorizationResource $resource, SiteContext $site): void
            {
                $this->owner = OwnershipScope::site($site);
            }
            /**
             * Provide the explicit scopeFor fixture contract.
             * @param AuthorizationResource $resource Explicit fixture input.
             * @return OwnershipScope Fixture result.
             * @since 0.1.0
             */
            public function scopeFor(AuthorizationResource $resource): OwnershipScope
            {
                return $this->owner ?? throw new AuthorizationResourceOwnershipUnknown($resource);
            }
            /**
             * Provide the explicit remove fixture contract.
             * @param AuthorizationResource $resource Explicit fixture input.
             * @param SiteContext $expectedSite Explicit fixture input.
             * @return void Fixture result.
             * @since 0.1.0
             */
            public function remove(AuthorizationResource $resource, SiteContext $expectedSite): void
            {
                $actual = $this->scopeFor($resource);
                $expected = OwnershipScope::site($expectedSite);
                if (!$actual->equals($expected)) {
                    throw new ResourceSiteOwnershipConflict($resource, $expected, $actual);
                }
                $this->owner = null;
            }
            /**
             * Provide the explicit reassign fixture contract.
             * @param ResourceOwnership $owner Explicit fixture input.
             * @param OwnershipScope $expected Explicit fixture input.
             * @return void Fixture result.
             * @since 0.1.0
             */
            public function reassign(ResourceOwnership $owner, OwnershipScope $expected): void
            {
                $actual = $this->scopeFor($owner->resource);
                if (!$actual->equals($expected)) {
                    throw new ResourceSiteOwnershipConflict($owner->resource, $expected, $actual);
                }
                $this->owner = $owner->scope;
            }
        };
        $resource = AuthorizationResource::item('record', '1');
        $a = SiteContext::fromString('a');
        $b = SiteContext::fromString('b');
        $this->assertThrows(
            fn () => $port->scopeFor($resource),
            AuthorizationResourceOwnershipUnknown::class,
            'missing'
        );
        $port->record($resource, $a);
        $this->assertTrue($port->scopeFor($resource)->equals(OwnershipScope::site($a)));
        $new = ResourceOwnership::of($resource, OwnershipScope::site($b), new ResourceOwnershipScopePolicy([]));
        $port->reassign($new, OwnershipScope::site($a));
        $this->assertThrows(
            fn () => $port->reassign($new, OwnershipScope::site($a)),
            ResourceSiteOwnershipConflict::class,
            'stale expected owner'
        );
        $this->assertThrows(fn () => $port->remove($resource, $a), ResourceSiteOwnershipConflict::class, 'wrong site');
        $this->assertTrue($port->scopeFor($resource)->equals(OwnershipScope::site($b)));
        $port->remove($resource, $b);
        $this->assertThrows(
            fn () => $port->scopeFor($resource),
            AuthorizationResourceOwnershipUnknown::class,
            'removed'
        );
    }

    /**
     * Group ports exchange package values and must report unavailable groups explicitly.
     * @return void
     * @since 0.1.0
     */
    public function testSiteGroupPortConformance(): void
    {
        $port = new class implements SiteGroupRegistry, SiteGroupWriter {
            /**
             * Retain explicit fixture state.
             * @var ?SiteGroup
             * @since 0.1.0
             */
            private ?SiteGroup $stored = null;
            /**
             * Provide the explicit group fixture contract.
             * @param string $identifier Explicit fixture input.
             * @return SiteGroup Fixture result.
             * @since 0.1.0
             */
            public function group(string $identifier): SiteGroup
            {
                if ($this->stored === null || $this->stored->identifier !== $identifier) {
                    throw new SiteGroupUnknown($identifier);
                }
                return $this->stored;
            }
            /**
             * Provide the explicit all fixture contract.
             * @return list<SiteGroup> Fixture result.
             * @since 0.1.0
             */
            public function all(): array
            {
                return $this->stored === null ? [] : [$this->stored];
            }
            /**
             * Provide the explicit save fixture contract.
             * @param SiteGroup $group Explicit fixture input.
             * @return void Fixture result.
             * @since 0.1.0
             */
            public function save(SiteGroup $group): void
            {
                $this->stored = $group;
            }
            /**
             * Provide the explicit addSite fixture contract.
             * @param string $group Explicit fixture input.
             * @param SiteContext $site Explicit fixture input.
             * @return void Fixture result.
             * @since 0.1.0
             */
            public function addSite(string $group, SiteContext $site): void
            {
                $old = $this->group($group);
                $this->stored = new SiteGroup($old->identifier, $old->name, [...$old->members, $site->identifier()]);
            }
            /**
             * Provide the explicit removeSite fixture contract.
             * @param string $group Explicit fixture input.
             * @param SiteContext $site Explicit fixture input.
             * @return void Fixture result.
             * @since 0.1.0
             */
            public function removeSite(string $group, SiteContext $site): void
            {
                $old = $this->group($group);
                $this->stored = new SiteGroup(
                    $old->identifier,
                    $old->name,
                    array_values(array_diff($old->members, [$site->identifier()]))
                );
            }
        };
        $this->assertSame([], $port->all());
        $this->assertThrows(fn () => $port->group('group'), SiteGroupUnknown::class, 'missing group');
        $port->save(new SiteGroup('group', 'Group', ['123']));
        $port->addSite('group', SiteContext::fromString('456'));
        $this->assertSame(['123', '456'], $port->group('group')->members);
        $port->removeSite('group', SiteContext::fromString('123'));
        $this->assertSame(['456'], $port->all()[0]->members);
    }

    /**
     * Freshness protocol refuses actor/site/version mismatches; lock intent remains explicit.
     * @return void
     * @since 0.1.0
     */
    public function testMembershipFreshnessPortConformance(): void
    {
        $port = new class implements MembershipContextValidator {
            /**
             * Retain explicit fixture state.
             * @var bool
             * @since 0.1.0
             */
            public bool $lockRequested = false;
            /**
             * Provide the explicit current fixture contract.
             * @param string $subjectId Explicit fixture input.
             * @param SiteContext $site Explicit fixture input.
             * @param MembershipContext $membership Explicit fixture input.
             * @param bool $lock Explicit fixture input.
             * @return bool Fixture result.
             * @since 0.1.0
             */
            public function current(
                string $subjectId,
                SiteContext $site,
                MembershipContext $membership,
                bool $lock = false
            ): bool {
                $this->lockRequested = $lock;
                return $subjectId === 'actor' && $site->identifier() === 'site'
                    && $membership->membershipVersion() === 2 && $membership->policyGeneration() === 3;
            }
        };
        $fresh = new MembershipContext(
            '018f22e2-7c8b-7ab0-8f3a-88e8026bb302',
            OrganizationContext::fromString('org'),
            null,
            2,
            3
        );
        $stale = new MembershipContext($fresh->membershipId(), $fresh->organization(), null, 1, 3);
        $this->assertTrue($port->current('actor', SiteContext::fromString('site'), $fresh, true));
        $this->assertTrue($port->lockRequested);
        $this->assertFalse($port->current('actor', SiteContext::fromString('site'), $stale));
        $this->assertFalse($port->lockRequested);
        $this->assertFalse($port->current('other', SiteContext::fromString('site'), $fresh));
        $this->assertFalse($port->current('actor', SiteContext::fromString('foreign'), $fresh));
    }

    /**
     * Public gateway and recorder require explicit canonical values; neither ships an authority adapter.
     * @return void
     * @since 0.1.0
     */
    public function testGatewayAndRecorderSignatureConformance(): void
    {
        $contracts = [
            AuthorizationGateway::class => [
                'decide' => [[ExecutionContext::class, Capability::class, AuthorizationResource::class],
                    AuthorizationDecision::class],
                'assertAllowed' => [[ExecutionContext::class, Capability::class, AuthorizationResource::class], 'void'],
                'assertCanDelegate' => [[ExecutionContext::class, Capability::class, GrantScope::class], 'void'],
            ],
            AuthorizationDecisionRecorder::class => [
                'record' => [[ExecutionContext::class, Capability::class, AuthorizationResource::class,
                    AuthorizationDecision::class], 'void'],
            ],
        ];
        foreach ($contracts as $name => $methods) {
            $type = new ReflectionClass($name);
            $this->assertTrue($type->isInterface());
            foreach ($methods as $method => [$parameters, $return]) {
                $reflection = $type->getMethod($method);
                $actual = array_map(static function ($p): string {
                    $type = $p->getType();
                    return $type instanceof ReflectionNamedType ? $type->getName() : '';
                }, $reflection->getParameters());
                $this->assertSame($parameters, $actual);
                $this->assertSame($return, (string) $reflection->getReturnType());
            }
        }
    }

    /**
     * Port return boundaries reject untyped/fabricated domain values.
     * @return void
     * @since 0.1.0
     */
    public function testPortReturnTypeBoundary(): void
    {
        $method = new \ReflectionMethod(ResourceSiteOwnership::class, 'scopeFor');
        $this->assertSame(OwnershipScope::class, (string) $method->getReturnType());
        $method = new \ReflectionMethod(OwnershipScope::class, 'site');
        $this->assertThrows(
            fn () => $method->invoke(null, new \stdClass()),
            TypeError::class,
            'untyped site cannot cross the package boundary'
        );
    }
}
