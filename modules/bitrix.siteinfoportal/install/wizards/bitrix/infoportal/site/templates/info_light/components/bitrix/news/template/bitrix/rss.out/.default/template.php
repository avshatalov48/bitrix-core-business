<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?='<?xml version="1.0" encoding="'.SITE_CHARSET.'"?>'?>
<rss version="2.0"<?if($arParams["YANDEX"]) echo ' xmlns="http://backend.userland.com/rss2" xmlns:yandex="http://news.yandex.ru"';?>>
<channel>
<title><?=$arResult["NAME"].($arResult["SECTION"]["NAME"] <> ''?" / ".$arResult["SECTION"]["NAME"]:"")?></title>
<link><?="http://".$arResult["SERVER_NAME"]?></link>
<description><?=$arResult["SECTION"]["DESCRIPTION"] <> ''?$arResult["SECTION"]["DESCRIPTION"]:$arResult["DESCRIPTION"]?></description>
<lastBuildDate><?=date("r")?></lastBuildDate>
<ttl><?=$arResult["RSS_TTL"]?></ttl>
<?if(is_array($arResult["PICTURE"])):?>
<image>
	<title><?=$arResult["NAME"]?></title>
	<url><?="http://".$arResult["SERVER_NAME"].$arResult["PICTURE"]["SRC"]?></url>
	<link><?="http://".$arResult["SERVER_NAME"]?></link>
	<width><?=$arResult["PICTURE"]["WIDTH"]?></width>
	<height><?=$arResult["PICTURE"]["HEIGHT"]?></height>
</image>
<?endif?>
<?foreach($arResult["ITEMS"] as $arItem):?>
<item>
	<title><?=$arItem["title"]?></title>
	<link><?=$arItem["link"]?></link>
	<description><?=$arItem["description"]?></description>
	<?if(is_array($arItem["enclosure"])):?>
		<enclosure url="<?=$arItem["enclosure"]["url"]?>" length="<?=$arItem["enclosure"]["length"]?>" type="<?=$arItem["enclosure"]["type"]?>"/>
	<?endif?>
	<?if($arItem["category"]):?>
		<category><?=$arItem["category"]?></category>
	<?endif?>
	<?if($arParams["YANDEX"]):?>
		<yandex:full-text><?=$arItem["full-text"]?></yandex:full-text>
	<?endif?>
	<pubDate><?=$arItem["pubDate"]?></pubDate>
</item>
<?endforeach?>
</channel>
</rss>
