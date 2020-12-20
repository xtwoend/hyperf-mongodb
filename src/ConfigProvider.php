<?php


namespace Hyperf\Mongodb;

use Hyperf\Mongodb\MongoDbResolver;
use Hyperf\Mongodb\MongoDbResolverInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                MongoDbResolverInterface::class => MongoDbResolver::class
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of mongodb client.',
                    'source' => __DIR__ . '/../publish/mongodb.php',
                    'destination' => BASE_PATH . '/config/autoload/mongodb.php',
                ],
            ],
        ];
    }
}