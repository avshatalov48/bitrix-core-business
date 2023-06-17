<?php
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
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\UI;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Uri;

/** @var array $arParams */
/** @var array $arResult */
/** @var string $templateFolder */
/** @var \LandingSiteTileComponent $component */

Extension::load(['sidepanel', 'main.qrcode', 'ui.dialogs.messagebox', 'marketplace', 'applayout', 'ui.fonts.opensans']);
Loader::includeModule("ui");

$isAjax = $component->isAjax();
$context = Application::getInstance()->getContext();
$request = $context->getRequest();

if(Loader::includeModule('ui'))
{
	UI\Extension::load('ui.buttons');
	UI\Extension::load('main.loader');
}
?>

<div class="landing-settings" id="landing-settings">
	<?php
	$menuItems = [];
	$pages = [];
	foreach($arResult['ITEMS'] as $code => $link)
	{
		$menuItem = [
			'NAME' => $link['name'],
			'ACTIVE' => (bool)($link['current'] ?? null),
		];

		if ($link['page'] ?? null)
		{
			$menuItem['ATTRIBUTES'] = [
				'data-page' => $link['page'],
			];
			$pages[$code] = $link;
		}
		elseif ($link['placement'])
		{
			$menuItem['ATTRIBUTES'] = [
				'href' => $link['link'] ?? null,
				'data-app-id' => $link['appId'],
				'data-placement' => $link['placement'],
				'data-placement-id' => $link['placementId'],
				'data-page' => $link['page'] ?? null,
			];
		}

		$menuItems[] = $menuItem;
	}
	?>

	<?php
	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrappermenu",
		'',
		[
			"ID" => "landing-settings-sidemenu",
			"ITEMS" => $menuItems,
		]
	);
	?>

	<div id="landing-settings-content">
		<?php
		if ($arResult['ERRORS'])
		{
			foreach ($arResult['ERRORS'] as $errorCode => $errorMessage)
			{
				$errorMessage .= $component->getSettingLinkByError(
					$errorCode
				);
				if ($arResult['FATAL'])
				{
					?>
					<div class="landing-error-page">
						<div class="landing-error-page-inner">
							<div class="landing-error-page-title"><?= $errorMessage ?></div>
							<div class="landing-error-page-img">
								<div class="landing-error-page-img-inner"></div>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}
		?>
	</div>

	<?php
	$buttonSave = [
		'TYPE' => 'save',
		'ID' => 'landing-settings-save-btn',
		'NAME' => 'submit',
	];
	$buttonCancel = [
		'TYPE' => 'cancel',
	];

	$APPLICATION->IncludeComponent(
		'bitrix:ui.button.panel',
		'',
		['BUTTONS' => [$buttonSave, $buttonCancel]]
	);
	?>

	<script>
		BX.ready(function() {
			new BX.Landing.Component.LandingSettings(
				<?= CUtil::PhpToJSObject([
					'siteId' => $arParams['SITE_ID'],
					'landingId' => $arParams['LANDING_ID'],
					'pages' => $pages,
					'menuId' => 'landing-settings-sidemenu',
					'containerId' => 'landing-settings-content',
					'saveButtonId' => 'landing-settings-save-btn',
				]) ?>,
			);
		});
	</script>

	<!-- fonts proxy-->
	<?= $component->getFontProxyUrlScript() ?>
</div>