<?php

use Bitrix\Main\Loader;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

Loader::includeModule('ui');

Toolbar::deleteFavoriteStar();

$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('CATALOG_PRODUCTCARD_FEEDBACK_TITLE'));
?>
<script id="bx24_form_inline" data-skip-moving="true">
	(function(w, d, u, b) {
		w['Bitrix24FormObject'] = b;
		w[b] = w[b] ||
			function() {
				arguments[0].ref = u;
				(w[b].forms = w[b].forms || []).push(arguments[0])
			};
		if (w[b]['forms']) return;
		var s = d.createElement('script');
		s.async = 1;
		s.src = u + '?' + (1 * new Date());
		var h = d.getElementsByTagName('script')[0];
		h.parentNode.insertBefore(s, h);
	})(window, document, 'https://landing.bitrix24.ru/bitrix/js/crm/form_loader.js', 'b24form');
</script>
<div class="catalog-productcard-limit-container">
	<div class="catalog-productcard-limit-inner" id="catalog-productcard-feedback-form">
		<script>
			BX.ready(function() {
				var options = <?=\CUtil::PhpToJSObject($arResult);?>;
				options.node = BX('catalog-productcard-feedback-form');
				b24form(options);
			});
		</script>
	</div>
</div>