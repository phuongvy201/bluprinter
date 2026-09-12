<?php

namespace App\Console\Commands;

use App\Mail\PromoCodeIssuedMail;
use App\Models\Order;
use App\Models\PromoCode;
use App\Services\PromoCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWinBackPromoCodes extends Command
{
    protected $signature = 'promo:win-back {--days=60 : Days since last order}';

    protected $description = 'Send win-back promo codes to inactive customers';

    public function handle(PromoCodeService $promoCodeService): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $emails = Order::query()
            ->whereIn('payment_status', ['paid', 'completed'])
            ->whereNotNull('customer_email')
            ->select('customer_email', 'user_id')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->groupBy('customer_email', 'user_id')
            ->havingRaw('MAX(created_at) <= ?', [$cutoff])
            ->get();

        $sent = 0;

        foreach ($emails as $row) {
            $email = strtolower(trim($row->customer_email));
            if ($email === '') {
                continue;
            }

            $promo = $promoCodeService->issueWinBackCode($email, $row->user_id, $days);
            if (! $promo) {
                continue;
            }

            try {
                Mail::to($email)->send(new PromoCodeIssuedMail($promo));
                $sent++;
            } catch (\Throwable $e) {
                $this->warn("Failed to email {$email}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} win-back promo code(s).");

        return self::SUCCESS;
    }
}
