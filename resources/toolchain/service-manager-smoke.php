<?php

/**
 * Resolve every exported runtime service in a real no-dev Laminas host container.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

use Kumwe\Access\AuthorizationGateway;
use Kumwe\Access\AuthorizationPolicyRegistry;
use Kumwe\Access\AuthorizationResource;
use Kumwe\Access\CompositeResourceOwnershipReferences;
use Kumwe\Access\ConfigProvider;
use Kumwe\Access\OwnershipScopeLevel;
use Kumwe\Access\ResourceOwnershipScopePolicy;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;

require $argv[1] ?? dirname(__DIR__, 2) . '/vendor/autoload.php';

$configuration = ['kumwe' => ['access' => [
    'membership_resource_types' => ['record'],
    'reserved_ownership_rules' => ['record' => 'site_only'],
    'reference_inspectors' => [],
]]];
$provider = new ConfigProvider();
$dependencies = $provider()['dependencies'];
$container = new ServiceManager($dependencies + ['services' => ['config' => $configuration]]);
$manifest = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/service-map/v1.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
if (!is_array($manifest) || !is_array($manifest['factories'] ?? null)) {
    throw new RuntimeException('The shipped service map has no factory list.');
}
foreach ($manifest['factories'] as $entry) {
    if (!is_array($entry) || !is_string($entry['service'] ?? null) || !class_exists($entry['service'])) {
        throw new RuntimeException('The shipped service map contains an invalid service class.');
    }
    $serviceClass = $entry['service'];
    $service = $container->get($serviceClass);
    if (!$service instanceof $serviceClass || $container->get($serviceClass) !== $service) {
        throw new RuntimeException('A declared shared service does not resolve with its documented lifetime.');
    }
}
if ($container->has(AuthorizationGateway::class)) {
    throw new RuntimeException('The package must not install host authorization authority.');
}
$registry = $container->get(AuthorizationPolicyRegistry::class);
$policy = $container->get(ResourceOwnershipScopePolicy::class);
$references = $container->get(CompositeResourceOwnershipReferences::class);
if (
    $registry->capabilityDefinitions()->ownedBy('core') !== []
    || $policy->permits('record', OwnershipScopeLevel::Installation)
    || $references->sitesReferencing(AuthorizationResource::item('record', '1'), ['a']) !== []
) {
    throw new RuntimeException('Resolved services do not preserve their documented empty/fail-closed policy.');
}
foreach (array_keys($dependencies['factories']) as $service) {
    $missingConfiguration = new ServiceManager($dependencies + ['services' => ['config' => []]]);
    try {
        $missingConfiguration->get($service);
    } catch (ServiceNotCreatedException $error) {
        if (!$error->getPrevious() instanceof InvalidArgumentException) {
            throw $error;
        }
        continue;
    }
    throw new RuntimeException('A service resolved without its required explicit host policy.');
}
echo "Laminas container verified: three shared services, fail-closed configuration and no host authority.\n";
