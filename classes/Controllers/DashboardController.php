<?php
namespace GoatPen\Controllers;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use GoatPen\{Session, User};

class DashboardController
{
    protected $renderer;

    public function __construct(ContainerInterface $container)
    {
        $this->renderer = $container['renderer'];
    }

    public function indexAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if(!$user = Session::getUser()){
            //redirect
        }

        $user['campaigns'] = json_decode($user['campaign']) ?? [];

        $campaigns = [];
        foreach(CampaignController::getJSONList() as $c){
            if(in_array($c['slug'], $user['campaigns'])) $campaigns[] = $c;
        }
            
        //redirect if user has only one campaign
        if(1 === count($campaigns)){
            header('Location: '.$_SERVER['REQUEST_URI'].'/'.$campaigns[0]['slug']);
            exit;
        }

        return $this->renderer->render($response, '/client-dashboard/campaign-list.phtml', compact('campaigns'));
    }
}
