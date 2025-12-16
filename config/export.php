<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Export Service Mapping
    |--------------------------------------------------------------------------
    |
    | This configuration maps export type keys to their corresponding service classes.
    | The controller will use this mapping to dynamically instantiate the appropriate
    | export service based on the type parameter from the request.
    |
    */

    'services' => [
        'diploma-verification' => \App\Services\DiplomaVerificationService::class,
        'bachelor-confirmation' => \App\Services\BachelorConfirmationService::class,
        'bachelor-info' => \App\Services\BachelorInfoService::class,
    ],
];
