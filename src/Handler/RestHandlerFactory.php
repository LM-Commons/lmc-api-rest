<?php

declare(strict_types=1);

namespace Lmc\Api\Rest\Handler;

use Lmc\Api\Rest\AbstractResourceHandler;
use Lmc\Api\Rest\AbstractResourceListener;
use Lmc\Api\Rest\Exception\ServiceNotCreatedException;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\ProblemDetails\ProblemDetailsResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use function gettype;
use function is_object;
use function sprintf;

class RestHandlerFactory
{
    public function __invoke(ContainerInterface $container, string $requestedName): RestHandler
    {
        /** @var array $config */
        $config = $container->get('config');
        $config = $config['lmc_api'];
        /** @var array $restConfig */
        $restConfig = $config['rest'][$requestedName];

        if ($container->has($restConfig['resource'])) {
            /** @var AbstractResourceListener $resourceHandler */
            $resourceHandler = $container->get($restConfig['resource']);
        } else {
            $resourceHandler = new $restConfig['resource']();
        }

        if (! $resourceHandler instanceof AbstractResourceHandler) {
            throw new ServiceNotCreatedException(
                sprintf(
                    '%s expects that the "resource" reference a service that extends '
                    . 'Lmc\Api\Rest\AbstractResourceHandler; received %s',
                    __METHOD__,
                    is_object($resourceHandler) ? $resourceHandler::class : gettype($resourceHandler)
                )
            );
        }

        // this may not be required
        /*
        $resourceIdentifiers = [$listener::class];
        if (isset($config['resource_identifiers'])) {
            if (! is_array($config['resource_identifiers'])) {
                $config['resource_identifiers'] = (array) $config['resource_identifiers'];
            }
            $resourceIdentifiers = array_merge($resourceIdentifiers, $config['resource_identifiers']);
        }
*/

        $identifier = $requestedName;
        if (isset($restConfig['identifier'])) {
            $identifier = $restConfig['identifier'];
        }
        $handlerClass = $restConfig['handler_class'] ?? RestHandler::class;

        $handler = new $handlerClass(
            $container->get(ResponseFactoryInterface::class),
            $resourceHandler,
            $restConfig,
            $container->get(ProblemDetailsResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(HalResponseFactory::class)
        );

        if (isset($restConfig['entity_class'])) {
            $resourceHandler->setEntityClass($restConfig['entity_class']);
        }
        if (isset($restConfig['collection_class'])) {
            $resourceHandler->setCollectionClass($restConfig['collection_class']);
        }
        return $handler;
    }
}
