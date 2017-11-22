<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApi;

use KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApiBuilderTest;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SecurityTest extends OpenApiBuilderTest
{
    protected function setUp()
    {
        $this->setUpDescription('tests/definitions/openapi/mixed-security.yml');
    }

    /**
     * @test
     */
    public function noSecurityMarksOperationUnsecured()
    {
        $this->assertFalse(
            $this->description
                ->getPath('/none')
                ->getOperation('get')
                ->isSecured()
        );
    }

    /**
     * @test
     */
    public function apiKeySecurityMarksOperationSecured()
    {
        $this->assertTrue(
            $this->description
                ->getPath('/apiKey')
                ->getOperation('get')
                ->isSecured()
        );
    }

    /**
     * @test
     */
    public function oauth2SecurityMarksOperationSecured()
    {
        $this->assertTrue(
            $this->description
                ->getPath('/oauth2')
                ->getOperation('get')
                ->isSecured()
        );
    }
}
