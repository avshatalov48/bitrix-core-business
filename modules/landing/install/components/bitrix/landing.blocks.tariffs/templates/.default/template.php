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
	<p class="g-landing-alert"> <?= Loc::getMessage('BLOCK_MESSAGE') ?> </p>
</div>

<div class="landing-block-table-container"></div>

<script class="landing-block-tariff-script">
	(function() {
		BX.ready(() => {
			function isValidLink(link)
			{
				const reg = /#landing\d+|#block\d+|#crmFormPopup\d+|#crmPhone\d+/i;
				return !reg.test(link);
			}

			const option = <?= CUtil::PhpToJsObject($arParams['OPTION']) ?>;
			if (option.partnerId && option.partnerId === 0)
			{
				delete option.partnerId;
			}
			option.host = window.location.host;

			const link1 = document.querySelector('.landing-block-link-1');
			if (link1)
			{
				const href1 = link1.getAttribute("href");
				if (href1 && isValidLink(href1))
				{
					option.replace.order.url.BASIC = href1;
				}
			}

			const link2 = document.querySelector('.landing-block-link-2');
			if (link2)
			{
				const href2 = link2.getAttribute("href");
				if (href2 && isValidLink(href2))
				{
					option.replace.order.url.STD = href2;
				}
			}

			const link3 = document.querySelector('.landing-block-link-3');
			if (link3)
			{
				const href3 = link3.getAttribute("href");
				if (href3 && isValidLink(href3))
				{
					option.replace.order.url.PRO = href3;
				}
			}

			const link4 = document.querySelector('.landing-block-link-4');
			if (link4)
			{
				const href4 = link4.getAttribute("href");
				if (href4 && isValidLink(href4))
				{
					option.replace.order.url.ENT = href4;
				}
			}

			//button compare tariff
			const link5 = document.querySelector('.landing-block-link-5');
			if (link5)
			{
				const href5 = link5.getAttribute("href");
				if (href5 && isValidLink(href5))
				{
					option.replace.template = {
						'message': {
							'COMPARE': {
								'BUTTON': {
									'HREF': link5.getAttribute("href"),
								},
							},
						},
					};
				}
			}

			const script = document.querySelector('.landing-block-tariff-script');
			script.setAttribute('data-sb-b24-table', JSON.stringify(option));

			function getDomainZone(zone)
			{
				switch (zone)
				{
					case 'br':
						return 'com.br';
					case 'by':
						return 'by';
					case 'co':
						return 'co';
					case 'de':
						return 'de';
					case 'en':
						return 'com';
					case 'eu':
						return 'eu';
					case 'fr':
						return 'fr';
					case 'id':
						return 'id';
					case 'in':
						return 'in';
					case 'it':
						return 'it';
					case 'jp':
						return 'jp';
					case 'kz':
						return 'kz';
					case 'la':
						return 'es';
					case 'ms':
						return 'com';
					case 'mx':
						return 'mx';
					case 'pl':
						return 'pl';
					case 'ru':
						return 'ru';
					case 'cn':
						return 'cn';
					case 'th':
						return 'com';
					case 'tr':
						return 'com.tr';
					case 'ua':
						return 'ua';
					case 'uk':
						return 'uk';
					case 'vn':
						return 'vn';
					default:
						return 'com';
				}
			}
			const url = 'https://www.bitrix24.' + getDomainZone(option['locationAreaId']) + '/public/js/prices/intranet/intranet.buy.table.js';

			(function(d, n, u) {
				let s = d.createElement('script'), r = (Date.now() / 3600000 | 0);
				s.async = 1;
				s.src = u + '?' + r;
				n.after(s);
			})(document, script, url);
			BX.addCustomEvent("BX.SB.Price.Application:onAfterLoadFromHtml", () => {
				const table = document.querySelector('.bx-sb-b24-price-table');
				if (table !== null)
				{
					const container = document.querySelector('.landing-block-table-container');
					container.append(table);

					const infoElement = document.querySelector('.landing-block-info');
					infoElement.hidden = true;
				}
			});

			BX.addCustomEvent('BX.SB.Price.Order.Button:onClick', function(event) {
				event = event || window.event;
				if (!!event.event)
				{
					event.event.preventDefault();
				}
				if (!BX.Type.isUndefined(event.code))
				{
					if (event.code === 'BASIC')
					{
						link1.click();
					}
					if (event.code === 'STD')
					{
						link2.click();
					}
					if (event.code === 'PRO100')
					{
						link3.click();
					}
					if (event.code.indexOf('ENT') === 0)
					{
						//all tariffs of the ENT line
						link4.click();
					}
				}
			});

			BX.addCustomEvent('BX.SB.Price.Compare.Button:onClick', function(event) {
				if (
					link5.getAttribute("href") !== null
					&& link5.getAttribute("href") !== 'selectActions:'
				)
				{
					event = event || window.event;
					if (!!event.event)
					{
						event.event.preventDefault();
					}
					link5.click();
				}
			});
		});

	})();
</script>