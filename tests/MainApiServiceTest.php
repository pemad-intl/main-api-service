<?php

namespace Tests;

use Pemad\MainApi\MainApiService;
use Illuminate\Support\Facades\Http;

class MainApiServiceTest extends TestCase
{
    public function test_get_uses_signature_and_returns_response()
    {
        Http::fake([
            'https://example.test/*' => Http::response(['ok' => true], 200),
        ]);

        $api = $this->app->make(MainApiService::class);

        $res = $api->get('/test', ['a' => 1]);

        $this->assertTrue($res->successful());
        $this->assertEquals(200, $res->status());
    }
}
