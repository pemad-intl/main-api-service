<?php

namespace Pemad\MainApi\Console;

use Illuminate\Console\Command;
use Pemad\MainApi\MainApiService;

class TestMainApi extends Command
{
    protected $signature = 'mainapi:test {endpoint=/hrms/health}';
    protected $description = 'Test Main API connectivity and credentials';

    public function handle(MainApiService $api)
    {
        $endpoint = $this->argument('endpoint') ?? '/hrms/health';
        $this->info("Testing Main API: {$endpoint}");

        try {
            $response = $api->get($endpoint);

            $this->line("HTTP: " . $response->status());
            $this->line($response->body());

            if ($response->failed()) {
                return 1;
            }

            $this->info('Success');
            return 0;
        } catch (\Throwable $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
