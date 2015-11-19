<?php
require __DIR__ . '/autoload.php';

use Upwork\API;
use Upwork\API\Config;
use Upwork\API\Client;
use Upwork\API\Routers\Jobs\Profile;
use Upwork\API\Routers\Jobs\Search;
use App\Classes\Db;
use App\Classes\Upwork;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
//use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\PHPConsoleHandler;


$configDb = json_decode(file_get_contents(__DIR__ . '/config.json'));
$db = new Db($configDb);

$accessToken = '6cd6202680a20796889a537ae28bb51e';
$accessSecret = '2d2c4ec57fd374c4';

$stream = new StreamHandler(__DIR__ . '/exceptions.log', Logger::DEBUG);
//$browser = new BrowserConsoleHandler(Logger::DEBUG, true);
$phpConsole = new PHPConsoleHandler();
$logger = new Logger('rss_logger');
$logger->pushHandler($stream);
//$logger->pushHandler($browser);
$logger->pushHandler($phpConsole);

$config = new Config(
    [
        'consumerKey' => '0b890e685dbaa9fddaf51df47f924096',
        'consumerSecret' => '70590fda6583b2db',
        'accessToken' => $accessToken,
        'accessSecret' => $accessSecret,
        'debug' => false,
        'authType' => 'OAuthPHPLib'
    ]
);

$client = new Client($config);
$client->getServer()->getInstance()->addServerToken($config::get('consumerKey'),
'access', $accessToken, $accessSecret, 0);
$profile = new Profile($client);
$jobs = new Search($client);
$params = ['q' => '*', 'category2' => 'Web, Mobile & Software Dev', 'paging' => '0;100'];
$arrJobs = $jobs->find($params);
foreach ($arrJobs->jobs as $i => $job) {
//    var_dump($i.' = '.$job->id);
    $res = Upwork::findOne($db, $job->id);
    if (!isset($res)) {
        try {
            $specific = $profile->getSpecific($job->id);
            $info = $specific->profile;
            $skillsArr = [];
            $skillsStr = '';
            if ($info->op_required_skills &&
                $info->op_required_skills->op_required_skill
            ) {
                $skills = $info->op_required_skills->op_required_skill;
                if (is_array($skills)) {
                    $skillsArr = array_map(function ($s) {
                        return $s->skill;
                    }, $skills);
                    $skillsStr = implode(', ', $skillsArr);
                } else if (is_object($skills)) {
                    $skillsStr = $skills->skill;
                }
            }
            $upwork = new Upwork();
            $upwork->sample_id = ++$i;
            $upwork->sample_date = date('Y-m-d H:i:s');
            $upwork->job_id = $job->id;
            $upwork->url = $job->url;
            $upwork->created_at = date('Y-m-d H:i:s', $info->op_ctime / 1000);
            $upwork->title = $info->op_title;
            $upwork->description = addslashes($info->op_description);
            $upwork->type = $info->job_type;
            $upwork->budget = $info->amount;
            $upwork->engagement = $info->op_engagement;
            $upwork->engagement_weeks = $info->engagement_weeks;
            $upwork->contractor_tier = $info->op_contractor_tier;
            $upwork->skills = $skillsStr;
            $upwork->insert($db);
        } catch (OAuthException2 $e) {
            $logger->addInfo($e->getMessage());
//            var_dump($job);
        }
    }
}

$result = Upwork::findAll($db);
echo json_encode($result, JSON_UNESCAPED_UNICODE);