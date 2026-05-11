<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class UpdateOrdersPaidAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-paid-amount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update paid_amount for all orders based on total_amount - remaining_amount';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update paid_amount for all orders...');

        try {
            // Update all orders: paid_amount = total_amount - remaining_amount
            DB::statement('UPDATE orders SET paid_amount = total_amount - COALESCE(remaining_amount, 0)');

            $count = Order::count();
            $this->info("Successfully updated paid_amount for {$count} orders!");

            // Show some examples
            $this->info("\nSample orders after update:");
            $orders = Order::select('order_number', 'total_amount', 'remaining_amount', 'paid_amount', 'payment_status')
                ->limit(10)
                ->get();

            $this->table(
                ['Order Number', 'Total', 'Remaining', 'Paid', 'Status'],
                $orders->map(fn($o) => [
                    $o->order_number,
                    number_format($o->total_amount, 0),
                    number_format($o->remaining_amount ?? 0, 0),
                    number_format($o->paid_amount, 0),
                    $o->payment_status
                ])
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error updating orders: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
