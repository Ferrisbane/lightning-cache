<?php

namespace Ferrisbane\Cache;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Laravel5ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerAttempt();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Register attempt cache function
     *
     * @return void
     */
    protected function registerAttempt()
    {
        /**
         * Reattempt to cache data but fallback to the old cached data if it fails
         *
         * @param  string   $key
         * @param  \Closure $callback
         * @param  int      $minutes
         * @param  int      $retry
         * @return mixed
         */
        Cache::macro('attempt', function($key, \Closure $callback, $minutes = 60, $retry = 60)
        {
            $attemptKey = $key.'.attempt';

            // If the cache has the data stored AND the cache has already tried to attempt caching
            if (Cache::has($key) && Cache::has($attemptKey) && $attemptData = Cache::get($attemptKey)) {
                // If the last successful caching of data was less than the number of minutes to cache data
                if ($attemptData['lastSuccess']->diffInMinutes() < $minutes) {
                    // Return the cached data
                    return Cache::get($key);
                }

                // If the last attempted run was less then the retry minutes, return the cached data,
                // until we want to retry again
                if ($attemptData['lastAttempt']->diffInMinutes() < $retry) {
                    return Cache::get($key);
                }
            }

            $now = Carbon::now();
            // If there is no attempt data yet then set a default
            if (empty($attemptData)) {
                $attemptData = [
                    'lastAttempt' => $now,
                    'lastSuccess' => $now,
                ];
            }


            $attemptData['lastAttempt'] = $now;
            // Pull the value from the closure
            $value = $callback();

            // If the value is null then return the old cached data
            if (is_null($value)) {
                Cache::forever($attemptKey, $attemptData);
                return Cache::get($key);
            }

            // If we were successful then update the cache and return the value
            $attemptData['lastSuccess'] = $now;
            Cache::forever($attemptKey, $attemptData);

            Cache::forever($key, $value);
            return $value;
        });
    }
}
