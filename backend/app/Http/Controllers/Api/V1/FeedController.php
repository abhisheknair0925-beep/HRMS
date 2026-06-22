<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\Appreciation;
use App\Models\CompanyHoliday;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        $feed = [];

        // 1. Announcements
        $announcements = Announcement::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($announcements as $ann) {
            $feed[] = [
                'id' => 'ann-' . $ann->id,
                'type' => 'announcement',
                'title' => $ann->title,
                'detail' => $ann->content,
                'date' => $ann->created_at->format('M d, Y'),
                'timestamp' => $ann->created_at->timestamp,
            ];
        }

        // 2. Birthdays (employees celebrating this month or next)
        $employees = Employee::where('company_id', $companyId)->get();
        $currentMonth = (int) now()->format('m');

        foreach ($employees as $emp) {
            if ($emp->dob) {
                $dobMonth = (int) $emp->dob->format('m');
                if ($dobMonth === $currentMonth) {
                    $day = $emp->dob->format('d');
                    $feed[] = [
                        'id' => 'bday-' . $emp->id,
                        'type' => 'birthday',
                        'title' => $emp->first_name . ' ' . $emp->last_name . "'s Birthday",
                        'detail' => "{$emp->first_name} is celebrating their birthday on {$emp->dob->format('F d')}! Send them your best wishes.",
                        'date' => $emp->dob->format('M d'),
                        'timestamp' => now()->startOfMonth()->addDays((int)$day - 1)->timestamp,
                    ];
                }
            }
        }

        // 3. Work Anniversaries (employees who joined in this month)
        foreach ($employees as $emp) {
            if ($emp->joining_date) {
                $joinMonth = (int) $emp->joining_date->format('m');
                if ($joinMonth === $currentMonth) {
                    $years = now()->format('Y') - $emp->joining_date->format('Y');
                    if ($years > 0) {
                        $day = $emp->joining_date->format('d');
                        $feed[] = [
                            'id' => 'anniv-' . $emp->id,
                            'type' => 'anniversary',
                            'title' => $emp->first_name . ' ' . $emp->last_name . "'s Work Anniversary",
                            'detail' => "{$emp->first_name} is celebrating {$years} year" . ($years > 1 ? 's' : '') . " of building outstanding systems at HumaNode!",
                            'date' => $emp->joining_date->format('M d'),
                            'timestamp' => now()->startOfMonth()->addDays((int)$day - 1)->timestamp,
                        ];
                    }
                }
            }
        }

        // 4. Peer Appreciations
        $appreciations = Appreciation::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($appreciations as $app) {
            $feed[] = [
                'id' => 'feed-app-' . $app->id,
                'type' => 'anniversary', // theme maps to anniversary in react component type styling
                'title' => "{$app->sender_name} appreciated {$app->receiver_name}!",
                'detail' => "\"{$app->message}\"",
                'date' => $app->created_at->format('M d, Y'),
                'timestamp' => $app->created_at->timestamp,
            ];
        }

        // 5. Corporate Holidays
        $holidays = CompanyHoliday::where('company_id', $companyId)
            ->orderBy('holiday_date', 'asc')
            ->limit(5)
            ->get();

        foreach ($holidays as $hol) {
            $feed[] = [
                'id' => 'holiday-' . $hol->id,
                'type' => 'event',
                'title' => $hol->name,
                'detail' => "Upcoming holiday: {$hol->name}.",
                'date' => $hol->holiday_date->format('M d'),
                'timestamp' => $hol->holiday_date->timestamp,
            ];
        }

        // Sort feed items by timestamp descending, fallback to created_at
        usort($feed, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return $this->successResponse($feed, 'Social engagement feed compiled.');
    }
}
