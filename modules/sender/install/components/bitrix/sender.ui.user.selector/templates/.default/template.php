<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CAllMain $APPLICATION */
/** @global CAllUser $USER */
/** @global CAllDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arParams['ID'] = $arParams['ID'] ?: 'def';
$containerId = 'sender-ui-user-selector-' . $arParams['ID'];
?>
<span id="<?=htmlspecialcharsbx($containerId)?>" class="sender-ui-user-selector-wrap">
	<?
	$APPLICATION->IncludeComponent('bitrix:sender.ui.tile.selector', '', array(
		'ID' => $arParams['ID'],
		'LIST' => $arResult['LIST'],
		'SHOW_BUTTON_ADD' => false,
		'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_UI_TILE_SELECTOR_SELECT')
	));
	?>
	<input type="hidden" id="<?=$arParams['INPUT_NAME']?>"
		name="<?=$arParams['INPUT_NAME']?>"
		value="<?=htmlspecialcharsbx(implode(',', $arResult['LIST_USER']))?>"
	>

	<?
	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.selector",
		".default",
		array(
			'ID' => $arParams['ID'],
			'BIND_ID' => $containerId,
			'ITEMS_SELECTED' => [],
			'CALLBACK' => array(
				'select' => 'BX.Sender.UI.UserSelectorController.select',
				'unSelect' => 'BX.Sender.UI.UserSelectorController.unSelect',
				'openDialog' => "BX.Sender.UI.UserSelectorController.openDialog",
				'closeDialog' => "BX.Sender.UI.UserSelectorController.closeDialog",
				'openSearch' => "BX.Sender.UI.UserSelectorController.openSearch"
			),
			'OPTIONS' => array(
				'useNewCallback' => 'Y',
				'extranetContext' => false,
				'eventInit' => 'BX.Sender.UI.UserSelectorController::init',
				'eventOpen' => 'BX.Sender.UI.UserSelectorController::open',
				'context' => $arResult['destinationContextOwner'],
				'contextCode' => 'U',
				'useSearch' => 'N',
				'userNameTemplate' => CUtil::JSEscape($arParams["NAME_TEMPLATE"]),
				'useClientDatabase' => 'Y',
				'allowEmailInvitation' => 'N',
				'enableAll' => 'N',
				'enableDepartments' => 'N',
				'enableSonetgroups' => 'N',
				'departmentSelectDisable' => 'Y',
				'allowAddUser' => 'N',
				'allowAddCrmContact' => 'N',
				'allowAddSocNetGroup' => 'N',
				'allowSearchEmailUsers' => 'N',
				'allowSearchCrmEmailUsers' => 'N',
				'allowSearchNetworkUsers' => 'N',
				'allowSonetGroupsAjaxSearchFeatures' => 'N'
			)
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>

	<script type="text/javascript">
		BX.ready(function () {
			BX.SenderUiUserSelector = new BX.Sender.UI.UserSelector(<?=Json::encode(array(
				'containerId' => $containerId,
				'id' => $arParams['ID'],
				'duplicates' => false,
				'readonly' => false,
				'actionUri' => $arResult['ACTION_URL'],
				'inputName' => $arParams['INPUT_NAME'],
			))?>);
		});
	</script>
</span>