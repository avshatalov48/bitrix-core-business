<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

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
$containerId = 'main-user-selector-' . $arParams['ID'];
?>
<span id="<?=htmlspecialcharsbx($containerId)?>" class="main-user-selector-wrap">
	<?if ($arResult['IS_INPUT_MULTIPLE']):?>
		<?foreach ($arResult['TILE_ID_LIST'] as $id):?>
			<input type="hidden" name="<?=$arParams['INPUT_NAME']?>"
				value="<?=htmlspecialcharsbx($id)?>"
			>
		<?endforeach;?>
	<?else:?>
		<input type="hidden" id="<?=$arParams['INPUT_NAME']?>"
			name="<?=$arParams['INPUT_NAME']?>"
			value="<?=htmlspecialcharsbx(implode(',', $arResult['TILE_ID_LIST']))?>"
		>
	<?endif;?>

	<?
	$APPLICATION->IncludeComponent('bitrix:ui.tile.selector', '', array(
		'ID' => $arParams['ID'],
//		'LIST' => $arResult['LIST'],
		'SHOW_BUTTON_ADD' => false,
		'LOCK' => $arParams['LOCK'],
		'READONLY' => $arParams['READONLY'],
		'MULTIPLE' => $arResult['IS_INPUT_MULTIPLE'],
		'BUTTON_SELECT_CAPTION' => (!empty($arParams['BUTTON_SELECT_CAPTION']) ? $arParams['BUTTON_SELECT_CAPTION'] : Loc::getMessage('MAIN_USER_SELECTOR_SELECT')),
		'BUTTON_SELECT_CAPTION_MORE' => (!empty($arParams['BUTTON_SELECT_CAPTION_MORE']) ? $arParams['BUTTON_SELECT_CAPTION_MORE'] : Loc::getMessage('MAIN_USER_SELECTOR_SELECT')),
		'MANUAL_INPUT_END' => true,
		'FIRE_CLICK_EVENT' => ($arResult['FIRE_CLICK_EVENT'] ? 'BX.Main.SelectorV2:onAfterAddData' : false),
	));
	?>

	<?
	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.selector",
		".default",
		array(
			'API_VERSION' => (!empty($arParams['API_VERSION']) && intval($arParams['API_VERSION']) >= 2 ? $arParams['API_VERSION'] : 2),
			'ID' => $arParams['ID'],
			'BIND_ID' => $containerId,
			'ITEMS_SELECTED' => $arResult['ITEMS_SELECTED'],
			'ITEMS_UNDELETABLE' => $arResult['ITEMS_UNDELETABLE'],
			'CALLBACK' => array(
				'select' => 'BX.Main.User.SelectorController.select',
				'unSelect' => 'BX.Main.User.SelectorController.unSelect',
				'openDialog' => "BX.Main.User.SelectorController.openDialog",
				'closeDialog' => "BX.Main.User.SelectorController.closeDialog",
				'openSearch' => "BX.Main.User.SelectorController.openSearch",
				'closeSearch' => "BX.Main.User.SelectorController.closeSearch"
			),
			'CALLBACK_BEFORE' => (!empty($arParams["CALLBACK_BEFORE"]) && is_array($arParams["CALLBACK_BEFORE"]) ? $arParams["CALLBACK_BEFORE"] : []),
			'OPTIONS' => [
					'useNewCallback' => 'Y',
					'eventInit' => 'BX.Main.User.SelectorController::init',
					'eventOpen' => 'BX.Main.User.SelectorController::open',
				]
				+
				$arParams['SELECTOR_OPTIONS']
				+
				[
					'lazyLoad' => (
						(
							(isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] === 'Y')
							|| (
								!empty($arParams["SELECTOR_OPTIONS"])
								&& !empty($arParams["SELECTOR_OPTIONS"]['lazyload'])
								&& $arParams["SELECTOR_OPTIONS"]['lazyload'] == 'Y'
							)
						)
						&& empty($arResult['ITEMS_SELECTED'])
							? 'Y'
							: 'N'
					),
					'multiple' => ($arResult["IS_INPUT_MULTIPLE"] ? 'Y' : 'N'),
					'extranetContext' => false,
					'context' => $arParams['ID'],
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
			]
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>

	<script>
		BX.ready(function () {
			try
			{
				new BX.Main.User.Selector(<?=Json::encode(array(
					'containerId' => $containerId,
					'id' => $arParams['ID'],
					'duplicates' => false,
					'inputName' => $arParams['INPUT_NAME'],
					'isInputMultiple' => $arResult['IS_INPUT_MULTIPLE'],
					'useSymbolicId' => $arParams['USE_SYMBOLIC_ID'],
					'openDialogWhenInit' => $arParams['OPEN_DIALOG_WHEN_INIT'],
					'lazyload' => (isset($arParams['LAZYLOAD']) && $arParams['LAZYLOAD'] === 'Y' && empty($arResult['ITEMS_SELECTED']))
				))?>);
			}
			catch (e)
			{
				console.log(e.name + ': ' + e.message);
			}
		});
	</script>
</span>