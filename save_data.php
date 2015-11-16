<?php
require __DIR__ . '/autoload.php';

use Upwork\API;
use Upwork\API\Config;
use Upwork\API\Client;
use Upwork\API\Routers\Jobs\Profile;
use Upwork\API\Routers\Jobs\Search;
use Upwork\API\Routers\Metadata;
use FastFeed\FastFeed;
use FastFeed\Parser\RSSParser;
use Guzzle\Http\Client as HttpClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
//use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\PHPConsoleHandler;
use App\Classes\Rss;
use App\Classes\Db;

$configDb = json_decode(file_get_contents(__DIR__ . '/config.json'));
$db = new Db($configDb);

$_SESSION['access_token'] = '6cd6202680a20796889a537ae28bb51e';
$_SESSION['access_secret'] = '2d2c4ec57fd374c4';

$config = new Config(
    [
        'consumerKey' => '0b890e685dbaa9fddaf51df47f924096',
        'consumerSecret' => '70590fda6583b2db',
        'accessToken' => $_SESSION['access_token'],
        'accessSecret' => $_SESSION['access_secret'],
        'debug' => false,
        'authType' => 'OAuthPHPLib'
    ]
);

$client = new Client($config);
if (!empty($_SESSION['access_token']) && !empty($_SESSION['access_secret'])) {
    $client->getServer()
        ->getInstance()
        ->addServerToken(
            $config::get('consumerKey'),
            'access',
            $_SESSION['access_token'],
            $_SESSION['access_secret'],
            0
        );
} else {
    $accessTokenInfo = $client->auth();
}

$httpClient = new HttpClient();
$stream = new StreamHandler(__DIR__ . '/exceptions.log', Logger::DEBUG);
//$browser = new BrowserConsoleHandler(Logger::DEBUG, true);
$phpConsole = new PHPConsoleHandler();
$logger = new Logger('rss_logger');
$logger->pushHandler($stream);
//$logger->pushHandler($browser);
$logger->pushHandler($phpConsole);

$metadata = new Metadata($client);
//var_dump($metadata->getCategoriesV2());
var_dump($metadata->getSkills());

$profile = new Profile($client);
$jobs = new Search($client);
$params = ['category2' => 'Web, Mobile & Software Dev Jobs', 'days_posted' => 335,'skills' => ['css',
              'css3',
              'database-design',
              'database-programming',
              'english',
              'html',
              'javascript',
              'mysql',
              'php',
              'python']];
$arrJobs = $jobs->find($params);
//var_dump($arrJobs);

$fastFeed = new FastFeed($httpClient, $logger);
$parser = new RSSParser();
$fastFeed->pushParser($parser);
$fastFeed->addFeed('upwork', 'https://www.upwork.com/jobs/rss?cn1[]=Web%2C+Mobile+%26+Software+Dev&cn2[]=Web+Development&t[]=0&t[]=1&dur[]=0&dur[]=1&dur[]=13&dur[]=26&dur[]=none&wl[]=10&wl[]=30&wl[]=none&tba[]=0&tba[]=1-9&tba[]=10-&exp[]=1&exp[]=2&exp[]=3&amount[]=Min&amount[]=Max&sortBy=s_ctime+desc&_redirected');
$items = $fastFeed->fetch('upwork');
foreach ($items as $key => $item) {
    $itemId = $item->getId();
    $jobId = '~' . explode('?source=rss', explode('_%7E', $itemId)[1])[0];
    $res = Rss::findOne($db, $jobId);
    if (!isset($res)) {
        try {
            $specific = $profile->getSpecific($jobId);
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
            $rss = new Rss;
            $rss->id = $jobId;
            $rss->url = $itemId;
            $rss->created_at = date('Y-m-d H:i:s', $info->op_ctime / 1000);
            $rss->title = $info->op_title;
            $rss->description = addslashes($info->op_description);
            $rss->type = $info->job_type;
            $rss->budget = $info->amount;
            $rss->engagement = $info->op_engagement;
            $rss->engagement_weeks = $info->engagement_weeks;
            $rss->contractor_tier = $info->op_contractor_tier;
            $rss->skills = $skillsStr;
            $rss->insert($db);
        } catch (OAuthException2 $e) {
            $logger->addInfo($e->getMessage());
        }
    }
}

