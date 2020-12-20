<?php
/**
 * Created by PhpStorm.
 * User: adamchen1208
 * Date: 2020/7/24
 * Time: 15:30
 */

namespace Hyperf\Mongodb;

use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Hyperf\Mongodb\Pool\PoolFactory;
use Hyperf\Mongodb\Exception\MongoDBException;

/**
 * Class Mongodb
 * @package Hyperf\Mongodb
 */
class MongoDbResolver implements MongoDbResolverInterface
{
    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default = 'default';


    public function __construct(PoolFactory $factory)
    {
        $this->factory = $factory;
    }
    
    /**
     * The key to identify the connection object in coroutine context.
     */
    private function getContextKey(): string
    {
        return sprintf('mongodb.connection.%s', $this->default);
    }

    /**
     * Get a database connection instance.
     *
     * @param string $name
     * @return MongodbConnection
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $connection = null;
        $id = $this->getContextKey($name);
        if (Context::has($id)) {
            $connection = Context::get($id);
        }

        if (! $connection instanceof MongodbConnection) {
            $pool = $this->factory->getPool($name);
            $connection = $pool->get();
            try {
                $connection = $connection->getConnection();
                Context::set($id, $connection);
            } finally {
                if (Coroutine::inCoroutine()) {
                    defer(function () use ($connection, $id) {
                        Context::set($id, null);
                        $connection->release();
                    });
                }
            }
        }

        return $connection;
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }
}