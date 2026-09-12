<?php

/**
 * Prove package manifests, changelog, Composer metadata, documentation and the release record agree.
 *
 * The public API manifest is generated from source by tools/verify-public-api.php; this tool holds the two
 * hand-written manifests and the surrounding records to it. Every capability names only exported symbols and
 * existing documents; the service map declares no provider and says why; every manifest carries the package
 * coordinate, the canonical namespace and the release the changelog records; every exported symbol and public
 * method is documented in docs/public-api.md; and, once docs/release-record.md exists, its recorded manifest
 * digests are the digests of the files beside it.
 *
 * @since  0.1.0
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

/**
 * Decode one JSON object file or record why it cannot be used.
 *
 * @param   string        $path      Repository-relative path.
 * @param   list<string>  $failures  Findings collected so far.
 *
 * @return  array<string, mixed>  Decoded object, or an empty array after a recorded failure.
 *
 * @since   0.1.0
 */
function contextManifest(string $path, array &$failures): array
{
    $root = dirname(__DIR__);
    $bytes = is_file($root . '/' . $path) ? file_get_contents($root . '/' . $path) : false;
    if ($bytes === false) {
        $failures[] = "{$path} is missing.";

        return [];
    }
    try {
        $decoded = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        $failures[] = "{$path} is not valid JSON: {$error->getMessage()}";

        return [];
    }
    if (!is_array($decoded) || array_is_list($decoded)) {
        $failures[] = "{$path} must be a JSON object.";

        return [];
    }
    if (!str_ends_with($bytes, "\n") || str_contains($bytes, "\t")) {
        $failures[] = "{$path} must end with one newline and use spaces.";
    }
    /** @var array<string, mixed> $decoded */

    return $decoded;
}

$composerJson = contextManifest('composer.json', $failures);
$publicApi = contextManifest('resources/public-api/v1.json', $failures);
$capabilities = contextManifest('resources/capabilities/v1.json', $failures);
$serviceMap = contextManifest('resources/service-map/v1.json', $failures);

$package = is_string($composerJson['name'] ?? null) ? $composerJson['name'] : '';
$autoload = $composerJson['autoload'] ?? null;
$namespace = null;
if (is_array($autoload) && is_array($autoload['psr-4'] ?? null) && count($autoload['psr-4']) === 1) {
    $namespace = array_key_first($autoload['psr-4']);
}
if ($package !== 'kumwe/access-control' || $namespace !== 'Kumwe\\Access\\') {
    $failures[] = 'composer.json must name kumwe/access-control with the single PSR-4 root Kumwe\\Access\\.';
}
if (
    ($composerJson['require'] ?? null) !== ['php' => '^8.5', 'kumwe/access-context' => '0.1.2',
    'psr/container' => '^2.0']
) {
    $failures[] = 'composer.json must match the reviewed exact published dependency tuple.';
}

$release = null;
$changelog = is_file($root . '/CHANGELOG.md') ? file_get_contents($root . '/CHANGELOG.md') : false;
if ($changelog === false) {
    $failures[] = 'CHANGELOG.md is missing.';
} else {
    foreach (explode("\n", $changelog) as $line) {
        if (str_starts_with($line, '## ') && !in_array(trim($line), ['## Unreleased', '## [Unreleased]'], true)) {
            if (preg_match('/^## \[?([0-9]+\.[0-9]+\.[0-9]+)\]?( .*)?$/', trim($line), $match) === 1) {
                $release = $match[1];
            } else {
                $failures[] = 'The newest CHANGELOG.md heading does not parse as a release record: ' . trim($line);
            }
            break;
        }
    }
    if ($release === null) {
        $failures[] = 'CHANGELOG.md records no `## X.Y.Z` release heading.';
    }
}

$expectedSchemas = [
    'resources/public-api/v1.json' => [$publicApi, 'kumwe-package-public-api/v1'],
    'resources/capabilities/v1.json' => [$capabilities, 'kumwe-package-capabilities/v1'],
    'resources/service-map/v1.json' => [$serviceMap, 'kumwe-package-service-map/v1'],
];
foreach ($expectedSchemas as $path => [$document, $schema]) {
    if ($document === []) {
        continue;
    }
    if (($document['schema'] ?? null) !== $schema) {
        $failures[] = "{$path} must declare schema {$schema}.";
    }
    if (($document['package'] ?? null) !== $package) {
        $failures[] = "{$path} must name the Composer package {$package}.";
    }
    if (($document['release'] ?? null) !== $release) {
        $failures[] = "{$path} must record release {$release}, the newest CHANGELOG.md heading.";
    }
    if ($path !== 'resources/service-map/v1.json' && ($document['namespace'] ?? null) !== $namespace) {
        $failures[] = "{$path} must record the canonical namespace {$namespace}.";
    }
}

/** @var array<string, array<string, mixed>> $symbols */
$symbols = is_array($publicApi['symbols'] ?? null) ? $publicApi['symbols'] : [];
if ($symbols === []) {
    $failures[] = 'resources/public-api/v1.json exports no symbols.';
}
foreach ($symbols as $fqcn => $entry) {
    if (!is_string($fqcn) || !str_starts_with($fqcn, 'Kumwe\\Access\\')) {
        $failures[] = 'The public API manifest exports a symbol outside the canonical namespace.';
        continue;
    }
    $file = is_array($entry) ? ($entry['file'] ?? null) : null;
    if (!is_string($file) || !is_file($root . '/' . $file)) {
        $failures[] = "The public API manifest names a missing file for {$fqcn}.";
    }
}

$documentation = is_file($root . '/docs/public-api.md') ? file_get_contents($root . '/docs/public-api.md') : false;
if ($documentation === false) {
    $failures[] = 'docs/public-api.md is missing.';
} else {
    foreach ($symbols as $fqcn => $entry) {
        if (!is_string($fqcn) || !is_array($entry)) {
            continue;
        }
        if (!str_contains($documentation, '`' . $fqcn . '`')) {
            $failures[] = "docs/public-api.md does not document {$fqcn}.";
            continue;
        }
        $methods = is_array($entry['methods'] ?? null) ? array_keys($entry['methods']) : [];
        foreach ($methods as $method) {
            if (!str_contains($documentation, '`' . $method . '(')) {
                $failures[] = "docs/public-api.md does not document {$fqcn}::{$method}().";
            }
        }
        $constants = is_array($entry['constants'] ?? null) ? array_keys($entry['constants']) : [];
        foreach ($constants as $constant) {
            if (!str_contains($documentation, '`' . $constant . '`')) {
                $failures[] = "docs/public-api.md does not document {$fqcn}::{$constant}.";
            }
        }
    }
}

$entries = is_array($capabilities['capabilities'] ?? null) ? $capabilities['capabilities'] : [];
if ($entries === []) {
    $failures[] = 'resources/capabilities/v1.json declares no capability.';
}
$ids = [];
$referenced = [];
foreach ($entries as $entry) {
    $id = is_array($entry) ? ($entry['id'] ?? null) : null;
    if (!is_string($id) || preg_match('/^access-control\.[a-z0-9-]+(\.[a-z0-9-]+)*$/', $id) !== 1) {
        $failures[] = 'A capability identifier must be dot-separated and prefixed with access-control.';
        continue;
    }
    if (isset($ids[$id])) {
        $failures[] = "The capability identifier {$id} repeats.";
    }
    $ids[$id] = true;
    foreach (is_array($entry['symbols'] ?? null) ? $entry['symbols'] : [] as $symbol) {
        if (!is_string($symbol) || !array_key_exists($symbol, $symbols)) {
            $failures[] = "Capability {$id} names a symbol the public API does not export.";
            continue;
        }
        $referenced[$symbol] = true;
    }
    foreach (is_array($entry['documentation'] ?? null) ? $entry['documentation'] : [] as $document) {
        if (!is_string($document) || !is_file($root . '/' . $document)) {
            $failures[] = "Capability {$id} links documentation that does not exist.";
        }
    }
}
foreach (array_keys($symbols) as $fqcn) {
    if (!isset($referenced[$fqcn])) {
        $failures[] = "No capability claims the exported symbol {$fqcn}.";
    }
}
if (!array_key_exists('native_requirements', $capabilities) || $capabilities['native_requirements'] !== null) {
    $failures[] = 'resources/capabilities/v1.json must declare native_requirements: null.';
}
if (($capabilities['deprecations'] ?? null) !== []) {
    $failures[] = 'resources/capabilities/v1.json must declare no deprecations in this release.';
}

if ($serviceMap !== []) {
    if (($serviceMap['config_provider'] ?? null) !== 'Kumwe\\Access\\ConfigProvider') {
        $failures[] = 'The service map must declare the canonical ConfigProvider.';
    }
    $factories = $serviceMap['factories'] ?? null;
    if (!is_array($factories)) {
        $failures[] = 'Factories must be an array.';
        $factories = [];
    }
    foreach ($factories as $factory) {
        if (
            !is_array($factory) || !is_string($factory['service'] ?? null)
            || !is_string($factory['factory'] ?? null)
            || !isset($symbols[$factory['service']], $symbols[$factory['factory']])
        ) {
            $failures[] = 'Every factory and service must be a documented public symbol.';
        }
    }
}

$releaseRecord = is_file($root . '/docs/release-record.md')
    ? file_get_contents($root . '/docs/release-record.md')
    : false;
if ($releaseRecord === false) {
    $failures[] = 'docs/release-record.md is required for release and App adoption.';
}
if ($releaseRecord !== false) {
    if (!str_starts_with($releaseRecord, "---\nschema: kumwe-package-release-record/v1\n")) {
        $failures[] = 'docs/release-record.md must open with the kumwe-package-release-record/v1 front matter.';
    }
    preg_match_all(
        '/^    - path: (\S+)\n      sha256: "?([a-f0-9]{64})"?$/m',
        $releaseRecord,
        $recorded,
        PREG_SET_ORDER,
    );
    $recordedPaths = [];
    foreach ($recorded as [, $path, $digest]) {
        $recordedPaths[$path] = true;
        $actual = is_file($root . '/' . $path) ? hash_file('sha256', $root . '/' . $path) : null;
        if ($actual !== $digest) {
            $failures[] = "docs/release-record.md records a stale digest for {$path}.";
        }
    }
    foreach (array_keys($expectedSchemas) as $path) {
        if (!isset($recordedPaths[$path])) {
            $failures[] = "docs/release-record.md records no digest for {$path}.";
        }
    }
    $record = '/^  changelog_record: "CHANGELOG\.md ## ' . preg_quote((string) $release, '/') . '"$/m';
    if (preg_match($record, $releaseRecord) !== 1) {
        $failures[] = "docs/release-record.md must cite the changelog record for release {$release}.";
    }
}

if ($failures !== []) {
    fwrite(
        STDERR,
        'Manifest verification failed (' . count($failures) . " finding(s)):\n - " . implode("\n - ", $failures) . "\n",
    );
    exit(1);
}

printf(
    "Manifests agree: %d exported symbols, %d capabilities, explicit provider, release %s%s.\n",
    count($symbols),
    count($ids),
    (string) $release,
    $releaseRecord === false ? '' : ', release record digests current',
);
