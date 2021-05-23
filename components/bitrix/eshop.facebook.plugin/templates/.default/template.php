<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
$this->setFrameMode(true);?>
<div id="fb-root"></div>
<script type="text/javascript">(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "https://connect.facebook.net/<?=GetMessage("ESHOP_FACEBOOK_PLUGIN_SET")?>/all.js#xfbml=1&version=v2.8";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<h4><?=GetMessage("ESHOP_SOCNET_TITLE")?></h4>
<div class="fb-like-box" data-href="<?=$arParams["ESHOP_FACEBOOK_LINK"]?>" data-width="<?=$arParams["ESHOP_PLUGIN_WIDTH"]?>" <?if ($arParams["ESHOP_PLUGIN_HEIGHT"]):?>data-height="<?=$arParams["ESHOP_PLUGIN_HEIGHT"]?>"<?endif?> data-show-faces="true" data-stream="false" data-header="true"></div>