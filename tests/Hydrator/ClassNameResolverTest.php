<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Hydrator\ClassNameResolver;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\ClassNotFoundException;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Pet;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 *
 * @runTestsInSeparateProcesses Makes sure the class has to be autoloaded
 */
class ClassNameResolverTest extends TestCase
{
    /**
     * @test
     */
    public function canResolveExistingClass()
    {
        $resolver = new ClassNameResolver([__NAMESPACE__ . '\\Types']);
        $this->assertSame(Pet::class, $resolver->resolve('Pet'));
    }

    /**
     * @test
     */
    public function willUseCache()
    {
        $resolver = new ClassNameResolver([__NAMESPACE__ . '\\Types']);

        $start = microtime(true);
        $resolver->resolve('Pet');
        $first = microtime(true) - $start;

        $repeats = 5;
        $start = microtime(true);
        for ($i = 0; $i < $repeats; ++$i) {
            $resolver->resolve('Pet');
        }

        $this->assertLessThan($first, (microtime(true) - $start) / ($repeats - 1));
    }

    /**
     * @test
     */
    public function willThrowExceptionWhenClassNameIsNotResolvable()
    {
        $resolver = new ClassNameResolver([__NAMESPACE__ . '\\Types']);

        $this->expectException(ClassNotFoundException::class);
        $resolver->resolve('ProjectX');
    }
}
