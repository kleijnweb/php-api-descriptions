<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document\Definition\Validator;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Validator\MetaSchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Validator\OpenApiDefinitionValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiDefinitionValidatorTest extends TestCase
{
    /**
     * @var MetaSchemaValidator
     */
    private $validator;

    protected function setUp()
    {
        /** @var SchemaValidator $validator */
        $this->validator = new OpenApiDefinitionValidator();
    }

    /**
     * @test
     */
    public function canValidate()
    {
        $this->validator->validate(json_decode(file_get_contents('tests/definitions/openapi/petstore.json')));
    }

    /**
     * @test
     */
    public function canInvalidate()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validate((object)[]);
    }
}
