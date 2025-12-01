<?php

return [
    'client_id'     => env('QUICKBOOKS_CLIENT_ID'),
    'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
    'redirect_uri'  => env('QUICKBOOKS_REDIRECT_URI'),
    'environment'   => env('QUICKBOOKS_ENVIRONMENT', 'sandbox'),

    // default realm id (can also be stored per user in DB)
    'realm_id'      => env('QUICKBOOKS_REALM_ID'),
];
