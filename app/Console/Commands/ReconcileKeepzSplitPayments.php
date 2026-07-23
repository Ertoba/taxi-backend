<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Strategies\KeepzSplitStrategy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileKeepzSplitPayments extends Command
{
    protected $signature = 'payments:reconcile-keepz-split {--limit=200}';

    protected $description = 'Reconcile pending Keepz split ride payments with the Keepz status API';

    public function handle(): int
    {
        $limit = max(1, min((int) $this->option('limit'), 1000));
        $strategy = new KeepzSplitStrategy;
        $processed = 0;
        $completed = 0;

        Transaction::query()
            ->where('gateway_name', 'keepz')
            ->whereIn('payment_status', ['pending', 'initial', 'processing'])
            ->where('response_data', 'like', '%"keepz_split":true%')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (Transaction $transaction) use ($strategy, &$processed, &$completed): void {
                $processed++;

                try {
                    $status = $strategy->reconcilePendingTransaction($transaction);
                    if ($status === 'success') {
                        $completed++;
                    }
                } catch (\Throwable $exception) {
                    Log::error('Keepz split reconciliation failed for a transaction.', [
                        'transaction_id' => $transaction->id,
                        'booking_id' => $transaction->booking_id,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            });

        $this->info("Keepz split reconciliation processed {$processed} transaction(s); {$completed} completed.");

        return self::SUCCESS;
    }
}
