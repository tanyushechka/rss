<?php
require __DIR__ . '/vendor/autoload.php';
use Upwork\API;
use Upwork\API\Config;
use Upwork\API\Client;
use Upwork\API\Routers\Jobs\Profile;
use FastFeed\FastFeed;
use FastFeed\Parser\RSSParser;
use Guzzle\Http\Client as HttpClient;
use Monolog\Logger;

$config = new Config(
    [
        'consumerKey' => '0b890e685dbaa9fddaf51df47f924096',
        'consumerSecret' => '70590fda6583b2db',
        'accessToken' => '6cd6202680a20796889a537ae28bb51e',
        'accessSecret' => '2d2c4ec57fd374c4',
        'debug' => false,
        'authType' => 'OAuthPHPLib'
    ]
);

$client = new Client($config);
$profile = new Profile($client);
$httpClient = new HttpClient();
$logger = new Logger('name');
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
    $skillsArr = [];
    if ($info->op_required_skills &&
        $info->op_required_skills->op_required_skill) {
        $skills = $info->op_required_skills->op_required_skill;
        foreach ($skills as $skill) {
            array_push($skillsArr, $skill->skill);   //todo - function for work with array
        }
    }
    $row = new stdClass;
    $row->id = $jobId;
    $row->url = $itemId;
    $row->created_at = $info->op_ctime;
    $row->title = $info->op_title;
    $row->description = $info->op_description;
    $row->type = $info->job_type;
    $row->budget = $info->amount;
    $row->engagement = $info->op_engagement;
    $row->engagement_weeks = $info->op_engagement_weeks;
    $row->contractor_tier = $info->op_contractor_tier;
    $row->skills = implode(', ', $skillsArr);
    var_dump($row);
}

