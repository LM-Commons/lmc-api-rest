<?php

declare(strict_types=1);

namespace Lmc\Api\Rest;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use function array_key_exists;
use function is_array;

class OptionsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): OptionsMiddleware
    {
        return new OptionsMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $this->getConfig($container)
        );
    }

    private function getConfig(ContainerInterface $container): array
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['lmc_api'] ?? [];
        if (
            ! array_key_exists('rest', $config)
            || ! is_array($config['rest'])
        ) {
            return [];
        }

        return $config['rest'];
    }
}
