<?php

declare(strict_types=1);

namespace Kumwe\Access\Tests\Case;

use Kumwe\Access\ConfigProvider;
use Kumwe\Access\MembershipContextValidator;
use Kumwe\Access\MembershipDirectory;
use Kumwe\Access\Tests\TestCase;
use Kumwe\Context\Value\MembershipContext;
use Kumwe\Context\Value\SiteContext;
use ReflectionClass;

/**
 * Exact membership port signatures preserve host freshness and selection responsibility.
 * @since 0.1.0
 */
final class MembershipDirectoryTest extends TestCase
{
    /**
     * Membership lookup refines the existing freshness port without widening its inputs.
     * @return void
     * @since 0.1.0
     */
    public function testMembershipDirectorySignatureConformance(): void
    {
        $directory = new ReflectionClass(MembershipDirectory::class);
        $this->assertTrue($directory->isInterface());
        $this->assertTrue($directory->implementsInterface(MembershipContextValidator::class));
        $resolve = $directory->getMethod('resolve');
        $this->assertSame('?' . MembershipContext::class, (string) $resolve->getReturnType());
        $this->assertSame(5, $resolve->getNumberOfParameters());
        $this->assertSame(SiteContext::class, (string) $resolve->getParameters()[1]->getType());
        $this->assertNull($resolve->getParameters()[3]->getDefaultValue());
        $this->assertSame(false, $resolve->getParameters()[4]->getDefaultValue());
        $current = $directory->getMethod('current');
        $parent = new ReflectionClass(MembershipContextValidator::class);
        foreach ($current->getParameters() as $index => $parameter) {
            $this->assertSame(
                (string) $parent->getMethod('current')->getParameters()[$index]->getType(),
                (string) $parameter->getType(),
            );
        }
        $this->assertSame('bool', (string) $current->getReturnType());
        $this->assertSame('array', (string) $directory->getMethod('selections')->getReturnType());
    }

    /**
     * Importing the package cannot install or construct a membership authority.
     * @return void
     * @since 0.1.0
     */
    public function testMembershipAuthorityIsNeverRegisteredByDefault(): void
    {
        $configuration = (new ConfigProvider())();
        $this->assertFalse(isset($configuration['dependencies']['factories'][MembershipDirectory::class]));
        $this->assertFalse(isset($configuration['dependencies']['factories'][MembershipContextValidator::class]));
        $this->assertFalse((new ReflectionClass(MembershipDirectory::class))->isInstantiable());
    }
}
