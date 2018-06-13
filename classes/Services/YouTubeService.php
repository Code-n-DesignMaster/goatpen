<?php
namespace GoatPen\Services;

use Google_Client;
use Google_Service_YouTube_ChannelListResponse;
use Google_Service_YouTube_VideoListResponse;
use Google_Service_YouTube;

class YouTubeService
{
    protected $connection;

    public function __construct()
    {
        if (! $this->connection) {
            $client = new Google_Client;
            $client->setDeveloperKey(YOUTUBE_API_KEY);

            $this->connection = new Google_Service_YouTube($client);
        }

        return $this->connection;
    }

    public function getChannelFromId(string $id): Google_Service_YouTube_ChannelListResponse
    {
        return $this->connection->channels->listChannels('id,statistics', [
            'id' => $id,
        ]);
    }

    public function getChannelFromUser(string $user): Google_Service_YouTube_ChannelListResponse
    {
        return $this->connection->channels->listChannels('id,statistics', [
            'forUsername' => $user,
        ]);
    }

    public function getVideoFromId(string $id): Google_Service_YouTube_VideoListResponse
    {
        return $this->connection->videos->listVideos('id,statistics', [
            'id' => $id,
        ]);
    }
}
