<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description;

use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Path;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Path
     */
    private $path;

    protected function setUp()
    {
        $this->path = new Path(
            '/foo/bar',
            (object)[
                'get'        => (object)[],
                'parameters' => [
                    (object)['name' => 'bar', 'in' => Parameter::IN_QUERY, 'type' => 'string']
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function willCreatePathParameters()
    {
        $params = $this->path->getPathParameters();
        $this->assertInternalType('array', $params);
        $this->assertInstanceOf(Parameter::class, $params[0]);
    }

    /**
     * @test
     */
    public function willAddPathParametersToOperation()
    {
        $path = new Path(
            '/foo/bar',
            (object)[
                'get'        => (object)[],
                'parameters' => [
                    (object)['name' => 'bar', 'in' => Parameter::IN_QUERY, 'type' => 'string']
                ]
            ]
        );

        $this->assertInstanceOf(Parameter::class, $path->getOperation('get')->getParameter('bar'));
    }
}
