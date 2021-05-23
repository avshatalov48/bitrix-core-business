<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?='<?xml version="1.0" encoding="'.SITE_CHARSET.'"?>'?>
<rss version="2.0">
<channel>
<title><?=$arResult["NAME"]?></title>
<link><?=$arResult["URL"]?></link>
<description><?=$arResult["DESCRIPTION"]?></description>
<lastBuildDate><?=date("r")?></lastBuildDate>
<ttl><?=$arResult["RSS_TTL"]?></ttl>
<?if(is_array($arResult["PICTURE"])):?>
<image>
	<title><?=$arResult["NAME"]?></title>
	<url><?=$arResult["PICTURE"]["FILE"]["SRC"]?></url>
	<link><?=$arResult["URL"]?></link>
	<width><?=$arResult["PICTURE"]["WIDTH"]?></width>
	<height><?=$arResult["PICTURE"]["HEIGHT"]?></height>
</image>
<?endif?>
<?foreach($arResult["Events"] as $arEvent):?>
<item>
	<title><?=$arEvent["TITLE_FORMAT"]?></title>
	<link><?=$arEvent["URL"]?></link>
	<description><?=$arEvent["MESSAGE_FORMAT"]?></description>
	<pubDate><?=$arEvent["LOG_DATE"]?></pubDate>
</item>
<?endforeach?>
</channel>
</rss>
