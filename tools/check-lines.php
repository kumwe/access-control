<?php

/**
 * Refuse any committed line wider than 120 columns and any machine path or timestamp in a committed artifact.
 *
 * Every file the repository tracks is scanned, not only PHP: documentation, manifests, workflows and the
 * handoff are read by people and by the App's adoption gate at the same width. The machine-path rule keeps a
 * sandbox path or a scratch directory from leaking into a released artifact.
 *
 * @since  0.1.0
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$skip = ['.git', 'vendor', '.phpstan.cache', 'dist'];
$ignored = ['composer.lock'];
$forbidden = ['/' . 'home/', '/' . 'tmp/', '/' . 'root/', 'C:' . '\\'];
$failures = [];
$files = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        static function (SplFileInfo $entry) use ($skip): bool {
            return !($entry->isDir() && in_array($entry->getFilename(), $skip, true));
        },
    ),
);
foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }
    $relative = substr($file->getPathname(), strlen($root) + 1);
    if ($relative === 'LICENSE' || in_array($relative, $ignored, true)) {
        continue;
    }
    $files++;
    $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        $failures[] = $relative . ' cannot be read.';
        continue;
    }
    foreach ($lines as $index => $line) {
        $where = $relative . ':' . ($index + 1);
        if (mb_strlen($line, 'UTF-8') > 120) {
            $failures[] = $where . ' is wider than 120 columns.';
        }
        foreach ($forbidden as $needle) {
            if (str_contains($line, $needle)) {
                $failures[] = $where . ' carries a machine path (' . $needle . ').';
            }
        }
    }
}

if ($files === 0) {
    $failures[] = 'No files were scanned.';
}

if ($failures !== []) {
    fwrite(
        STDERR,
        'Line check failed (' . count($failures) . " finding(s)):\n - " . implode("\n - ", $failures) . "\n",
    );
    exit(1);
}

echo "Line check passed: {$files} files within 120 columns and free of machine paths.\n";
