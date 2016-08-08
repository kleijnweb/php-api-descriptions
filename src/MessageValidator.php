<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\DefaultValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\ValidationResult;
use KleijnWeb\PhpApi\Descriptions\Request\RequestParameterAssembler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MessageValidator
{
    /**
     * @var Description
     */
    private $description;

    /**
     * @var RequestParameterAssembler
     */
    private $parameterAssembler;

    /**
     * @var SchemaValidator
     */
    private $validator;

    /**
     * MessageValidator constructor.
     *
     * @param Description               $description
     * @param RequestParameterAssembler $parameterAssembler
     * @param SchemaValidator           $validator
     */
    public function __construct(
        Description $description,
        RequestParameterAssembler $parameterAssembler = null,
        SchemaValidator $validator = null
    ) {
        $this->description        = $description;
        $this->parameterAssembler = $parameterAssembler ?: new RequestParameterAssembler();
        $this->validator          = $validator ?: new DefaultValidator();
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $path
     *
     * @return ValidationResult
     */
    public function validateRequest(ServerRequestInterface $request, string $path): ValidationResult
    {
        $operation         = $this->description->getPath($path)->getOperation($request->getMethod());
        $schema            = $operation->getRequestSchema();
        $requestParameters = $this->parameterAssembler->getRequestParameters($request, $operation);

        return $this->validator->validate($schema, $requestParameters);
    }

    /**
     * @param array|object           $body
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param string                 $path
     *
     * @return ValidationResult
     */
    public function validateResponse(
        $body,
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $path
    ): ValidationResult {
    


        $operation = $this->description->getPath($path)->getOperation($request->getMethod());
        $schema    = $operation->getResponse($response->getStatusCode())->getSchema();

        return $this->validator->validate($schema, $body);
    }
}
