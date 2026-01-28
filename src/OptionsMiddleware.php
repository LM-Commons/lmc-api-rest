<?php

declare(strict_types=1);

namespace Lmc\Api\Rest;

use Mezzio\Router\RouteResult;
use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_key_exists;
use function array_walk;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function strtoupper;

readonly class OptionsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private array $config,
    ) {
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
    /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        if (null === $routeResult) {
            return $handler->handle($request);
        }
        $routeMatchName = $routeResult->getMatchedRouteName();

        if (! array_key_exists($routeMatchName, $this->config)) {
            // No matching route, do nothing
            return $handler->handle($request);
        }

        $config  = $this->getConfigForRoute($this->config[$routeMatchName], $request);
        $methods = $this->normalizeMethods($config);

        $method = $request->getMethod();
        if ($method === 'OPTIONS') {
            return $this->getOptionsResponse($request, $methods);
        }

        if (in_array($method, $methods, true)) {
            return $handler->handle($request);
        }

        return $this->get405Response();
    }

    private function getConfigForRoute(array $config, ServerRequestInterface $request): array
    {
        $collectionConfig = [];
        if (
            array_key_exists('collection_http_methods', $config)
            && is_array($config['collection_http_methods'])
        ) {
            $collectionConfig = $config['collection_http_methods'];
            // Ensure the HTTP method names are normalized
            array_walk($collectionConfig, function (string &$value) {
                $value = strtoupper($value);
            });
        }

        /** @var string|false $identifier */
        $identifier = false;
        if (array_key_exists('route_identifier_name', $config)) {
            $identifier = $config['route_identifier_name'];
        }
        if ($identifier === false || $request->getAttribute($identifier) === null) {
            return $collectionConfig;
        }

        if (
            array_key_exists('entity_http_methods', $config)
            && is_array($config['entity_http_methods'])
        ) {
            $entityConfig = $config['entity_http_methods'];
            // Ensure the HTTP method names are normalized
            /** @var string $value */
            array_walk($entityConfig, function (&$value) {
                $value = strtoupper($value);
            });
            return $entityConfig;
        }

        return [];
    }

    private function normalizeMethods(array|string $methods): array
    {
        if (is_string($methods)) {
            $methods = (array) $methods;
        }

        array_walk($methods, function (&$value) {
            return strtoupper($value);
        });
        return $methods;
    }

    private function getOptionsResponse(ServerRequestInterface $request, array $methods): ResponseInterface
    {
        return $this->responseFactory
        ->createResponse()
        ->withHeader('Allow', implode(', ', $methods));
    }

    private function get405Response(): ResponseInterface
    {
        return $this->responseFactory->createResponse(405, 'Method not allowed');
    }
}
