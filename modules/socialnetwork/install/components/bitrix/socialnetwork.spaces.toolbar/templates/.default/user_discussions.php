<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION CMain
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponent $component
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Socialnetwork\Helper\UI\Discussions\DiscussionsFilter;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Calendar\Rooms\Util;

Extension::load([
	'ui.buttons',
	'ui.notification',
	'ui.switcher',
	'ui.popupcomponentsmaker',
	'socialnetwork.post-form',
	'calendar.entry',
]);

$messages = Loc::loadLanguageFile(__DIR__ . '/discussions.php');

Asset::getInstance()->addJS('/bitrix/components/bitrix/socialnetwork.log.filter/templates/.default/script.js');

?>

<div class="sn-spaces__toolbar-space_basic">
	<div class="sn-spaces__toolbar-space_left-content">
		<div id="sn-spaces-toolbar-discussions-add-btn"></div>
		<div
			class="sn-spaces__toolbar_filter-container ui-ctl ui-ctl-textbox ui-ctl-wa ui-ctl-after-icon ui-ctl-round ui-ctl-transp-white-borderless"
			id="sn-spaces__toolbar_filter-container"
		>
			<?php
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.filter',
				'',
				[
					'THEME' => Bitrix\Main\UI\Filter\Theme::SPACES,
					'GRID_ID' => $arResult['FILTER_ID'],
					'FILTER_ID' => $arResult['FILTER_ID'],
					'FILTER' => $arResult['FILTER'],
					'FILTER_FIELDS' => [],
					'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
					'ENABLE_LIVE_SEARCH' => true,
					'RESET_TO_DEFAULT_MODE' => false,
					'ENABLE_LABEL' => true,
					'CONFIG' => [
						'AUTOFOCUS' => false,
						'POPUP_BIND_ELEMENT_SELECTOR' => '#'.htmlspecialcharsbx($arResult['FILTER_ID']).'_filter_container_max',
						'POPUP_OFFSET_LEFT' => DiscussionsFilter::POPUP_OFFSET_LEFT,
						'DEFAULT_PRESET' => false
					]
				],
			);
			?>
		</div>
	</div>
	<div class="sn-spaces__toolbar-space_right-content">
		<div id="sn_spaces-toolbar-discussions-composition-btn"></div>
		<div id="sn-spaces-toolbar-discussions-more-btn"></div>
	</div>
</div>

<script>
	BX.ready(function(){
		oLFFilter.initFilter({
			version: 2,
			filterId: '<?= htmlspecialcharsbx($arResult['FILTER_ID']) ?>',
			minSearchStringLength: <?=CAllSQLWhere::FT_MIN_TOKEN_SIZE ?>
		});

		BX.message(<?= Json::encode($messages) ?>);

		const discussionsToolbar = new BX.Socialnetwork.Spaces.DiscussionsToolbar({
			type: '<?=$arResult['pageType']?>',
			locationAccess: '<?=Util::getLocationAccess($arResult['userId'])?>',
			userId: <?=$arResult['userId']?>,
			ownerId: <?=$arResult['userId']?>,
			spaceId: <?= $arResult['groupId'] ?>,
			spaceName: '<?= CUtil::JSescape($arResult['spaceName']) ?>',
			compositionFilters: <?= Json::encode($arResult['compositionFilters']) ?>,
			isSmartTrackingMode: '<?= $arResult['isSmartTrackingMode'] ?>',
			mainFilterId: '<?= $arResult['FILTER_ID'] ?>',
			pathToUserPage: '<?= CUtil::JSescape($arParams['PATH_TO_USER_DISCUSSIONS']) ?>',
			pathToGroupPage: '<?= CUtil::JSescape($arParams['PATH_TO_GROUP_DISCUSSIONS']) ?>',
			pathToFilesPage: '<?= CUtil::JSescape($arResult['pathToFilesPage']) ?>',
			appliedFields: '<?= Json::encode($arResult['appliedFields']) ?>',
			filterId: '<?= $arResult['FILTER_ID'] ?>',
			filterContainer: document.getElementById('sn-spaces__toolbar_filter-container'),
			isDiskStorageWasObtained: '<?= $arResult['storage'] === null ? 'N' : 'Y' ?>',
		});

		discussionsToolbar.renderAddBtnTo(document.getElementById('sn-spaces-toolbar-discussions-add-btn'));
		discussionsToolbar.renderCompositionBtnTo(document.getElementById('sn_spaces-toolbar-discussions-composition-btn'));
		discussionsToolbar.renderSettingsBtnTo(document.getElementById('sn-spaces-toolbar-discussions-more-btn'));
	});
</script>