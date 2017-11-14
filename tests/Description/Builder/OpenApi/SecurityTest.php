<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApi;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
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

    public function testNoSecurityMarksOperationUnsecured()
    {
        self::assertFalse(
            $this->description
                ->getPath('/none')
                ->getOperation('get')
                ->isSecured()
        );
    }

    public function testApiKeySecurityMarksOperationSecured()
    {
        self::assertTrue(
            $this->description
                ->getPath('/apiKey')
                ->getOperation('get')
                ->isSecured()
        );
    }

    public function testOauth2SecurityMarksOperationSecured()
    {
        self::assertTrue(
            $this->description
                ->getPath('/oauth2')
                ->getOperation('get')
                ->isSecured()
        );
    }
}
