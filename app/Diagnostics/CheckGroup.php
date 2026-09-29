<?php

namespace App\Diagnostics;

enum CheckGroup: string
{
    case Runtime = 'Runtime';
    case Extensions = 'Extensions';
    case Filesystem = 'Filesystem';
    case Database = 'Database';
    case CacheSession = 'Cache & Session';
    case Queue = 'Queue';
    case Network = 'Network';
    case Application = 'Application';
    case Assets = 'Assets';

    public function description(): string
    {
        return match ($this) {
            self::Runtime => 'PHP version, SAPI, ini limits and interpreter capabilities.',
            self::Extensions => 'Loaded PHP extensions required or recommended for Laravel.',
            self::Filesystem => 'Writable paths, symlink support and disk space.',
            self::Database => 'Database connectivity, DDL/DML and transaction behaviour.',
            self::CacheSession => 'Cache and session drivers actually usable on this host.',
            self::Queue => 'Queue connection, worker availability and dispatch round trip.',
            self::Network => 'Outbound HTTP, DNS, TLS and mail transport reachability.',
            self::Application => 'Application configuration, security posture and document root.',
            self::Assets => 'Vite build output and asset serving readiness.',
        };
    }

    /**
     * A short, emoji-free identifier for UI use (inline SVG path data).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Runtime => 'M13 10V3L4 14h7v7l9-11h-7z',
            self::Extensions => 'M11 4a2 2 0 114 0v1h2a2 2 0 012 2v2h-1a2 2 0 100 4h1v2a2 2 0 01-2 2h-2v-1a2 2 0 10-4 0v1H7a2 2 0 01-2-2v-2H4a2 2 0 110-4h1V7a2 2 0 012-2h2V4z',
            self::Filesystem => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
            self::Database => 'M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3-3.582 3-8 3-8-1.343-8-3zm0 0v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7M4 12c0 1.657 3.582 3 8 3s8-1.343 8-3',
            self::CacheSession => 'M5 13l4 4L19 7',
            self::Queue => 'M4 6h16M4 12h16M4 18h7',
            self::Network => 'M5 12a7 7 0 1114 0 7 7 0 01-14 0zM12 5v.01M12 19v-.01M5 12h.01M19 12h.01',
            self::Application => 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082',
            self::Assets => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        };
    }
}
