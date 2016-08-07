<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\OpenApi;

use KleijnWeb\ApiDescriptions\Description\Response;
use KleijnWeb\ApiDescriptions\Description\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiResponse extends Response
{
    /**
     * Response constructor.
     *
     * @param int       $code
     * @param \stdClass $definition
     */
    public function __construct(int $code, \stdClass $definition)
    {
        $this->code   = $code;
        $this->schema = Schema::get(isset($definition->schema) ? $definition->schema : null);
    }
}
