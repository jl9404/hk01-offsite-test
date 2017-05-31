<?php

use Illuminate\Contracts\Bus\Dispatcher;

// https://github.com/laravel/framework/commit/61f2e7b4106f8eb0b79603d9792426f7c6a6d273
if (! function_exists('dispatch_now')) {
    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * @param  mixed  $job
     * @param  mixed  $handler
     * @return mixed
     */
    function dispatch_now($job, $handler = null)
    {
        return app(Dispatcher::class)->dispatchNow($job, $handler);
    }
}