<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Util;

use KleijnWeb\PhpApi\Descriptions\Description\Operation;

class OkStatusResolver
{
    /**
     * @param mixed     $operationResult
     * @param Operation $operation
     * @return int
     */
    public function resolve($operationResult, Operation $operation): int
    {
        $codes = $operation->getStatusCodes();

        if ($operationResult === null) {
            if (in_array(204, $codes)) {
                return 204;
            }
        }

        $statusCode = 200;
        foreach ($codes as $code) {
            if ('2' == substr((string)$code, 0, 1)) {
                $statusCode = $code;
                break;
            }
        }

        return $statusCode;
    }
}
