<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Mongodb;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Mongodb\MongoDbConnection;
use Psr\Container\ContainerInterface;
use Hyperf\Mongodb\MongoDbResolverInterface;

/**
 * Mongodb Helper.
 **/

class Mongodb
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __call($name, $arguments)
    {
        if ($name === 'connection') {
            return $this->__connection(...$arguments);
        }
        return $this->__connection()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $db = ApplicationContext::getContainer()->get(Mongodb::class);
        if ($name === 'connection') {
            return $db->__connection(...$arguments);
        }
        return $db->__connection()->{$name}(...$arguments);
    }

    private function __connection($pool = 'default'): MongoDbConnection
    {
        $resolver = $this->container->get(MongoDbResolverInterface::class);
        return $resolver->connection($pool);
    }
}
