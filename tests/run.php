<?php

/**
 * Dependency-free test runner: discovers tests/Case/*Test.php, runs every public method beginning with "test",
 * and reports one line per file.
 *
 * Assertions come from Kumwe\Access\Tests\TestCase. No framework, so the suite runs on any supported PHP with no
 * composer install. Discovery fails closed: deleting the suite, or leaving a discovered case with no test
 * methods, is a build failure rather than an empty success.
 *
 * @since  0.1.0
 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Kumwe\\Access\\Tests\\' => __DIR__ . '/',
        'Kumwe\\Access\\' => dirname(__DIR__) . '/src/',
    ];
    foreach ($prefixes as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $path = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($path)) {
                require $path;
            }

            return;
        }
    }
});

$files = glob(__DIR__ . '/Case/*Test.php');
$files = $files === false ? [] : $files;
sort($files);

if ($files === []) {
    fwrite(STDERR, "Access Control suite failed: no test case files were discovered.\n");
    exit(1);
}

$listOnly = in_array('--list-json', $argv ?? [], true);
$inventory = [];
$totalTests = 0;
$totalAssertions = 0;
$failures = [];

foreach ($files as $file) {
    $class = 'Kumwe\\Access\\Tests\\Case\\' . basename($file, '.php');
    if (!class_exists($class)) {
        $failures[] = "{$file} declares no {$class}.";
        continue;
    }
    $case = new $class();
    if (!$case instanceof Kumwe\Access\Tests\TestCase) {
        $failures[] = "{$class} does not extend the suite's TestCase.";
        continue;
    }
    $ran = 0;
    foreach (get_class_methods($case) as $method) {
        if (!str_starts_with($method, 'test')) {
            continue;
        }
        $totalTests++;
        $ran++;
        $inventory[$class . '::' . $method] = 'tests/Case/' . basename($file);
        if ($listOnly) {
            continue;
        }
        try {
            (new ReflectionMethod($case, $method))->invoke($case);
        } catch (Throwable $error) {
            $failures[] = sprintf(
                '%s::%s - %s (%s:%d)',
                $class,
                $method,
                $error->getMessage(),
                basename($error->getFile()),
                $error->getLine(),
            );
        }
    }
    if ($ran === 0) {
        $failures[] = "{$class} declares no public test methods.";
    }
    $totalAssertions += $case->assertionCount();
    if (!$listOnly) {
        echo sprintf("%-40s %3d tests\n", basename($file), $ran);
    }
}

if ($totalTests === 0) {
    $failures[] = 'No tests ran.';
}

if ($failures !== []) {
    fwrite(STDERR, "\nFailures:\n - " . implode("\n - ", $failures) . "\n");
    exit(1);
}

if ($listOnly) {
    ksort($inventory, SORT_STRING);
    echo json_encode($inventory, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
    exit(0);
}

echo "\nAccess Control suite passed: {$totalTests} tests, {$totalAssertions} assertions.\n";
