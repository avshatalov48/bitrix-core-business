<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

if ($arParams['IFRAME'] === 'Y')
{
	$APPLICATION->RestartBuffer();
	$APPLICATION->ShowHead();

}

Extension::load(
	[
		'ui.common',
		'ui.info-helper',
	]
);

Loc::loadMessages(__FILE__);

$id = md5($arParams['APP_CODE']);
$iframeId = 'app_install_helper_iframe_' . $id;
if (!empty($arResult['HELPER_DATA']['URL'])):?>
	<div
		id="app_install_helper_<?=$id?>"
		class="app-install-helper-landing"
	>
		<iframe
			id="<?=$iframeId?>"
			name="<?=$iframeId?>"
			src="<?=$arResult['HELPER_DATA']['URL']?>"
			class="app-install-helper-landing-iframe"
		></iframe>
	</div>
	<script>
		BX.ready(
			function() {
				BX.Rest.Marketplace.Install.initHelper(<?=Json::encode(
					[
						'iframeId' => $iframeId,
						'code' => $arResult['APP']['CODE'],
						'frameUrlTemplate' => $arResult['HELPER_DATA']['TEMPLATE_URL'],
					]
				)?>);
			}
		);
	</script>
<?php
endif;
if ($arParams['IFRAME'] === 'Y')
{
	CMain::FinalActions();
	die();
}