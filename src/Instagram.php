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
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * It's an unofficial Instagram PHP SDK.
 */
class Instagram
{
    /**
     * @var string|null The Instagram AccessToken.
     */
    protected $token;

    /**
     * @var string|null New Guzzle Client.
     */
    protected $client;

    /**
     * @param string|null $token The Instagram AccessToken.
     *
     */
    public function __construct(string $token = null)
    {
        $dotenv = Dotenv::createImmutable(__DIR__.'/../../../../');
        $dotenv->safeLoad();

        $this->token = $token;

        $this->client = new Client([
            "base_uri" => "https://graph.instagram.com/{$_ENV['INSTAGRAM_GRAPH_VERSION']}/",
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
     * Get Page Access Token from Instagram Graph API Callback.
     *
     * @param string $code The code that returned by Instagram after user callback.
     * @return array|bool
     *
     * @throws GuzzleException
     */
    public static function getPageAccessToken(string $code): array|bool
    {
        $instagramLogin = new InstagramGraphLogin();
        $connectedAccountsData = $instagramLogin->getAccessToken($code);

        return $connectedAccountsData;
    }

    /**
     * Get Instagram User Info.
     *
     * @param string $access_token Access token
     * @param string $fields "id,name"
     * @return array|false
     *
     * @throws GuzzleException
     */
    public static function getUserInfo(string $access_token, string $fields = ''): array|bool
    {
        $instagramLogin = new InstagramGraphLogin();
        return $instagramLogin->getUserInfo($access_token, $fields);
    }

    /**
     * Get Request on Instagram Graph API.
     *
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @return array
     *
     * @throws GuzzleException
     */
    public function get(string $endpoint): array
    {
        return (new InstagramPayloads)->getPayload($endpoint, $this->token);
    }

    /**
     * POST Request on Instagram Graph API.
     *
     * @param array $params Post parameters.
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @return array
     *
     * @throws GuzzleException
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
     * @throws GuzzleException
     */
    public function delete(array $params, string $endpoint): array
    {
        return (new InstagramPayloads)->deletePayload($params, $endpoint, $this->token);
    }

    /**
     * Get Instagram Connected Account Information.
     *
     * @param string $access_token Access token
     * @return array
     *
     * @throws GuzzleException
     */
    public function getConnectedAccount(string $access_token): array
    {
        $instagramLogin = new InstagramGraphLogin();
        return $instagramLogin->getUserInfo($access_token);
    }

    /**
     * Subscribe Webhook to Graph API.
     *
     * @param string $accessToken Access token
     * @param array $subscribed_fields Page field (example: ["messages"])
     * @return array
     *
     * @throws GuzzleException
     */
    public function subscribeWebhook(string $accessToken, array $subscribed_fields = ["messages"]): array
    {
        $fields = implode(",", $subscribed_fields);

        return $this->post([],'/me/subscribed_apps?subscribed_fields='.$fields.'&access_token='.$accessToken);
    }

    /**
     * Get Comment Graph API.
     *
     * @param string $comment_id Comment ID
     * @param array $fields Required fields
     * @return array
     *
     * @throws GuzzleException
     */
    public function getComment(string $comment_id, array $fields = ['timestamp','text']): array
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
     * @param string $recipient_id Post or Comment ID
     * @param string $message Comment's Text
     * @return array
     * @throws GuzzleException
     */
    public function addComment(string $recipient_id, string $message): array
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
     * @throws GuzzleException
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
     * @throws GuzzleException
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

    /**
     * Get Message Graph API.
     *
     * @param string $message_id Message ID
     * @param array $fields Required fields
     * @return array
     *
     * @throws GuzzleException
     */
    public function getMessage(string $message_id, array $fields = []): array
    {
        if (empty($message_id)) {

            $error = 'Instagram Messenger GET Message: Missing Message ID!';

            error_log($error);

            throw new InstagramException($error);
        }

        $endpoint = '/'.$message_id.'?fields=message,from,created_time,attachments';

        if (count($fields) > 0) {
            $fields_str = implode(",", $fields);
            $endpoint = '/'.$message_id.'?fields='.$fields_str;
        }

        return self::get($endpoint);
    }

    /**
     * Add Text Message Graph API.
     *
     * @param string $recipient_id Instagram USER_ID
     * @param string $message Message's Text
     * @return array
     *
     * @throws GuzzleException
     */
    public function addTextMessage(string $recipient_id, string $message): array
    {
        if (empty($recipient_id) || empty($message)) {

            $error = 'Instagram ADD Text Message in Messenger: Missing message or recipient!';

            error_log($error);

            throw new InstagramException($error);
        }

        $endpoint = '/me/messages';

        $params = [
            'recipient' => [
                'id' => $recipient_id,
            ],
            'message' => [
                'text' => $message,
            ],
        ];

        return self::post($params, $endpoint);
    }

    /**
     * Add Media Message Graph API.
     *
     * @param string $recipient_id Instagram USER_ID
     * @param string $url Message Attachment's url
     * @param string $type Message Attachment's type
     * @return array
     *
     * @throws GuzzleException
     */
    public function addMediaMessage(string $recipient_id, string $url, string $type = "image"): array
    {
        if (empty($recipient_id) || empty($url)) {

            $error = 'Instagram ADD Media Message in Messenger: Missing attachment or recipient!';

            error_log($error);

            throw new InstagramException($error);
        }

        $endpoint = '/me/messages';

        $params = [
            'recipient' => [
                'id' => $recipient_id,
            ],
            'message' => [
                'attachment' => [
                    'type' => $type,
                    'payload' => [
                        'url' => $url,
                    ],
                ],
            ],
        ];

        return self::post($params, $endpoint);
    }
}
