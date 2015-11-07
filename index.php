<?php
require __DIR__.'/vendor/autoload.php';

use FastFeed\FastFeed;
use FastFeed\Parser\RSSParser;
use Guzzle\Http\Client;
use Monolog\Logger;



$client = new Client();
$logger = new Logger('name');
$fastFeed = new FastFeed($client, $logger);
$fastFeed->pushParser(new RSSParser());
$fastFeed->addFeed('default', 'https://www.upwork.com/jobs/rss?cn1[]=Web%2C+Mobile+%26+Software+Dev&cn2[]=Web+Development&t[]=0&t[]=1&dur[]=0&dur[]=1&dur[]=13&dur[]=26&dur[]=none&wl[]=10&wl[]=30&wl[]=none&tba[]=0&tba[]=1-9&tba[]=10-&exp[]=1&exp[]=2&exp[]=3&amount[]=Min&amount[]=Max&sortBy=s_ctime+desc&_redirected');
$items = $fastFeed->fetch('default');
foreach ($items as $item) {
    echo '<p>' . $item->getName() . '</p>' . PHP_EOL;
}