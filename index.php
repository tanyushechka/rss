<?php
require __DIR__.'/vendor/autoload.php';
use Upwork\API;
use Upwork\API\Config;
use Upwork\API\Client;
use Upwork\API\Routers\Auth;
use Upwork\API\Routers\Jobs\Search;
use Upwork\API\Routers\Jobs\Profile;
use FastFeed\FastFeed;
use FastFeed\Parser\RSSParser;
use Guzzle\Http\Client as HttpClient;
use Monolog\Logger;
session_start();
$_SESSION['access_token'] = '6cd6202680a20796889a537ae28bb51e';
$_SESSION['access_secret']= '2d2c4ec57fd374c4';

$config = new Config(
    [
        'consumerKey'       => '0b890e685dbaa9fddaf51df47f924096',
        'consumerSecret'    => '70590fda6583b2db',
        'accessToken'       => $_SESSION['access_token'],
        'accessSecret'      => $_SESSION['access_secret'],
//        'verifySsl'         => true,
        'debug'             => false,
        'authType'          => 'OAuthPHPLib'
    ]
);

//var_dump(get_loaded_extensions());
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
    // $accessTokenInfo has the following structure
    // array('access_token' => ..., 'access_secret' => ...);
    // keeps the access token in a secure place
    // gets info of authenticated user
    $accessTokenInfo = $client->auth();
}
$auth = new Auth($client);
$info = $auth->getUserInfo();

//print_r($info);
$jobs = new Search($client);
$params = ["title" => "Web"];
$jj = $jobs->find($params);
var_dump($jj->jobs);
//$k = 9;
//var_dump($jj->jobs[$k]->id);
//var_dump($jj->jobs[$k]->url);
//var_dump($jj->jobs[$k]->date_created);
//var_dump($jj->jobs[$k]->title);
//var_dump($jj->jobs[$k]->snippet);
//var_dump($jj->jobs[$k]->job_type);
//var_dump($jj->jobs[$k]->budget);
//var_dump($jj->jobs[$k]->duration);
//var_dump($jj->jobs[$k]->workload);


$httpClient = new HttpClient();
$logger = new Logger('name');
$fastFeed = new FastFeed($httpClient, $logger);
$parser = new RSSParser();
$fastFeed->pushParser($parser);
$fastFeed->addFeed('upwork', 'https://www.upwork.com/jobs/rss?cn1[]=Web%2C+Mobile+%26+Software+Dev&cn2[]=Web+Development&t[]=0&t[]=1&dur[]=0&dur[]=1&dur[]=13&dur[]=26&dur[]=none&wl[]=10&wl[]=30&wl[]=none&tba[]=0&tba[]=1-9&tba[]=10-&exp[]=1&exp[]=2&exp[]=3&amount[]=Min&amount[]=Max&sortBy=s_ctime+desc&_redirected');
$items = $fastFeed->fetch('upwork');
foreach ($items as $key => $item) {
    var_dump($item);

}

