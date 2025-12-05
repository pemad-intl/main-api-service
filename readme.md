# pemad/main-api-service

## Install
composer require pemad-intl/main-api-service

## Publish config
php artisan vendor:publish --tag=mainapi-config

## .env
MAIN_API_URL=https://example.test
MAIN_API_CODE=appcode
MAIN_API_SECRET=xxx
MAIN_API_KEY=vAWG...

## Usage
Resolve via container:
$api = app(\Pemad\MainApi\MainApiService::class);

$response = $api->get('/api/user', ['limit' => 100]);

$response = $api->post('/api/sync', ['empl' => 69]);

## Artisan test
php artisan mainapi:test /api/health
