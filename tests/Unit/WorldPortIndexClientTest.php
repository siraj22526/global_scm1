<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Integrations\WorldPortIndexClient;
use Illuminate\Support\Facades\Http;

class WorldPortIndexClientTest extends TestCase
{
    public function test_get_ports_fetches_and_returns_ports_array()
    {
        Http::fake([
            'raw.githubusercontent.com/tayljordan/ports/*' => Http::response([
                'ports' => [
                    [
                        'wpi_port_id' => 12345,
                        'wpi_port_name' => 'Tanjung Priok',
                        'country' => 'Indonesia',
                        'latitude' => -6.1,
                        'longitude' => 106.9,
                        'port_size' => 'Large'
                    ]
                ]
            ], 200)
        ]);

        $client = new WorldPortIndexClient();
        $ports = $client->getPorts();

        $this->assertIsArray($ports);
        $this->assertCount(1, $ports);
        $this->assertEquals('Tanjung Priok', $ports[0]['wpi_port_name']);
        $this->assertEquals('Indonesia', $ports[0]['country']);
    }
}
