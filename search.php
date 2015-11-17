<?php
require __DIR__ . '/autoload.php';

use Upwork\API;
use Upwork\API\Config;
use Upwork\API\Client;
use Upwork\API\Routers\Jobs\Profile;
use Upwork\API\Routers\Jobs\Search;
use App\Classes\Db;
use FastFeed\FastFeed;
use FastFeed\Parser\RSSParser;
use Guzzle\Http\Client as HttpClient;
use Monolog\Logger;

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
date_default_timezone_set('Europe/Moscow');

$jobs = new Search($client);
$params = ['q' => '*', 'category2' => 'Web, Mobile & Software Dev'];
$arrJobs = $jobs->find($params);
foreach ($arrJobs->jobs as $job) {
    $date_created = $job->date_created;
    echo $job->id . '- - - -' . $date_created . '<br>';
    echo $job->url.'<br>';
}
echo '<br><br><br>';
$profile = new Profile($client);
$logger = new Logger('rss_logger');
$httpClient = new HttpClient();
$fastFeed = new FastFeed($httpClient, $logger);
$parser = new RSSParser();
$fastFeed->pushParser($parser);
$fastFeed->addFeed('upwork', 'https://www.upwork.com/jobs/rss?cn1[]=Web%2C+Mobile+%26+Software+Dev&cn2[]=Web+Development&t[]=0&t[]=1&dur[]=0&dur[]=1&dur[]=13&dur[]=26&dur[]=none&wl[]=10&wl[]=30&wl[]=none&tba[]=0&tba[]=1-9&tba[]=10-&exp[]=1&exp[]=2&exp[]=3&amount[]=Min&amount[]=Max&sortBy=s_ctime+desc&_redirected');
$items = $fastFeed->fetch('upwork');
foreach ($items as $key => $item) {
    $itemId = $item->getId();
    $jobId = '~' . explode('?source=rss', explode('_%7E', $itemId)[1])[0];
    $specific = $profile->getSpecific($jobId);
    $info = $specific->profile;
    $created_at = date('Y-m-d H:i:s', $info->op_ctime / 1000);
    echo $jobId . '- - - -' . $created_at . '<br>';
    echo $itemId . '<br>';


}