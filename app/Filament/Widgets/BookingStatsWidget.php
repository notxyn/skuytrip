<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Attraction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $paidBookings = Booking::where('status', 'paid')->count();
        $totalRevenue = Booking::where('status', 'paid')->sum('total');
        $totalAttractions = Attraction::count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Bookings', $totalBookings)
                ->description('All time bookings')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Pending Payments', $pendingBookings)
                ->description('Awaiting payment confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Completed Payments', $paidBookings)
                ->description('Successfully paid bookings')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('From paid bookings')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Total Attractions', $totalAttractions)
                ->description('Available destinations')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),

            Stat::make('Registered Users', $totalUsers)
                ->description('Total user accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
} 