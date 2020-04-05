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
$this->setFrameMode(true);
?>
<div class="rss-show">
<h2><?echo $arResult["title"] ?></h2>
<?
if(is_array($arResult["item"])):
foreach($arResult["item"] as $arItem):?>
	<?if(strlen($arItem["enclosure"]["url"])>0):?>
		<img src="<?=$arItem["enclosure"]["url"]?>" alt="<?=$arItem["enclosure"]["url"]?>" /><br />
	<?endif;?>
	<?if(strlen($arItem["pubDate"])>0):?>
		<p><?=CIBlockRSS::XMLDate2Dec($arItem["pubDate"], FORMAT_DATE)?></p>
	<?endif;?>
	<?if(strlen($arItem["link"])>0):?>
		<a href="<?=$arItem["link"]?>"><?=$arItem["title"]?></a>
	<?else:?>
		<?=$arItem["title"]?>
	<?endif;?>
	<p>
	<?echo $arItem["description"];?>
	</p>
	<br />
<?endforeach;
endif;?>
</div>