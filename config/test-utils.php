<?php

return [
    'entities_namespace' => 'App\\', // Namespace of models
    'user' => App\User::class, // The User full qualified class
    'transactions' => true, // Use database transactions in api tests
];