<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\DateTimeNotParsableException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DateTimeSerializer
{
    const FORMAT_RFC3339_USEC = 'Y-m-d\TH:i:s.uP';
    const FORMAT_RFC3339_MSEC = \DateTime::RFC3339_EXTENDED;
    const FORMAT_RFC3339      = \DateTime::RFC3339;
    const FORMAT_ISO8601      = \DateTime::ATOM;

    /**
     * @var string
     */
    protected $outputFormat;

    /**
     * @var string
     */
    protected $inputDateTimeFormats = [
        self::FORMAT_RFC3339_USEC,
        self::FORMAT_RFC3339_MSEC,
        self::FORMAT_RFC3339,
        self::FORMAT_ISO8601,
    ];

    /**
     * DateTimeSerializer constructor.
     *
     * @param string|string[] ...$formats
     */
    public function __construct(string ...$formats)
    {
        if (isset($formats[0])) {
            $this->outputFormat = $formats[0];
        }

        $this->inputDateTimeFormats = $formats + $this->inputDateTimeFormats;
    }

    /**
     * @param \DateTimeInterface $value
     * @param Schema             $schema
     *
     * @return string
     */
    public function serialize(\DateTimeInterface $value, Schema $schema): string
    {
        if ($schema instanceof ScalarSchema && $schema->hasFormat(Schema::FORMAT_DATE)) {
            return $value->format('Y-m-d');
        }

        return $value->format($this->outputFormat ?: self::FORMAT_RFC3339_USEC);
    }

    /**
     * @param mixed  $value
     * @param Schema $schema
     *
     * @return \DateTime
     *
     */
    public function deserialize($value, Schema $schema): \DateTime
    {
        if ($schema instanceof ScalarSchema && $schema->hasFormat(Schema::FORMAT_DATE)) {
            if (false === $result = \DateTime::createFromFormat('Y-m-d H:i:s', "$value 00:00:00")) {
                throw new DateTimeNotParsableException(
                    sprintf("'%s' not parsable in YYYY-MM-DD format", $value)
                );
            }

            return $result;
        }

        foreach ($this->inputDateTimeFormats as $format) {
            if (false !== $result = \DateTime::createFromFormat($format, $value)) {
                return $result;
            }
        }

        throw new DateTimeNotParsableException(
            sprintf("Datetime '%s' not parsable as one of '%s'", $value, implode(', ', $this->inputDateTimeFormats))
        );
    }
}
