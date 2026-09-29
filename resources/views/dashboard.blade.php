<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hosting Compatibility Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Hero header --}}
        <header class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-6 p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Hosting Compatibility</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Hosting Compatibility Dashboard</h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-400">
                            A live probe of what this hosting account actually supports, with evidence for every claim.
                        </p>
                    </div>

                    @php($overall = $report->overallStatus())
                    <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold {{ $overall->badgeClasses() }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $overall->dotClasses() }}" aria-hidden="true"></span>
                        <span>Overall: {{ $overall->label() }}</span>
                    </div>
                </div>

                {{-- Stat tiles --}}
                @php($counts = $report->counts())
                <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['key' => 'pass', 'label' => 'Pass', 'classes' => 'text-emerald-500 dark:text-emerald-400'],
                        ['key' => 'warn', 'label' => 'Warn', 'classes' => 'text-amber-500 dark:text-amber-400'],
                        ['key' => 'fail', 'label' => 'Fail', 'classes' => 'text-rose-500 dark:text-rose-400'],
                        ['key' => 'skipped', 'label' => 'Skipped', 'classes' => 'text-slate-500 dark:text-slate-400'],
                    ] as $tile)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/50">
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $tile['label'] }}</dt>
                            <dd class="mt-1 text-2xl font-bold {{ $tile['classes'] }}">{{ $counts[$tile['key']] }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Summary bar --}}
                <div class="grid grid-cols-1 gap-3 border-t border-slate-200 pt-5 text-sm sm:grid-cols-2 lg:grid-cols-5 dark:border-slate-800">
                    <div>
                        <span class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">PHP version</span>
                        <span class="font-mono">{{ PHP_VERSION }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Laravel version</span>
                        <span class="font-mono">{{ \Illuminate\Foundation\Application::VERSION }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Server software</span>
                        <span class="font-mono break-all">{{ $_SERVER['SERVER_SOFTWARE'] ?? PHP_SAPI }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Database driver</span>
                        <span class="font-mono">{{ config('database.default') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Document root</span>
                        <span class="font-mono break-all">{{ public_path() }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5 text-sm dark:border-slate-800">
                    <a href="{{ route('dashboard.report') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        View raw JSON report
                    </a>
                    <a href="{{ route('phpinfo') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        View phpinfo()
                    </a>
                </div>
            </div>
        </header>

        {{-- One card per group --}}
        <main class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @foreach ($report->groups() as $groupData)
                @php($group = $groupData['group'])
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" aria-labelledby="group-{{ \Illuminate\Support\Str::slug($group->value) }}">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 id="group-{{ \Illuminate\Support\Str::slug($group->value) }}" class="text-lg font-semibold">{!! $group->value !!}</h2>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $group->description() }}</p>
                    </div>

                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($groupData['results'] as $result)
                            <li class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="font-medium">{{ $result->label }}</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $result->status->badgeClasses() }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $result->status->dotClasses() }}" aria-hidden="true"></span>
                                        <span>{{ $result->status->label() }}</span>
                                    </span>
                                </div>
                                <p class="mt-1 break-all font-mono text-sm text-slate-700 dark:text-slate-300">{{ $result->value }}</p>
                                @if ($result->detail)
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $result->detail }}</p>
                                @endif

                                @if (! empty($result->children))
                                    <details class="mt-2 rounded-lg bg-slate-50 p-2 dark:bg-slate-950/50">
                                        <summary class="cursor-pointer select-none text-sm font-medium text-slate-600 dark:text-slate-400">
                                            {{ count($result->children) }} detail{{ count($result->children) === 1 ? '' : 's' }}
                                        </summary>
                                        <ul class="mt-2 space-y-1.5 pl-3">
                                            @foreach ($result->children as $child)
                                                <li class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                                    <span class="font-mono">{{ $child->label }}</span>
                                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $child->status->badgeClasses() }}">
                                                        <span class="h-1.5 w-1.5 rounded-full {{ $child->status->dotClasses() }}" aria-hidden="true"></span>
                                                        <span>{{ $child->value }}</span>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </main>

        {{-- Interactive probes --}}
        <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Interactive probes</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                These mutate state on demand instead of running automatically on page load.
            </p>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['id' => 'storage', 'url' => route('probe.storage'), 'label' => 'Storage write/read'],
                    ['id' => 'queue', 'url' => route('probe.queue'), 'label' => 'Queue dispatch'],
                    ['id' => 'mail', 'url' => route('probe.mail'), 'label' => 'Send test mail'],
                    ['id' => 'http', 'url' => route('probe.http'), 'label' => 'Outbound HTTP'],
                    ['id' => 'cache', 'url' => route('probe.cache'), 'label' => 'Cache round trip'],
                ] as $probe)
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-800" data-probe-card="{{ $probe['id'] }}">
                        <h3 class="font-medium">{{ $probe['label'] }}</h3>

                        @if ($probe['id'] === 'mail')
                            <label for="probe-mail-to" class="mt-2 block text-xs font-medium text-slate-500 dark:text-slate-400">
                                Recipient email
                            </label>
                            <input
                                type="email"
                                id="probe-mail-to"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950"
                                placeholder="you@example.com"
                                value="probe@example.com"
                            >
                        @endif

                        <button
                            type="button"
                            class="probe-trigger mt-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white"
                            data-probe-url="{{ $probe['url'] }}"
                            data-probe-id="{{ $probe['id'] }}"
                        >
                            Run probe
                        </button>

                        <div class="probe-result mt-3 hidden rounded-lg p-2 text-sm" data-probe-result="{{ $probe['id'] }}"></div>
                    </div>
                @endforeach

                {{-- Upload probe needs a real file input --}}
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-800" data-probe-card="upload">
                    <h3 class="font-medium">File upload</h3>
                    <label for="probe-upload-file" class="mt-2 block text-xs font-medium text-slate-500 dark:text-slate-400">
                        Choose a file
                    </label>
                    <input
                        type="file"
                        id="probe-upload-file"
                        class="mt-1 w-full text-sm"
                    >
                    <button
                        type="button"
                        id="probe-upload-trigger"
                        class="probe-trigger mt-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white"
                        data-probe-url="{{ route('probe.upload') }}"
                        data-probe-id="upload"
                    >
                        Run probe
                    </button>
                    <div class="probe-result mt-3 hidden rounded-lg p-2 text-sm" data-probe-result="upload"></div>
                </div>
            </div>
        </section>

        <footer class="mt-8 text-center text-xs text-slate-400 dark:text-slate-600">
            Read-only checks run on page load. Interactive probes only run when you click them.
        </footer>
    </div>
</body>
</html>
