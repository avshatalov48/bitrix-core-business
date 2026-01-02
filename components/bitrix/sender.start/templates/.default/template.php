<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass . " " : "") . "no-all-paddings no-background sender-start--modifier");
Extension::load(
	[
		"ui.icons",
		"ui.info-helper",
		'ui.feedback.form',
		'crm.ads.conversion',
		'ui.tour'
	]
);

$APPLICATION->IncludeComponent("bitrix:ui.tile.list", "", [
	'ID' => 'sender-start-helper',
	'LIST' => [],
]);

$containerId = 'sender-start-container';
?>
<div id="<?= htmlspecialcharsbx($containerId) ?>" class="sender-start-wrap">

	<? if (!empty($arResult['MESSAGES']['MAILING']['TILES'])): ?>
		<div class="sender-start-block">
			<div class="sender-start-title">
				<?= Loc::getMessage('SENDER_START_CREATE_LETTER') ?>
			</div>
			<? $APPLICATION->IncludeComponent("bitrix:ui.tile.list", "", [
				'ID' => 'sender-start-mailings',
				'LIST' => $arResult['MESSAGES']['MAILING']['TILES'],
			]); ?>
		</div>
	<? endif; ?>

	<? if (!empty($arResult['MESSAGES']['ADS']['TILES'])): ?>
		<div class="sender-start-block">
			<div class="sender-start-title">
				<?= Loc::getMessage('SENDER_START_CREATE_AUDIENCE') ?>
			</div>
			<? $APPLICATION->IncludeComponent("bitrix:ui.tile.list", "", [
				'ID' => 'sender-start-ads',
				'LIST' => $arResult['MESSAGES']['ADS']['TILES'],
			]); ?>
		</div>
	<? endif; ?>

	<? if (!empty($arResult['MESSAGES']['MARKETING']['TILES'])): ?>
		<div class="sender-start-block">
			<div class="sender-start-title">
				<?= Loc::getMessage('SENDER_START_CREATE_NEW_AD') ?>
			</div>
			<? $APPLICATION->IncludeComponent("bitrix:ui.tile.list", "", [
				'ID' => 'sender-start-marketing',
				'LIST' => $arResult['MESSAGES']['MARKETING']['TILES'],
			]); ?>
		</div>
	<? endif; ?>

	<? if (!empty($arResult['MESSAGES']['RC']['TILES'])): ?>
		<div class="sender-start-block">
			<div class="sender-start-title">
				<?= Loc::getMessage('SENDER_START_CREATE_RC') ?>
			</div>
			<? $APPLICATION->IncludeComponent("bitrix:ui.tile.list", "", [
				'ID' => 'sender-start-rc',
				'LIST' => $arResult['MESSAGES']['RC']['TILES'],
			]); ?>
		</div>
	<? endif; ?>


	<? if (!empty($arResult['MESSAGES']['TOLOKA']['TILES'])): ?>
	<?endif;?>

	<?if (!empty($arResult['MESSAGES']['CONVERSION']['TILES'])):?>
		<div class="sender-start-block">
			<div class="sender-start-title">
				<?=Loc::getMessage('SENDER_START_CREATE_FACEBOOK_CONVERSION')?>
			</div>
			<?$APPLICATION->IncludeComponent("bitrix:ui.tile.list", "", [
				'ID' => 'sender-start-conversion',
				'LIST' => $arResult['MESSAGES']['CONVERSION']['TILES'],
			]);?>
		</div>
	<?endif;?>

	<?if (!empty($arResult['MESSAGES']['YANDEX']['TILES'])):?>
		<div class="sender-start-block">
			<div class="sender-start-title">
				<?= Loc::getMessage('SENDER_START_CREATE_YANDEX') ?>
			</div>
			<? $APPLICATION->IncludeComponent("bitrix:ui.tile.list", "", [
				'ID' => 'sender-start-yandex',
				'LIST' => $arResult['MESSAGES']['YANDEX']['TILES'],
			]); ?>
		</div>
	<? endif; ?>

	<div class="sender-start-block">
		<div class="sender-start-title">
			<?= Loc::getMessage('SENDER_START_CONFIGURATION_HELP') ?>
		</div>
		<div class="ui-tile-list-wrap">
			<div data-role="tile/items" class="ui-tile-list-list">
				<div
					class="ui-tile-list-item sender-ui-tile-custom-list-item"
					data-forms='<?= \Bitrix\Main\Web\Json::encode($arResult['FEEDBACK_FORMS_DATA']) ?>'
					style=""
					onclick="BX.UI.Feedback.Form.open(
						{
						title:'<?= htmlspecialcharsbx(CUtil::JSescape(
						Loc::getMessage('SENDER_START_CONFIGURATION_NEED_HELP')
						)) ?>',
						forms: JSON.parse(this.dataset.forms),
						id:'sender-configuration-help',
						portalUri: '<?= htmlspecialcharsbx(CUtil::JSescape($arResult['FEEDBACK_FORM_URI'])) ?>',
						presets: {
						source: 'sender',
						},
						}
						);"
				>
			<span class="sender-ui-tile-custom-list-item-subtitle">
				<?= Loc::getMessage('SENDER_START_CONFIGURATION_HELP_ORDER') ?>
			</span>
					<button
						class="ui-btn ui-btn-primary"><?= Loc::getMessage('SENDER_START_CONFIGURATION_ORDER') ?></button>
				</div>
			</div>
		</div>
	</div>
	<script>
		BX.ready(() => {
			BX.message(<?= CUtil::phpToJsObject(Loc::loadLanguageFile(__FILE__)) ?>);
			BX.Sender.Start.init(<?= CUtil::PhpToJSObject([
				'containerId' => $containerId,
				'needShowMasterYandexInitialTour' => $arResult['SHOW_MASTER_YANDEX_INITIAL_TOUR'] ?? false,
				'masterYandexInitialTourId' => $arResult['MASTER_YANDEX_INITIAL_TOUR_ID'],
				'masterYandexInitialTourHelpdeskCode' => $arResult['MASTER_YANDEX_INITIAL_TOUR_HELPDESK_CODE'],
			]) ?>);
		});
	</script>
</div>
