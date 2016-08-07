<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Document\Definition\Validator;

use KleijnWeb\ApiDescriptions\Description\Document\Definition\Validator\MetaSchemaValidator;
use KleijnWeb\ApiDescriptions\Description\Document\Definition\Validator\OpenApiDefinitionValidator;
use KleijnWeb\ApiDescriptions\Description\Schema\Validator\SchemaValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiDefinitionValidatorTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->validator->validate((object)[]);
    }
}
