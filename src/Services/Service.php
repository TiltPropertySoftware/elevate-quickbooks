<?php

namespace ShowHouseSoftware\Quickbooks\Services;

use ShowHouseSoftware\Quickbooks\Builders\Builder;
use ShowHouseSoftware\Quickbooks\Client;
use ShowHouseSoftware\Quickbooks\Query;

class Service extends Client
{
    /**
     * Resource endpoint of this service.
     *
     * @var string
     */
    protected static $resource;

    /**
     * Entity name of this service. Must correspond to actual object type in QuickBooks.
     *
     * @var string
     */
    protected static $entity;

    /**
     * Some resource endpoint sends response without root. Set this to false when that happens.
     *
     * @var bool
     */
    protected static $responseHasRoot = true;

    /**
     * Load a single item.
     *
     * @param $id
     *
     * @return mixed
     */
    public function load($id)
    {
        return parent::get($this->getResourceName().'/'.$id)->{$this->getEntityName()};
    }

    /**
     * Create a single item.
     *
     * @param array $data Item information
     *
     * @return string
     */
    public function create($data)
    {
        $response = parent::post($this->getResourceName(), $data);

        // Response has no root, send it back immediately.
        if (!static::$responseHasRoot) {
            return $response;
        }

        return $response->{$this->getEntityName()};
    }

    /**
     * Update an entity.
     *
     * @param array $data Item information.
     *
     * @return string
     */
    public function update($data)
    {
        $response = parent::post($this->getResourceName().'?operation=update', $data);

        // Response has no root, send it back immediately.
        if (!static::$responseHasRoot) {
            return $response;
        }

        return $response->{$this->getEntityName()};
    }

    /**
     * Delete an entity.
     *
     * @param string $id        Item information.
     * @param int    $syncToken
     */
    public function delete($id, $syncToken = 0)
    {
        return parent::post($this->getResourceName().'?operation=delete', [
            'Id'        => $id,
            'SyncToken' => $syncToken,
        ])->{$this->getEntityName()};
    }

    /**
     * Query QuickBooks. Use Query to construct the query itself.
     *
     * @return object
     */
    public function query()
    {
        return (new Query($this))->entity($this->getEntityName());
    }

    /**
     * Get all items of this Entity.
     *
     * @return object
     */
    public function all()
    {
        return $this->query()->get();
    }

    /**
     * Get builder instance to construct entity data.
     *
     * @return Builder
     */
    public function getBuilder()
    {
        $class = '\ShowHouseSoftware\Quickbooks\Builders\\'.$this->getClassName();

        return new $class($this);
    }

    /**
     * Get Entity Name.
     *
     * @return string
     */
    public function getEntityName()
    {
        if (static::$entity) {
            return static::$entity;
        }

        return $this->getClassName();
    }

    /**
     * Get name of this class.
     *
     * @return string
     */
    public function getClassName()
    {
        $fullClass = get_called_class();
        $exploded = explode('\\', $fullClass);

        return end($exploded);
    }

    /**
     * Get the name of this service.
     *
     * @return string
     */
    public function getResourceName()
    {
        return static::$resource ?: strtolower($this->getEntityName());
    }
}
