<?php

/**
 * Enforce the package boundary without external tooling.
 *
 * Every source file declares strict types, sits in the canonical PSR-4 namespace, declares exactly the type
 * its file names, and names no type outside the package or PHP itself: no driver, no host, no container and
 * no framework. The contract layer never reaches the test-support layer, and no source file selects an
 * implementation at runtime. Composer metadata keeps the runtime requirement at PHP alone and preserves the
 * repository license.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $argv[1] ?? $root . '/src';
$namespaceRoot = 'Kumwe\\Access';
$layers = [
    '' => ['AuthorizationDecision',
        'AuthorizationDecisionRecorder',
        'AuthorizationDefinitionLifecycle',
        'AuthorizationDenied',
        'AuthorizationGateway',
        'AuthorizationPolicyRegistry',
        'AuthorizationResource',
        'AuthorizationResourceOwnershipUnknown',
        'Capability',
        'CapabilityDefinition',
        'CapabilityDefinitionRegistry',
        'CompositeResourceOwnershipReferences',
        'ConfigProvider',
        'DecisionCombiner',
        'DecisionState',
        'GrantScope',
        'MembershipContextValidator',
        'MembershipDirectory',
        'MembershipRequirement',
        'OwnershipNarrowingRefused',
        'OwnershipNarrowingUnbounded',
        'OwnershipScope',
        'OwnershipScopeChangeRejected',
        'OwnershipScopeLevel',
        'OwnershipScopeNotPermitted',
        'OwnershipScopeNotSiteBound',
        'OwnershipScopeRule',
        'ResourceOwnership',
        'ResourceOwnershipReferences',
        'ResourceOwnershipScopePolicy',
        'ResourcePolicyDefinition',
        'ResourcePolicyRegistry',
        'ResourcePolicyTarget',
        'ResourceSiteOwnership',
        'ResourceSiteOwnershipConflict',
        'ResourceSiteOwnershipWriter',
        'SiteGroup',
        'SiteGroupRegistry',
        'SiteGroupUnknown',
        'SiteGroupWriter'],
    'Container' => ['AuthorizationPolicyRegistryFactory', 'CompositeResourceOwnershipReferencesFactory',
        'ResourceOwnershipScopePolicyFactory'],
];
$runtimeSelection = ['class_alias', 'class_exists', 'interface_exists', 'extension_loaded', 'function_exists',
    'ffi', 'getenv', 'time', 'microtime', 'hrtime', 'random_bytes', 'random_int',
    'file_get_contents', 'file_put_contents',
    'fopen', 'exec', 'shell_exec', 'system', 'passthru', 'unserialize', 'serialize', 'session_start'];
$errors = [];
$files = [];

if (is_dir($source)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}
sort($files);

foreach ($files as $path) {
    $relative = 'src/' . str_replace('\\', '/', substr($path, strlen($source) + 1));
    $code = file_get_contents($path);
    if (!is_string($code)) {
        $errors[] = $relative . ' cannot be read.';
        continue;
    }
    if (!str_contains($code, 'declare(strict_types=1);')) {
        $errors[] = $relative . ' does not enable strict types.';
    }

    $pathPart = substr($relative, strlen('src/'), -strlen('.php'));
    $segments = explode('/', $pathPart);
    $fileName = array_pop($segments);
    $expectedNamespace = $namespaceRoot . ($segments === [] ? '' : '\\' . implode('\\', $segments));
    if (
        preg_match('/^namespace\s+([^;]+);/m', $code, $namespaceMatch) !== 1
        || trim($namespaceMatch[1]) !== $expectedNamespace
    ) {
        $errors[] = $relative . ' does not declare its PSR-4 namespace ' . $expectedNamespace . '.';
        continue;
    }
    $declared = preg_match_all(
        '/^(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|enum|trait)\s+(\w+)/m',
        $code,
        $typeMatches,
    );
    if ($declared !== 1 || $typeMatches[1][0] !== $fileName) {
        $errors[] = $relative . ' must declare exactly the one type named by its file.';
    }

    $layer = $segments[0] ?? '';
    if (!in_array($fileName, $layers[$layer] ?? [], true)) {
        $errors[] = $relative . ' adds an unreviewed production type.';
    }
    if (preg_match('/^(class|readonly class|abstract class|trait) /m', $code) === 1) {
        $errors[] = $relative . ' must be final, an interface or enum.';
    }
    if (!isset($layers[$layer])) {
        $errors[] = $relative . ' belongs to an unclassified layer; every source file sits under a known layer.';
        continue;
    }

    foreach (token_get_all($code) as $token) {
        if (!is_array($token)) {
            continue;
        }
        [$id, $text, $line] = $token;
        if (
            $id === T_VARIABLE && in_array($text, ['$_SERVER', '$_ENV', '$_SESSION', '$_REQUEST',
            '$_GET', '$_POST', '$_COOKIE', '$GLOBALS'], true)
        ) {
            $errors[] = $relative . ' reads ambient host state.';
        }
        if (
            $id === T_EVAL || $id === T_INCLUDE || $id === T_REQUIRE
            || $id === T_INCLUDE_ONCE || $id === T_REQUIRE_ONCE
        ) {
            $errors[] = $relative . ' dynamically loads code.';
        }
        if ($id === T_STRING && in_array(strtolower($text), $runtimeSelection, true)) {
            $errors[] = sprintf('%s:%d selects behaviour at runtime through %s().', $relative, $line, $text);
            continue;
        }
        if (!in_array($id, [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
            continue;
        }
        $name = ltrim($text, '\\');
        if (in_array(strtolower($name), $runtimeSelection, true)) {
            $errors[] = $relative . ' uses a forbidden qualified runtime function.';
        }
        if (!str_contains($name, '\\')) {
            continue;
        }
        if ($name === $namespaceRoot) {
            continue;
        }
        if (
            in_array($name, ['Kumwe\\Context\\Value\\ExecutionContext',
            'Kumwe\\Context\\Value\\SiteContext', 'Kumwe\\Context\\Value\\MembershipContext'], true)
        ) {
            continue;
        }
        if ($layer === 'Container' && $name === 'Psr\\Container\\ContainerInterface') {
            continue;
        }
        if (!str_starts_with($name, $namespaceRoot . '\\')) {
            $errors[] = sprintf(
                '%s:%d names %s, which is neither a package type nor a PHP type.',
                $relative,
                $line,
                $name,
            );
            continue;
        }
        $target = explode('\\', substr($name, strlen($namespaceRoot) + 1))[0];
        if (!in_array($target, [...$layers[''], 'Container'], true)) {
            $errors[] = sprintf('%s crosses from %s into forbidden %s.', $relative, $layer, $target);
        }
    }
}

$composerBytes = file_get_contents($root . '/composer.json');
$composer = is_string($composerBytes) ? json_decode($composerBytes, true) : null;
$composer = is_array($composer) ? $composer : [];
if (($composer['name'] ?? null) !== 'kumwe/access-control') {
    $errors[] = 'composer.json must name the package kumwe/access-control.';
}
if (($composer['license'] ?? null) !== 'Apache-2.0') {
    $errors[] = 'composer.json must advertise the repository Apache-2.0 license exactly.';
}
$runtime = is_array($composer['require'] ?? null) ? array_keys($composer['require']) : [];
if ($runtime !== ['php', 'kumwe/access-context', 'psr/container']) {
    $errors[] = 'Runtime dependencies are limited to PHP, exact Access Context and PSR Container.';
}
if (($composer['autoload'] ?? null) !== ['psr-4' => [$namespaceRoot . '\\' => 'src/']]) {
    $errors[] = 'composer.json must autoload exactly the one canonical namespace Kumwe\\Access\\ from src/.';
}
if (array_key_exists('version', $composer)) {
    $errors[] = 'composer.json must not carry a version; the changelog is the release record.';
}

if ($errors !== []) {
    fwrite(STDERR, "Architecture verification failed:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

echo 'Architecture verified: ' . count($files)
    . " source files under Kumwe\\Access, no host/driver coupling; container access limited to factories.\n";
