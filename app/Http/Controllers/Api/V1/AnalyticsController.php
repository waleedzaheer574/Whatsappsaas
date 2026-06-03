<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, AnalyticsService $analytics)
    {
        return $this->success($analytics->summary((int) $request->query('workspace_id', 1)), 'Analytics fetched successfully');
    }
}
