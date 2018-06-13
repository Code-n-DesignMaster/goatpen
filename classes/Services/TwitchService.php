<?php
namespace GoatPen\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class TwitchService
{
    protected $client;
    protected $accessToken;

    public function __construct()
    {
        if (! $this->client) {
            $this->client = new Client([
                'base_uri' => 'https://api.twitch.tv',
            ]);
        }

        return $this->client;
    }

    private function getAccessToken(): string
    {
        if (! $this->accessToken) {
            $response = $this->client->request('POST', 'kraken/oauth2/token', [
                'query' => [
                    'client_id'     => TWITCH_CLIENT_ID,
                    'client_secret' => TWITCH_CLIENT_SECRET,
                    'grant_type'    => 'client_credentials',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return '';
            }

            $auth = json_decode($response->getBody());

            $this->accessToken = $auth->access_token;
        }

        return $this->accessToken;
    }

    public function getChannelFromId(int $id)
    {
        return $this->get('helix/users', ['id' => $id]);
    }

    public function getChannelFromUser(string $user)
    {
        return $this->get('helix/users', ['login' => $user]);
    }

    public function getVideoFromId(string $id)
    {
        return $this->get('helix/videos', ['id' => $id]);
    }

    private function get(string $path, array $query)
    {
        try {
            $response = $this->client->request('GET', $path, [
                'query'   => $query,
                'headers' => [
                    'Client-ID'     => TWITCH_CLIENT_ID,
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
            ]);
        } catch (ClientException $exception) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return json_decode($response->getBody());
    }
}
