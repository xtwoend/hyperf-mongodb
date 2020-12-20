<?php

namespace Hyperf\Mongodb;

use MongoDB\Client;
use Hyperf\Pool\Pool;
use Hyperf\Pool\Connection;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Mongodb\Exception\MongoDBException;
use Hyperf\Pool\Exception\ConnectionException;

class MongoDbConnection extends Connection implements ConnectionInterface
{
    /**
     * @var Manager
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = $config;
        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection()
    {
        // TODO: Implement getActiveConnection() method.
        if ($this->check()) {
            return $this;
        }
        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }
        return $this;
    }

    /**
     * Reconnect the connection.
     */
    public function reconnect(): bool
    {
        try {
            $username = $this->config['username'];
            $password = $this->config['password'];
            if (!empty($username) && !empty($password)) {
                $uri = sprintf(
                    'mongodb://%s:%s@%s:%d/',
                    $username,
                    $password,
                    $this->config['host'],
                    $this->config['port']
                );
            } else {
                $uri = sprintf(
                    'mongodb://%s:%d/',
                    $this->config['host'],
                    $this->config['port']
                );
            }
            
            $urlOptions = [];
            $driverOptions = [];
    
            $replica = isset($this->config['replica']) ? $this->config['replica'] : null;
            if ($replica) {
                $urlOptions['replicaSet'] = $replica;
            }
            $client = new Client($uri, $urlOptions, $driverOptions);
            $this->connection = $client->selectDatabase($this->config['db']);

        } catch (InvalidArgumentException $e) {
            throw new MongoDBException('mongodb Connection parameter error:' . $e->getMessage());
        } catch (RuntimeException $e) {
            throw new MongoDBException('mongodb uri format error:' . $e->getMessage());
        }
        $this->lastUseTime = microtime(true);
        return true;
    }

    /**
     * Close the connection.
     */
    public function close(): bool
    {
        // TODO: Implement close() method.
        return true;
    }
}