<?php

namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\Formatters\CommentResponseFormatter;
use GoatPen\Services\{DeliverablesService, TagsService};
use GoatPen\ViewHelpers\{Notification, Paginator};
use GoatPen\{Campaign, CampaignComment, Channel, Metric, Post, Queue, Session, Stat, Task};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class SheetsController
{
    protected $csrf;
    protected $renderer;
    protected $client;
    protected $service;

    const ROWS_LOAD_COUNT_MAX = 1000;
    const APPLICATION_NAME = 'Goat Sheets Reader';
    const SCOPES = \Google_Service_Sheets::SPREADSHEETS;

    public function __construct(ContainerInterface $container)
    {
        $this->csrf     = $container['csrf'];
        $this->renderer = $container['renderer'];
    }

    public function init($client = null){
        if(!$client) $client = self::getClient();
        $this->client = $client;
        $this->service = new \Google_Service_Sheets($client);
    }// f

    public function getClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName(self::APPLICATION_NAME);
        $client->setScopes(self::SCOPES);
        $client->setAuthConfig(GOOGLE_CLIENT_SECRET_PATH);
        //$client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = GOOGLE_CREDENTIALS_PATH;
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
        $client->setAccessToken($accessToken);

        // Refresh
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }// f

    public function readTabs($sheetId, $tabs){
		$tR = [];

        foreach($tabs as $tab){
            $tR[] = $this->readTabFormattedDaily($sheetId, $tab);
        }
		return $tR;
    }//f

    private function getAllTabs($sheetId){
        $tR = [];
        $result = $this->service->spreadsheets->get($sheetId);
        $sheets = $result->getSheets();
        foreach($sheets as $one){
            $tR[] = $one->getProperties()->getTitle();
        }
        return $tR;
    }// f

    private function readTabFormattedDaily($sheetId, $tabName)
    {
        #readData
        $topBar = $this->fetchTwoLineBar($sheetId, $tabName, 1);
        $topBar = self::formatTopBar($topBar);

        #extract config
        //$config = $topBar['config'] ?? [];
        //unset($topBar['config']);

        $dayChart = $this->fetchDailyChart($sheetId, $tabName, 7);

        #add start date
        if($dayChart)
            $topBar['start_date']= self::findEarliestDate(array_keys($dayChart));

        #extract metrics
        foreach($topBar as $k=>$l){
        $res = self::analyseMetricByKey($k,$l);
            if($res){
                unset($topBar[$k]);
                $topBar['metrics_overall'][] = $res;
            }
        }

        #add day chart
        $topBar['metrics_by_day'] = $dayChart;
        return $topBar;
    }// f


    private function analyseMetricByKey($key, $val){
        $key = strtolower($key); $segs = explode('_',$key); $last = array_pop($segs);
        if(!in_array($last,['guaranteed','estimated'])) return false;
        return [
            'commitment_level' => $last,
            'name' => implode('_', $segs),
            'value' => $val
        ];
    }// f

    private function findEarliestDate($dates)
    {
        $key = $dates[0]; $value = strtotime($dates[0]);

        for($i = 1; $i < count($dates); $i++){
            if(strtotime($dates[$i]) < $value){
                $key = $dates[$i];
                $value = strtotime($dates[$i]);
            }
        }
        return $key;
    }// f

    private function formatTopBar($topBar)
    {
        #make budget integer
        $topBar['BUDGET_TOTAL'] = ($topBar['BUDGET_TOTAL'] ?? 0) * 1;
        $topBar['BUDGET_SPENT'] = ($topBar['BUDGET_SPENT'] ?? 0) * 1;

        #create config:graph
        $config = [];
        foreach($topBar as $k => $l){
            $t = explode('_',$k);
            if('GRAPH' !== $t[0]) continue;
            array_shift($t);
            $config['graph'][strtolower(implode('_',$t))] = strtolower($l);
            $key = $k;
            unset($topBar[$key]);
        }
        $topBar['config'] = $config;

        #split top_performing_posts
        $topPosts = [];
        if(!empty($topBar['TOP_PERFORMING_POSTS'])){
            $t = str_replace("\r","\n", $topBar['TOP_PERFORMING_POSTS']);
            $t = explode("\n",$t);
            $t = array_filter($t);
            $topPosts = array_values($t);
        }
        $topBar['TOP_PERFORMING_POSTS'] = $topPosts;

        #make metrics integer
        foreach($topBar as $k => &$l){
            if(self::analyseMetricByKey($k,$l)) $l *= 1;
        }
        unset($l);

        #make keys lowercase
        $keys = array_map(function($x){return strtolower($x);},array_keys($topBar));
        return array_combine($keys, array_values($topBar));
    }// f

    private function fetchDailyChart($sheetId, $tabName, $row)
    {
        #top keys
        if(!$fieldKeys = $this->getFieldKeysFromRow($sheetId, $tabName, $row))
                return null;
        $fieldKeys = array_map(function($x){return strtolower($x);},$fieldKeys);

        $row++;
        $finalLetter = self::progressLetter('A',count($fieldKeys));
        $finalRow = $row + self::ROWS_LOAD_COUNT_MAX;

        $response = $this->service->spreadsheets_values->get($sheetId, "$tabName!A$row:$finalLetter$finalRow");
        if(!$fieldVals = $response->getValues()) return [];

        $tR = [];
        foreach($fieldVals as $l){
            if(count($fieldKeys) !== count($l)) continue;
            $t = array_combine($fieldKeys, $l);
            array_shift($t);
            $t = array_map(function($x){return str_replace(',','',$x)*1;},$t);
            $tR[$l[0]] = $t;
        }

        return $tR;
    }// f

    private function progressLetter($start, $columnCount){
        return chr(ord($start) + $columnCount - 1);
    }//f

    private function fetchTwoLineBar($sheetId, $tabName, $row = 1)
    {
        if(!$fieldKeys = $this->getFieldKeysFromRow($sheetId, $tabName, $row))
                return null;
        $row++;
		$response = $this->service->spreadsheets_values->get($sheetId,"$tabName!A$row:ZZ$row");
        if(!$fieldVals = $response->getValues()) return null;
        $fieldVals=$fieldVals[0];

        $tR = [];
        foreach($fieldKeys as $indK => $heading){
                $tR[$heading] = isset($fieldVals[$indK])?$fieldVals[$indK]:null;
        }
        return $tR;
    }// f

    private function getFieldKeysFromRow($sheetId, $tabName = '', $row = 1)
    {
		$response = $this->service->spreadsheets_values->get($sheetId,"$tabName!A$row:ZZ$row");
        $t = $response->getValues();
        $t && $t=$t[0]; return $t;
    }//  f

    //CONTROLLER ACTIONS

	public function reloadDataAction(RequestInterface $request, ResponseInterface
		$response, $args): ResponseInterface
    {

        //load tabs
		$this->init();
        $tabs = $this->getAllTabs(\SHEETS_SHEETID);

        //check current users status
        $user = Session::getUser();
        if (! $user->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        $user['campaigns'] = json_decode($user['campaign']) ?? [];

        $recognizedCampaigns = CampaignController::getJSONList();
        $tabs = array_flip($tabs);
        foreach($recognizedCampaigns as $l){
            if(isset($tabs[$l['name']])){
                if(!in_array($l['slug'],$user['campaigns'])){
                    unset($tabs[$l['name']]);
                }
            }
        }

        $tabs = array_keys($tabs);

        //get tabs data
		$tabsData = $this->readTabs(\SHEETS_SHEETID, $tabs);

		//saveIntoFiles
		foreach($tabsData as $campaign){
			$slug = preg_replace('/[^-A-Za-z0-9]/','',$campaign['slug']);
            $campaign['last_updated'] = time();
			file_put_contents(SHEETS_LOCAL_STORE_DIR.$slug,json_encode($campaign));
		}

        return $this->renderer->render($response, '/client-dashboard/data-reload.phtml', compact('tabs'));
    }

}// c
