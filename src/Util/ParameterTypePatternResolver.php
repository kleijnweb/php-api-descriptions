<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Util;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;

class ParameterTypePatternResolver
{
    /**
     * @param Schema $schema
     * @return string
     */
    public function resolve(Schema $schema): string
    {
        $typePattern = '.*';

        switch ($type = $schema->getType()) {
            case Schema::TYPE_INT:
                $typePattern = '\d+';
                break;
            case Schema::TYPE_NUMBER:
                $typePattern = '\d+(\.\d+)?';
                break;
            case Schema::TYPE_NULL:
                $typePattern = 'null';
                break;
            case Schema::TYPE_STRING:
                /** @var $schema ScalarSchema $routeString */
                if ($pattern = $schema->getPattern()) {
                    $typePattern = $pattern;
                } elseif ($enum = $schema->getEnum()) {
                    $typePattern = '(' . implode('|', $enum) . ')';
                }
                break;
            default:
                return $typePattern;
        }

        return $typePattern;
    }
}
