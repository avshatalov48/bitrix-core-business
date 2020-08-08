<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location;

Loc::loadMessages(__FILE__);
?>

<?if(!empty($arResult['ERRORS']['FATAL'])):?>

	<?foreach($arResult['ERRORS']['FATAL'] as $error):?>
		<?=ShowError($error)?>
	<?endforeach?>

<?else:?>

	<?CJSCore::Init(array("fx"));?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_widget.js')?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_etc.js')?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_pager.js')?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_combobox.js')?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_chainedselectors.js')?>

	<div id="sls-<?=$arResult['RANDOM_TAG']?>" class="bx-slst<?if($arResult['ADMIN_MODE']):?> bx-admin-mode<?endif?>">

		<?if(is_array($arResult['DEFAULT_LOCATIONS']) && !empty($arResult['DEFAULT_LOCATIONS'])):?>

			<div class="bx-ui-sls-quick-locations quick-locations">

				<?foreach($arResult['DEFAULT_LOCATIONS'] as $lid => $loc):?>
					<a href="javascript:void(0)" data-id="<?=$loc['ID']?>" class="quick-location-tag"><?=htmlspecialcharsbx($loc['NAME'])?></a>
				<?endforeach?>

			</div>

		<?endif?>
		
		<?if(is_array($arResult['TRUNK_NAMES']) && !empty($arResult['TRUNK_NAMES'])):?>
			<div class="bx-ui-sls-tree-trunk">
				<?=htmlspecialcharsbx(implode(', ', $arResult['TRUNK_NAMES']))?>
			</div>
		<?endif?>

		<input type="hidden" name="<?=$arParams['INPUT_NAME']?>" value="<?=$arResult['VALUE']?>" class="bx-ui-slst-target" />

		<div class="bx-ui-slst-pool">
		</div>

		<?if(!$arParams['SUPPRESS_ERRORS']):?>
			<div data-bx-ui-id="slst-error">
				<?if(!empty($arResult['ERRORS']['NONFATAL'])):?>

					<?foreach($arResult['ERRORS']['NONFATAL'] as $error):?>
						<?=ShowError($error)?>
					<?endforeach?>

				<?endif?>
			</div>
		<?endif?>

		<script type="text/html" data-template-id="bx-ui-slst-selector-scope">

			<div class="dropdown-block bx-ui-slst-input-block<?=($arParams['DISABLE_KEYBOARD_INPUT'] == 'Y' ? ' disabled-keyboard' : '')?>">
				<span class="dropdown-icon"></span>
				<input type="text" name="" value="" autocomplete="off" class="dropdown-field" />
				<div class="bx-ui-combobox-container" style="margin: 0px; padding: 0px; border: none; position: relative;">
					<?if($arParams['DISABLE_KEYBOARD_INPUT'] == 'Y'):?>
						<div class="bx-ui-combobox-fake bx-combobox-fake-as-input">
							<?=Loc::getMessage('SALE_SLS_SELECTOR_PROMPT')?>
						</div>
					<?else:?>
						<input type="text" value="" autocomplete="off" class="bx-ui-combobox-fake" placeholder="<?=Loc::getMessage('SALE_SLS_SELECTOR_PROMPT')?>" />
					<?endif?>
				</div>
				<div class="dropdown-fade2white"></div>
				<div class="bx-ui-combobox-loader" data-bx-ui-id="combobox-loader"></div>
				<div class="bx-ui-combobox-toggle" title="<?=Loc::getMessage('SALE_SLS_OPEN_CLOSE_POPUP')?>" data-bx-ui-id="combobox-toggle"></div>

				<div class="bx-ui-combobox-dropdown" data-bx-ui-id="combobox-dropdown">
					
					<div data-bx-ui-id="pager-pane">
					</div>
				</div>
			</div>

		</script>

		<div class="bx-ui-slst-loader"></div>
	</div>

	<script type="text/javascript">

		if (!window.BX && top.BX)
			window.BX = top.BX;

		<?if($arParams['JS_CONTROL_DEFERRED_INIT'] <> ''):?>
		if (typeof window.BX.locationsDeferred == 'undefined') window.BX.locationsDeferred = {};
		window.BX.locationsDeferred['<?=$arParams['JS_CONTROL_DEFERRED_INIT']?>'] = function () {
			<?endif?>

			<?if($arParams['JS_CONTROL_GLOBAL_ID'] <> ''):?>
			if (typeof window.BX.locationSelectors == 'undefined') window.BX.locationSelectors = {};
			window.BX.locationSelectors['<?=$arParams['JS_CONTROL_GLOBAL_ID']?>'] =
			<?endif?>

			new BX.Sale.component.location.selector.steps(<?=CUtil::PhpToJSObject(array(

				// common
				'scope' => 'sls-'.$arResult['RANDOM_TAG'],
				'source' => $this->__component->getPath().'/get.php',
				'query' => array(
					'FILTER' => array(
						'SITE_ID' => !empty($arParams['FILTER_SITE_ID']) && $arParams['FILTER_BY_SITE'] ? $arParams['FILTER_SITE_ID'] : ''
					),
					'BEHAVIOUR' => array(
						'SEARCH_BY_PRIMARY' => $arParams['SEARCH_BY_PRIMARY'] ? '1' : '0',
						'LANGUAGE_ID' => LANGUAGE_ID
					),
				),

				'selectedItem' => intval($arResult['LOCATION']['ID']),
				'knownBundles' => $arResult['PRECACHED_POOL_JSON'],
				'provideLinkBy' => $arParams['PROVIDE_LINK_BY'],

				'messages' => array(
					'notSelected' => Loc::getMessage('SALE_SLS_SELECTOR_PROMPT'),
					'error' => Loc::getMessage('SALE_SLS_ERROR_OCCURED'),
					'nothingFound' => Loc::getMessage('SALE_SLS_NOTHING_FOUND'),
					'clearSelection' => '--- '.Loc::getMessage('SALE_SLS_OTHER_CANCEL_SELECTION')
				),

				// "js logic"-related part
				'callback' => $arParams['JS_CALLBACK'],
				'useSpawn' => $arParams['USE_JS_SPAWN'] == 'Y',
				'initializeByGlobalEvent' => $arParams['INITIALIZE_BY_GLOBAL_EVENT'],
				'globalEventScope' => $arParams['GLOBAL_EVENT_SCOPE'],

				// specific
				'rootNodeValue' => intval($arResult['ROOT_NODE']),
				'showDefault' => false,

				// a trouble of BX.merge() array over object. will be fixed later, but for now as a hotfix
				'bundlesIncomplete' => array('a' => true) + (is_array($arResult['BUNDLES_INCOMPLETE']) ? $arResult['BUNDLES_INCOMPLETE'] : array()),

				'autoSelectWhenSingle' => $arParams['SELECT_WHEN_SINGLE'] != 'N',
				'types' => $arResult['TYPES'],

				// spike for sale.order.ajax
				'disableKeyboardInput' => $arParams['DISABLE_KEYBOARD_INPUT'] == 'Y',
				'dontShowNextChoice' => $arParams['DISABLE_KEYBOARD_INPUT'] == 'Y',

			), false, false, true)?>);

		<?if($arParams['JS_CONTROL_DEFERRED_INIT'] <> ''):?>
		};
		<?endif?>

	</script>

<?endif?>
