<?php

namespace App\Services;

use App\Models\QuickBooksToken;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class QuickBooksService
{
    public function getDataService(): DataService
    {
        // For now assume single tenant (one QuickBooks company)
        $token = QuickBooksToken::firstOrFail();

        // Base config using what we have in DB
        $config = [
            'auth_mode'       => 'oauth2',
            'ClientID'        => config('quickbooks.client_id'),
            'ClientSecret'    => config('quickbooks.client_secret'),
            'RedirectURI'     => config('quickbooks.redirect_uri'),
            'scope'           => 'com.intuit.quickbooks.accounting',
            'baseUrl'         => config('quickbooks.environment') === 'production' ? 'Production' : 'Development',
            'accessTokenKey'  => $token->access_token,
            'refreshTokenKey' => $token->refresh_token,
            'QBORealmID'      => $token->realm_id,
        ];

        $dataService = DataService::Configure($config);

        // 🔁 Always refresh the access token using the stored refresh token
        $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();

        try {
            $newAccessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken(
                $token->refresh_token
            );

            // Store the latest tokens (VERY important)
            $token->update([
                'access_token'  => $newAccessTokenObj->getAccessToken(),
                'refresh_token' => $newAccessTokenObj->getRefreshToken() ?: $token->refresh_token,
                // still not using expires_at; leave null or parse if you want
            ]);

            // Tell DataService to use the new token object
            $dataService->updateOAuth2Token($newAccessTokenObj);
        } catch (\Throwable $e) {
            // If refresh fails, you usually need to re-connect app
            // You can add logging here if you like
        }

        return $dataService;
    }
}
