<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Document\Reader;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface Reader
{
    /**
     * @param string $uri
     *
     * @return Response
     */
    public function read(string $uri): Response;
}
