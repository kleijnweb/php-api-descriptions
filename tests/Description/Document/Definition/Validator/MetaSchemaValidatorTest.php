<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document\Definition\Validator;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Validator\MetaSchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\ValidationResult;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class MetaSchemaValidatorTest extends TestCase
{
    /**
     * @var ValidationResult
     */
    private $expectedValidationResult;

    /**
     * @var MetaSchemaValidator
     */
    private $validator;

    protected function setUp()
    {
        $validator = $this->getMockForAbstractClass(SchemaValidator::class);
        $validator->expects(self::once())->method('validate')->willReturnCallback(function () {
            return $this->expectedValidationResult;
        });
        /** @var SchemaValidator $validator */
        $this->validator = new MetaSchemaValidator((object)[], $validator);
    }

    public function testCanValidate()
    {
        $this->expectedValidationResult = new ValidationResult(true, []);

        $this->validator->validate((object)[]);
    }

    public function testCanInvalidate()
    {
        $this->expectedValidationResult = new ValidationResult(false, []);

        self::expectException(\InvalidArgumentException::class);
        $this->validator->validate((object)[]);

    }
}
