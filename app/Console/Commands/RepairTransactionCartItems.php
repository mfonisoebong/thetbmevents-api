<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairTransactionCartItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Notes:
     * - By default this runs as a dry-run. Pass --force to write changes.
     */
    protected $signature = 'app:repair-transaction-cart-items
        {--since=2025-12-24 14:51:09 : Only fix transactions created at/after this timestamp}
        {--chunk=500 : Number of rows to process per chunk}
        {--limit=0 : Max number of rows to scan (0 = no limit)}
        {--force : Actually persist changes (otherwise dry-run)}
        {--connection= : Optional DB connection name}';

    protected $description = 'Repairs corrupted transactions.cart_items that were stored as JSON strings (double-encoded), rewriting them as proper JSON arrays.';

    public function handle(): int
    {
        $sinceRaw = (string) $this->option('since');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $limit = max(0, (int) $this->option('limit'));
        $shouldWrite = (bool) $this->option('force');
        $connection = $this->option('connection');

        try {
            $since = Carbon::parse($sinceRaw);
        } catch (\Throwable $e) {
            $this->error("Invalid --since value: {$sinceRaw}");
            return self::FAILURE;
        }

        if (!$shouldWrite) {
            $this->warn('Dry-run mode (no changes will be persisted). Pass --force to write changes.');
        }

        $query = Transaction::query()
            ->when($connection, fn ($q) => $q->setConnection($connection))
            ->where('created_at', '>=', $since)
            ->select(['id', 'cart_items', 'created_at', 'updated_at']);

        if ($limit > 0) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();
        $this->info("Found {$total} transaction(s) created at/after {$since->toDateTimeString()} to scan.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $scanned = 0;
        $fixed = 0;
        $alreadyOk = 0;
        $skippedInvalid = 0;
        $failed = 0;

        // UUID primary keys: avoid chunkById(); use ordered chunking.
        $query
            ->orderBy('created_at')
            ->orderBy('id')
            ->chunk($chunkSize, function ($transactions) use (
                &$scanned,
                &$fixed,
                &$alreadyOk,
                &$skippedInvalid,
                &$failed,
                $shouldWrite,
                $bar
            ) {
                if ($shouldWrite) {
                    DB::beginTransaction();
                }

                try {
                    foreach ($transactions as $tx) {
                        $scanned++;

                        [$changed, $normalized] = $this->normalizeCartItems($tx->cart_items);

                        if (!$changed) {
                            $alreadyOk++;
                            $bar->advance();
                            continue;
                        }

                        if ($normalized === null) {
                            $skippedInvalid++;
                            $bar->advance();
                            continue;
                        }

                        if ($shouldWrite) {
                            // Preserve semantics: write as native PHP array so the JSON cast persists correctly.
                            // Avoid model events/side-effects.
                            $tx->cart_items = $normalized;
                            $tx->saveQuietly();
                        }

                        $fixed++;
                        $bar->advance();
                    }

                    if ($shouldWrite) {
                        DB::commit();
                    }
                } catch (\Throwable $e) {
                    if ($shouldWrite) {
                        DB::rollBack();
                    }

                    $failed += $transactions->count();
                    // Keep running; log a concise error.
                    $this->newLine();
                    $this->error('Chunk failed: ' . $e->getMessage());

                    // Advance bar for remaining in chunk to keep progress consistent.
                    $remaining = $transactions->count();
                    $bar->advance($remaining);
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info('Scan complete.');
        $this->line("Scanned: {$scanned}");
        $this->line("Fixed: {$fixed}" . ($shouldWrite ? '' : ' (dry-run)'));
        $this->line("Already OK: {$alreadyOk}");
        $this->line("Skipped (invalid/unrecognized): {$skippedInvalid}");
        $this->line("Failed (chunk errors): {$failed}");

        if (!$shouldWrite) {
            $this->warn('No changes were persisted. Re-run with --force to apply fixes.');
        }

        return self::SUCCESS;
    }

    /**
     * Normalize cart_items.
     *
     * @return array{0: bool, 1: array|null} [changed, normalizedArrayOrNull]
     */
    private function normalizeCartItems($value): array
    {
        // Healthy case: cast already returned an array.
        if (is_array($value)) {
            return [false, null];
        }

        if (!is_string($value)) {
            // Unexpected type (null/object/etc.) - don't touch.
            return [false, null];
        }

        $raw = trim($value);
        if ($raw === '') {
            return [false, null];
        }

        // 1st decode.
        $decoded1 = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [true, null];
        }

        // If it's already an array after decoding once, it's a JSON string stored in the JSON column.
        // We normalize it to an array.
        if (is_array($decoded1)) {
            return [true, $decoded1];
        }

        // If it's a string, it might be double-encoded JSON.
        if (is_string($decoded1)) {
            $decoded2 = json_decode($decoded1, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded2)) {
                return [true, null];
            }

            return [true, $decoded2];
        }

        // Anything else (number/bool/null/object) - skip.
        return [true, null];
    }
}
