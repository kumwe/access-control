<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use InvalidArgumentException;
use Kumwe\Access\AuthorizationPolicyRegistry;
use Kumwe\Access\AuthorizationResource;
use Kumwe\Access\CompositeResourceOwnershipReferences;
use Kumwe\Access\ConfigProvider;
use Kumwe\Access\Container\AuthorizationPolicyRegistryFactory;
use Kumwe\Access\Container\CompositeResourceOwnershipReferencesFactory;
use Kumwe\Access\Container\ResourceOwnershipScopePolicyFactory;
use Kumwe\Access\OwnershipScopeLevel;
use Kumwe\Access\ResourceOwnershipReferences;
use Kumwe\Access\ResourceOwnershipScopePolicy;
use Kumwe\Access\Tests\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Service construction requires explicit host policies and typed collaborators.
 * @since 0.1.0
 */
final class FactoryTest extends TestCase
{
    /**
     * Provider is stable, contains no active definitions and registers no authority gateway.
     * @return void
     * @since 0.1.0
     */
    public function testProviderAndFactories(): void
    {
        $provider = new ConfigProvider();
        $this->assertSame($provider(), $provider());
        $config = $provider();
        $this->assertSame([
            AuthorizationPolicyRegistry::class => AuthorizationPolicyRegistryFactory::class,
            ResourceOwnershipScopePolicy::class => ResourceOwnershipScopePolicyFactory::class,
            CompositeResourceOwnershipReferences::class => CompositeResourceOwnershipReferencesFactory::class,
        ], $config['dependencies']['factories']);
        $container = self::container(['kumwe' => ['access' => [
            'membership_resource_types' => ['record'],
            'reserved_ownership_rules' => ['record' => 'site_only'],
            'reference_inspectors' => [],
        ]]]);
        $registry = (new AuthorizationPolicyRegistryFactory())($container);
        $this->assertSame([], $registry->capabilityDefinitions()->ownedBy('core'));
        $policy = (new ResourceOwnershipScopePolicyFactory())($container);
        $this->assertFalse($policy->permits('record', OwnershipScopeLevel::Installation));
        $references = (new CompositeResourceOwnershipReferencesFactory())($container);
        $this->assertSame([], $references->sitesReferencing(AuthorizationResource::item('record', '1'), ['a']));
    }

    /**
     * Missing configuration refuses rather than silently widening membership or ownership.
     * @return void
     * @since 0.1.0
     */
    public function testMissingAndMalformedPolicyConfiguration(): void
    {
        foreach ([null, [], ['kumwe' => ['access' => []]]] as $config) {
            $container = self::container($config);
            foreach (
                [new AuthorizationPolicyRegistryFactory(), new ResourceOwnershipScopePolicyFactory(),
                new CompositeResourceOwnershipReferencesFactory()] as $factory
            ) {
                $this->assertThrows(fn () => $factory($container), InvalidArgumentException::class, 'missing');
            }
        }
        foreach (
            [['membership_resource_types' => ['record' => true]], ['membership_resource_types' => [1]],
            ['reserved_ownership_rules' => ['record' => 'wider']],
            ['reserved_ownership_rules' => ['site_only']]] as $bad
        ) {
            $container = self::container(['kumwe' => ['access' => $bad]]);
            $factory = isset($bad['membership_resource_types'])
                ? new AuthorizationPolicyRegistryFactory() : new ResourceOwnershipScopePolicyFactory();
            $this->assertThrows(fn () => $factory($container), InvalidArgumentException::class, 'malformed policy');
        }
    }

    /**
     * Inspectors are resolved explicitly, with bounded and nonrecursive service identities.
     * @return void
     * @since 0.1.0
     */
    public function testReferenceFactoryTypeAndRecursionBoundaries(): void
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
                return ['123'];
            }
        };
        $container = self::container(
            ['kumwe' => ['access' => ['reference_inspectors' => ['inspector']]]],
            ['inspector' => $inspector]
        );
        $references = (new CompositeResourceOwnershipReferencesFactory())($container);
        $this->assertSame(['123'], $references->sitesReferencing(AuthorizationResource::item('record', '1'), ['123']));
        foreach (
            [
            [CompositeResourceOwnershipReferences::class], ['wrong'], [123], array_fill(0, 65, 'inspector')
            ] as $bad
        ) {
            $container = self::container(
                ['kumwe' => ['access' => ['reference_inspectors' => $bad]]],
                ['wrong' => new \stdClass(), 'inspector' => $inspector]
            );
            $this->assertThrows(
                fn () => (new CompositeResourceOwnershipReferencesFactory())($container),
                InvalidArgumentException::class,
                'inspector configuration'
            );
        }
    }

    /**
     * @param mixed $config Host configuration fixture.
     * @param array<string, object> $services Explicit collaborators.
     * @return ContainerInterface Test-scoped container.
     * @since 0.1.0
     */
    private static function container(mixed $config, array $services = []): ContainerInterface
    {
        return new class ($config, $services) implements ContainerInterface {
            /**
             * @param mixed $config Configuration.
             * @param array<string, object> $services Services.
             * @since 0.1.0
             */
            public function __construct(private mixed $config, private array $services)
            {
            }
            /**
             * Provide the explicit get fixture contract.
             * @param string $id Explicit fixture input.
             * @return mixed Fixture result.
             * @since 0.1.0
             */
            public function get(string $id): mixed
            {
                return $id === 'config' ? $this->config : ($this->services[$id] ?? null);
            }
            /**
             * Provide the explicit has fixture contract.
             * @param string $id Explicit fixture input.
             * @return bool Fixture result.
             * @since 0.1.0
             */
            public function has(string $id): bool
            {
                return $id === 'config' || isset($this->services[$id]);
            }
        };
    }
}
