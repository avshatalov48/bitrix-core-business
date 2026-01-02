<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

/**
 * @global \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'popup',
	'sidepanel',
]);

$pageTitle = '';
if ($arParams['FEEDBACK_TYPE'] === 'integration_request')
{
	$pageTitle = Loc::getMessage('CATALOG_FEEDBACK_INTEGRATION_REQUEST_TITLE');
}

if (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y")
{
	$request = Context::getCurrent()->getRequest();
	$APPLICATION->RestartBuffer();
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<script data-skip-moving="true">
				// Prevent loading page without header and footer
				if (window === window.top)
				{
					window.location = "<?=CUtil::JSEscape((new Uri($request->getRequestUri()))->deleteParams(['IFRAME', 'IFRAME_TYPE']));?>" + window.location.hash;
				}
			</script>
			<script id="bx24_form_inline" data-skip-moving="true">
				(function(w,d,u,b){w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
					(w[b].forms=w[b].forms||[]).push(arguments[0])};
					if(w[b]['forms']) return;
					var s=d.createElement('script');s.async=1;s.src=u+'?'+(1*new Date());
					var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
				})(window,document,'<?= $arResult['domain'] ?>/bitrix/js/crm/form_loader.js','CatalogFeedback');
			</script>
			<?php
			$APPLICATION->ShowHead();
			?>
		</head>
		<body class="document-limit-slider">
			<div class="pagetitle-wrap">
				<div class="pagetitle-inner-container">
					<div class="pagetitle">
						<span id="pagetitle" class="pagetitle-item"><?=$pageTitle;?></span>
					</div>
				</div>
			</div>
			<div class="document-limit-container">
<?php
}

unset($arResult['domain']);
?>
			<div class="document-limit-inner" id="catalog-feedback-form">
				<script>
					BX.ready(function()
					{
						var options = <?= Json::encode($arResult); ?>;
						options.node = BX('catalog-feedback-form');
						CatalogFeedback(options);
					});
				</script>
			</div>
		</div>
<?php
if (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y")
{
	?>
		</body>
	</html><?php
	Application::getInstance()->terminate();
}
