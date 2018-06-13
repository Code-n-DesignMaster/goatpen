<?php
namespace GoatPen\Services;

use Facebook\Exceptions\{FacebookResponseException, FacebookSDKException};
use Facebook\Facebook;

class FacebookService
{
    protected $connection;

    public function __construct()
    {
        if (! $this->connection) {
            $this->connection = new Facebook([
                'app_id'                => FACEBOOK_APP_ID,
                'app_secret'            => FACEBOOK_APP_SECRET,
                'default_graph_version' => 'v2.11',
            ]);
        }

        return $this->connection;
    }

    public function getPageFromUrl(string $url)
    {
        return $this->get('/', [
            'id'     => $url,
            'fields' => 'id,name,fan_count',
        ]);
    }

    public function getPageFromId(string $id)
    {
        return $this->get('/' . $id, [
            'fields' => 'id,name,fan_count',
        ]);
    }

    public function getPostFromId(string $id)
    {
        return $this->get('/' . $id, [
            'fields' => 'id,reactions.limit(0).summary(true),comments.limit(0).summary(true),shares.summary(true)',
        ]);
    }

    private function get(string $path, array $params = [])
    {
        try {
            return $this->connection
                ->get($path . '?' . http_build_query($params), $this->connection->getApp()->getAccessToken())
                ->getDecodedBody();
        } catch (FacebookResponseException $exception) {
            return false;
        } catch (FacebookSDKException $exception) {
            return false;
        }
    }
}
