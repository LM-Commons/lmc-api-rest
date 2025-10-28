<?php

declare(strict_types=1);

namespace Lmc\Api\Rest;

use ArrayObject;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\ResponseCollection;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Paginator\Paginator;
use Laminas\Stdlib\Parameters;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Psr\Http\Message\ResponseInterface;

use function array_merge;
use function is_array;
use function is_object;

class Resource implements ResourceInterface
{
    use EventManagerAwareTrait;

    protected ?IdentityInterface $identity = null;

    protected ?InputFilterInterface $inputFilter = null;

    protected array $params = [];

    protected ?Parameters $queryParams = null;

    public function __construct(
        protected AbstractResourceHandler $resourceHandler
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setEventParams(array $params): Resource
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEventParams(): array
    {
        return $this->params;
    }

    public function setEventParam(string $name, mixed $value): Resource
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function getEventParam(mixed $name, mixed $default = null): mixed
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return $default;
    }

    public function setIdentity(?IdentityInterface $identity): self
    {
        $this->identity = $identity;
        return $this;
    }

    public function setInputFilter(?InputFilterInterface $inputFilter): self
    {
        $this->inputFilter = $inputFilter;
        return $this;
    }

    public function create(object|array $data): object|array
    {
        if (is_array($data)) {
            $data = (object) $data;
        }

        $results = $this->triggerEvent(__FUNCTION__, [
            'data' => $data,
        ]);
        $last    = $results->last();
        if (! is_array($last) && ! is_object($last)) {
            return $data;
        }
        return $last;
    }

    /**
     * @inheritDoc
     */
    public function update(int|string $id, object|array $data): object|array
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function replaceList(array $data): object|array
    {
        // TODO: Implement replaceList() method.
    }

    /**
     * @inheritDoc
     */
    public function patch(int|string $id, object|array $data): object|array
    {
        // TODO: Implement patch() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(int|string $id): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteList(?array $data = null): bool
    {
        // TODO: Implement deleteList() method.
    }

    /**
     * @inheritDoc
     */
    public function fetch(int|string $id): object|false|array
    {
        // TODO: Implement fetch() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(array $params = []): Paginator
    {
//        $params = func_get_args();
        return $this->resourceHandler->fetchAll($params);
        /*
        $results = $this->triggerEvent(__FUNCTION__, $params);
        $last    = $results->last();
        if (! is_array($last) && ! $last instanceof ResponseInterface) {
            return [];
        }
        return $last;
        */
    }

    private function triggerEvent(string $name, array $args): ResponseCollection
    {
        return $this->getEventManager()->triggerEventUntil(function ($result) {
            return $result instanceof ResponseInterface;
        }, $this->prepareEvent($name, $args));
    }

    private function prepareEvent(string $name, array $args): ResourceEvent
    {
        $event = new ResourceEvent($name, $this, $this->prepareEventParams($args));
        $event->setInputFilter($this->getInputFilter());
        $event->setIdentity($this->getIdentity());
        $event->setQueryParams($this->getQueryParams());
        //$event->setRouteMatch()
        return $event;
    }

    private function prepareEventParams(array $args): ArrayObject|array
    {
        $defaultParams = $this->getEventParams();
        $params        = array_merge($defaultParams, $args);
        if (empty($params)) {
            return $params;
        }

        return $this->getEventManager()->prepareArgs($params);
    }

    private function getInputFilter(): InputFilterInterface
    {
        return $this->inputFilter ?? $this->inputFilter = new InputFilter();
    }

    private function getIdentity(): IdentityInterface
    {
        return $this->identity;
    }

    private function getQueryParams(): ?Parameters
    {
        return $this->queryParams;
    }

    public function setResourceHandler(AbstractResourceHandler $resourceHandler): ResourceInterface
    {
        $this->resourceHandler = $resourceHandler;
        return $this;
    }
}
