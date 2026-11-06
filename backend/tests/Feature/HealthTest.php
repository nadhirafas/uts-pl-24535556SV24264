<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_200_saat_database_dan_cache_sehat(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'healthy')
            ->assertJsonPath('checks.cache.status', 'healthy');
    }

    public function test_health_503_dan_menyebut_database_saat_database_mati(): void
    {
        DB::shouldReceive('connection')
            ->andThrow(new PDOException("SQLSTATE[HY000] [2002] Connection refused for user 'rahasia'@'host'"));

        $response = $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('checks.database.status', 'unhealthy')
            ->assertJsonPath('checks.cache.status', 'healthy')
            ->assertJsonPath('unhealthy_components', ['database']);

        $this->assertStringNotContainsString("'rahasia'", $response->getContent());
    }

    public function test_health_503_dan_menyebut_cache_saat_cache_rusak(): void
    {
        Cache::shouldReceive('put')->andThrow(new \RuntimeException('cache tidak bisa ditulis'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('checks.cache.status', 'unhealthy')
            ->assertJsonPath('checks.database.status', 'healthy')
            ->assertJsonPath('unhealthy_components', ['cache']);
    }
}
