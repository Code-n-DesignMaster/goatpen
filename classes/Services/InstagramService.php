<?php
namespace GoatPen\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class InstagramService
{
    protected $client;

    public function __construct()
    {
        if (! $this->client) {
            $this->client = new Client([
                'base_uri' => 'https://www.instagram.com',
            ]);
        }

        return $this->client;
    }

    public function getChannelFromUser(string $user)
    {
        return $this->get($user . '/', ['__a' => '1']);
    }

    public function getPostFromId(string $id)
    {
        return $this->get('p/' . $id . '/', ['__a' => '1']);
    }

    private function get(string $path, array $query = [])
    {
        try {
            $response = $this->client->request('GET', $path, [
                'query' => $query,
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
