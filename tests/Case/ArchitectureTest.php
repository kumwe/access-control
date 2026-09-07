<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use Kumwe\Access\Tests\TestCase;
use RuntimeException;

/** Negative boundary probes hold forbidden host authority and runtime selection outside source. @since 0.1.0 */
final class ArchitectureTest extends TestCase
{
    /** The production boundary passes and representative forbidden source mutations fail. @return void @since 0.1.0 */
    public function testArchitectureBoundaryMutations(): void
    {
        $root = dirname(__DIR__, 2);
        $script = $root . '/tools/verify-architecture.php';
        $output = [];
        exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script), $output, $status);
        $this->assertSame(0, $status, implode("\n", $output));
        $directory = sys_get_temp_dir() . '/access-boundary-' . bin2hex(random_bytes(6));
        if (!mkdir($directory)) {
            throw new RuntimeException('Cannot create temporary architecture fixtures.');
        }
        $bodies = [
            'public function bad(): mixed { return new \\Kumwe\\App\\User(); }',
            'public function bad(): mixed { return new \\Kumwe\\Extension\\Capability(); }',
            'public function bad(): mixed { return \\time(); }',
            'public function bad(): mixed { return $_SERVER; }',
            'public function bad(): mixed { return class_exists("Host"); }',
            'public function bad(): mixed { return new \\Psr\\Container\\ContainerInterface(); }',
            'public function bad(): mixed { return eval("return 1;"); }',
            'public function bad(): mixed { return \\FFI::cdef("int test();"); }',
        ];
        try {
            foreach ($bodies as $body) {
                file_put_contents($directory . '/Capability.php', "<?php\n\ndeclare(strict_types=1);\n\n"
                    . "namespace Kumwe\\Access;\nfinal class Capability {\n    " . $body . "\n}\n");
                $output = [];
                exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' '
                    . escapeshellarg($directory) . ' 2>&1', $output, $status);
                $this->assertSame(1, $status, 'Forbidden source accepted: ' . $body);
            }
        } finally {
            unlink($directory . '/Capability.php');
            rmdir($directory);
        }
    }
}
