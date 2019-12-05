<?php
//Convert JSONfeed to RSS in a single function as a drop-in to make adding JSONfeed
//support to an aggregator easier
function convert_jsonfeed_to_rss($content = NULL, $max = NULL)
{
    //Test if the content is actual JSON
    json_decode($content);
    if( json_last_error() !== JSON_ERROR_NONE) return FALSE;

    //Now, is it valid JSONFeed?
    $jsonFeed = json_decode($content, TRUE);
    if (!isset($jsonFeed['result'])) return FALSE;

    //Decode the feed to a PHP array
    $jf = json_decode($content, TRUE);

    //Get the latest item publish date to use as the channel pubDate
    $latestDate = 0;
    foreach ($jf['result'] as $item) {
        $tanggal1 = gmdate('Y-m-d H:i:s', $item['upload_date']);
        if ($tanggal1> $latestDate) $latestDate = $tanggal1;
    }
    //$lastBuildDate = date(DATE_RSS, $latestDate);

    //Create the RSS feed
    $xmlFeed = new SimpleXMLElement('<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" version="2.0"></rss>');
    $xmlFeed->addChild("channel");

    //Required elements
    $nhentai = 'https://nhentai.net/g/'.$item['id'];
    $web = 'https://nhentai.net';
    //$data = '<img src="https://t.nhentai.net/galleries/'.$item['media_id'].'/cover.jpg"/>';
    $xmlFeed->channel->addChild("title", "<![CDATA[ nHentai - lastest ]]>");
    $xmlFeed->channel->addChild("link", $web);
    //$xmlFeed->channel->addChild("atom:link", 'href="http://rsshub.app/nhentai/language/english" rel="self" type="application/rss+xml"');
    $xmlFeed->channel->addChild("description", "<![CDATA[ nhentai - Made with love by Iqbal Rifai(https://github.com/py7hon/nhentai-rss ) ]]>");
    $xmlFeed->channel->addChild("generator", "nHentai RSS");
    $xmlFeed->channel->addChild("webMaster", "nhentai@rape.lol (SnowFagz)");
    $xmlFeed->channel->addChild("language", "en-us");
    $xmlFeed->channel->addChild("pubDate", $tanggal1);
    $xmlFeed->channel->addChild("ttl", "50");
    //$xmlFeed->channel->addChild("lastBuildDate", $tanggal1);
    //$xmlFeed->channel->createElementNS("media:thumbnail", $gambar);
    //$xmlFeed->channel->addChild("link", $gambar);
    //$enclosure = $newItem->addChild('enclosure');
    //$enclosure['url'] = $attachment['url'];
    //$enclosure['type'] = $attachment['mime_type'];
    //$enclosure['length'] = $attachment['size_in_bytes'];
    
    //Items
    foreach ($jf['result'] as $item) {
        $newItem = $xmlFeed->channel->addChild('item');
        $node = $newItem->addChild( 'image', Null, 'http://search.yahoo.com/mrss/' );
        $a[1] = ($item["images"]["cover"]["t"] == "p") ? "png" : "jpg";
        //$tags = $item["tags"]["name"];
        $gambar = 'https://t.nhentai.net/galleries/'.$item['media_id'].'/cover.'.$a[1];
        $srcg = '<![CDATA[ <img src="'.$gambar.'"/> ]]>';
        $tanggal = gmdate('Y-m-d H:i:s', $item['upload_date']);
        //Standard stuff
        if (isset($item['id'])) $newItem->addChild('guid', $item['id']);
        if (isset($item['title']['english'])) $newItem->addChild('title', $item['title']['english']);
        if (isset($item['title']['english'])) $newItem->addChild('description', $item['title']['english']);
        //if (isset($gambar)) $newItem->createElementNS("media:thumbnail", $gambar);
        //if (isset($gambar)) $newItem->addChild('image', $gambar);
        if (isset($tanggal)) $newItem->addChild('pubDate', $tanggal);
        if (isset($nhentai)) $newItem->addChild('link',  $nhentai);
        if (isset($gambar)) $node->addAttribute('url', $gambar);
    }

    //Make the output pretty
    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xmlFeed->asXML());
    return $dom->saveXML();
}


$content = @file_get_contents("http://nhtai-api.glitch.me/api/search");
header('Content-type: application/xml');
echo convert_jsonfeed_to_rss($content)."\n";
