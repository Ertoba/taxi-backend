<?php

namespace App\Http\Controllers\Traits;

use App\Models\AppUser;
use App\Models\Booking;
use App\Models\GeneralSetting;
use App\Models\Payout;
use App\Models\VendorWallet;
use Carbon\Carbon;
use DB;

trait VendorWalletTrait
{
    use NotificationTrait;

    public function addToVendorWallet($vendorId, $amount, $bookingId = null, $payoutId = null, $description = null)
    {
        VendorWallet::create([
            'vendor_id' => $vendorId,
            'amount' => $amount,
            'booking_id' => $bookingId,
            'payout_id' => $payoutId,
            'type' => 'credit',
            'description' => $description,
        ]);
        $transactionType = 'credit';
        // this method is in this class
        $this->sendNotificationOnWalletTransaction($vendorId, $amount, $transactionType);
    }

    public function deductFromVendorWallet($vendorId, $amount, $bookingId = null, $payoutId = null, $description = null)
    {
        VendorWallet::create([
            'vendor_id' => $vendorId,
            'amount' => $amount,
            'booking_id' => $bookingId,
            'payout_id' => $payoutId,
            'type' => 'debit',
            'description' => $description,
        ]);
        $transactionType = 'debit';
        $this->sendNotificationOnWalletTransaction($vendorId, $amount, $transactionType);
    }

    public function getVendorWalletBalance($vendorId)
    {
        $walletSums = $this->accountingWalletQuery($vendorId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as total_credit,
                COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as total_debit,
                COALESCE(SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END), 0) as total_refund
            ")
            ->first();

        return (float) $walletSums->total_credit
            - (float) $walletSums->total_debit
            - (float) $walletSums->total_refund;
    }

    public function addVendorWalletTransaction($vendorId, $amount, $type, $bookingId = null, $payoutId = null, $description = null)
    {
        DB::beginTransaction();

        try {
            if ($type === 'credit') {
                $this->addToVendorWallet($vendorId, $amount, $bookingId, $payoutId, $description);
            } elseif ($type === 'debit') {
                $this->deductFromVendorWallet($vendorId, $amount, $bookingId, $payoutId, $description);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getVendorWalletTransactionsDetails($user_id, $offset = 0, $limit = 0)
    {

        $transactions = VendorWallet::where('vendor_id', $user_id)
            ->orderBy('booking_id', 'desc')
            ->orderByRaw("FIELD(type, 'debit', 'credit')")

            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
        foreach ($transactions as &$transaction1) {
            $transaction1['created_at'] = Carbon::parse($transaction1['created_at'])->format('j M Y');
            $transaction1['updated_at'] = Carbon::parse($transaction1['updated_at'])->format('j M Y');
        }

        $transactions = collect($transactions);

        $nextOffset = $offset + count($transactions);
        if ($transactions->isEmpty()) {
            $nextOffset = -1;
        }

        return [
            'transactions' => $transactions,
            'offset' => $nextOffset,
        ];
    }

    public function getTotalEarningsForVendor($vendorId)
    {
        $totalEarnings = VendorWallet::where('vendor_id', $vendorId)
            ->where('type', 'credit')
            ->sum('amount');

        return $totalEarnings;
    }

    public function getTotalRefundForVendor($vendorId)
    {
        $totalEarnings = VendorWallet::where('vendor_id', $vendorId)
            ->where('type', 'refund')
            ->sum('amount');

        return $totalEarnings;
    }

    public function getTotalIncomeForVendor($vendorId)
    {

        $totalEarnings = Booking::where('host_id', $vendorId)
            ->where('status', 'Completed')
            ->where('vendor_commission_given', '=', '0')
            ->sum('vendor_commission');

        return $totalEarnings;
    }

    public function getTotalWithdrawlForVendor($vendorId, $payout_status)
    {
        $totalEarnings = Payout::where('vendorid', $vendorId)
            ->where('payout_status', $payout_status)
            ->sum('amount');

        return $totalEarnings;
    }

    public function sendNotificationOnWalletTransaction($userId, $amount, $transactionType)
    {
        $user = AppUser::where('id', $userId)->first();
        if ($user) {
            $settings = GeneralSetting::whereIn('meta_key', ['general_email', 'general_default_currency'])
                ->get()
                ->keyBy('meta_key');

            $general_email = $settings['general_email'] ?? null;
            $general_default_currency = $settings['general_default_currency'] ?? null;

            $template_id = 7;
            $valuesArray = $user->toArray();
            $valuesArray = $user->only(['first_name', 'last_name', 'email', 'phone_country', 'phone']);
            $valuesArray['phone'] = $valuesArray['phone_country'].$valuesArray['phone'];
            $valuesArray['payout_amount'] = $amount;
            $valuesArray['payout_bank'] = '';
            $valuesArray['support_email'] = $general_email->meta_value;
            $valuesArray['currency_code'] = $general_default_currency->meta_value;
            $valuesArray['payout_date'] = now()->format('Y-m-d');
            $valuesArray['transaction_type'] = $transactionType;
            $this->sendAllNotifications($valuesArray, $user->id, $template_id);
        }
    }

    public function sendNotificationOnTicketReply($threadId, $userId, $title, $template_id)
    {

        $user = AppUser::find($userId);

        $settings = GeneralSetting::whereIn('meta_key', ['general_email', 'general_name'])
            ->get()
            ->keyBy('meta_key');

        $general_email = $settings['general_email'] ?? null;
        $website_name = $settings['general_email'] ?? null;

        $valuesArray = $user->toArray();
        $valuesArray = $user->only(['first_name', 'last_name', 'email']);
        $valuesArray['support_email'] = $general_email->meta_value;
        $valuesArray['update_date'] = now()->format('Y-m-d');
        $valuesArray['ticket_id'] = $threadId;
        $valuesArray['subject'] = $title;
        $valuesArray['website_name'] = $website_name;

        $this->sendAllNotifications($valuesArray, $userId, $template_id);
    }

    public function getVendorWalletSummary($vendorId)
    {
        // Get credit, debit, refund totals in one query
        $walletSums = $this->accountingWalletQuery($vendorId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as total_credit,
                COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as total_debit,
                COALESCE(SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END), 0) as total_refund
            ")
            ->first();

        // Get withdrawal totals by status in one query
        $payoutSums = Payout::selectRaw("
            COALESCE(SUM(CASE WHEN payout_status = 'Pending' THEN amount ELSE 0 END), 0) as total_pending,
            COALESCE(SUM(CASE WHEN payout_status = 'Success' THEN amount ELSE 0 END), 0) as total_success
        ")
            ->where('vendorid', $vendorId)
            ->first();

        // Total income from Booking
        $totalIncome = Booking::where('host_id', $vendorId)
            ->where('status', 'Completed')
            ->where('vendor_commission_given', '=', '0')
            ->sum('vendor_commission');
        $ledgerBalance = (float) $walletSums->total_credit
            - (float) $walletSums->total_debit
            - (float) $walletSums->total_refund;
        $pendingPayout = max(0, $ledgerBalance - (float) $payoutSums->total_pending);
        $cashCommissionDue = max(0, -$ledgerBalance);
        $settlementThreshold = max(
            25,
            (float) (GeneralSetting::getMetaValue('minimum_negative_balance') ?: 25)
        );
        $commissionSettlementAmount = floor($cashCommissionDue / $settlementThreshold)
            * $settlementThreshold;

        return [
            'walletBalance' => number_format($ledgerBalance, 2),
            'pendingToWithdrawl' => number_format($payoutSums->total_pending, 2),
            'totalWithdrawled' => number_format($payoutSums->total_success, 2),
            'totalEarning' => number_format($walletSums->total_credit, 2),
            'refunded' => number_format($walletSums->total_refund, 2),
            'incoming_amount' => number_format($totalIncome, 2),
            'pendingPayout' => number_format($pendingPayout, 2),
            'cashCommissionDue' => number_format($cashCommissionDue, 2),
            'commissionSettlementThreshold' => number_format($settlementThreshold, 2),
            'commissionSettlementAmount' => number_format($commissionSettlementAmount, 2),
            'canSettleCommission' => $commissionSettlementAmount > 0,
        ];
    }

    /**
     * Keepz Split settles the driver's share directly and therefore its
     * booking-linked wallet rows are audit-only. The accounting ledger keeps
     * only non-booking adjustments and the canonical cash-commission debit.
     */
    private function accountingWalletQuery($vendorId)
    {
        return VendorWallet::query()
            ->where('vendor_id', $vendorId)
            ->where(function ($query): void {
                $query->whereNull('booking_id')
                    ->orWhere('booking_id', 0)
                    ->orWhere(
                        'description',
                        'like',
                        'Cash ride platform commission for booking #%'
                    );
            });
    }
}
