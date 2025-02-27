<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class FacebookController extends Controller
{
    private const GRAPH_API_URL = 'https://graph.facebook.com/v20.0';
    private const DEFAULT_EMAIL_DOMAIN = '@noemail.com';

    /**
     * Redirect to Facebook authentication page.
     */
    public function redirectToProvider()
    {
        try {
            return Socialite::driver('facebook')->stateless()->redirect();
        } catch (\Exception $e) {
            Log::error('Facebook Login Error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Facebook login failed.');
        }
    }

    /**
     * Handle Facebook callback and store token.
     */
    public function handleProviderCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            Log::info('Facebook User Data:', [
                'id' => $facebookUser->id,
                'name' => $facebookUser->name,
                'email' => $facebookUser->email,
                'avatar' => $facebookUser->avatar,
                'token' => $facebookUser->token,
                'raw' => $facebookUser->user, // Full raw data
            ]);

            $authUser = User::updateOrCreate(
                ['facebook_id' => $facebookUser->id],
                [
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email ?? 'facebook_' . $facebookUser->id . self::DEFAULT_EMAIL_DOMAIN,
                    'avatar' => $facebookUser->avatar,
                    'facebook_token' => $facebookUser->token,
                    'password' => bcrypt(str()->random(16)),
                ]
            );

            Auth::login($authUser, true);
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            Log::error('Facebook Callback Error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Facebook login failed.');
        }
    }


    /**
     * Logout user and revoke Facebook token.
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('welcome');
    }

    /**
     * Fetch user's Facebook pages using the stored token.
     */
    public function fetchFacebookPages()
    {
        $user = Auth::user();
        if (!$user || !$user->facebook_token) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        try {
            $response = Http::get(self::GRAPH_API_URL . "/me/accounts", [
                'access_token' => $user->facebook_token,
            ]);

            if ($response->failed()) {
                Log::error('Failed to fetch Facebook pages', ['response' => $response->json()]);
                return response()->json(['error' => 'Failed to fetch pages'], 500);
            }

            $pages = $response->json();
            Log::info('Fetched Facebook Pages:', $pages);

            return response()->json($pages);
        } catch (\Exception $e) {
            Log::error('Error fetching Facebook pages: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch pages'], 500);
        }
    }


    /**
     * Fetch page insights based on given page ID.
     */
    public function fetchPageAnalytics(Request $request, $pageId)
    {
        $user = Auth::user();
        if (!$user || !$user->facebook_token) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        try {
            // Get page access token
            $pageResponse = Http::withToken($user->facebook_token)
                ->get(self::GRAPH_API_URL . "/{$pageId}?fields=access_token");

            if ($pageResponse->failed() || !isset($pageResponse->json()['access_token'])) {
                Log::error('Failed to retrieve page access token', ['response' => $pageResponse->json()]);
                return response()->json(['error' => 'Failed to retrieve page access token'], 500);
            }

            $pageAccessToken = $pageResponse->json()['access_token'];
            Log::info('Page Access Token Retrieved:', ['page_id' => $pageId, 'token' => $pageAccessToken]);

            // Define the metrics to fetch
            $metrics = [
                'page_fans',
                'page_engaged_users',
                'page_impressions',
                'page_actions_post_reactions_total',
                'page_post_engagements'
            ];

            // Build query parameters
            $queryParams = [
                'metric' => implode(',', $metrics),
                'period' => $request->query('period', 'total_over_range'),
                'since' => $request->query('since'),
                'until' => $request->query('until'),
            ];

            // Fetch insights
            $fullUrl = self::GRAPH_API_URL . "/{$pageId}/insights?" . http_build_query(array_filter($queryParams));
            \Log::info("Facebook Insights API URL: " . $fullUrl);
            $insightsResponse = Http::withToken($pageAccessToken)
                ->get(self::GRAPH_API_URL . "/{$pageId}/insights", array_filter($queryParams));

            if ($insightsResponse->failed()) {
                Log::error('Failed to fetch Facebook analytics', ['response' => $insightsResponse->json()]);
                return response()->json(['error' => 'Failed to fetch analytics'], 500);
            }

            $analytics = $insightsResponse->json()['data'] ?? [];
            Log::info('Fetched Facebook Analytics:', $analytics);

            return response()->json($analytics);
        } catch (\Exception $e) {
            Log::error('Error fetching Facebook analytics: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch analytics'], 500);
        }
    }
}
