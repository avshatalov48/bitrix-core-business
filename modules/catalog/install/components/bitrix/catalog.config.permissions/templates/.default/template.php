<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */
$this->addExternalCss('/bitrix/css/main/table/style.css');

Bitrix\Main\UI\Extension::load(
	[
		'ui.buttons',
		'ui.icons',
		'ui.notification',
		'ui.accessrights',
		'ui.selector',
		'ui',
		'ui.info-helper',
		'ui.actionpanel',
		'ui.design-tokens',

	]
);
\Bitrix\Main\UI\Extension::load('loader');


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
	"bitrix:main.ui.selector",
	".default",
	[
		'API_VERSION'    => 2,
		'ID'             => $componentId,
		'ITEMS_SELECTED' => [],
		'CALLBACK'       => [
			'select'      => "AccessRights.onMemberSelect",
			'unSelect'    => "AccessRights.onMemberUnselect",
			'openDialog'  => 'function(){}',
			'closeDialog' => 'function(){}',
		],
		'OPTIONS' => [
			'eventInit'                => $initPopupEvent,
			'eventOpen'                => $openPopupEvent,
			'useContainer'             => 'Y',
			'lazyLoad'                 => 'Y',
			'context'                  => 'CATALOG_PERMISSION',
			'contextCode'              => '',
			'useSearch'                => 'Y',
			'useClientDatabase'        => 'Y',
			'allowEmailInvitation'     => 'N',
			'enableAll'                => 'N',
			'enableUsers'              => 'Y',
			'enableDepartments'        => 'Y',
			'enableGroups'             => 'Y',
			'departmentSelectDisable'  => 'N',
			'allowAddUser'             => 'Y',
			'allowAddCrmContact'       => 'N',
			'allowAddSocNetGroup'      => 'N',
			'allowSearchEmailUsers'    => 'N',
			'allowSearchCrmEmailUsers' => 'N',
			'allowSearchNetworkUsers'  => 'N',
			'useNewCallback'           => 'Y',
			'multiple'                 => 'Y',
			'enableSonetgroups'        => 'Y',
			'showVacations'            => 'Y',
		]
	],
	false,
	["HIDE_ICONS" => "Y"]
);

$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
	'HIDE'    => true,
	'BUTTONS' => [
		[
			'TYPE'    => 'save',
			'ONCLICK' => "AccessRights.sendActionRequest()",

		],
		[
			'TYPE'    => 'cancel',
			'ONCLICK' => "AccessRights.fireEventReset()",
		],
	],
]);

?>

<script>
	var AccessRights = new BX.UI.AccessRights({
		renderTo: document.getElementById('bx-catalog-role-main'),
		userGroups: <?= CUtil::PhpToJSObject($arResult['USER_GROUPS']) ?>,
		accessRights: <?= CUtil::PhpToJSObject($arResult['ACCESS_RIGHTS']); ?>,
		component: 'bitrix:catalog.config.permissions',
		actionSave: 'savePermissions',
		actionDelete: 'deleteRole',
		popupContainer: '<?= $componentId ?>',
		openPopupEvent: '<?= $openPopupEvent ?>'
	});

	AccessRights.draw();
	
	BX.ready(function() {
		setTimeout(function() {
			BX.onCustomEvent('<?= $initPopupEvent ?>', [{
				openDialogWhenInit: false,
				multiple: true
			}]);
		});
	});
</script>
