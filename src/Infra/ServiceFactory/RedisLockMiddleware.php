<?php


namespace Cafe\Infra\ServiceFactory;


use Cafe\Application\Write\LockedCommand;
use League\Tactician\Middleware;
use malkusch\lock\mutex\PHPRedisMutex;
use Redis;

class RedisLockMiddleware implements Middleware
{

    private Redis $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('redis');
    }

    public function execute($command, callable $next)
    {
        if ($command instanceof LockedCommand) {
            $mutex = new PHPRedisMutex([$this->redis], $command->lockName(), 120);

            return $mutex->synchronized(
                function () use ($next, $command) {
                    return $next($command);
                }
            );
        }

        return $next($command);
    }
}