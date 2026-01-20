<?php

return [
    'theme' => 'default',

    /*
     * The HTML `<title>` for the generated documentation, and the name of the generated Postman collection.
     */
    'title' => 'Event Space Booking System API Documentation',

    /*
     * A short description of your API, shown in the docs' intro.
     */
    'description' => 'API for managing event space bookings, calendar events, and staff assignments.',

    /*
     * The base URL for your API.
     */
    'base_url' => env('APP_URL', 'http://localhost'),

    /*
     * The routes for which documentation should be generated.
     */
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/v1/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [],
            'apply' => [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ],
        ],
    ],

    /*
     * The type of documentation output to generate.
     */
    'type' => 'static',

    /*
     * Static output settings.
     */
    'static' => [
        'output_path' => 'public/docs',
    ],

    /*
     * Postman collection generation settings.
     */
    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],

    /*
     * OpenAPI spec generation settings.
     */
    'openapi' => [
        'enabled' => true,
        'overrides' => [],
    ],

    /*
     * API information and metadata.
     */
    'logo' => false,

    'intro_text' => <<<INTRO
Welcome to the Event Space Booking System API documentation.

This API allows you to:
- Browse and book event spaces
- Manage events and bookings
- View calendar data in FullCalendar-compatible format
- Access staff assignments

## Base URL

All API requests should be made to: `{(money_sign)base_url}/api/v1`

## Authentication

Most endpoints are public for reading. Creating bookings requires authentication using Laravel Sanctum.

To authenticate:
1. Login via `POST /api/v1/login` with email and password
2. Receive an API token in the response
3. Include the token in subsequent requests: `Authorization: Bearer {token}`

## Rate Limiting

- **Unauthenticated**: 60 requests per minute
- **Authenticated**: 120 requests per minute

## Response Format

All responses follow this structure:

```json
{
  "success": true,
  "message": "Success message",
  "data": {}
}
```

Error responses include an `errors` object with validation details.
INTRO,

    /*
     * Example requests for endpoints.
     */
    'examples' => [
        'faker_seed' => null,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    /*
     * The strategies Scribe will use to extract information about your routes.
     */
    'strategies' => [
        'metadata' => [
            \Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromDocBlocks::class,
            \Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromMetadataAttributes::class,
        ],
        'urlParameters' => [
            \Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromLaravelAPI::class,
            \Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromUrlParamAttribute::class,
            \Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromUrlParamTag::class,
        ],
        'queryParameters' => [
            \Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromFormRequest::class,
            \Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromInlineValidator::class,
            \Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromQueryParamAttribute::class,
            \Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromQueryParamTag::class,
        ],
        'headers' => [
            \Knuckles\Scribe\Extracting\Strategies\Headers\GetFromHeaderAttribute::class,
            \Knuckles\Scribe\Extracting\Strategies\Headers\GetFromHeaderTag::class,
        ],
        'bodyParameters' => [
            \Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromFormRequest::class,
            \Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromInlineValidator::class,
            \Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromBodyParamAttribute::class,
            \Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromBodyParamTag::class,
        ],
        'responses' => [
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseAttributes::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseTransformerTags::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseApiResourceTags::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseTag::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseFileTag::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\ResponseCalls::class,
        ],
        'responseFields' => [
            \Knuckles\Scribe\Extracting\Strategies\ResponseFields\GetFromResponseFieldAttribute::class,
            \Knuckles\Scribe\Extracting\Strategies\ResponseFields\GetFromResponseFieldTag::class,
        ],
    ],

    /*
     * Authentication settings.
     */
    'auth' => [
        'enabled' => true,
        'default' => false,
        'in' => 'bearer',
        'name' => 'Authorization',
        'use_value' => env('SCRIBE_AUTH_TOKEN'),
        'placeholder' => '{YOUR_AUTH_TOKEN}',
        'extra_info' => 'You can retrieve your token by making a POST request to `/api/v1/login` with your email and password.',
    ],

    /*
     * Text to place in the "Try It Out" section.
     */
    'try_it_out' => [
        'enabled' => true,
        'base_url' => env('SCRIBE_TRY_IT_OUT_URL'),
        'use_csrf' => false,
    ],

    /*
     * Generate a Postman collection.
     */
    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],

    /*
     * Generate an OpenAPI spec.
     */
    'openapi' => [
        'enabled' => true,
        'overrides' => [],
    ],

    /*
     * Custom logos, etc.
     */
    'logo' => false,
    'last_updated' => 'Last updated: {date}',

    /*
     * Automatically add markdown to example requests.
     */
    'examples' => [
        'faker_seed' => null,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    /*
     * How to order groups of endpoints.
     */
    'groups' => [
        'order' => [
            'Authentication',
            'Event Spaces',
            'Events & Bookings',
            'Calendar API',
            'Staff',
        ],
        'default' => 'Endpoints',
    ],
];
