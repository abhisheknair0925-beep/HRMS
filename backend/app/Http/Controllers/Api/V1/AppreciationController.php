<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appreciation;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppreciationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $appreciations = Appreciation::orderBy('created_at', 'desc')->get();
        
        $mapped = $appreciations->map(function ($apprec) {
            return [
                'id' => $apprec->id,
                'sender' => $apprec->sender_name,
                'receiver' => $apprec->receiver_name,
                'message' => $apprec->message,
                'theme' => $apprec->theme,
            ];
        });

        return $this->successResponse($mapped, 'Appreciations retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receiver' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'theme' => 'required|string|in:indigo,rose,emerald,amber',
        ]);

        $sender = $request->user();
        
        // Try to match receiver name to a user to get the receiver_id
        $receiverUser = User::where('name', $validated['receiver'])->first();

        $appreciation = Appreciation::create([
            'company_id' => $sender->company_id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'receiver_id' => $receiverUser?->id,
            'receiver_name' => $validated['receiver'],
            'message' => $validated['message'],
            'theme' => $validated['theme'],
        ]);

        return $this->successResponse($appreciation, 'Appreciation card sent successfully.', 201);
    }
}
