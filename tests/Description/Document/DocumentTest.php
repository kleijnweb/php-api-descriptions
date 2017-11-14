<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Document;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DocumentTest extends TestCase
{
    /**
     * @var Document
     */
    private $document;

    protected function setUp()
    {
        $loader         = new DefinitionLoader();
        $uri            = 'tests/definitions/openapi/petstore.yml';
        $this->document = new Document($uri, $loader->load($uri));
    }

    public function testCanApplyRecursiveCallback()
    {
        $keys = [];
        $this->document->apply(function ($value, $key) use (&$keys) {
            $keys[] = $key;
        });

        self::assertCount(601, $keys);
    }

    public function testCanModifyValuesUsingRecursiveCallback()
    {
        $this->document->apply(function (&$value) {
            if (is_scalar($value)) {
                $value = __CLASS__;
            }
        });

        $this->document->apply(function ($value) use (&$values) {
            if (is_scalar($value)) {
                $values[] = $value;
            }
        });

        self::assertCount(1, array_unique($values));
    }

    public function testCanStopRecursiveProcessingByReturningFalse()
    {
        $this->document->apply(function ($value, $key) use (&$keys) {

            if ($key === 'paths') {

                return false;
            }
            $keys[] = $key;

            return true;
        });
        self::assertCount(15, $keys);
    }
}
