<?php

/**
 * This file is part of the amirsarhang/instagram-php-sdk library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Amirhossein Sarhangian <ah.sarhangian@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Amirsarhang;

/**
 * Graph Login for Instagram PHP SDK.
 */
class InstagramGraphLogin {

    /*
     * Facebook Object
     */
    private $fb;

    /*
     * Facebook helper
     */
    private $fbHelper;

    /**
     * Instantiates a new Facebook class object, Facebook Helper
     *
     */
    public function __construct()
    {
        $this->fb = new \Facebook\Facebook([
            'app_id' => $_ENV['FACEBOOK_APP_ID'],
            'app_secret' => $_ENV['FACEBOOK_APP_SECRET'],
            'default_graph_version' => $_ENV['FACEBOOK_GRAPH_VERSION'],
            'persistent_data_handler' => new SessionPersistentDataHandler()
        ]);
        $this->fbHelper = $this->fb->getRedirectLoginHelper();
    }

    /**
     * Get Login Url for your Application
     *
     * @param array $permissions
     * @return string
     */
    public function getLoginUrl(array $permissions): string
    {
        return $this->fbHelper->getLoginUrl($_ENV['INSTAGRAM_CALLBACK_URL'], $permissions);
    }

    /**
     * returns an AccessToken.
     *
     *
     * @return null|false
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getAccessToken()
    {
        try {
            $accessToken = $this->fbHelper->getAccessToken();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            error_log('Graph returned an error: ' . $e->getMessage());
            return false;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            error_log('Facebook SDK returned an error: ' . $e->getMessage());
            return false;
        }

        if (!isset($accessToken)) {
            if ($this->fbHelper->getError()) {
                $error = "Error: " . $this->fbHelper->getError() . "\n";
                $error .= "Error Code: " . $this->fbHelper->getErrorCode() . "\n";
                $error .= "Error Reason: " . $this->fbHelper->getErrorReason() . "\n";
                $error .= "Error Description: " . $this->fbHelper->getErrorDescription() . "\n";

                error_log($error);
            } else {
                error_log('Bad request');
            }
            return false;
        }

        $oAuth2Client = $this->fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId($_ENV['FACEBOOK_APP_ID']);
        $tokenMetadata->validateExpiration();


        return $accessToken->getValue();
    }

    /**
     * returns User Info for Connected Instagram IDs
     *
     * @return array|false
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getUserInfo()
    {
        return ["access_token" => $this->getAccessToken()];
    }

}
