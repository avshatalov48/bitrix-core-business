<?php

use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var Tariff24Block $classBlock
 */

$option = $classBlock->get('OPTION');
?>

<section class="landing-block g-pt-0 g-p-20 g-pl-0 g-pr-0">
	<div class="landing-block-info g-min-height-200 g-flex-centered g-height-100">
		<p class="g-landing-alert"> MESS[BLOCK_11_3_TEXT] </p>
	</div>
	<script data-skip-moving="true" data-sb-b24-table="<?= htmlspecialcharsbx(Json::encode($option))?>">
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
</section>
