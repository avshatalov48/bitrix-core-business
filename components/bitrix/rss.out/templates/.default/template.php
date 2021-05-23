<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(false);
?>
<?='<?xml version="1.0" encoding="'.SITE_CHARSET.'"?>'?>
<rss version="2.0"<?if($arParams["YANDEX"])
{
	echo ' xmlns="'.$arResult["PROTOCOL"].'cyber.harvard.edu/rss/rss.html" xmlns:yandex="'.$arResult["PROTOCOL"].'news.yandex.ru"';
}
?>>
<channel>
<title><?=$arResult["NAME"].($arResult["SECTION"]["NAME"] <> ''?" / ".$arResult["SECTION"]["NAME"]:"")?></title>
<link><?=CHTTP::URN2URI("", $arResult["SERVER_NAME"])?></link>
<description><?=$arResult["SECTION"]["DESCRIPTION"] <> ''?$arResult["SECTION"]["DESCRIPTION"]:$arResult["DESCRIPTION"]?></description>
<lastBuildDate><?=date("r")?></lastBuildDate>
<ttl><?=$arResult["RSS_TTL"]?></ttl>
<?if(is_array($arResult["PICTURE"])):?>
	<?$image = mb_substr($arResult["PICTURE"]["SRC"], 0, 1) == "/"? CHTTP::URN2URI($arResult["PICTURE"]["SRC"], $arResult["SERVER_NAME"]): $arResult["PICTURE"]["SRC"];?>
	<?if($arParams["YANDEX"]):?>
		<yandex:logo><?=$image?></yandex:logo>
		<?
		$squareSize = min($arResult["PICTURE"]["WIDTH"], $arResult["PICTURE"]["HEIGHT"]);
		if ($squareSize > 0)
		{
			$squarePicture = CFile::ResizeImageGet(
				$arResult["PICTURE"],
				array("width" => $squareSize, "height" => $squareSize),
				BX_RESIZE_IMAGE_EXACT
			);
			if ($squarePicture)
			{
				$squareImage = mb_substr($squarePicture["src"], 0, 1) == "/"? CHTTP::URN2URI($squarePicture["src"], $arResult["SERVER_NAME"]): $squarePicture["src"];
				?><yandex:logo type="square"><?=$squareImage?></yandex:logo><?
			}
		}
		?>
	<?else:?>

		<image>
			<title><?=$arResult["NAME"]?></title>
			<url><?=$image?></url>
			<link><?=CHTTP::URN2URI("", $arResult["SERVER_NAME"])?></link>
			<width><?=$arResult["PICTURE"]["WIDTH"]?></width>
			<height><?=$arResult["PICTURE"]["HEIGHT"]?></height>
		</image>
	<?endif?>
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
