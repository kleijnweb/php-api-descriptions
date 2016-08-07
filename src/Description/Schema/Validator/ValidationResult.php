<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\Schema\Validator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ValidationResult
{
    /**
     * @var bool
     */
    private $valid;

    /**
     * @var string[]
     */
    private $errorMessages;

    /**
     * ValidationResult constructor.
     *
     * @param bool      $valid
     * @param string[] $errorMessages
     */
    public function __construct(bool $valid, array $errorMessages)
    {
        $this->valid         = $valid;
        $this->errorMessages = $errorMessages;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }
}
