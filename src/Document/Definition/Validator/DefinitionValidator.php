<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Document\Definition\Validator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface DefinitionValidator
{
    /**
     * @param \stdClass $definition
     */
    public function validate(\stdClass $definition);
}
