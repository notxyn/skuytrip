protected function schedule(Schedule $schedule)
{
    $schedule->command('export:recommendation-data')->everyMinute();
}