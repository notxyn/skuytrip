<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportRecommendationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-recommendation-data';

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
        \File::put(base_path('attractions.json'), \App\Models\Attraction::all()->toJson(JSON_PRETTY_PRINT));
        \File::put(base_path('bookings.json'), \App\Models\Booking::all()->toJson(JSON_PRETTY_PRINT));
        $this->info('Exported attractions.json and bookings.json');
        return 0;
    }
}
