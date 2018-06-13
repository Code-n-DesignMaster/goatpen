<?php
namespace GoatPen\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use stdClass;

class TwitterService
{
    protected $connection;

    public function __construct()
    {
        if (! $this->connection) {
            $this->connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_ACCESS_KEY, TWITTER_ACCESS_SECRET);
        }

        return $this->connection;
    }

    public function getUserFromId(string $id): stdClass
    {
        return $this->connection->get('users/show', [
            'user_id' => $id,
        ]);
    }

    public function getUserFromName(string $name): stdClass
    {
        return $this->connection->get('users/show', [
            'screen_name' => $name,
        ]);
    }

    public function getPostFromId(string $id): array
    {
        return $this->connection->get('statuses/lookup', [
            'id'               => $id,
            'include_entities' => 'false',
            'trim_user'        => 'true',
        ]);
    }
}
