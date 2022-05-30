<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OverdueBookingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'overdue:booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Overdue booking';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ids = collect(DB::select("SELECT * FROM location_user WHERE deactivate < now() AND status = 1"))->pluck('id')->implode(',');
        DB::select("UPDATE location_user SET status = 0 WHERE id IN ($ids)");
        return 0;
    }
}
