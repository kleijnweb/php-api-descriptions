<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\DateTimeSerializer;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\DateTimeNotParsableException;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DateTimeSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function willSerializeDates()
    {
        $date       = '2016-01-01';
        $serializer = new DateTimeSerializer();
        $schema     = new ScalarSchema((object)['type' => Schema::TYPE_STRING, 'format' => Schema::FORMAT_DATE]);
        $actual     = $serializer->serialize(new \DateTime($date), $schema);
        $this->assertSame($date, $actual);
    }

    /**
     * @test
     */
    public function willDeserializeDatesToMidnight()
    {
        $serializer = new DateTimeSerializer();
        $schema     = new ScalarSchema((object)[
            'type'   => Schema::TYPE_STRING,
            'format' => Schema::FORMAT_DATE,
        ]);

        $actual                 = $serializer->deserialize('2016-01-01', $schema);
        $midnightFirstOfJanuary = new \DateTime('2016-01-01 00:00:00');
        $this->assertSame('000000000000', $midnightFirstOfJanuary->diff($actual)->format('%Y%M%D%H%I%S'));
    }

    /**
     * @test
     */
    public function willSerializeDateTime()
    {
        $dateTime   = '2016-01-01T23:59:59.000000+01:00';
        $serializer = new DateTimeSerializer();
        $schema     = new ScalarSchema((object)['type' => Schema::TYPE_STRING, 'format' => Schema::FORMAT_DATE_TIME]);

        $this->assertSame($dateTime, $serializer->serialize(new \DateTime($dateTime), $schema));
    }

    /**
     * @test
     */
    public function willDeserializeDateTime()
    {
        $dateTime   = '2016-01-01T23:59:59+01:00';
        $serializer = new DateTimeSerializer();
        $schema     = new ScalarSchema((object)[
            'type'   => Schema::TYPE_STRING,
            'format' => Schema::FORMAT_DATE_TIME,
        ]);

        $actual                 = $serializer->deserialize($dateTime, $schema);
        $midnightFirstOfJanuary = new \DateTime($dateTime);

        $this->assertSame('000000000000', $midnightFirstOfJanuary->diff($actual)->format('%Y%M%D%H%I%S'));
    }

    /**
     * @test
     */
    public function willSerializeValueUsingAnySchemaUsingDateTimeFormat()
    {
        $dateTime   = new \DateTime('midnight');
        $serializer = new DateTimeSerializer(\DateTime::RSS);
        $schema     = new AnySchema();
        $actual     = $serializer->serialize($dateTime, $schema);

        $this->assertEquals($dateTime->format(\DateTime::RSS), $actual);
    }

    /**
     * @test
     */
    public function willThrowExceptionWhenDateNotParsableAccordingToFormat()
    {
        $serializer = new DateTimeSerializer();
        $schema     = new ScalarSchema((object)['format' => Schema::FORMAT_DATE]);

        $this->expectException(DateTimeNotParsableException::class);

        $serializer->deserialize('2016-01-01T23:59:59+01:00', $schema);
    }

    /**
     * @test
     */
    public function willThrowExceptionWhenDateTimeNotParsableAccordingToFormats()
    {
        $serializer = new DateTimeSerializer(\DateTime::RSS);
        $schema     = new ScalarSchema((object)['format' => Schema::FORMAT_DATE]);

        $this->expectException(DateTimeNotParsableException::class);

        $serializer->deserialize('2016-01-01T23:59:59+01:00', $schema);
    }

    /**
     * @test
     */
    public function willDeserializeValueUsingScalarSchemaUsingCustomDateTimeFormat()
    {
        $preciseDateTimeFormat = 'm-d-Y\TH:i:s.uP';
        $preciseDateTime       = '01-01-2010T23:45:59.000002+01:00';

        $schemaDefinition         = new \stdClass();
        $schemaDefinition->format = Schema::FORMAT_DATE_TIME;

        $schema = new ScalarSchema($schemaDefinition);

        $serializer = new DateTimeSerializer($preciseDateTimeFormat);
        $actualDate = $serializer->deserialize($preciseDateTime, $schema);

        $this->assertEquals(\DateTime::createFromFormat($preciseDateTimeFormat, $preciseDateTime), $actualDate);
    }
}
