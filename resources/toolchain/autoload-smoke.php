<?php

/**
 * Prove the installed production Composer autoloader exposes the complete public API and the examples run.
 *
 * The script is itself a shipped package resource, so the same proof runs in the source checkout and again after
 * the exported consumer archive has been extracted and installed with --no-dev --classmap-authoritative. Its
 * symbol list comes only from the shipped public API manifest; it also proves the service map's promise that no
 * ConfigProvider exists, and executes every documented example so a consumer sees the package work end to end.
 *
 * @since  0.1.0
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$autoload = $argv[1] ?? $root . '/vendor/autoload.php';
$manifestPath = $root . '/resources/public-api/v1.json';
$capabilitiesPath = $root . '/resources/capabilities/v1.json';
$serviceMapPath = $root . '/resources/service-map/v1.json';

if (!is_file($autoload)) {
    fwrite(STDERR, "Composer autoload smoke failed: vendor/autoload.php is missing.\n");
    exit(1);
}

require $autoload;

/**
 * Decode one shipped JSON object or stop the smoke.
 *
 * @param   string  $path  Absolute path of the manifest.
 *
 * @return  array<string, mixed>  Decoded object.
 *
 * @since   0.1.0
 */
function contextSmokeJson(string $path): array
{
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        fwrite(STDERR, "Composer autoload smoke failed: {$path} is unreadable.\n");
        exit(1);
    }
    try {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        fwrite(STDERR, 'Composer autoload smoke failed: ' . $error->getMessage() . "\n");
        exit(1);
    }
    if (!is_array($decoded) || array_is_list($decoded)) {
        fwrite(STDERR, "Composer autoload smoke failed: {$path} is not a JSON object.\n");
        exit(1);
    }
    /** @var array<string, mixed> $decoded */

    return $decoded;
}

$manifest = contextSmokeJson($manifestPath);
$capabilities = contextSmokeJson($capabilitiesPath);
$serviceMap = contextSmokeJson($serviceMapPath);

/** @var array<string, array{kind?: mixed}> $symbols */
$symbols = is_array($manifest['symbols'] ?? null) ? $manifest['symbols'] : [];
if ($symbols === []) {
    fwrite(STDERR, "Composer autoload smoke failed: the public API manifest exports no symbols.\n");
    exit(1);
}

$failures = [];
foreach ($symbols as $name => $shape) {
    $kind = $shape['kind'] ?? null;
    $loaded = match ($kind) {
        'class' => class_exists($name),
        'interface' => interface_exists($name),
        'enum' => enum_exists($name),
        default => false,
    };
    if (!$loaded) {
        $failures[] = sprintf('%s (%s) did not load', $name, is_string($kind) ? $kind : 'invalid kind');
    }
}

foreach (is_array($capabilities['capabilities'] ?? null) ? $capabilities['capabilities'] : [] as $capability) {
    $names = is_array($capability) && is_array($capability['symbols'] ?? null) ? $capability['symbols'] : [];
    foreach ($names as $symbol) {
        if (!is_string($symbol) || !array_key_exists($symbol, $symbols)) {
            $failures[] = 'a capability names an unexported symbol';
        }
    }
}

if (
    !array_key_exists('config_provider', $serviceMap)
    || $serviceMap['config_provider'] !== null
    || class_exists('Kumwe\\Access\\ConfigProvider')
) {
    $failures[] = 'the service map promises no provider, but one exists or is declared';
}

$examples = glob($root . '/examples/*.php');
$examples = $examples === false ? [] : $examples;
sort($examples);
if ($examples === []) {
    $failures[] = 'no documented example is shipped';
}
foreach ($examples as $example) {
    $output = [];
    exec(
        escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($example) . ' ' . escapeshellarg($autoload) . ' 2>&1',
        $output,
        $status,
    );
    if ($status !== 0 || $output === []) {
        $failures[] = sprintf('example %s exited %d: %s', basename($example), $status, implode(' ', $output));
    }
}

if ($failures !== []) {
    fwrite(STDERR, "Composer autoload smoke failed:\n - " . implode("\n - ", $failures) . "\n");
    exit(1);
}

printf(
    "Composer autoload smoke passed: %d public symbols loaded, no provider, %d examples ran.\n",
    count($symbols),
    count($examples),
);
