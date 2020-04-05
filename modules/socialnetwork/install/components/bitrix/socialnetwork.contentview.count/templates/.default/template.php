<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if ($arResult['AVAILABLE'])
{
	CUtil::InitJSCore(array("content_view"));
}

?><span class="feed-content-view-cnt-wrap" id="feed-post-contentview-cnt-wrap-<?=htmlspecialcharsbx($arResult["CONTENT_ID"])?>"><?
	?><span class="feed-content-view-cnt<?=(!$arResult['AVAILABLE'] ? " feed-content-view-cnt-lock" : "")?>" id="feed-post-contentview-cnt-<?=htmlspecialcharsbx($arResult["CONTENT_ID"])?>"><?
	if ($arResult['AVAILABLE'])
	{
		echo (isset($arResult["CONTENT_VIEW_CNT"]) ? $arResult["CONTENT_VIEW_CNT"] : 0);
	}
	elseif (SITE_TEMPLATE_ID == 'bitrix24')
	{
		?><span class="tariff-lock" onclick="B24.licenseInfoPopup.show('contentViewCounter', '<?=htmlspecialcharsbx(GetMessageJS("SCVC_TEMPLATE_LICENSE_TITLE"))?>', '<?=htmlspecialcharsbx(GetMessageJS("SCVC_TEMPLATE_LICENSE_TEXT"))?>')"></span><?
	}
	?></span><?
?></span><?

if ($arResult['AVAILABLE'])
{
	?><span class="bx-contentview-wrap-block" id="bx-contentview-cnt-popup-cont-<?=htmlspecialcharsbx($arResult['CONTENT_ID'])?>" style="display:none;"><?
		?><span class="bx-contentview-popup-name-new contentview-name"><?
			?><?=GetMessage("SCVC_TEMPLATE_POPUP_TITLE")?><?
		?></span><?
		?><span class="bx-contentview-popup"><?
			?><span class="bx-contentview-wait"></span><?
		?></span><?
	?></span><?
	?><script>
	BX.ready(function() {
		var ratingCounter = new BX.UserContentView.Counter();
		ratingCounter.init({
			contentId : '<?=htmlspecialcharsbx($arResult["CONTENT_ID"])?>',
			nodeId : 'feed-post-contentview-cnt-<?=htmlspecialcharsbx($arResult["CONTENT_ID"])?>',
			pathToUserProfile : '<?=htmlspecialcharsbx($arResult["PATH_TO_USER_PROFILE"])?>'
		});
	});
	</script><?
}


