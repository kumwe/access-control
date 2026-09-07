<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use InvalidArgumentException;
use Kumwe\Access\AuthorizationDenied;
use Kumwe\Access\AuthorizationResource;
use Kumwe\Access\AuthorizationResourceOwnershipUnknown;
use Kumwe\Access\CompositeResourceOwnershipReferences;
use Kumwe\Access\OwnershipNarrowingRefused;
use Kumwe\Access\OwnershipNarrowingUnbounded;
use Kumwe\Access\OwnershipScope;
use Kumwe\Access\OwnershipScopeChangeRejected;
use Kumwe\Access\OwnershipScopeLevel;
use Kumwe\Access\OwnershipScopeNotPermitted;
use Kumwe\Access\OwnershipScopeNotSiteBound;
use Kumwe\Access\OwnershipScopeRule;
use Kumwe\Access\ResourceOwnership;
use Kumwe\Access\ResourceOwnershipReferences;
use Kumwe\Access\ResourceOwnershipScopePolicy;
use Kumwe\Access\ResourceSiteOwnershipConflict;
use Kumwe\Access\SiteGroup;
use Kumwe\Access\SiteGroupUnknown;
use Kumwe\Access\Tests\TestCase;
use Kumwe\Context\Value\SiteContext;

/**
 * Ownership containment, explicit rules and refusal data remain portable.
 * @since 0.1.0
 */
final class OwnershipTest extends TestCase
{
    /**
     * Groups preserve numeric strings and normalized immutable snapshots.
     * @return void
     * @since 0.1.0
     */
    public function testGroupSnapshotAndContainment(): void
    {
        $group = new SiteGroup(' GROUP ', ' Shared clients ', ['123', '001', '123', 'Site-A']);
        $this->assertSame('group', $group->identifier);
        $this->assertSame('Shared clients', $group->name);
        $this->assertSame(['001', '123', 'site-a'], $group->members);
        $this->assertTrue($group->contains(SiteContext::fromString('123')));
        $this->assertFalse($group->contains(SiteContext::fromString('1')));
        $scope = OwnershipScope::group($group);
        $this->assertTrue($scope->contains(SiteContext::fromString('site-a')));
        $this->assertFalse($scope->contains(SiteContext::fromString('site-b')));
        $this->assertSame('group:group', $scope->describe());
        $this->assertTrue($scope->equals(OwnershipScope::group(new SiteGroup('group', 'Updated', ['other']))));
        $this->assertNull($scope->siteOrNull());
        $this->assertThrows(
            fn () => $scope->requireSite(),
            OwnershipScopeNotSiteBound::class,
            'group cannot select site'
        );
    }

    /**
     * Group normalization never erases forbidden controls and entry bounds count duplicates.
     * @return void
     * @since 0.1.0
     */
    public function testGroupBoundaries(): void
    {
        foreach (["\0group", "group\n", '', str_repeat('g', 192)] as $bad) {
            $this->assertThrows(
                fn () => new SiteGroup($bad, 'Group', ['site']),
                InvalidArgumentException::class,
                'group identifier'
            );
        }
        foreach (['', ' ', "bad\0", str_repeat('n', 192)] as $bad) {
            $this->assertThrows(
                fn () => new SiteGroup('group', $bad, ['site']),
                InvalidArgumentException::class,
                'label'
            );
        }
        $this->assertThrows(fn () => new SiteGroup('group', 'Group', []), InvalidArgumentException::class, 'empty');
        $this->assertThrows(
            fn () => new SiteGroup('group', 'Group', array_fill(0, 4097, 'site')),
            InvalidArgumentException::class,
            'consumed bound'
        );
        $this->assertSame(['site'], (new SiteGroup('group', 'Group', array_fill(0, 4096, 'site')))->members);
    }

    /**
     * Site identity and installation reach are distinct from final permission.
     * @return void
     * @since 0.1.0
     */
    public function testScopeIdentityAndReach(): void
    {
        $site = SiteContext::fromString('site-a');
        $scope = OwnershipScope::site($site);
        $this->assertSame($site->identifier(), $scope->requireSite()->identifier());
        $this->assertSame('site:site-a', $scope->describe());
        $this->assertTrue($scope->contains($site));
        $this->assertFalse($scope->contains(SiteContext::fromString('site-b')));
        $this->assertFalse($scope->isInstallation());
        $installation = OwnershipScope::installation();
        $this->assertTrue($installation->isInstallation());
        $this->assertTrue($installation->contains($site));
        $this->assertFalse($installation->equals($scope));
        $this->assertNull($installation->siteOrNull());
        $this->assertSame([], $installation->sites);
        $this->assertThrows(fn () => $installation->requireSite(), OwnershipScopeNotSiteBound::class, 'installation');
        foreach (OwnershipScopeLevel::cases() as $a) {
            foreach (OwnershipScopeLevel::cases() as $b) {
                $this->assertSame($a->reach() > $b->reach(), $a->widerThan($b));
            }
        }
    }

    /**
     * All rule/level pairs follow the normative isolation matrix.
     * @return void
     * @since 0.1.0
     */
    public function testRuleMatrixAndExplicitHostPolicy(): void
    {
        $expected = [[true, false, false], [true, true, false], [true, true, true]];
        foreach (OwnershipScopeRule::cases() as $r => $rule) {
            $levels = [];
            foreach (OwnershipScopeLevel::cases() as $l => $level) {
                $this->assertSame($expected[$r][$l], $rule->permits($level));
                if ($expected[$r][$l]) {
                    $levels[] = $level;
                }
            }
            $this->assertSame($levels, $rule->levels());
        }
        $policy = new ResourceOwnershipScopePolicy(['fixed' => OwnershipScopeRule::SiteOrGroup]);
        $policy->register('zeta', OwnershipScopeRule::SiteGroupOrInstallation);
        $policy->register('alpha', OwnershipScopeRule::SiteOnly);
        $policy->register('alpha', OwnershipScopeRule::SiteOnly);
        $this->assertSame(['alpha', 'fixed', 'zeta'], array_keys($policy->table()));
        $this->assertSame(OwnershipScopeRule::SiteOnly, $policy->rule('unknown'));
        $this->assertFalse($policy->permits('unknown', OwnershipScopeLevel::Group));
        $this->assertTrue($policy->permits('fixed', OwnershipScopeLevel::Group));
        $this->assertThrows(
            fn () => $policy->register('fixed', OwnershipScopeRule::SiteOrGroup),
            InvalidArgumentException::class,
            'reserved is never redeclared'
        );
        $this->assertThrows(
            fn () => $policy->register('alpha', OwnershipScopeRule::SiteGroupOrInstallation),
            InvalidArgumentException::class,
            'cannot widen declared'
        );
        $this->assertThrows(
            fn () => $policy->register('Bad', OwnershipScopeRule::SiteOnly),
            InvalidArgumentException::class,
            'category'
        );
        $this->assertThrows(
            fn () => new ResourceOwnershipScopePolicy(['Bad' => OwnershipScopeRule::SiteOnly]),
            InvalidArgumentException::class,
            'reserved category'
        );
    }

    /**
     * A resource value proves the supplied rule allows its level, excluding collections.
     * @return void
     * @since 0.1.0
     */
    public function testResourceOwnershipBoundaries(): void
    {
        $resource = AuthorizationResource::item('record', '123');
        $site = OwnershipScope::site(SiteContext::fromString('a'));
        $policy = new ResourceOwnershipScopePolicy([]);
        $owner = ResourceOwnership::of($resource, $site, $policy);
        $this->assertSame($site, $owner->scope);
        $this->assertSame($resource, $owner->resource);
        $this->assertThrows(
            fn () => ResourceOwnership::of($resource, OwnershipScope::installation(), $policy),
            OwnershipScopeNotPermitted::class,
            'unknown remains isolated'
        );
        $this->assertThrows(
            fn () => ResourceOwnership::of(AuthorizationResource::collection('record'), $site, $policy),
            InvalidArgumentException::class,
            'collection cannot own'
        );
    }

    /**
     * Reference composition preserves exact string identities and restricts inspectors to candidates.
     * @return void
     * @since 0.1.0
     */
    public function testReferenceUnionAndBounds(): void
    {
        $inspector = new class implements ResourceOwnershipReferences {
            /**
             * Provide the explicit sitesReferencing fixture contract.
             * @param AuthorizationResource $resource Explicit fixture input.
             * @param list<string> $sites Explicit fixture input.
             * @return list<string> Fixture result.
             * @since 0.1.0
             */
            public function sitesReferencing(AuthorizationResource $resource, array $sites): array
            {
                return ['foreign', '123', '001', '123'];
            }
        };
        $resource = AuthorizationResource::item('record', '1');
        $composite = new CompositeResourceOwnershipReferences([$inspector, $inspector]);
        $this->assertSame(['001', '123'], $composite->sitesReferencing($resource, ['123', '001']));
        $this->assertSame([], $composite->sitesReferencing($resource, []));
        $this->assertThrows(
            fn () => new CompositeResourceOwnershipReferences(array_fill(0, 65, $inspector)),
            InvalidArgumentException::class,
            'inspector bound'
        );
        $this->assertThrows(
            fn () => $composite->sitesReferencing($resource, array_fill(0, 4097, '123')),
            InvalidArgumentException::class,
            'candidate bound'
        );
        $hostile = new class implements ResourceOwnershipReferences {
            /**
             * Provide the explicit sitesReferencing fixture contract.
             * @param AuthorizationResource $resource Explicit fixture input.
             * @param list<string> $sites Explicit fixture input.
             * @return list<string> Fixture result.
             * @since 0.1.0
             */
            public function sitesReferencing(AuthorizationResource $resource, array $sites): array
            {
                return array_fill(0, 4097, '123');
            }
        };
        $this->assertThrows(fn () => (new CompositeResourceOwnershipReferences([$hostile]))
            ->sitesReferencing($resource, ['123']), InvalidArgumentException::class, 'returned bound');
    }

    /**
     * Refusal types preserve stable metadata and exact messages without ambient authority.
     * @return void
     * @since 0.1.0
     */
    public function testRefusalDataContracts(): void
    {
        $resource = AuthorizationResource::item('record', '123');
        $a = OwnershipScope::site(SiteContext::fromString('a'));
        $b = OwnershipScope::site(SiteContext::fromString('b'));
        $cases = [
            [new AuthorizationResourceOwnershipUnknown($resource),
                'No authoritative site ownership exists for record:123.'],
            [new SiteGroupUnknown('group'), 'No enabled site group is declared under the identifier group.'],
            [new OwnershipScopeNotSiteBound(OwnershipScope::installation()),
                'Ownership scope installation:* names no single site, so work cannot be executed on its behalf.'],
            [new ResourceSiteOwnershipConflict($resource, $a, $b),
                'Refusing to change record:123 ownership held by site:b on behalf of site:a.'],
            [new OwnershipNarrowingUnbounded($resource, OwnershipScope::installation()),
                'Refusing to narrow record:123 out of installation:*, '
                . 'because the sites losing reach cannot be enumerated.'],
            [new OwnershipScopeChangeRejected($resource, $a, $b, 'widen'),
                'Cannot widen record:123 from site:a to site:b; the target does not move in that direction.'],
            [new OwnershipScopeNotPermitted($resource, $b, OwnershipScopeRule::SiteOnly),
                'Resources of category record cannot be owned at site:b; this build permits site only.'],
        ];
        foreach ($cases as [$error, $message]) {
            $this->assertSame($message, $error->getMessage());
            $this->assertSame(0, $error->getCode());
            $this->assertNull($error->getPrevious());
        }
        $narrow = new OwnershipNarrowingRefused($resource, $a, ['b']);
        $this->assertSame(['b'], $narrow->referencingSites);
        $this->assertSame('Refusing to narrow record:123 to site:a while b still refer to it.', $narrow->getMessage());
        $denied = new AuthorizationDenied('actor', 'record.read', 'record', '123', 'a', 'rule.v1', 'no_grant');
        $this->assertSame('actor', $denied->subject);
        $this->assertSame('record.read', $denied->action);
        $this->assertSame('record', $denied->resourceType);
        $this->assertSame('123', $denied->resourceIdentifier);
        $this->assertSame('a', $denied->siteIdentifier);
        $this->assertSame('rule.v1', $denied->policy);
        $this->assertSame('no_grant', $denied->reason);
        $this->assertSame(
            'Subject actor is not authorized to perform record.read on record:123 in site a.',
            $denied->getMessage()
        );
    }
}
