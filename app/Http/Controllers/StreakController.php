<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    public function __construct(private StreakService $streakService) {}

    public function getStreakInfo(Request $request){
        $user = $request->user();
        
        $streakInfo = $this->streakService->getStreakInfo($user);

        return response()->json([
            'status'=>'success',
            'data' => $streakInfo,
        ]);
    }    
}
