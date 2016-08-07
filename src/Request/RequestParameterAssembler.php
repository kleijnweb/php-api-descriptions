<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Request;

use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Parameter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestParameterAssembler
{
    /**
     * @var ParameterCoercer
     */
    private $parameterCoercer;

    /**
     * ApiRequest constructor.
     *
     * @param ParameterCoercer $parameterCoercer
     */
    public function __construct(ParameterCoercer $parameterCoercer = null)
    {
        $this->parameterCoercer = $parameterCoercer ?: new ParameterCoercer();
    }

    /**
     * @param ServerRequestInterface $httpRequest
     * @param Operation              $operation
     *
     * @return \stdClass
     */
    public function getRequestParameters(ServerRequestInterface $httpRequest, Operation $operation): \stdClass
    {
        $indexed    = array_combine(
            explode('/', trim($operation->getPath(), '/')),
            explode('/', trim($httpRequest->getUri()->getPath(), '/'))
        );
        $pathParams = (object)[];

        foreach ($indexed as $key => $value) {
            if (0 == strpos('{', $key)) {
                $pathParams->{trim($key, '{}')} = $value;
            }
        }

        $parameters     = (object)[];
        $queryParams    = (object)$httpRequest->getQueryParams();
        $headers        = $httpRequest->getHeaders();
        $headerParamMap = array_combine(array_map(function ($key) {
            return $this->getHeaderParameterName($key);
        }, array_keys($headers)), array_keys($headers));

        foreach ($operation->getParameters() as $parameter) {
            $paramName = $parameter->getName();

            switch ($parameter->getIn()) {
                case Parameter::IN_QUERY:
                    $parameters->$paramName = $this->parameterCoercer->coerce($parameter, $queryParams->$paramName);
                    break;
                case Parameter::IN_BODY:
                    $parameters->$paramName = $httpRequest->getParsedBody();
                    break;
                case Parameter::IN_PATH:
                    $parameters->$paramName = $this->parameterCoercer->coerce($parameter, $pathParams->$paramName);
                    break;
                case Parameter::IN_HEADER:
                    if (isset($headers[$paramName])) {
                        $value = $headers[$paramName];
                    } elseif (isset($headerParamMap[$paramName])) {
                        $value = $headers[$headerParamMap[$paramName]];
                    } else {
                        break;
                    }
                    $parameters->$paramName = $this->parameterCoercer->coerce($parameter, $value);
                    break;
            }
        }

        return $parameters;
    }

    private function getHeaderParameterName($headerName)
    {
        $replacements = [
            function ($matches) {
                return strtolower($matches[2]);
            },
            function ($matches) {
                return strtoupper($matches[2]);
            },
            function ($matches) {
                return strtoupper($matches[1]);
            },
        ];

        foreach (['/^(X-)?(.*)/i', '/(\-)([\S]{1})/', '/(^[\S]{1})/',] as $index => $pattern) {
            $headerName = preg_replace_callback($pattern, $replacements[$index], $headerName);
        }

        return lcfirst($headerName);
    }
}
