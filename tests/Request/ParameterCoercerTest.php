<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Request;

use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Request\ParameterCoercer;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterCoercerTest extends TestCase
{
    /**
     * @var ParameterCoercer
     */
    private $coercer;

    protected function setUp()
    {
        $this->coercer = new ParameterCoercer();
    }

    /**
     * @test
     */
    public function willReturnOriginalValueIfTypeDoesNotMatchKnownType()
    {
        $value  = (object)[];
        $actual = $this->coercer->coerce($this->createParameter(['getType' => 'x']), $value);
        $this->assertSame($value, $actual);
    }

    /**
     * @dataProvider conversionProvider
     * @test
     *
     * @param string $type
     * @param mixed  $value
     * @param mixed  $expected
     * @param string $format
     */
    public function willInterpretValuesAsExpected($type, $value, $expected, $format = null)
    {
        $stubs       = [];
        $schemaStubs = ['getType' => $type];

        if ($type === 'array') {
            $stubs['getCollectionFormat'] = $format;
        }
        if ($type === 'string') {
            $schemaStubs['getFormat'] = $format;
        }

        $actual = $this->coercer->coerce($this->createParameter($schemaStubs, $stubs), $value);

        if (is_object($expected)) {
            $this->assertEquals($expected, $actual);

            return;
        }
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider malformedConversionProvider
     * @test
     *
     * @param string $type
     * @param mixed  $value
     */
    public function willNotChangeUninterpretableValues($type, $value)
    {
        $actual = $this->coercer->coerce($this->createParameter(['getType' => $type]), $value);
        $this->assertSame($value, $actual);
    }

    /**
     * @dataProvider malformedDateTimeConversionProvider
     * @test
     *
     * @param string $format
     * @param mixed  $value
     */
    public function willNotChangeUninterpretableDateTimeAsExpected($format, $value)
    {
        $actual = $this->coercer->coerce(
            $this->createParameter([
                'getType'   => 'string',
                'getFormat' => $format,
            ]),
            $value
        );
        $this->assertSame($value, $actual);
    }

    /**
     * @test
     */
    public function willThrowUnsupportedExceptionInPredefinedCases()
    {
        $this->expectException(\RuntimeException::class);
        $this->coercer->coerce(
            $this->createParameter(
                ['getType' => 'array'],
                ['getCollectionFormat' => 'multi']
            ),
            ''
        );
    }

    /**
     * @return array
     */
    public static function conversionProvider()
    {
        $now       = new \DateTime();
        $midnight  = new \DateTime('midnight today');
        $object    = (object)[];
        $object->a = 'b';
        $object->c = 'd';

        return [
            ['boolean', '0', false],
            ['boolean', 'FALSE', false],
            ['boolean', 'false', false],
            ['boolean', '1', true],
            ['boolean', 'TRUE', true],
            ['boolean', 'true', true],
            ['integer', '1', 1],
            ['integer', '21474836470', 21474836470],
            ['integer', '00005', 5],
            ['number', '1', 1],
            ['number', '1.5', 1.5],
            ['number', '1', 1],
            ['number', '1.5', 1.5],
            ['string', '1', '1'],
            ['string', '1.5', '1.5'],
            ['string', '€', '€'],
            ['null', '', null],
            ['string', $midnight->format('Y-m-d'), $midnight->format('Y-m-d'), 'date'],
            ['string', $now->format(\DateTime::W3C), $now->format(\DateTime::W3C), 'date-time'],
            ['array', [1, 2, 3, 4], [1, 2, 3, 4]],
            ['array', 'a', ['a']],
            ['array', 'a,b,c', ['a', 'b', 'c']],
            ['array', 'a, b,c ', ['a', ' b', 'c ']],
            ['array', 'a', ['a'], 'ssv'],
            ['array', 'a b c', ['a', 'b', 'c'], 'ssv'],
            ['array', 'a  b c ', ['a', '', 'b', 'c', ''], 'ssv'],
            ['array', 'a', ['a'], 'tsv'],
            ['array', "a\tb\tc", ['a', 'b', 'c'], 'tsv'],
            ['array', "a\t b\tc ", ['a', ' b', 'c '], 'tsv'],
            ['array', 'a', ['a'], 'pipes'],
            ['array', 'a|b|c', ['a', 'b', 'c'], 'pipes'],
            ['array', 'a| b|c ', ['a', ' b', 'c '], 'pipes'],
            ['object', ['a' => 'b', 'c' => 'd'], $object],
            ['object', '', null],
        ];
    }

    /**
     * @return array
     */
    public static function malformedConversionProvider()
    {
        return [
            ['boolean', 'a'],
            ['boolean', ''],
            ['boolean', "\0"],
            ['boolean', null],
            ['integer', '1.0'],
            ['integer', 'TRUE'],
            ['integer', ''],
            ['number', 'b'],
            ['number', 'FALSE'],
            ['null', '0'],
            ['null', 'FALSE'],
            ['object', ['a', 'c']],
            ['object', 'FALSE'],
        ];
    }

    /**
     * @return array
     */
    public static function malformedDateTimeConversionProvider()
    {
        return [
            ['date', '01-01-1970'],
            ['date-time', '1970-01-01TH:i:s'], # Missing timezone
        ];
    }

    /**
     * @param array $schemaStubs
     * @param array $stubs
     *
     * @return Parameter
     */
    private function createParameter(array $schemaStubs = [], array $stubs = []): Parameter
    {
        $parameterMock = $this->getMockBuilder(Parameter::class)->disableOriginalConstructor()->getMock();

        foreach ($stubs as $methodName => $value) {
            $parameterMock->expects($this->any())->method($methodName)->willReturn($value);
        }

        switch ($schemaStubs['getType']) {
            case Schema::TYPE_ARRAY:
                $schemaMock = $this->getMockBuilder(ArraySchema::class)->disableOriginalConstructor()->getMock();
                break;
            case Schema::TYPE_OBJECT:
                $schemaMock = $this->getMockBuilder(ObjectSchema::class)->disableOriginalConstructor()->getMock();
                break;
            default:
                $schemaMock = $this->getMockBuilder(ScalarSchema::class)->disableOriginalConstructor()->getMock();
        }

        $parameterMock->expects($this->any())->method('getSchema')->willReturn($schemaMock);

        foreach ($schemaStubs as $methodName => $value) {
            $schemaMock->expects($this->any())->method($methodName)->willReturn($value);
        }

        return $parameterMock;
    }
}
