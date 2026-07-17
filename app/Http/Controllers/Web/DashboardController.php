<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RiskWeight;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * GET / - Landing Page
     */
    public function landing()
    {
        // Get popular countries (e.g. Germany, China, Indonesia, US, UK)
        $popular = Country::whereIn('iso2', ['DE', 'CN', 'ID', 'US', 'GB'])
            ->with('latestRiskScore')
            ->get();

        return view('dashboard.landing', compact('popular'));
    }

    /**
     * GET /dashboard - Main Dashboard
     */
    public function dashboard()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('dashboard.index', compact('countries'));
    }

    /**
     * GET /weather - Weather Map
     */
    public function weather()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('dashboard.weather', compact('countries'));
    }

    /**
     * GET /currency - Currency Impact
     */
    public function currency()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('dashboard.currency', compact('countries'));
    }

    /**
     * GET /news - News Intelligence
     */
    public function news()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('dashboard.news', compact('countries'));
    }

    /**
     * GET /ports - Ports Map
     */
    public function ports()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('dashboard.ports', compact('countries'));
    }

    /**
     * GET /analytics - Data Visualizations
     */
    public function analytics()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('dashboard.analytics', compact('countries'));
    }

    /**
     * GET /compare - Country Comparison
     */
    public function compare()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('dashboard.compare', compact('countries'));
    }

    /**
     * GET /watchlist - User Monitored Countries
     */
    public function watchlist()
    {
        return view('dashboard.watchlist');
    }

    /**
     * GET /admin - Admin Control Panel
     */
    public function admin()
    {
        $weights = RiskWeight::all()->pluck('weight', 'component')->toArray();
        return view('dashboard.admin', compact('weights'));
    }
}
