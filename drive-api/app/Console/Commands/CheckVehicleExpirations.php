<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\User;
use App\Notifications\VehicleExpiryNotification;
use Carbon\Carbon;

#[Signature('app:check-vehicle-expirations')]
#[Description('Checks vehicle registration and insurance expirations and sends notifications')]
class CheckVehicleExpirations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vehicles = Vehicle::whereNotNull('registration_expiry_date')
            ->orWhereNotNull('insurance_expiry_date')
            ->get();

        $admins = User::where('legacy_permissions', 'LIKE', '%"dashboard.live_map_tracker"%')
            ->orWhere('role', 'developer')
            ->get();

        if ($admins->isEmpty()) {
            $this->info('No admins to notify.');
            return;
        }

        $now = Carbon::now()->startOfDay();

        foreach ($vehicles as $vehicle) {
            $this->checkExpiry($vehicle, 'registration_expiry_date', 'Registration', $admins, $now);
            $this->checkExpiry($vehicle, 'insurance_expiry_date', 'Insurance', $admins, $now);
        }

        $this->info('Vehicle expirations checked successfully.');
    }

    private function checkExpiry($vehicle, $field, $label, $admins, $now)
    {
        if (!$vehicle->{$field}) return;

        $expiryDate = Carbon::parse($vehicle->{$field})->startOfDay();

        // Expired (Urgent)
        if ($now->greaterThan($expiryDate)) {
            $days = abs($now->diffInDays($expiryDate));
            $message = "{$label} for {$vehicle->unit_id} ({$vehicle->plate_number}) has EXPIRED {$days} days ago!";
            $this->notifyAdmins($admins, $vehicle, $label, 'urgent', $message);
            return;
        }

        // Upcoming (Warning)
        $thresholds = [
            '3 months' => $now->copy()->addMonths(3),
            '2 months' => $now->copy()->addMonths(2),
            '1 month' => $now->copy()->addMonth(),
            '3 weeks' => $now->copy()->addWeeks(3),
            '2 weeks' => $now->copy()->addWeeks(2),
            '1 week' => $now->copy()->addWeek(),
        ];

        foreach ($thresholds as $timeframe => $targetDate) {
            if ($targetDate->isSameDay($expiryDate)) {
                $message = "{$label} for {$vehicle->unit_id} ({$vehicle->plate_number}) will expire in exactly {$timeframe}.";
                $this->notifyAdmins($admins, $vehicle, $label, 'warning', $message);
                break; // Only notify once per run per field
            }
        }
    }

    private function notifyAdmins($admins, $vehicle, $type, $status, $message)
    {
        $this->info("Sending notification: {$message}");
        foreach ($admins as $admin) {
            $admin->notify(new VehicleExpiryNotification($vehicle->unit_id, $type, $status, $message));
        }
    }
}
