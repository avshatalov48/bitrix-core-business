<?php
/**
 * @var $component \SaleFacebookConversion
 * @var $this \CBitrixComponentTemplate
 * @var $arResult array
 * @var $arParams array
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Extension::load(['seo.ads.login', 'ui.buttons', 'ui.switcher', 'ui.notification']);

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<div id="facebook_conversion_container"></div>
<script>
	BX.ready(function() {
		BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
		BX.SaleFacebookConversion.Instance = new BX.SaleFacebookConversion(
			'facebook_conversion_container',
			{
				eventName: '<?=CUtil::JSEscape($arResult['eventName'])?>',
				facebookBusinessParams: <?=CUtil::PhpToJSObject($arResult['facebookBusinessParams'])?>,
				shops: <?=CUtil::PhpToJSObject($arResult['shops'])?>,
				conversionDataLabelsText: <?=CUtil::PhpToJSObject($arResult['conversionDataLabelsText'])?>,
				title: '<?=CUtil::JSEscape($arResult['title'])?>',
			},
		);
	});
</script>