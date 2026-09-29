<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProbeTest extends TestCase
{
    private function assertProbeShape(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertOk()->assertJsonStructure(['label', 'status', 'value', 'detail']);
        $this->assertContains($response->json('status'), ['pass', 'warn', 'fail', 'skipped']);
    }

    public function test_storage_probe_writes_reads_and_cleans_up(): void
    {
        $response = $this->postJson('/probe/storage');

        $this->assertProbeShape($response);
        $response->assertJsonPath('status', 'pass');
    }

    public function test_queue_probe_dispatches_a_job(): void
    {
        Queue::fake();

        $response = $this->postJson('/probe/queue');

        $this->assertProbeShape($response);
        Queue::assertPushed(\App\Jobs\ProbeJob::class);
    }

    public function test_queue_probe_round_trips_on_the_sync_driver(): void
    {
        $response = $this->postJson('/probe/queue');

        $this->assertProbeShape($response);
        $response->assertJsonPath('status', 'pass');
    }

    public function test_upload_probe_accepts_a_file_and_reports_its_size(): void
    {
        $response = $this->post('/probe/upload', [
            'file' => UploadedFile::fake()->create('probe.txt', 12),
        ], ['Accept' => 'application/json']);

        $this->assertProbeShape($response);
        $response->assertJsonPath('status', 'pass');
    }

    public function test_upload_probe_rejects_a_request_without_a_file(): void
    {
        $this->post('/probe/upload', [], ['Accept' => 'application/json'])
            ->assertStatus(422);
    }

    public function test_mail_probe_sends_through_the_configured_mailer(): void
    {
        Mail::fake();

        $response = $this->postJson('/probe/mail', ['to' => 'probe@example.com']);

        $this->assertProbeShape($response);
    }

    public function test_mail_probe_validates_the_recipient(): void
    {
        $this->postJson('/probe/mail', ['to' => 'not-an-email'])->assertStatus(422);
    }

    public function test_outbound_http_probe_reports_a_result(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response('ok', 200),
        ]);

        $response = $this->postJson('/probe/http');

        $this->assertProbeShape($response);
        $response->assertJsonPath('status', 'pass');
    }

    public function test_cache_probe_round_trips_a_value(): void
    {
        $response = $this->postJson('/probe/cache');

        $this->assertProbeShape($response);
        $response->assertJsonPath('status', 'pass');
    }
}
