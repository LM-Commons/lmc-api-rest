<?php

declare(strict_types=1);

namespace Lmc\Api\Rest;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\Paginator\Paginator;

/**
 * Interface describing operations for a given resource.
 */
interface ResourceInterface extends EventManagerAwareInterface
{
    /**
     * Set the event parameters
     */
    public function setEventParams(array $params): self;

    /**
     * Get the event parameters
     */
    public function getEventParams(): array;

    public function setEventParam(string $name, mixed $value): mixed;

    public function getEventParam(mixed $name, mixed $default = null): mixed;

    public function create(object|array $data): object|array;

    public function setResourceHandler(AbstractResourceHandler $resourceHandler): self;

    /**
     * Update (replace) an existing record
     */
    public function update(int|string $id, object|array $data): object|array;

    /**
     * Update (replace) an existing collection of records
     */
    public function replaceList(array $data): object|array;

    /**
     * Partial update of an existing record
     */
    public function patch(int|string $id, object|array $data): object|array;

    /**
     * Delete an existing record
     */
    public function delete(int|string $id): bool;

    /**
     * Delete an existing collection of records
     */
    public function deleteList(?array $data = null): bool;

    /**
     * Fetch an existing record
     */
    public function fetch(int|string $id): object|false|array;

    /**
     * Fetch a collection of records
     */
    public function fetchAll(array $params = []): Paginator;
}
