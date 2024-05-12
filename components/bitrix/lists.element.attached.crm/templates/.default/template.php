<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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

use Bitrix\Main\Localization\Loc;

CJSCore::init(['lists', 'bp_starter']);
?>

<script>
	BX.ready(function() {
		BX.Lists['<?=$arResult['JS_OBJECT']?>'] = new BX.Lists.ListsElementAttachedCrm({
			randomString: '<?=$arResult['RAND_STRING']?>',
			jsObject: '<?=$arResult['JS_OBJECT']?>',
			entityId: '<?=$arResult['ENTITY_ID']?>',
			entityType: '<?=$arResult['ENTITY_TYPE']?>',
			singleMode: '<?=$arResult['SINGLE_MODE']?>',
			iblockId: '<?=$arResult['IBLOCK_ID']?>',
			gridPrefixId: '<?=$arResult['GRID_PREFIX_ID']?>',
			listElementTemplateUrl: <?=\Bitrix\Main\Web\Json::encode($arResult['LIST_ELEMENT_TEMPLATE_URL'])?>,
			fieldsForSetValue: <?=\Bitrix\Main\Web\Json::encode($arResult['FIELDS_FOR_SET_VALUE'])?>,
			backEndUrl: '<?=$componentPath.'/lazyload.ajax.php?&site='.SITE_ID.'&'.bitrix_sessid_get().''?>'
		});
		BX.message({
			LEACT_DELETE_POPUP_TITLE: '<?=GetMessageJS("LEACT_DELETE_POPUP_TITLE")?>',
			LEACT_DELETE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("LEACT_DELETE_POPUP_ACCEPT_BUTTON")?>',
			LEACT_DELETE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("LEACT_DELETE_POPUP_CANCEL_BUTTON")?>',
			LEACT_TOOLBAR_ELEMENT_DELETE_WARNING: '<?=GetMessageJS("LEACT_TOOLBAR_ELEMENT_DELETE_WARNING_BRIEF")?>'
		});
	});
</script>

<?if($arResult['SINGLE_MODE'] && $arResult['IBLOCK_PERMISSION'][$arResult['IBLOCK_ID']]['ADD_ELEMENT']):?>
<div class="leac-title-wrap">
	<div class="leac-title-menu">
		<div class="sidebar-buttons">
			<span id="leac-button-add-element-<?=$arResult['IBLOCK_ID']?>" class="webform-small-button webform-small-button-blue">
				<span class="webform-small-button-icon"></span>
				<span class="webform-small-button-text">
					<?=htmlspecialcharsbx($arResult['BUTTON_NAME_ELEMENT_ADD'])?>
				</span>
			</span>
		</div>
	</div>
</div>
<?endif;?>

<? foreach($arResult['GRID_ID'] as $iblockId => $gridId): ?>
	<div id="container_<?=$gridId?>">

		<?if(!$arResult['SINGLE_MODE']):?>
			<div class="leac-grid-title">
				<?=htmlspecialcharsbx($arResult['LIST_IBLOCK_NAME'][$iblockId])?>
			</div>
		<?endif;?>

		<div class="leac-grid-container">
			<?=
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.grid',
				'',
				array(
					'GRID_ID' => $gridId,
					'COLUMNS' => $arResult['GRID_HEADERS'][$iblockId],
					'ROWS' => $arResult['GRID_ROWS'][$iblockId] ?? [],
					'NAV_STRING' => $arResult['GRID_NAVIGATION'][$iblockId]['NAV_STRING'] ?? null,
					'TOTAL_ROWS_COUNT' => $arResult['GRID_NAVIGATION'][$iblockId]['TOTAL_ROWS_COUNT'] ?? 0,
					'AJAX_MODE' => 'Y',
					'AJAX_ID' => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
					'ENABLE_NEXT_PAGE' => $arResult['GRID_NAVIGATION'][$iblockId]['ENABLE_NEXT_PAGE'] ?? false,
					'PAGE_SIZES' => $arResult['GRID_NAVIGATION'][$iblockId]['PAGE_SIZES'] ?? null,
					'ACTION_PANEL' => $arResult['GRID_GROUP_ACTIONS'][$iblockId],
					'SHOW_CHECK_ALL_CHECKBOXES' => true,
					'SHOW_ROW_CHECKBOXES' => true,
					'SHOW_ROW_ACTIONS_MENU' => true,
					'SHOW_GRID_SETTINGS_MENU' => true,
					'SHOW_NAVIGATION_PANEL' => true,
					'SHOW_PAGINATION' => true,
					'SHOW_SELECTED_COUNTER' => true,
					'SHOW_TOTAL_COUNTER' => true,
					'SHOW_PAGESIZE' => true,
					'SHOW_ACTION_PANEL' => true,
					'ALLOW_COLUMNS_SORT' => true,
					'ALLOW_COLUMNS_RESIZE' => true,
					'ALLOW_HORIZONTAL_SCROLL' => true,
					'ALLOW_SORT' => true,
					'ALLOW_PIN_HEADER' => true,
					'AJAX_OPTION_JUMP' => 'N',
					"AJAX_OPTION_HISTORY" => "N"
				)
			);
			?>
		</div>
	</div>
<? endforeach; ?>