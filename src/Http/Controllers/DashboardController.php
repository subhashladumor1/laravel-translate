<?php

namespace Subhashladumor1\Translate\Http\Controllers;

use Illuminate\Routing\Controller;
use Subhashladumor1\Translate\Facades\Translate;

class DashboardController extends Controller
{
    /**
     * Display the analytics dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $analytics = Translate::getAnalytics();
        
        $cacheHits = $analytics['cache_hits'] ?? 0;
        $cacheMisses = $analytics['cache_misses'] ?? 0;
        $totalRequests = $cacheHits + $cacheMisses;
        $hitRate = $totalRequests > 0 ? round(($cacheHits / $totalRequests) * 100, 2) : 0;

        return view('translate::dashboard', [
            'analytics' => $analytics,
            'cacheHits' => $cacheHits,
            'cacheMisses' => $cacheMisses,
            'totalRequests' => $totalRequests,
            'hitRate' => $hitRate,
        ]);
    }
}
