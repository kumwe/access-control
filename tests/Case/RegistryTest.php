<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use InvalidArgumentException;
use Kumwe\Access\AuthorizationDefinitionLifecycle as Lifecycle;
use Kumwe\Access\AuthorizationPolicyRegistry;
use Kumwe\Access\AuthorizationResource;
use Kumwe\Access\Capability;
use Kumwe\Access\CapabilityDefinition;
use Kumwe\Access\CapabilityDefinitionRegistry;
use Kumwe\Access\GrantScope;
use Kumwe\Access\MembershipRequirement;
use Kumwe\Access\ResourcePolicyDefinition;
use Kumwe\Access\ResourcePolicyRegistry;
use Kumwe\Access\ResourcePolicyTarget;
use Kumwe\Access\Tests\TestCase;

/**
 * Definitions, lifecycle, collisions, delegation and owner revocation.
 * @since 0.1.0
 */
final class RegistryTest extends TestCase
{
    /**
     * Capability metadata retains approved validation and deterministic serialization.
     * @return void
     * @since 0.1.0
     */
    public function testCapabilityDefinitions(): void
    {
        $capability = self::capability();
        $this->assertSame(['global', 'record', 'site'], $capability->allowedScopes);
        $this->assertTrue($capability->allowsHumanGrant());
        $this->assertTrue($capability->allowsDelegation(GrantScope::named('record', 'id')));
        $this->assertFalse($capability->allowsDelegation(GrantScope::named('unknown', 'id')));
        $this->assertSame('acme.editor.manage', $capability->toArray()['id']);
        $this->assertSame('acme/editor', $capability->toArray()['owner']);
        $this->assertSame(1, $capability->toArray()['version']);
        foreach (Lifecycle::cases() as $state) {
            $value = self::capability($state);
            $expected = in_array($state, [Lifecycle::Active, Lifecycle::Deprecated], true);
            $this->assertSame($expected, $state->enforceable());
            $this->assertSame($expected, $value->enforceable());
            $this->assertSame($expected, $value->allowsDelegation(GrantScope::global()));
        }
        $system = new CapabilityDefinition(
            Capability::fromString('system.work'),
            'core',
            [],
            false,
            true,
            Lifecycle::Active,
            1
        );
        $this->assertFalse($system->allowsHumanGrant());
        $this->assertFalse($system->allowsDelegation(GrantScope::global()));
    }

    /**
     * Namespaces, version and input-work limits are checked without trusting callers.
     * @return void
     * @since 0.1.0
     */
    public function testCapabilityDefinitionBoundaries(): void
    {
        foreach (['vendor', 'Acme/editor', '../editor', 'acme/editor/extra'] as $owner) {
            $this->assertThrows(
                fn () => CapabilityDefinition::assertOwner($owner),
                InvalidArgumentException::class,
                'owner'
            );
        }
        $this->assertThrows(
            fn () => CapabilityDefinition::assertOwnedIdentifier('acme/editor', 'acme.editorial.x', 'id'),
            InvalidArgumentException::class,
            'namespace segment'
        );
        foreach ([['GLOBAL'], array_fill(0, 65, 'site')] as $scopes) {
            $this->assertThrows(fn () => new CapabilityDefinition(
                Capability::fromString('acme.editor.x'),
                'acme/editor',
                $scopes,
                true,
                false,
                Lifecycle::Active,
                1
            ), InvalidArgumentException::class, 'scopes');
        }
        $this->assertThrows(fn () => new CapabilityDefinition(
            Capability::fromString('x'),
            'core',
            [],
            true,
            false,
            Lifecycle::Active,
            0
        ), InvalidArgumentException::class, 'version');
        $registry = new CapabilityDefinitionRegistry();
        $registry->register(self::capability());
        $this->assertThrows(
            fn () => $registry->register(self::capability(Lifecycle::Disabled)),
            InvalidArgumentException::class,
            'collision irrespective of lifecycle'
        );
    }

    /**
     * Policy data is deterministic, bounded and cannot assign extension system authority.
     * @return void
     * @since 0.1.0
     */
    public function testPolicyDefinitionAuthority(): void
    {
        $core = new ResourcePolicyDefinition(
            'core.record.read',
            'core',
            Capability::fromString('record.read'),
            [new ResourcePolicyTarget('record')],
            true,
            ['system:worker', 'system:bootstrap', 'system:worker'],
            Lifecycle::Deprecated,
            2
        );
        $this->assertSame(['system:bootstrap', 'system:worker'], $core->systemIdentities);
        $this->assertSame($core->systemIdentities, $core->toArray()['system_identities']);
        $this->assertTrue($core->allowsSystemIdentity('system:worker'));
        $this->assertFalse($core->allowsSystemIdentity('system:other'));
        $this->assertTrue($core->matches(AuthorizationResource::item('record', '1')));
        $this->assertFalse($core->matches(AuthorizationResource::item('other', '1')));
        $extension = self::policy();
        $this->assertSame([], $extension->systemIdentities);
        $this->assertFalse($extension->allowsSystemIdentity('system:worker'));
        $this->assertTrue($extension->overlaps(self::policy('acme.editor.other')));
        $this->assertFalse($extension->overlaps(new ResourcePolicyDefinition(
            'core.other.read',
            'core',
            Capability::fromString('other.read'),
            [new ResourcePolicyTarget('record')],
            false,
            [],
            Lifecycle::Active,
            1
        )));
    }

    /**
     * Hostile definitions fail before becoming active registry state.
     * @return void
     * @since 0.1.0
     */
    public function testPolicyDefinitionBoundaries(): void
    {
        foreach (
            [[], [new ResourcePolicyTarget('record'), new ResourcePolicyTarget('record', ['x'])],
            array_fill(0, 65, new ResourcePolicyTarget('record'))] as $targets
        ) {
            $this->assertThrows(
                fn () => new ResourcePolicyDefinition(
                    'core.record.read',
                    'core',
                    Capability::fromString('record.read'),
                    $targets,
                    false,
                    [],
                    Lifecycle::Active,
                    1
                ),
                InvalidArgumentException::class,
                'targets'
            );
        }
        foreach ([['worker'], ["system:worker\0"], array_fill(0, 65, 'system:worker')] as $systems) {
            $this->assertThrows(fn () => new ResourcePolicyDefinition(
                'core.record.read',
                'core',
                Capability::fromString('record.read'),
                [new ResourcePolicyTarget('record')],
                false,
                $systems,
                Lifecycle::Active,
                1
            ), InvalidArgumentException::class, 'system identity');
        }
        $this->assertThrows(fn () => new ResourcePolicyDefinition(
            'acme.editor.read',
            'acme/editor',
            Capability::fromString('acme.editor.read'),
            [new ResourcePolicyTarget('record')],
            false,
            ['system:worker'],
            Lifecycle::Active,
            1
        ), InvalidArgumentException::class, 'extension authority');
        $this->assertThrows(
            fn () => new ResourcePolicyDefinition(
                'record.read',
                'core',
                Capability::fromString('record.read'),
                [new ResourcePolicyTarget('record')],
                false,
                [],
                Lifecycle::Active,
                1
            ),
            InvalidArgumentException::class,
            'core policy namespace'
        );
    }

    /**
     * Owner binding, deterministic listing and overlap remain lifecycle-independent.
     * @return void
     * @since 0.1.0
     */
    public function testRegistryCollisionAndOrdering(): void
    {
        $capabilities = new CapabilityDefinitionRegistry();
        $registry = new ResourcePolicyRegistry($capabilities);
        $this->assertThrows(fn () => $registry->register(self::policy()), InvalidArgumentException::class, 'unknown');
        $capabilities->register(self::capability());
        $this->assertTrue($capabilities->isOwnedBy(Capability::fromString('acme.editor.manage'), 'acme/editor'));
        $this->assertFalse($capabilities->isOwnedBy(Capability::fromString('acme.editor.manage'), 'foreign/owner'));
        $this->assertSame([self::capability()->toArray()], array_map(
            fn ($d) => $d->toArray(),
            $capabilities->ownedBy('acme/editor')
        ));
        $registry->register(self::policy('acme.editor.z', 'z'));
        $registry->register(self::policy('acme.editor.a', 'a'));
        $this->assertSame(['acme.editor.a', 'acme.editor.z'], array_map(
            fn ($d) => $d->id,
            $registry->definitionsFor(Capability::fromString('acme.editor.manage'))
        ));
        $this->assertSame(2, count($registry->ownedBy('acme/editor')));
        $this->assertThrows(
            fn () => $registry->register(self::policy('acme.editor.z', 'z')),
            InvalidArgumentException::class,
            'duplicate'
        );
        $this->assertThrows(
            fn () => $registry->register(self::policy('acme.editor.second', 'z', Lifecycle::Disabled)),
            InvalidArgumentException::class,
            'overlap even disabled'
        );
        $this->assertThrows(
            fn () => $registry->register(new ResourcePolicyDefinition(
                'other.owner.record',
                'other/owner',
                Capability::fromString('acme.editor.manage'),
                [new ResourcePolicyTarget('record')],
                false,
                [],
                Lifecycle::Active,
                1
            )),
            InvalidArgumentException::class,
            'foreign owner'
        );
    }

    /**
     * Direct capability withdrawal makes old policies inert, including after foreign re-registration.
     * @return void
     * @since 0.1.0
     */
    public function testCapabilityWithdrawalFailsClosed(): void
    {
        $capabilities = new CapabilityDefinitionRegistry();
        $registry = new ResourcePolicyRegistry($capabilities);
        $action = Capability::fromString('acme.editor.manage');
        $resource = AuthorizationResource::item('record', '1');
        $capabilities->register(self::capability());
        $registry->register(self::policy());
        $this->assertSame('acme.editor.records', $registry->definitionFor($action, $resource)?->id);
        $capabilities->removeOwner('acme/editor');
        $this->assertNull($registry->definitionFor($action, $resource));
        $this->assertSame([], $registry->definitionsFor($action));
        $capabilities->register(self::capability());
        $this->assertNull($registry->definitionFor($action, $resource));
        $this->assertSame([], $registry->definitionsFor($action));
        $capabilities->removeOwner('acme/editor');
        $capabilities->register(new CapabilityDefinition($action, 'core', ['site'], true, false, Lifecycle::Active, 1));
        $this->assertNull($registry->definitionFor($action, $resource));
        $this->assertSame([], $registry->definitionsFor($action));
        $registry->removeOwner('acme/editor');
        $this->assertSame([], $registry->ownedBy('acme/editor'));
    }

    /**
     * Metadata lookup does not imply final authorization and host membership policy is explicit.
     * @return void
     * @since 0.1.0
     */
    public function testComposedRegistryAndDelegation(): void
    {
        $registry = new AuthorizationPolicyRegistry(new MembershipRequirement(['record']));
        $action = Capability::fromString('acme.editor.manage');
        $resource = AuthorizationResource::item('record', '1');
        $this->assertFalse($registry->supports($action, $resource));
        $this->assertFalse($registry->requiresMembershipContext($action));
        $registry->registerCapability(self::capability());
        $registry->registerResourcePolicy(self::policy());
        $this->assertTrue($registry->supports($action, $resource));
        $this->assertTrue($registry->allowsHumanGrant($action));
        $this->assertFalse($registry->requiresGlobalGrant($action, $resource));
        $this->assertFalse($registry->allowsSystemIdentity($action, $resource, 'system:worker'));
        $this->assertTrue($registry->requiresMembershipContext($action));
        foreach (
            [
            GrantScope::global(), GrantScope::named('site', 'site1'), GrantScope::named('record', '1')
            ] as $scope
        ) {
            $this->assertTrue($registry->supportsDelegation($action, $scope));
        }
        $this->assertFalse($registry->supportsDelegation($action, GrantScope::named('other', '1')));
        $this->assertSame($registry->capability($action), $registry->capabilityDefinitions()->definition($action));
        $this->assertSame(
            $registry->resourcePolicy($action, $resource),
            $registry->resourcePolicies()->definitionFor($action, $resource)
        );
        $registry->removeOwner('acme/editor');
        $this->assertFalse($registry->supports($action, $resource));
        $this->assertFalse($registry->allowsHumanGrant($action));
        $this->assertFalse($registry->supportsDelegation($action, GrantScope::global()));
    }

    /**
     * Disabled capabilities and policies never create authority.
     * @return void
     * @since 0.1.0
     */
    public function testDisabledAndExplicitMembershipPolicy(): void
    {
        $requirement = new MembershipRequirement(['record', 'alpha', 'record']);
        $this->assertSame(['alpha', 'record'], $requirement->resourceTypes);
        $this->assertTrue($requirement->requiredFor('record'));
        $this->assertFalse($requirement->requiredFor('business_record'));
        $this->assertThrows(fn () => new MembershipRequirement(['Record']), InvalidArgumentException::class, 'grammar');
        $this->assertThrows(
            fn () => new MembershipRequirement(array_fill(0, 4097, 'record')),
            InvalidArgumentException::class,
            'work bound'
        );
        foreach ([[Lifecycle::Disabled, Lifecycle::Active], [Lifecycle::Active, Lifecycle::Retired]] as [$c, $p]) {
            $registry = new AuthorizationPolicyRegistry($requirement);
            $registry->registerCapability(self::capability($c));
            $registry->registerResourcePolicy(self::policy(lifecycle: $p));
            $this->assertFalse($registry->supports(
                Capability::fromString('acme.editor.manage'),
                AuthorizationResource::item('record', '1')
            ));
            $this->assertFalse($registry->requiresMembershipContext(Capability::fromString('acme.editor.manage')));
        }
    }

    /**
     * @param Lifecycle $lifecycle Definition state.
     * @return CapabilityDefinition Fixture.
     * @since 0.1.0
     */
    private static function capability(Lifecycle $lifecycle = Lifecycle::Active): CapabilityDefinition
    {
        return new CapabilityDefinition(
            Capability::fromString('acme.editor.manage'),
            'acme/editor',
            ['site', 'record', 'global', 'site'],
            true,
            false,
            $lifecycle,
            1
        );
    }

    /**
     * @param string $id Policy identity.
     * @param string $type Resource type.
     * @param Lifecycle $lifecycle Definition state.
     * @return ResourcePolicyDefinition Fixture.
     * @since 0.1.0
     */
    private static function policy(
        string $id = 'acme.editor.records',
        string $type = 'record',
        Lifecycle $lifecycle = Lifecycle::Active
    ): ResourcePolicyDefinition {
        return new ResourcePolicyDefinition(
            $id,
            'acme/editor',
            Capability::fromString('acme.editor.manage'),
            [new ResourcePolicyTarget($type)],
            false,
            [],
            $lifecycle,
            1
        );
    }
}
