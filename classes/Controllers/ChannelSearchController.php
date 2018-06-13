<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Channel;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class ChannelSearchController
{
    public function searchAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $influencer = $request->getParam('influencer');
        $platform   = $request->getParam('platform');

        try {
            if (strlen($influencer) === 0 && strlen($platform) === 0) {
                throw new Exception('Please enter some search parameters');
            }

            $channels = Channel::query()
                ->select('channels.id', 'influencers.name as influencer', 'channels.name as channel', 'platforms.id as platform_id', 'platforms.name as platform', 'channels.price')
                ->join('influencers', 'influencers.id', '=', 'channels.influencer_id')
                ->join('platforms', 'platforms.id', '=', 'channels.platform_id')
                ->orderBy('influencers.name', 'asc')
                ->orderBy('channels.name', 'asc')
                ->orderBy('platforms.name', 'asc');

            if (strlen($influencer) > 0) {
                $channels->where('influencers.name', 'like', '%' . $influencer . '%');
            }

            if (strlen($platform) > 0) {
                $channels->where('channels.platform_id', '=', $platform);
            }

            if ($channels->count() === 0) {
                throw new Exception('There are no results that match your search');
            }

            return $response->withJson($channels->get());
        } catch (Exception $exception) {
            return $response->withJson(['error' => $exception->getMessage()], 400);
        }
    }
}
