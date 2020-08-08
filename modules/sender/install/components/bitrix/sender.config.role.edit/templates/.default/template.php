<?

use Bitrix\Main\Localization\Loc as Loc;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CAllMain $APPLICATION
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

	]
);
\Bitrix\Main\UI\Extension::load('loader');

//CUtil::InitJSCore(Array('access'));

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-all-paddings no-background");

Loc::loadMessages(__FILE__);
$componentId    = 'bx-access-group';
$initPopupEvent = 'sender:onComponentLoad';
$openPopupEvent = 'sender:onComponentOpen';
$cantUse = isset($arResult['CANT_USE']);
\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

if($arResult['DEAL_CATEGORIES'])
{
	$text = "";
	foreach ($arResult['DEAL_CATEGORIES'] as $dealCategory)
	{
		if($dealCategory['id'] === $arParams['ID'])
		{
			$text = $dealCategory['text'];
			break;
		}
	}
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton(
		new \Bitrix\UI\Buttons\Button(
			[
				"color" => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
				"size"  => \Bitrix\UI\Buttons\Size::MEDIUM,
				"text" => $text,
				"menu" => [
					"items" => $arResult['DEAL_CATEGORIES'],
					"maxHeight" => 300,
					"minWidth" => 100,
				],
			]
		)
	);
}
?>
<span id="<?=$componentId?>>">

<div id="bx-sender-role-main"></div>
<?php

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.selector",
	".default",
	[
		'API_VERSION'    => 2,
		'ID'             => $componentId,
		'BIND_ID'        => $componentId,
		'ITEMS_SELECTED' => [],
		'CALLBACK'       => [
			'select'      => "AccessRights.onMemberSelect",
			'unSelect'    => "AccessRights.onMemberUnselect",
			'openDialog'  => 'function(){}',
			'closeDialog' => 'function(){}',
		],
		'OPTIONS'        => [
			'eventInit'                => $initPopupEvent,
			'eventOpen'                => $openPopupEvent,
			'useContainer'             => 'Y',
			'lazyLoad'                 => 'Y',
			'context'                  => 'SENDER_PERMISSION',
			'contextCode'              => '',
			'useSearch'                => 'Y',
			'useClientDatabase'        => 'Y',
			'allowEmailInvitation'     => 'N',
			'enableAll'                => 'N',
			'enableUsers'              => 'Y',
			'enableDepartments'        => 'Y',
			'enableGroups'             => 'N',
			'departmentSelectDisable'  => 'N',
			'allowAddUser'             => 'N',
			'allowAddCrmContact'       => 'N',
			'allowAddSocNetGroup'      => 'N',
			'allowSearchEmailUsers'    => 'N',
			'allowSearchCrmEmailUsers' => 'N',
			'allowSearchNetworkUsers'  => 'N',
			'useNewCallback'           => 'Y',
			'multiple'                 => 'Y',
			'enableSonetgroups'        => 'N',
			'showVacations'            => 'Y',
		]
	],
	false,
	["HIDE_ICONS" => "Y"]
);
if($cantUse)
{
	$APPLICATION->IncludeComponent("bitrix:ui.info.helper", "", array());
	?>
	<script>
			BX.ready(function (){
				BX.UI.InfoHelper.show('limit_crm_access_permissions_crm_marketing');
			});
		</script>
	<?
}

$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
	'HIDE'    => true,
	'BUTTONS' => [
		[
			'TYPE'    => 'save',
			'ONCLICK' => $cantUse? "BX.UI.InfoHelper.show('limit_crm_access_permissions_crm_marketing')":
				"AccessRights.sendActionRequest()",

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
		renderTo: document.getElementById('bx-sender-role-main'),
		userGroups: <?= CUtil::PhpToJSObject($arResult['USER_GROUPS']) ?>,
		accessRights: <?= CUtil::PhpToJSObject($arResult['ACCESS_RIGHTS']); ?>,
		component: 'bitrix:sender.config.role.edit',
		actionSave: 'savePermissions',
		additionalSaveParams: {
			dealCategoryId: '<?= $arParams['ID'] ?>'
		},
		loadParams: {
			dealCategoryId: '<?= $arParams['ID'] ?>'
		},
		actionDelete: 'deleteRole',
		popupContainer: '<?= $componentId ?>',
		openPopupEvent: '<?= $openPopupEvent ?>'
	});

	AccessRights.draw();
	setTimeout(function() {
			BX.onCustomEvent('<?= $initPopupEvent ?>', [{
				openDialogWhenInit: false,
				multiple: true
			}]);
		},
	1);
</script>