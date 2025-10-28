<?php

declare(strict_types=1);

namespace Lmc\Api\Rest;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'lmc_api'      => $this->getApiConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'factories' => [
                OptionsMiddleware::class => OptionsMiddlewareFactory::class,
            ],
        ];
    }

    private function getApiConfig(): array
    {
        return [
            'rest' => [
                // @codingStandardsIgnoreStart
                // 'listener' => '', // resource listener class
                // 'route name' => [
                //     'collection_http_methods'    => [
                //         /* array of HTTP methods that are allowed on collections */
                //         'get'
                //     ],
                //     'collection_name'            => 'Name of property denoting collection in response',
                //     'collection_query_whitelist' => [
                //         /* array of query string parameters to whitelist and return
                //          * when generating links to the collection. E.g., "sort",
                //          * "filter", etc.
                //          */
                //     ],
                //     'controller_class'           => 'Name of Laminas\ApiTools\Rest\RestController derivative, if not using that class',
                //     'route_identifier_name'      => 'Name of parameter in route that acts as an entity identifier',
                //     'listener'                   => 'Name of service/class that acts as a listener on the composed Resource',
                //     'page_size'                  => 'Integer specifying the number of results to return per page, if collections are paginated',
                //     'page_size_param'            => 'Name of query string parameter that specifies the number of results to return per page',
                //     'entity_http_methods'      => [
                //         /* array of HTTP methods that are allowed on individual entities */
                //         'get', 'post', 'delete'
                //     ],
                // ],
                // repeat for each controller you want to define
                // @codingStandardsIgnoreEnd
            ],
        ];
    }
}
