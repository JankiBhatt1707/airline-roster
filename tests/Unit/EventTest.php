<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_roster_and_parse_events()
    {
        $file = UploadedFile::fake()->create('roster.txt');
        
        $response = $this->postJson('/api/upload-roster', [
            'file' => $file,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('events', ['type' => 'DO']);
        $this->assertDatabaseHas('events', ['type' => 'FLT', 'flight_number' => 'DX77']);
    }

    public function test_get_events_between_dates()
    {
        $startDate = now()->subDays(5)->toDateString();
        $endDate = now()->addDays(5)->toDateString();

        $response = $this->getJson("/api/events?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJsonCount(10); // Update this based on the number of events within the given date range
    }

}
?>