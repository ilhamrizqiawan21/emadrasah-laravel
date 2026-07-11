<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

// Define rate limiters for the application
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by(
        $request->user()?->id ?: $request->ip()
    );
});

RateLimiter::for('auth', function (Request $request) {
    // Stricter limit for authentication attempts to prevent brute force
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('upload', function (Request $request) {
    // Limit file upload requests
    return Limit::perMinute(10)->by(
        $request->user()?->id ?: $request->ip()
    );
});

RateLimiter::for('search', function (Request $request) {
    // Limit search queries to prevent abuse
    return Limit::perMinute(30)->by(
        $request->user()?->id ?: $request->ip()
    );
});

RateLimiter::for('export', function (Request $request) {
    // Limit export operations (PDF, Excel)
    return Limit::perMinute(5)->by(
        $request->user()?->id ?: $request->ip()
    )->response(function ($request, array $headers) {
        return response('Too Many Export Requests. Please try again later.', 429, $headers);
    });
});
