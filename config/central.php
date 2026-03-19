<?php

return [
    /*
     * Central (landlord) database connection.
     * Use CENTRAL_DB_* to override without affecting tenant connections.
     */
    'connection' => env('CENTRAL_DB_CONNECTION', 'central'),
    'database' => env('CENTRAL_DB_DATABASE', env('DB_DATABASE', 'postgres')),
];
