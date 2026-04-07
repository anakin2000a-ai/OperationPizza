<?php

$devMode = (int) env('DEV_MODE', 0) === 1;

$authSubject = $devMode
    ? 'auth.testing.v1.>'
    : 'auth.v1.>';

$qaSubject = $devMode
    ? 'qa.testing.v1.>'
    : 'qa.v1.>';

return [
    'dev_mode' => $devMode,
    'host' => env('NATS_HOST', '127.0.0.1'),
    'port' => (int) env('NATS_PORT', 4222),

    'user' => env('NATS_USER'),
    'pass' => env('NATS_PASS'),
    'token' => env('NATS_TOKEN'),

    // ⚠️ Optional: you can remove this if OperationPizza does NOT publish events
    'publishers' => [
        [
            'name' => $devMode
                ? env('NATS_OPERATION_PIZZA_STREAM', 'OPERATION_PIZZA_TESTING_EVENTS')
                : env('NATS_OPERATION_PIZZA_STREAM', 'OPERATION_PIZZA_EVENTS'),
            'subjects' => [$qaSubject],
        ],
    ],

    /**
     * Streams configuration (Consumer side)
     */
    'streams' => [
        [
            // ✅ SAME stream as Pizza/Auth system
            'name' => $devMode
                ? env('NATS_AUTH_STREAM', 'AUTH_TESTING_EVENTS')
                : env('NATS_AUTH_STREAM', 'AUTH_EVENTS'),

            // 🔥 IMPORTANT: MUST be UNIQUE for OperationPizza
            'durable' => $devMode
                ? env('NATS_AUTH_DURABLE', 'OPERATION_PIZZA_AUTH_TESTING_CONSUMER')
                : env('NATS_AUTH_DURABLE', 'OPERATION_PIZZA_AUTH_CONSUMER'),

            // ✅ listen to auth events
            'filter_subject' => $authSubject,
        ],

        // You can add more streams later if needed
    ],

    'pull' => [
        'batch' => (int) env('NATS_PULL_BATCH', 25),
        'timeout_ms' => (int) env('NATS_PULL_TIMEOUT_MS', 2000),
        'sleep_ms' => (int) env('NATS_PULL_SLEEP_MS', 250),
    ],
];