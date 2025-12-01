<?php

namespace App\Http\Controllers;

use App\Models\QuickBooksToken;
use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;

class QuickBooksAuthController extends Controller
{
    private function makeDataService(): DataService
    {
        return DataService::Configure([
            'auth_mode'    => 'oauth2',
            'ClientID'     => config('quickbooks.client_id'),
            'ClientSecret' => config('quickbooks.client_secret'),
            'RedirectURI'  => config('quickbooks.redirect_uri'),
            'scope'        => 'com.intuit.quickbooks.accounting',
            'baseUrl'      => config('quickbooks.environment') === 'production' ? 'Production' : 'Development',
        ]);
    }

    /**
     * STEP 1: Redirect user to QuickBooks login/consent.
     */
    public function connect()
    {
        $dataService       = $this->makeDataService();
        $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();

        // ✅ THIS is the correct method – NOT getAuthorizationUrl()
        $authUrl = $oauth2LoginHelper->getAuthorizationCodeURL();

        return redirect()->away($authUrl);
    }

    /**
     * STEP 2: QuickBooks redirects back here with ?code=...&realmId=...
     */
    public function callback(Request $request)
    {
        $code    = $request->query('code');
        $realmId = $request->query('realmId');

        if (!$code || !$realmId) {
            abort(400, 'Missing code or realmId in callback.');
        }

        $dataService       = $this->makeDataService();
        $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();

        // Exchange code for access + refresh token
        $accessTokenObj = $oauth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);

        QuickBooksToken::updateOrCreate(
            ['user_id' => null],          // single-tenant
            [
                'realm_id'      => $realmId,
                'access_token'  => $accessTokenObj->getAccessToken(),
                'refresh_token' => $accessTokenObj->getRefreshToken(),
                'access_token_expires_at' => null,
            ]
        );

        return redirect()
            ->route('dashboard')
            ->with('status', 'QuickBooks connected successfully!');
    }
}
