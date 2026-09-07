<?php

/** Explicitly assemble neutral policy metadata; the host still owns final authority. @since 0.1.0 */

declare(strict_types=1);

use Kumwe\Access\AuthorizationDefinitionLifecycle;
use Kumwe\Access\AuthorizationPolicyRegistry;
use Kumwe\Access\AuthorizationResource;
use Kumwe\Access\Capability;
use Kumwe\Access\CapabilityDefinition;
use Kumwe\Access\MembershipRequirement;
use Kumwe\Access\ResourcePolicyDefinition;
use Kumwe\Access\ResourcePolicyTarget;

require $argv[1] ?? dirname(__DIR__) . '/vendor/autoload.php';

$registry = new AuthorizationPolicyRegistry(new MembershipRequirement(['example_record']));
$capability = Capability::fromString('example.editor.read');
$registry->registerCapability(new CapabilityDefinition(
    $capability,
    'example/editor',
    ['site'],
    true,
    false,
    AuthorizationDefinitionLifecycle::Active,
    1
));
$registry->registerResourcePolicy(new ResourcePolicyDefinition(
    'example.editor.records',
    'example/editor',
    $capability,
    [new ResourcePolicyTarget('example_record', ['123'])],
    false,
    [],
    AuthorizationDefinitionLifecycle::Active,
    1
));
$resource = AuthorizationResource::item('example_record', '123');
if (!$registry->supports($capability, $resource) || !$registry->requiresMembershipContext($capability)) {
    throw new RuntimeException('Portable policy metadata did not match.');
}
echo "Owner-bound policy supports exact numeric string resource; fresh host membership is required.\n";
