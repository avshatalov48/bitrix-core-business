<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var LandingBlocksTariffsComponent $component */
/** @var \Bitrix\Landing\Landing $landing */
/** @var \CMain $APPLICATION */

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
?>

<div class="landing-block-info g-min-height-200 g-flex-centered g-height-100">
	<p class="g-landing-alert"> <?= Loc::getMessage('BLOCK_MESSAGE');?> </p>
</div>

<script class="landing-block-tariff-script" data-skip-moving="true">
	script = document.querySelector('.landing-block-tariff-script');
	option = <?= CUtil::PhpToJsObject($arParams['OPTION']) ?>;
	if (document.querySelector('.landing-block-link-1').getAttribute("href"))
	{
		option.replace.order.url.BASIC = document.querySelector('.landing-block-link-1').getAttribute("href");
	}
	if (document.querySelector('.landing-block-link-2').getAttribute("href"))
	{
		option.replace.order.url.STD = document.querySelector('.landing-block-link-2').getAttribute("href");
	}
	if (document.querySelector('.landing-block-link-3').getAttribute("href"))
	{
		option.replace.order.url.PRO = document.querySelector('.landing-block-link-3').getAttribute("href");
	}
	option = JSON.stringify(option);
	script.setAttribute('data-sb-b24-table', option);

	(function(w, d, u) {
		let s = d.createElement('script'), r = (Date.now() / 1000 | 0);
		s.async = 1;
		s.src = u + '?' + r;
		let h = d.getElementsByTagName('script')[0];
		h.parentNode.insertBefore(s, h);
	})(window, document, 'https://www.bitrix24.ru/public/js/prices/intranet/intranet.buy.table.js');

	window.onload = function() {
		BX.addCustomEvent("BX.SB.Price.Application:onAfterLoadFromHtml", function ()
		{
			let infoElement = document.querySelector('.landing-block-info');
			infoElement.hidden = true;
		});
	};
	if (document.readyState === "interactive")
	{
		BX.addCustomEvent("BX.SB.Price.Application:onAfterLoadFromHtml", function ()
		{
			let infoElement = document.querySelector('.landing-block-info');
			infoElement.hidden = true;
		});
	}
	setTimeout(function timer() {
		let table = document.querySelector('.bx-sb-b24-price-table');
		if (table !== null)
		{
			let infoElement = document.querySelector('.landing-block-info');
			infoElement.hidden = true;
		}
	}, 5000);

</script>