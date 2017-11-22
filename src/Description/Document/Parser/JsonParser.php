<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Document\Parser;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JsonParser implements Parser
{
    /**
     * @param string $string
     *
     * @return mixed
     * @throws ParseException
     */
    public function parse(string $string)
    {
        $content = json_decode($string);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ParseException("Failed to parse as JSON: " . json_last_error_msg());
        }

        return $content;
    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    public function canParse(string $contentType): bool
    {
        return strpos($contentType, 'json') == strlen($contentType) - 4;
    }
}
