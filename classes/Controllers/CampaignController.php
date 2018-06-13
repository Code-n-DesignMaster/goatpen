<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\Formatters\CommentResponseFormatter;
//use GoatPen\Services\{DeliverablesService, TagsService};
use GoatPen\ViewHelpers\{Notification, Paginator};
//use GoatPen\{Campaign, CampaignComment, Channel, Metric, Post, Queue, Session, Stat, Task};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class CampaignController
{
    protected $csrf;
    protected $renderer;

    public function __construct(ContainerInterface $container)
    {
        $this->csrf     = $container['csrf'];
        $this->renderer = $container['renderer'];
    }

	/*
    public function listAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $campaigns = Campaign::query()
            ->orderBy('client', 'asc')
            ->orderBy('name', 'asc');

        $page = (int) $request->getParam('page', 1);

        return $this->renderer->render($response, '/campaigns/list.phtml', [
            'paginator' => new Paginator($campaigns->count(), $page),
            'campaigns' => $campaigns->offset(($page - 1) * Paginator::PAGE_SIZE)->limit(Paginator::PAGE_SIZE)->get(),
        ]);
    }
	 */

    public static function getJSONList()
    {
        //check if dir exists
        if(!is_dir(SHEETS_LOCAL_STORE_DIR)){
            mkdir(SHEETS_LOCAL_STORE_DIR);
        }

        //read the json files and iterate through them
        $list = array_diff(scandir(SHEETS_LOCAL_STORE_DIR), ['.','..']);

        $campaigns=[];
        foreach($list as $fn){
            $campaignData = json_decode(file_get_contents(SHEETS_LOCAL_STORE_DIR . $fn),true);
            if (isset($campaignData['thumbnail_url'])) {
                $campaigns[] = [
                    'name' => $campaignData['campaign_name'],
                    'thumbnail' => $campaignData['thumbnail_url'],
                    'date' => $campaignData['end_date'],
                    'slug' => $fn
                ];
            } else {
                $campaigns[] = [
                    'name' => $campaignData['campaign_name'],
                    'thumbnail' => NULL,
                    'date' => $campaignData['end_date'],
                    'slug' => $fn
                ];
            }
        }

        return $campaigns;
    }// f

    public function getDashboardAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
		try {
			$fn = SHEETS_LOCAL_STORE_DIR . $args['slug'];
			$campaignData = json_decode(file_get_contents($fn), true);
		} catch (Exception $exception) {
            Notification::add('The information cannot be loaded at this time.', 'danger');
            return $response->withRedirect('/dashboard');
		}

        $config = $campaignData['config'] ?? [];
        unset($campaignData['config']);

		return $this->renderer->render($response, '/client-dashboard/dashboard.phtml', [
            'data' => $campaignData,
            'config' => $config,
            'updatedAt' => $campaignData['last_updated'],
		]);
    }
}// c
