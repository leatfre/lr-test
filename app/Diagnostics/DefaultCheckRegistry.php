<?php

namespace App\Diagnostics;

use App\Diagnostics\Checks\Application\AppDebugCheck;
use App\Diagnostics\Checks\Application\AppEnvCheck;
use App\Diagnostics\Checks\Application\AppKeyCheck;
use App\Diagnostics\Checks\Application\ConfigCacheCheck;
use App\Diagnostics\Checks\Application\DocumentRootCheck;
use App\Diagnostics\Checks\Application\EnvExposureCheck;
use App\Diagnostics\Checks\Application\HttpsSchemeCheck;
use App\Diagnostics\Checks\Application\LaravelVersionCheck;
use App\Diagnostics\Checks\Application\LogsWritableCheck;
use App\Diagnostics\Checks\Assets\BuildEntryPointsCheck;
use App\Diagnostics\Checks\Assets\HotFileCheck;
use App\Diagnostics\Checks\Assets\NodeAvailabilityCheck;
use App\Diagnostics\Checks\Assets\ViteManifestCheck;
use App\Diagnostics\Checks\CacheSession\CacheDriverCheck;
use App\Diagnostics\Checks\CacheSession\CacheStoresProbeCheck;
use App\Diagnostics\Checks\CacheSession\SessionDriverCheck;
use App\Diagnostics\Checks\Database\ConnectionCheck;
use App\Diagnostics\Checks\Database\DdlDmlRoundTripCheck;
use App\Diagnostics\Checks\Database\MigrationTableCheck;
use App\Diagnostics\Checks\Database\ServerVersionCheck;
use App\Diagnostics\Checks\Database\TransactionRollbackCheck;
use App\Diagnostics\Checks\Extensions\OptionalExtensionsCheck;
use App\Diagnostics\Checks\Extensions\PdoDriversCheck;
use App\Diagnostics\Checks\Extensions\RequiredExtensionsCheck;
use App\Diagnostics\Checks\Filesystem\FreeDiskSpaceCheck;
use App\Diagnostics\Checks\Filesystem\OpenBasedirCheck;
use App\Diagnostics\Checks\Filesystem\PublicStorageLinkCheck;
use App\Diagnostics\Checks\Filesystem\RealpathCacheCheck;
use App\Diagnostics\Checks\Filesystem\SymlinkSupportCheck;
use App\Diagnostics\Checks\Filesystem\TempDirectoryCheck;
use App\Diagnostics\Checks\Filesystem\WritablePathsCheck;
use App\Diagnostics\Checks\Network\DnsResolutionCheck;
use App\Diagnostics\Checks\Network\MailTransportCheck;
use App\Diagnostics\Checks\Network\OutboundHttpCheck;
use App\Diagnostics\Checks\Network\TlsCaBundleCheck;
use App\Diagnostics\Checks\Queue\JobsTableCheck;
use App\Diagnostics\Checks\Queue\QueueConnectionCheck;
use App\Diagnostics\Checks\Queue\SyncDispatchCheck;
use App\Diagnostics\Checks\Queue\WorkerRunningCheck;
use App\Diagnostics\Checks\Runtime\DisabledFunctionsCheck;
use App\Diagnostics\Checks\Runtime\GarbageCollectionCheck;
use App\Diagnostics\Checks\Runtime\Integer64BitCheck;
use App\Diagnostics\Checks\Runtime\MaxExecutionTimeCheck;
use App\Diagnostics\Checks\Runtime\MaxInputVarsCheck;
use App\Diagnostics\Checks\Runtime\MemoryLimitCheck;
use App\Diagnostics\Checks\Runtime\OpcacheCheck;
use App\Diagnostics\Checks\Runtime\PhpVersionCheck;
use App\Diagnostics\Checks\Runtime\ProcessExecutionCheck;
use App\Diagnostics\Checks\Runtime\ServerApiCheck;
use App\Diagnostics\Checks\Runtime\TimezoneIcuCheck;
use App\Diagnostics\Checks\Runtime\UploadLimitsCheck;
use Illuminate\Contracts\Container\Container;

final class DefaultCheckRegistry implements CheckRegistry
{
    /**
     * The full set of checks, in the order they should be reported.
     *
     * @var array<int, class-string<Check>>
     */
    private const CHECKS = [
        // Runtime
        PhpVersionCheck::class,
        ServerApiCheck::class,
        MemoryLimitCheck::class,
        MaxExecutionTimeCheck::class,
        UploadLimitsCheck::class,
        MaxInputVarsCheck::class,
        OpcacheCheck::class,
        DisabledFunctionsCheck::class,
        ProcessExecutionCheck::class,
        TimezoneIcuCheck::class,
        Integer64BitCheck::class,
        GarbageCollectionCheck::class,

        // Extensions
        RequiredExtensionsCheck::class,
        OptionalExtensionsCheck::class,
        PdoDriversCheck::class,

        // Filesystem
        WritablePathsCheck::class,
        SymlinkSupportCheck::class,
        PublicStorageLinkCheck::class,
        OpenBasedirCheck::class,
        FreeDiskSpaceCheck::class,
        RealpathCacheCheck::class,
        TempDirectoryCheck::class,

        // Database
        ConnectionCheck::class,
        ServerVersionCheck::class,
        DdlDmlRoundTripCheck::class,
        TransactionRollbackCheck::class,
        MigrationTableCheck::class,

        // Cache & Session
        CacheDriverCheck::class,
        SessionDriverCheck::class,
        CacheStoresProbeCheck::class,

        // Queue
        QueueConnectionCheck::class,
        JobsTableCheck::class,
        WorkerRunningCheck::class,
        SyncDispatchCheck::class,

        // Network
        OutboundHttpCheck::class,
        DnsResolutionCheck::class,
        TlsCaBundleCheck::class,
        MailTransportCheck::class,

        // Application
        AppKeyCheck::class,
        AppEnvCheck::class,
        AppDebugCheck::class,
        HttpsSchemeCheck::class,
        EnvExposureCheck::class,
        LogsWritableCheck::class,
        ConfigCacheCheck::class,
        LaravelVersionCheck::class,
        DocumentRootCheck::class,

        // Assets
        ViteManifestCheck::class,
        BuildEntryPointsCheck::class,
        HotFileCheck::class,
        NodeAvailabilityCheck::class,
    ];

    public function __construct(private readonly Container $container) {}

    public function checks(): array
    {
        return array_map(
            fn (string $class) => $this->container->make($class),
            self::CHECKS,
        );
    }
}
