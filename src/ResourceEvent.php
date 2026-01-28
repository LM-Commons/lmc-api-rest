<?php

declare(strict_types=1);

namespace Lmc\Api\Rest;

use ArrayAccess;
use InvalidArgumentException;
use Laminas\EventManager\Event;
use Laminas\EventManager\Exception\InvalidArgumentException as EventManagerInvalidArgumentException;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\Stdlib\RequestInterface;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Psr\Http\Message\ServerRequestInterface;

use function gettype;
use function is_array;
use function is_object;
use function sprintf;

class ResourceEvent extends Event
{
    protected ?IdentityInterface $identity;

    protected ?InputFilterInterface $inputFilter;

    protected ?Parameters $queryParams;

    protected ?RequestInterface $request;

    protected ?RouteMatch $routeMatch;

    /**
     * Overload setParams to inject request object, if passed via params
     *
     * @param array|ArrayAccess|object $params
     */
    public function setParams($params): self
    {
        if (! is_array($params) && ! is_object($params)) {
            throw new EventManagerInvalidArgumentException(sprintf(
                'Event parameters must be an array or object; received "%s"',
                gettype($params)
            ));
        }

        if (is_array($params) || $params instanceof ArrayAccess) {
            if (isset($params['request'])) {
                $this->setRequest($params['request']);
                unset($params['request']);
            }
        }

        parent::setParams($params);
        return $this;
    }

    public function setIdentity(?IdentityInterface $identity = null): self
    {
        $this->identity = $identity;
        return $this;
    }

    public function getIdentity(): ?IdentityInterface
    {
        return $this->identity;
    }

    public function setInputFilter(?InputFilterInterface $inputFilter = null): self
    {
        $this->inputFilter = $inputFilter;
        return $this;
    }

    public function getInputFilter(): ?InputFilterInterface
    {
        return $this->inputFilter;
    }

    public function setQueryParams(?Parameters $params = null): self
    {
        $this->queryParams = $params;
        return $this;
    }

    public function getQueryParams(): ?Parameters
    {
        return $this->queryParams;
    }

    /**
     * Retrieve a single query parameter by name
     *
     * If not present, returns the $default value provided.
     */
    public function getQueryParam(string $name, mixed $default = null): mixed
    {
        $params = $this->getQueryParams();
        if (null === $params) {
            return $default;
        }

        return $params->get($name, $default);
    }

    public function setRequest(?RequestInterface $request = null): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return null|RequestInterface
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param RouteMatch|V2RouteMatch $matches
     */
    public function setRouteMatch($matches = null): self
    {
        if (null !== $matches && ! ($matches instanceof RouteMatch || $matches instanceof V2RouteMatch)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a null or %s or %s instances; received %s',
                __METHOD__,
                RouteMatch::class,
                V2RouteMatch::class,
                is_object($matches) ? $matches::class : gettype($matches)
            ));
        }
        $this->routeMatch = $matches;
        return $this;
    }

    /**
     * @return null|RouteMatch|V2RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * Retrieve a single route match parameter by name.
     *
     * If not present, returns the $default value provided.
     */
    public function getRouteParam(string $name, mixed $default = null): mixed
    {
        $matches = $this->getRouteMatch();
        if (null === $matches) {
            return $default;
        }

        return $matches->getParam($name, $default);
    }
}
