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

declare(strict_types=1);

namespace Amirsarhang;
use Facebook\Facebook;
use Dotenv\Dotenv;

/**
 * It's unofficial Instagram PHP SDK.
 */
class Instagram
{
    /**
     * @var string|null The Instagram or Facebook AccessToken.
     */
    protected $token;

    /**
     * @var string|null The subclass of the child GraphNode's.
     */
    protected $fb;

    /**
     * @param string|null $token The Instagram or Facebook AccessToken.
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function __construct(string $token = null)
    {
        $dotenv = Dotenv::createImmutable(__DIR__.'/../../../../');
        $dotenv->safeLoad();

        $this->token = $token;

        $this->fb = new Facebook([
            'app_id' => $_ENV['FACEBOOK_APP_ID'],
            'app_secret' => $_ENV['FACEBOOK_APP_SECRET'],
            'default_graph_version' => $_ENV['FACEBOOK_GRAPH_VERSION']
        ]);
    }

    /**
     * Generate Login and Authenticate URL to Instagram Graph API.
     *
     * @param array $permissions Instagram permissions
     * @return string
     */
    public function getLoginUrl(array $permissions): string
    {
        $instagramLogin = new InstagramGraphLogin();

        return $instagramLogin->getLoginUrl($permissions);
    }

    /**
     * Get User Access Token from Instagram Graph API Callback.
     *
     * @return string
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public static function getUserAccessToken(): string
    {
        $instagramLogin = new InstagramGraphLogin();
        $connectedAccountsData = $instagramLogin->getUserInfo();

        return $connectedAccountsData['access_token'];
    }

    /**
     * Get Request on Instagram Graph API.
     *
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @param bool|null $graphEdge The request should be on `graphEdge` or `graphNode`.
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function get(string $endpoint, bool $graphEdge = null): array
    {
        return (new InstagramPayloads)->getPayload($endpoint, $this->token, $graphEdge);
    }

    /**
     * POST Request on Instagram Graph API.
     *
     * @param array $params Post parameters.
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function post(array $params, string $endpoint): array
    {
        return (new InstagramPayloads)->postPayload($params, $endpoint, $this->token);
    }

    /**
     * DELETE Request on Instagram Graph API.
     *
     * @param array $params DELETE parameters.
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function delete(array $params, string $endpoint): array
    {
        return (new InstagramPayloads)->deletePayload($params, $endpoint, $this->token);
    }

    /**
     * Get Instagram connected Accounts List.
     *
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getConnectedAccountsList(): array
    {
        $accounts = self::get('/me/accounts', true);

        $connected_instagram_ids = [];
        foreach ($accounts as $value) {
            $result = self::get('/'.$value['id'].'?fields=instagram_business_account');

            if (@$result['instagram_business_account']) {
                $fb_data = [
                    'fb_page_id'=> $value['id'],
                    'fb_page_access_token'=> $value['access_token'],
                    'instagram_page_id'=> $result['instagram_business_account']['id'],
                ];
                // push instagram account ID to array
                $connected_instagram_ids[] = $fb_data;
            }
        }

        $instagram_accounts = [];
        foreach ($connected_instagram_ids as $value) {
            $response = self::get('/'.$value['instagram_page_id'].'?fields=name,biography,username,followers_count,follows_count,media_count,profile_picture_url,website');

            $instagram_account = $response;
            $instagram_account['fb_page_id'] = $value['fb_page_id'];
            $instagram_account['fb_page_access_token'] = $value['fb_page_access_token'];

            $instagram_accounts[] = json_decode(json_encode($instagram_account));
        }

        return ['success' => 'true', 'instagramAccounts' => $instagram_accounts];
    }

    /**
     * Subscribe Webhook to Graph API.
     *
     * @param int $facebookPageId Facebook Page ID
     * @param string $facebookPageAccessToken Facebook Page Access Token
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function subscribeWebhook(int $facebookPageId, string $facebookPageAccessToken): array
    {
        return $this->post([],'/'.$facebookPageId.'/subscribed_apps?subscribed_fields=email&access_token='.$facebookPageAccessToken);
    }

    /**
     * Get Comment Graph API.
     *
     * @param string $comment_id Comment ID
     * @param array $fields Required fields
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getComment(string $comment_id, array $fields = []): array
    {
        if (empty($comment_id)) {

            $error = 'Instagram Comment Message: Missing Comment ID!';

            error_log($error);

            throw new InstagramException($error);
        }

        $endpoint = '/'.$comment_id;

        if (count($fields) > 0) {
            $fields_str = implode(",", $fields);
            $endpoint = '/'.$comment_id.'?fields='.$fields_str;
        }

        return self::get($endpoint);
    }

    /**
     * Add Comment Graph API.
     *
     * @param string $message Comment's Text
     * @param string $recipient_id Post or Comment ID
     * @return array
     */
    public function addComment(string $message, string $recipient_id): array
    {
        $endpoint = $recipient_id.'/replies';

        // Check if we have recipient or content
        if (empty($message) || empty($recipient_id)) {

            $error = 'Instagram Comment Message: Missing message or recipient!';

            error_log($error);

            throw new InstagramException($error);
        }

        $params = [
            'message' => $message,
        ];

        return self::post($params, $endpoint);
    }

    /**
     * DELETE Comment Graph API.
     *
     * @param string $comment_id Comment ID
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function deleteComment(string $comment_id): array
    {
        if (empty($comment_id)) {

            $error = 'Instagram DELETE Comment Message: Missing Comment ID!';

            error_log($error);

            throw new InstagramException($error);
        }

        $endpoint = '/'.$comment_id;

        $params = [];

        return self::delete($params, $endpoint);
    }

    /**
     * Hide Comment Graph API.
     *
     * @param string $comment_id Comment ID
     * @param bool $status Hide => true | UnHide => false
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function hideComment(string $comment_id, bool $status): array
    {
        if (empty($comment_id)) {

            $error = 'Instagram HIDE Comment Message: Missing Comment ID';

            error_log($error);

            throw new InstagramException($error);
        }

        $endpoint = '/'.$comment_id;

        $params = [
            'hide' => $status,
        ];

        return self::post($params, $endpoint);
    }
}
