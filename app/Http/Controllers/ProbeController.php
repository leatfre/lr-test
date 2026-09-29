<?php

namespace App\Http\Controllers;

use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use App\Jobs\ProbeJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProbeController extends Controller
{
    public function storage(): JsonResponse
    {
        $path = 'hosting-check/probe-'.Str::random(8).'.txt';
        $expected = Str::random(16);

        try {
            $disk = Storage::disk('local');
            $disk->put($path, $expected);
            $actual = $disk->get($path);
            $disk->delete($path);

            if ($actual !== $expected) {
                return $this->respond(CheckResult::fail(
                    CheckGroup::Filesystem,
                    'Storage probe',
                    'mismatch',
                    'The file read back did not match what was written.',
                ));
            }

            return $this->respond(CheckResult::pass(
                CheckGroup::Filesystem,
                'Storage probe',
                'ok',
                'Wrote, read back and deleted a file on the local disk.',
            ));
        } catch (Throwable $e) {
            return $this->respond(CheckResult::fail(CheckGroup::Filesystem, 'Storage probe', 'failed', $e->getMessage()));
        }
    }

    public function queue(): JsonResponse
    {
        $marker = Str::random(12);
        $job = new ProbeJob($marker);

        try {
            ProbeJob::dispatch($marker);

            $found = Cache::get($job->markerKey()) !== null;

            if ($found) {
                Cache::forget($job->markerKey());

                return $this->respond(CheckResult::pass(
                    CheckGroup::Queue,
                    'Queue probe',
                    'pass',
                    'ProbeJob was dispatched and its marker was observed immediately (synchronous or in-process queue).',
                ));
            }

            return $this->respond(CheckResult::pass(
                CheckGroup::Queue,
                'Queue probe',
                'dispatched',
                'ProbeJob was dispatched. No marker was found yet; it will run once a worker processes the queue.',
            ));
        } catch (Throwable $e) {
            return $this->respond(CheckResult::fail(CheckGroup::Queue, 'Queue probe', 'failed', $e->getMessage()));
        }
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');

        return $this->respond(CheckResult::pass(
            CheckGroup::Filesystem,
            'Upload probe',
            (string) $file->getClientOriginalName(),
            number_format((float) $file->getSize()).' bytes received.',
        ));
    }

    public function mail(Request $request): JsonResponse
    {
        $request->validate([
            'to' => ['required', 'email'],
        ]);

        try {
            Mail::raw(
                'This is a test message sent by the Hosting Compatibility dashboard.',
                function ($message) use ($request) {
                    $message->to($request->string('to')->toString())->subject('Hosting compatibility probe');
                },
            );

            return $this->respond(CheckResult::pass(
                CheckGroup::Network,
                'Mail probe',
                'sent',
                'Dispatched through the configured mailer: '.config('mail.default'),
            ));
        } catch (Throwable $e) {
            return $this->respond(CheckResult::fail(CheckGroup::Network, 'Mail probe', 'failed', $e->getMessage()));
        }
    }

    public function http(): JsonResponse
    {
        try {
            $response = Http::timeout(5)->get('https://www.google.com/generate_204');

            if ($response->successful() || $response->status() === 204) {
                return $this->respond(CheckResult::pass(CheckGroup::Network, 'HTTP probe', 'HTTP '.$response->status()));
            }

            return $this->respond(CheckResult::warn(
                CheckGroup::Network,
                'HTTP probe',
                'HTTP '.$response->status(),
                'The request completed but returned an unexpected status.',
            ));
        } catch (Throwable $e) {
            return $this->respond(CheckResult::fail(CheckGroup::Network, 'HTTP probe', 'unreachable', $e->getMessage()));
        }
    }

    public function cache(): JsonResponse
    {
        $key = 'hosting-check-probe-'.Str::random(8);
        $expected = Str::random(12);

        try {
            Cache::put($key, $expected, 30);
            $actual = Cache::get($key);
            Cache::forget($key);

            if ($actual !== $expected) {
                return $this->respond(CheckResult::fail(CheckGroup::CacheSession, 'Cache probe', 'mismatch'));
            }

            return $this->respond(CheckResult::pass(
                CheckGroup::CacheSession,
                'Cache probe',
                'ok',
                'Configured store: '.config('cache.default'),
            ));
        } catch (Throwable $e) {
            return $this->respond(CheckResult::fail(CheckGroup::CacheSession, 'Cache probe', 'failed', $e->getMessage()));
        }
    }

    private function respond(CheckResult $result): JsonResponse
    {
        return response()->json($result->toArray());
    }
}
