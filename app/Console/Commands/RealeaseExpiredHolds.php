<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductsExpiredHoldsStock;
use Illuminate\Console\Command;

class RealeaseExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:realease-expired-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new UpdateProductsExpiredHoldsStock());
    }
}
