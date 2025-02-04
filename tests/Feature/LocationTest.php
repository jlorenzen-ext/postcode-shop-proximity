<?php

namespace Tests\Feature;

use App\Models\Postcode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateNewLocationWithSuccess(): void
    {
        $payload = [
            'name' => 'Cluckin Chicken',
            'longitude' => -3.154815,
            'latitude' => 55.882411,
            'status' => 'open',
            'type' => 'restaurant',
            'delivery_distance' => 2000,
        ];

        $response = $this->postJson('/api/location', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('locations', $payload);
    }

    public function testCreateNewLocationWithFailure(): void
    {
        // Send payload with missing coordinate
        $payload = [
            'name' => 'Cluckin Chicken',
            'longitude' => -3.154815,
            'latitude' => null,
            'status' => 'open',
            'type' => 'restaurant',
            'delivery_distance' => 2000,
        ];

        $response = $this->postJson('/api/location', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['latitude']);
    }

    public function testSearchLocationWithinRadiusOfPostcode()
    {
        $payload = [
            'name' => 'Cluckin Chicken',
            'longitude' => -3.154815,
            'latitude' => 55.882411,
            'status' => 'open',
            'type' => 'restaurant',
            'delivery_distance' => 2000,
        ];

        $response = $this->postJson('/api/location', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('locations', $payload);

        $this->createPostcodeEntries();

        // Search for postcode/radius that should return a location
        $response = $this->getJson('/api/location/search/radius?radius=1000&postcode=EH209EG');

        // Assert good response and entry present we expect ("Cluckin Chicken")
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Cluckin Chicken']);

        // Search for postcode/radius that should NOT return a location
        $response = $this->getJson('/api/location/search/radius?radius=1000&postcode=G718PB');

        // Assert good response and no entry present
        $response->assertStatus(200)
            ->assertJsonStructure([]);
    }

    public function testSearchLocationWithinEligibleDeliveryDistanceOfPostcode()
    {
        $payload = [
            'name' => 'Cluckin Chicken',
            'longitude' => -3.154815,
            'latitude' => 55.882411,
            'status' => 'open',
            'type' => 'restaurant',
            'delivery_distance' => 2000,
        ];

        $response = $this->postJson('/api/location', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('locations', $payload);

        $this->createPostcodeEntries();

        // Search for postcode that should return a location
        $response = $this->getJson('/api/location/search/delivery-distance?postcode=EH209EG');

        // Assert good response and entry present we expect ("Cluckin Chicken")
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Cluckin Chicken']);

        // Search for postcode that should NOT return a location
        $response = $this->getJson('/api/location/search/delivery-distance?postcode=G718PB');

        // Assert good response and no entry present
        $response->assertStatus(200)
            ->assertJsonStructure([]);
    }

    private function createPostcodeEntries()
    {
        $testPostcodes = [
            ['postcode' => 'EH209EG', 'longitude' => -3.154815, 'latitude' => 55.882411],
            ['postcode' => 'G718PB', 'longitude' => -2.154815, 'latitude' => 56.882411],
        ];

        foreach ($testPostcodes as $postcode) {
            Postcode::create([
                'coordinates' => new Point($postcode['latitude'], $postcode['longitude'], 4326),
                'longitude' => $postcode['longitude'],
                'latitude' => $postcode['latitude'],
                'postcode' => $postcode['postcode']
            ]);
        }
    }
}
