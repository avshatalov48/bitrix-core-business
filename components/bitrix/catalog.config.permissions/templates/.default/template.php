<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */
$this->addExternalCss('/bitrix/css/main/table/style.css');

Bitrix\Main\UI\Extension::load(['ui.accessrights.v2']);


$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-all-paddings no-background");

Loc::loadMessages(__FILE__);
$componentId    = 'bx-access-group';
$initPopupEvent = 'catalog:onComponentLoad';
$openPopupEvent = 'catalog:onComponentOpen';
$cantUse = isset($arResult['CANT_USE']);
\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
?>
<span id="<?=$componentId?>">

<div id="bx-catalog-role-main"></div>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	'',
	[
		'HIDE' => true,
		'BUTTONS' => [
			[
				'TYPE'    => 'save',
				'ONCLICK' => "AccessRights.sendActionRequest()",
			],
			[
				'TYPE' => 'custom',
				'LAYOUT' => (new Button())
					->setColor(Color::LINK)
					->setText(Loc::getMessage('CATALOG_CONFIG_CANCEL_BUTTON_ACCESS_RIGHTS'))
					->bindEvent('click', new \Bitrix\UI\Buttons\JsCode('AccessRights.fireEventReset()'))
					->render()
				,
			],
		],
	],
);
?>

<script>
	const AccessRights = new BX.UI.AccessRights.V2.App({
		renderTo: document.getElementById('bx-catalog-role-main'),
		userGroups: <?= Json::encode($arResult['USER_GROUPS']) ?>,
		accessRights: <?= Json::encode($arResult['ACCESS_RIGHTS']) ?>,
		component: 'bitrix:catalog.config.permissions',
		actionSave: 'savePermissions',
		analytics: <?= Json::encode($analytics ?? []) ?>,
		searchContainerSelector: "#uiToolbarContainer",
	});

	AccessRights.draw();
	
	BX.ready(function() {
		setTimeout(function() {
			BX.onCustomEvent('<?= $initPopupEvent ?>', [{
				openDialogWhenInit: false,
				multiple: true
			}]);

			const searchContainer = BX("uiToolbarContainer").querySelector(".ui-access-rights-v2-search");
			if (searchContainer)
			{
				BX.removeClass(searchContainer, "ui-ctl-w100");
				BX.addClass(searchContainer, "ui-ctl-w50");
			}
		});
	});
</script>
