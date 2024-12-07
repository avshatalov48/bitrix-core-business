<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
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
		'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_UI_TILE_SELECTOR_SELECT'),
		'MANUAL_INPUT_END' => true
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
			'API_VERSION' => 2,
			'ID' => $arParams['ID'],
			'BIND_ID' => $containerId,
			'ITEMS_SELECTED' => [],
			'CALLBACK' => array(
				'select' => 'BX.Sender.UI.UserSelectorController.select',
				'unSelect' => 'BX.Sender.UI.UserSelectorController.unSelect',
				'openDialog' => "BX.Sender.UI.UserSelectorController.openDialog",
				'closeDialog' => "BX.Sender.UI.UserSelectorController.closeDialog",
				'openSearch' => "BX.Sender.UI.UserSelectorController.openSearch",
				'closeSearch' => "BX.Sender.UI.UserSelectorController.closeSearch"
			),
			'OPTIONS' => array(
				'lazyLoad' => 'N',
				'multiple' => 'Y',
				'useNewCallback' => 'Y',
				'extranetContext' => false,
				'eventInit' => 'BX.Sender.UI.UserSelectorController::init',
				'eventOpen' => 'BX.Sender.UI.UserSelectorController::open',
				'context' => 'senderUISelector',
				'contextCode' => 'U',
				'useSearch' => 'N',
				'userNameTemplate' => CUtil::JSEscape($arParams["NAME_TEMPLATE"]),
				'useClientDatabase' => 'Y',
				'allowEmailInvitation' => 'N',
				'enableAll' => 'N',
				'enableDepartments' => 'Y',
				'enableSonetgroups' => 'N',
				'departmentSelectDisable' => 'Y',
				'allowAddUser' => 'N',
				'allowAddCrmContact' => 'N',
				'allowAddSocNetGroup' => 'N',
				'allowSearchEmailUsers' => 'N',
				'allowSearchCrmEmailUsers' => 'N',
				'allowSearchNetworkUsers' => 'N'
			)
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>

	<script>
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