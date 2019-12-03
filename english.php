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
        if ($tanggal1 > $latestDate) $latestDate = $tanggal1;
    }
    //$lastBuildDate = date(DATE_RSS, $latestDate);

    //Create the RSS feed
    $xmlFeed = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:image="http://purl.org/rss/1.0/modules/image/"
	></rss>');
    $xmlFeed->addChild("channel");

    //Required elements
    $nhentai = 'https://nhentai/g/'.$item['id'];
    $web = 'https://nhentai.net';
    //$data = '<img src="https://t.nhentai.net/galleries/'.$item['media_id'].'/cover.jpg"/>';
    $xmlFeed->channel->addChild("title", 'nHentai RSS Feed by. Iqbal Rifai');
    $xmlFeed->channel->addChild("pubDate", $tanggal1);
    $xmlFeed->channel->addChild("lastBuildDate", $tanggal1);
    $xmlFeed->channel->addChild("link", $web);
    //$xmlFeed->channel->createElementNS("media:thumbnail", $gambar);
    //$xmlFeed->channel->addChild("link", $gambar);
    //$enclosure = $newItem->addChild('enclosure');
    //$enclosure['url'] = $attachment['url'];
    //$enclosure['type'] = $attachment['mime_type'];
    //$enclosure['length'] = $attachment['size_in_bytes'];
    
    //Items
    foreach ($jf['result'] as $item) {
        $newItem = $xmlFeed->channel->addChild('item');
        $a[1] = ($item["images"]["cover"]["t"] == "p") ? "png" : "jpg";
        //$tags = $item["tags"]["name"];
        $gambar = 'https://t.nhentai.net/galleries/'.$item['media_id'].'/cover.'.$a[1];
        $tanggal = gmdate('Y-m-d H:i:s', $item['upload_date']);
        //Standard stuff
        if (isset($item['id'])) $newItem->addChild('guid', $item['id']);
        if (isset($item['title']['pretty'])) $newItem->addChild('title', $item['title']['pretty']);
        if (isset($item['title']['english'])) $newItem->addChild('description', $item['title']['english']);
        //if (isset($gambar)) $newItem->createElementNS("media:thumbnail", $gambar);
        if (isset($gambar)) $newItem->addChild('image', $gambar);
        if (isset($tanggal)) $newItem->addChild('pubDate', $tanggal);
        if (isset($nhentai)) $newItem->addChild('link',  $nhentai);
    }

    //Make the output pretty
    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xmlFeed->asXML());
    return $dom->saveXML();
}


$content = @file_get_contents("http://nhtai-api.glitch.me/api/search?query=english");
header('Content-type: application/xml');
echo convert_jsonfeed_to_rss($content)."\n";
