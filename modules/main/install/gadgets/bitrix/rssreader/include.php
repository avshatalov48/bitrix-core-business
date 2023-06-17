<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class gdRssFeeds
{
	var $title;
	var $link;
	var $description;
	var $pubDate;
	var $items = array();

}

function gdGetRss($rss_url, $cache_time = 0, $isHtml = false)
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	$cache = new CPHPCache();
	if(!$cache->StartDataCache($cache_time, 'c'.$rss_url.($isHtml ? 'y' : 'n'), "gdrss"))
	{
		$v = $cache->GetVars();
		return $v['oRss'];
	}

	$oRssFeeds = new gdRssFeeds();

	$ob = new CHTTP();
	$ob->http_timeout = 10;
	$ob->setFollowRedirect(true);
	$ob->HTTPQuery("GET", $rss_url);
	$res = $ob->result;

	if(!$res)
	{
		$cache->EndDataCache(array("oRss"=>false));
		return false;
	}

	if (preg_match("/<"."\\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\\?".">/i", $res, $matches))
	{
		$charset = trim($matches[1]);
		$res = \Bitrix\Main\Text\Encoding::convertEncoding($res, $charset, SITE_CHARSET);
	}

	$xml = new CDataXML();
	$xml->LoadString($res);

	$oNode = $xml->SelectNodes("/rss/channel/title");
	if(!$oNode)
	{
		$cache->EndDataCache(array("oRss"=>false));
		return false;
	}

	$oRssFeeds->title = $oNode->content;
	if (trim($oRssFeeds->title) == '')
	{
		if($oSubNode = $oNode->elementsByName("cdata-section"))
			$oRssFeeds->title = $oSubNode[0]->content;
	}

	if($oNode = $xml->SelectNodes("/rss/channel/link"))
		$oRssFeeds->link = $oNode->content;

	if($oNode = $xml->SelectNodes("/rss/channel/description"))
		$oRssFeeds->description = $oNode->content;
	if (trim($oRssFeeds->description) == '')
	{
		if($oNode && $oSubNode = $oNode->elementsByName("cdata-section"))
			$oRssFeeds->description = $oSubNode[0]->content;
	}

	if($oNode = $xml->SelectNodes("/rss/channel/pubDate"))
		$oRssFeeds->pubDate = $oNode->content;
	elseif($oNode = $xml->SelectNodes("/rss/channel/lastBuildDate"))
		$oRssFeeds->pubDate = $oNode->content;

	if($oNode = $xml->SelectNodes("/rss/channel"))
	{
		$oNodes = $oNode->elementsByName("item");
		foreach($oNodes as $oNode)
		{
			$item = array();

			if($oSubNode = $oNode->elementsByName("title"))
				$item["TITLE"] = $oSubNode[0]->content;
			if (trim($item["TITLE"]) == '' && !empty($oSubNode))
			{
				if($oSubNode = $oSubNode[0]->elementsByName("cdata-section"))
					$item["TITLE"] = $oSubNode[0]->content;
			}

			if($oSubNode = $oNode->elementsByName("link"))
				$item["LINK"] = $oSubNode[0]->content;

			if($oSubNode = $oNode->elementsByName("pubDate"))
				$item["PUBDATE"] = $oSubNode[0]->content;

			if($oSubNode = $oNode->elementsByName("description"))
				$item["DESCRIPTION"] = $oSubNode[0]->content;
			if (trim($item["DESCRIPTION"]) == '' && !empty($oSubNode))
			{
				if($oSubNode = $oSubNode[0]->elementsByName("cdata-section"))
					$item["DESCRIPTION"] = $oSubNode[0]->content;
			}

			if($oSubNode = $oNode->elementsByName("author"))
				$item["AUTHOR"] = $oSubNode[0]->content;
			if ((!isset($item["AUTHOR"]) || trim($item["AUTHOR"]) == '') && !empty($oSubNode))
			{
				if($oSubNode = $oSubNode[0]->elementsByName("cdata-section"))
					$item["AUTHOR"] = $oSubNode[0]->content;
			}

			$oRssFeeds->items[] = $item;
		}
	}

	$cache->EndDataCache(array("oRss"=>$oRssFeeds));

	return $oRssFeeds;
}
