<?php

namespace App\Shared\Services;

/**
 * Thin wrapper around Laravel's Redis-backed atomic lock so every domain
 * guards its critical sections the same way, without talking to the
 * Cache facade directly.
 */
class DistributedLock
{
    /**
     * Acquire a lock, run the callback, always release. Throws
     * Illuminate\Contracts\Cache\LockTimeoutException if not acquired in time.
     */
    public function run(string $key, callable $callback, int $ttlSeconds = 10, int $waitSeconds = 5): mixed
    {
        return \Illuminate\Support\Facades\Cache::lock($key, $ttlSeconds)->block($waitSeconds, $callback);
    }

    /**
     * Best-effort lock: returns null instead of throwing if unavailable.
     * Useful for idempotent event listeners where "someone else is already
     * handling this" is a valid, non-exceptional outcome.
     */
    public function attempt(string $key, callable $callback, int $ttlSeconds = 10): mixed
    {
        $lock = \Illuminate\Support\Facades\Cache::lock($key, $ttlSeconds);

        if (! $lock->get()) {
            return null;
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
