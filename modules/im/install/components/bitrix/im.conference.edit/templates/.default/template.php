<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.forms",
	"ui.buttons",
	"ui.buttons.icons",
	"ui.pinner",
	"ui.alerts",
	"ui.vue",
	"ui.notifications",
	"ui.entity-selector",
	"im.lib.clipboard",
	"im.component.conference.conference-edit",
	"calendar.planner",
	"calendar.util",
	"loader",
	"date",
]);

Asset::getInstance()->addCss('/bitrix/js/calendar/planner.css');
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-background");
?>

<div id="im-conference-create-wrap">
	<?php if ($arResult['COMPONENT_ERRORS']): ?>
		<div class="ui-alert ui-alert-danger im-conference-create-component-errors">
			<span class="ui-alert-message"><?= $arResult['COMPONENT_ERRORS'][0]->getMessage() ?></span>
		</div>
	<?php return; endif; ?>
	<div id="im-conference-create-fields">

	</div>
</div>
<script type="text/javascript">
	BX.ready(function(){
		new BX.Messenger.PhpComponent.ConferenceEdit(<?=Json::encode(
			[
				'id' => $arParams['ID'],
				'errors' => $arResult['ERRORS'],
				'pathToList' => $arParams['PATH_TO_LIST'],
				'fieldsData' => $arResult['FIELDS_DATA'],
				'presenters' => $arResult['PRESENTERS'],
				'chatHost' => $arResult['CHAT_HOST'],
				'chatUsers' => $arResult['CHAT_USERS'],
				'mode' => $arResult['MODE'],
				'publicLink' => $arResult['PUBLIC_LINK'],
				'chatId' => $arResult['CHAT_ID'],
				'invitation' => $arResult['INVITATION'],
				'broadcastingEnabled' => \Bitrix\Im\Settings::isBroadcastingEnabled()
			]
		)?>);
	});
</script>