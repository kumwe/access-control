<?php

/**
 * Minimal assertion base for the dependency-free suite.
 *
 * @since  0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Access\Tests;

use RuntimeException;
use Throwable;

/**
 * Counts assertions and turns a failed expectation into a thrown message the runner reports.
 *
 * @since  0.1.0
 */
abstract class TestCase
{
    /**
     * Assertions made so far by this case.
     *
     * @var    int
     * @since  0.1.0
     */
    private int $assertions = 0;

    /**
     * Report how many assertions the case has made.
     *
     * @return  int  Assertion count.
     *
     * @since   0.1.0
     */
    final public function assertionCount(): int
    {
        return $this->assertions;
    }

    /**
     * Require a condition to hold.
     *
     * @param   bool    $condition  Condition under test.
     * @param   string  $message    Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the condition is false.
     *
     * @since   0.1.0
     */
    final protected function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    /**
     * Require a condition not to hold.
     *
     * @param   bool    $condition  Condition under test.
     * @param   string  $message    Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the condition is true.
     *
     * @since   0.1.0
     */
    final protected function assertFalse(bool $condition, string $message): void
    {
        $this->assertTrue(!$condition, $message);
    }

    /**
     * Require two values to be identical.
     *
     * @param   mixed   $expected  Expected value.
     * @param   mixed   $actual    Observed value.
     * @param   string  $message   Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the values differ.
     *
     * @since   0.1.0
     */
    final protected function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new RuntimeException(
                $message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . '.',
            );
        }
    }

    /**
     * Require two values not to be identical.
     *
     * @param   mixed   $unexpected  Value that must not be observed.
     * @param   mixed   $actual      Observed value.
     * @param   string  $message     Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the values are identical.
     *
     * @since   0.1.0
     */
    final protected function assertNotSame(mixed $unexpected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($unexpected === $actual) {
            throw new RuntimeException($message . ' Both were ' . var_export($actual, true) . '.');
        }
    }

    /**
     * Require a value to be null.
     *
     * @param   mixed   $actual   Observed value.
     * @param   string  $message  Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the value is not null.
     *
     * @since   0.1.0
     */
    final protected function assertNull(mixed $actual, string $message): void
    {
        $this->assertSame(null, $actual, $message);
    }

    /**
     * Require a string to contain a substring.
     *
     * @param   string  $needle    Expected substring.
     * @param   string  $haystack  String under test.
     * @param   string  $message   Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the substring is absent.
     *
     * @since   0.1.0
     */
    final protected function assertStringContains(string $needle, string $haystack, string $message): void
    {
        $this->assertions++;
        if (!str_contains($haystack, $needle)) {
            throw new RuntimeException($message . ' Missing: ' . $needle);
        }
    }

    /**
     * Require a string not to contain a substring.
     *
     * @param   string  $needle    Forbidden substring.
     * @param   string  $haystack  String under test.
     * @param   string  $message   Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the substring is present.
     *
     * @since   0.1.0
     */
    final protected function assertStringExcludes(string $needle, string $haystack, string $message): void
    {
        $this->assertions++;
        if (str_contains($haystack, $needle)) {
            throw new RuntimeException($message . ' Forbidden substring present: ' . $needle);
        }
    }

    /**
     * Require an operation to throw a given exception type and hand the exception back for inspection.
     *
     * @param   callable         $operation       Operation expected to throw.
     * @param   class-string     $exceptionClass  Exception type that must be thrown.
     * @param   string           $message         Failure explanation.
     *
     * @return  Throwable  The thrown exception.
     *
     * @throws  RuntimeException  When nothing, or something else, is thrown.
     *
     * @since   0.1.0
     */
    final protected function assertThrows(callable $operation, string $exceptionClass, string $message): Throwable
    {
        $this->assertions++;
        try {
            $operation();
        } catch (Throwable $error) {
            if (!($error instanceof $exceptionClass)) {
                throw new RuntimeException(
                    $message . ' Threw ' . $error::class . ' instead of ' . $exceptionClass
                        . ': ' . $error->getMessage(),
                );
            }

            return $error;
        }

        throw new RuntimeException($message . ' Nothing was thrown; expected ' . $exceptionClass . '.');
    }

    /**
     * Require an operation to throw the package refusal with an exact operator-facing message.
     *
     * @param   callable  $operation  Operation expected to refuse.
     * @param   string    $expected   Exact refusal message.
     * @param   string    $message    Failure explanation.
     *
     * @return  void
     *
     * @throws  RuntimeException  When the operation does not refuse with that message.
     *
     * @since   0.1.0
     */
    final protected function assertRefused(callable $operation, string $expected, string $message): void
    {
        $error = $this->assertThrows($operation, \Kumwe\Access\Exception\InvalidContext::class, $message);
        $this->assertSame($expected, $error->getMessage(), $message . ' The refusal must name the rule.');
    }
}
