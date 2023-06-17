<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @global CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

$uiFilter = isset($arParams["UI_FILTER"]) && $arParams["UI_FILTER"];
if ($uiFilter)
{
	$arParams["USE_POPUP"] = true;
}

\Bitrix\Main\UI\Extension::load('ui.design-tokens');

if(!empty($arResult['ERRORS']['FATAL'])):
	foreach($arResult['ERRORS']['FATAL'] as $error):
		ShowError($error);
	endforeach;
else:
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_autocomplete.js');
	?>
	<div id="sls-<?=$arResult['RANDOM_TAG']?>" class="bx-sls <?= ($arResult['MODE_CLASSES'] !== '' ? $arResult['MODE_CLASSES'] : ''); ?>">

		<?php
		if (!empty($arResult['DEFAULT_LOCATIONS']) && is_array($arResult['DEFAULT_LOCATIONS'])):
		?>
			<div class="bx-ui-sls-quick-locations quick-locations">
				<?php
				foreach($arResult['DEFAULT_LOCATIONS'] as $lid => $loc):
				?>
					<a href="javascript:void(0)" data-id="<?=intval($loc['ID'])?>" class="quick-location-tag"><?=htmlspecialcharsbx($loc['NAME'])?></a>
				<?php
				endforeach;
				?>
			</div>
		<?php
		endif;

		$dropDownBlock = $uiFilter ? "dropdown-block-ui" : "dropdown-block"; ?>
		<div class="<?=$dropDownBlock?> bx-ui-sls-input-block">

			<span class="dropdown-icon"></span>
			<input type="text" autocomplete="off" name="<?=$arParams['INPUT_NAME']?>" value="<?=$arResult['VALUE']?>" class="dropdown-field" placeholder="<?=Loc::getMessage('SALE_SLS_INPUT_SOME')?> ..." />

			<div class="dropdown-fade2white"></div>
			<div class="bx-ui-sls-loader"></div>
			<div class="bx-ui-sls-clear" title="<?=Loc::getMessage('SALE_SLS_CLEAR_SELECTION')?>"></div>
			<div class="bx-ui-sls-pane"></div>

		</div>

		<script type="text/html" data-template-id="bx-ui-sls-error">
			<div class="bx-ui-sls-error">
				<div></div>
				{{message}}
			</div>
		</script>

		<script type="text/html" data-template-id="bx-ui-sls-dropdown-item">
			<div class="dropdown-item bx-ui-sls-variant">
				<span class="dropdown-item-text">{{display_wrapped}}</span>
				<?php
				if($arResult['ADMIN_MODE']):?>
					[{{id}}]
				<?php
				endif;
				?>
			</div>
		</script>

		<div class="bx-ui-sls-error-message">
			<?php
			if (!isset($arParams['SUPPRESS_ERRORS']) || !$arParams['SUPPRESS_ERRORS']):
				if(!empty($arResult['ERRORS']['NONFATAL'])):
					foreach($arResult['ERRORS']['NONFATAL'] as $error):
						ShowError($error);
					endforeach;
				endif;
			endif;
			?>
		</div>

	</div>

	<script>

		if (!window.BX && top.BX)
			window.BX = top.BX;

		<?php
		if($arParams['JS_CONTROL_DEFERRED_INIT'] <> ''):
		?>
		if (typeof window.BX.locationsDeferred == 'undefined') window.BX.locationsDeferred = {};
		window.BX.locationsDeferred['<?=$arParams['JS_CONTROL_DEFERRED_INIT']?>'] = function () {
		<?php
		endif;

			if($arParams['JS_CONTROL_GLOBAL_ID'] <> ''):
			?>
			if (typeof window.BX.locationSelectors == 'undefined') window.BX.locationSelectors = {};
			window.BX.locationSelectors['<?=$arParams['JS_CONTROL_GLOBAL_ID']?>'] =
			<?php
			endif;
			?>

			new BX.Sale.component.location.selector.search(<?=CUtil::PhpToJSObject(array(

				// common
				'scope' => 'sls-'.$arResult['RANDOM_TAG'],
				'source' => $this->__component->getPath().'/get.php',
				'query' => array(
					'FILTER' => array(
						'EXCLUDE_ID' => intval($arParams['EXCLUDE_SUBTREE']),
						'SITE_ID' => $arParams['FILTER_BY_SITE'] && !empty($arParams['FILTER_SITE_ID']) ? $arParams['FILTER_SITE_ID'] : ''
					),
					'BEHAVIOUR' => array(
						'SEARCH_BY_PRIMARY' => $arParams['SEARCH_BY_PRIMARY'] ? '1' : '0',
						'LANGUAGE_ID' => LANGUAGE_ID
					),
				),

				'selectedItem' => !empty($arResult['LOCATION']) ? $arResult['LOCATION']['VALUE'] : false,
				'knownItems' => $arResult['KNOWN_ITEMS'],
				'provideLinkBy' => $arParams['PROVIDE_LINK_BY'],

				'messages' => array(
					'nothingFound' => Loc::getMessage('SALE_SLS_NOTHING_FOUND'),
					'error' => Loc::getMessage('SALE_SLS_ERROR_OCCURED'),
				),

				// "js logic"-related part
				'callback' => $arParams['JS_CALLBACK'],
				'useSpawn' => $arParams['USE_JS_SPAWN'] == 'Y',
				'usePopup' => (bool)$arParams["USE_POPUP"],
				'initializeByGlobalEvent' => $arParams['INITIALIZE_BY_GLOBAL_EVENT'],
				'globalEventScope' => $arParams['GLOBAL_EVENT_SCOPE'],

				// specific
				'pathNames' => $arResult['PATH_NAMES'], // deprecated
				'types' => $arResult['TYPES'],

			), false, false, true)?>);

		<?php
		if ($arParams['JS_CONTROL_DEFERRED_INIT'] <> ''):
		?>
		};
		<?php
		endif;
		?>

	</script>

<?php
endif;
