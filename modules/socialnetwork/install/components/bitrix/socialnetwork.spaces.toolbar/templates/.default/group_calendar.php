<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION \CMain
 * @var array $arResult
 */

use Bitrix\Calendar\Rooms\Util;
use Bitrix\Calendar\Ui\CalendarFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load('ui.buttons');
$messages = Loc::loadLanguageFile(__DIR__ . '/calendar.php');
const FILTER_TYPE_GROUP = 'group';
$calendarFilter = new CalendarFilter();
$filterId = $calendarFilter::getFilterId(FILTER_TYPE_GROUP, $arResult['groupId'], $arResult['userId']);
?>

<div class="sn-spaces__toolbar-space_basic">
	<div class="sn-spaces__toolbar-space_left-content">
		<div id="sn-spaces-toolbar-calendar-add-btn"></div>
		<div
			class="sn-spaces__toolbar_filter-container ui-ctl ui-ctl-textbox ui-ctl-wa ui-ctl-after-icon ui-ctl-round ui-ctl-transp-white-borderless"
			id="sn-spaces__toolbar_filter-container"
		>
			<?php
				$APPLICATION->IncludeComponent(
					"bitrix:main.ui.filter",
					"",
					[
						'THEME' => Bitrix\Main\UI\Filter\Theme::SPACES,
						"FILTER_ID" => $filterId,
						"FILTER" => $calendarFilter::getFilters(),
						"FILTER_PRESETS" => $calendarFilter::getPresets(FILTER_TYPE_GROUP),
						'ENABLE_LIVE_SEARCH' => true,
						"ENABLE_LABEL" => true,
					],
					null,
					[
						"HIDE_ICONS" => true
					]
				);
			?>
		</div>
	</div>
</div>
<div class="sn-spaces__toolbar-space_right-content">
	<div id="sn-spaces-toolbar-calendar-more-btn"></div>
</div>

<script>
	BX.ready(function() {
		BX.message(<?= Json::encode($messages) ?>);

		const calendarToolbar = new BX.Socialnetwork.Spaces.CalendarToolbar({
			type: '<?=$arResult['pageType']?>',
			locationAccess: '<?= Util::getLocationAccess($arResult['userId'])?>',
			userId: <?=$arResult['userId']?>,
			ownerId: <?=$arResult['groupId']?>,
			filterId: '<?= $filterId ?>',
			filterContainer: document.getElementById('sn-spaces__toolbar_filter-container'),
		});

		calendarToolbar.renderAddBtnTo(document.getElementById('sn-spaces-toolbar-calendar-add-btn'));
		calendarToolbar.renderSettingsBtnTo(document.getElementById('sn-spaces-toolbar-calendar-more-btn'));
	});
</script>

