<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Internals\PrettyDate;

Loc::loadMessages(__FILE__);

/** @var CMain $APPLICATION */
/** @var SenderLetterEditComponent $component */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-sender-toloka-edit';

\Bitrix\Main\Loader::includeModule('ui');

Extension::load(["main.ui.filter", "ui.icons", "ui.buttons", "ui.buttons.icons", "ui.notification", "sender.toloka", "ui.sidepanel-content"]);

$APPLICATION->IncludeComponent(
	"bitrix:sender.ui.panel.title",
	"",
	[
		'LIST' => [
			[
				'type' => 'buttons',
				'list' => [
					[
						'type'    => 'ui-feedback',
						'content' => [
							'ID'          => 'sender-toloka',
							'FORMS'       => [
								['zones' => ['ru', 'by', 'kz'], 'id' => '267', 'lang' => 'ru', 'sec' => 'v0qv3c'],
							],
							'VIEW_TARGET' => false,
							'PRESETS'     => []
						]
					],
					[
						'type'    => 'default',
						'id'      => 'SENDER_TOLOKA_BUTTON_CHANGE',
						'caption' => Loc::getMessage('SENDER_TOLOKA_EDIT_CHANGE_TEMPLATE'),
						'visible' => true
					]
				]
			],
		]
	]
);
\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
?>
<div data-role="login" style="display: none" class="">
	<div class="ui-slider-section ui-slider-section-icon">
		<div class="ui-icon ui-slider-icon ui-icon-service-ya-toloka">
			<i></i>
		</div>
		<div class="ui-slider-content-box">
			<div class="ui-slider-heading-3"><?=Loc::getMessage('SENDER_TOLOKA_TITLE')?></div>
			<p class="ui-slider-paragraph-2"><?=Loc::getMessage('SENDER_TOLOKA_CONNECT_DESCRIPTION')?></p>
		</div>
	</div>
	<div class="ui-slider-section sender-toloka-field-section-control">
		<div class="sender-toloka-step-text">
			<label for="toloka-oauth-code">
				<?=Loc::getMessage(
					'SENDER_TOLOKA_OAUTH_TITLE'
				)?>
			</label>
		</div>
		<input type="text"
			name="toloka-oauth-code"
			id="toloka-oauth-code"
			data-role="toloka-oauth-code"
			size="50"
			class="sender-toloka-field-control-input">

		<div class="sender-toloka-step-text">
			<input type="button"
				onclick="window.Toloka.register();"
				class="webform-small-button webform-small-button-accept"
				value="<?=Loc::getMessage('SENDER_TOLOKA_CONNECT')?>">
		</div>

	</div>
	<div class="ui-slider-section sender-toloka-field-section-control">
		<div class="sender-toloka-field-box">
			<div class="sender-toloka-field-box-subtitle-darken">
				<?=Loc::getMessage('SENDER_TOLOKA_CONNECT_INSTRUCTION')?>
			</div>
			<div class="sender-toloka-field-button-box">
				<a onclick="top.BX.Helper.show('redirect=detail&code=11572528');"
					class="sender-toloka-field-button sender-toloka-field-button-connect">
					<div class="sender-toloka-field-button-icon"></div>
					<div class="sender-toloka-field-button-text">
						<div class="sender-toloka-field-button-subtitle">
							<?=Loc::getMessage('SENDER_TOLOKA_CONNECT_HELP')?>
						</div>
						<div class="sender-toloka-field-button-name">
							<?=Loc::getMessage('SENDER_TOLOKA_CONNECT_TITLE')?>
						</div>
					</div>
				</a>
			</div>
		</div>
	</div>
</div>

<form method="post" data-role="sender-toloka-form" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>"
	enctype="multipart/form-data">
	<?=bitrix_sessid_post()?>

	<div data-role="template-selector" class="bx-sender-letter-template-selector <?=(!$arResult['SHOW_TEMPLATE_SELECTOR']
		? (!$arResult['USE_TEMPLATES'] ? '' :'bx-sender-letter-hide') : ' ')?>">
		<?
		$APPLICATION->IncludeComponent(
			"bitrix:sender.template.selector",
			"",
			[
				"MESSAGE_CODE" => "toloka",
				"IS_TRIGGER"   => "N",
				"CACHE_TIME"   => "60",
				"CACHE_TYPE"   => "N",
			]
		);
		?>
	</div>

	<div data-role="editor" class="bx-sender-letter-step-2 <?=($arResult['SHOW_TEMPLATE_SELECTOR']
		? 'bx-sender-letter-hide' : 'bx-sender-letter-show')?>">
		<input type="hidden" name="MESSAGE_CODE" value="<?=htmlspecialcharsbx($arResult['MESSAGE_CODE'])?>">
		<input type="hidden" name="MESSAGE_ID" value="<?=htmlspecialcharsbx($arResult['MESSAGE_ID'])?>">


		<input data-role="template-type" type="hidden" name="TEMPLATE_TYPE" value="<?=htmlspecialcharsbx(
			$arResult['ROW']['TEMPLATE_TYPE'] ?? ''
		)?>">
		<input data-role="template-id" type="hidden" name="TEMPLATE_ID" value="<?=htmlspecialcharsbx(
			$arResult['ROW']['TEMPLATE_ID'] ?? ''
		)?>">

		<input data-role="dispatch" data-code="METHOD_CODE" type="hidden" name="DISPATCH[METHOD_CODE]">
		<input data-role="dispatch" data-code="DAYS_OF_WEEK" type="hidden" name="DISPATCH[DAYS_OF_WEEK]">
		<input data-role="dispatch" data-code="DAYS_OF_MONTH" type="hidden" name="DISPATCH[DAYS_OF_MONTH]">
		<input data-role="dispatch" data-code="MONTHS_OF_YEAR" type="hidden" name="DISPATCH[MONTHS_OF_YEAR]">
		<input data-role="dispatch" data-code="TIMES_OF_DAY" type="hidden" name="DISPATCH[TIMES_OF_DAY]">

		<div class="bx-sender-letter-field sender-letter-edit-row"
			style="display: none;"
		>
			<div class="bx-sender-caption sender-letter-edit-title"><?=Loc::getMessage(
					'SENDER_TOLOKA_EDIT_FIELD_NAME'
				)?>:
			</div>
			<div class="bx-sender-value">
				<input data-role="title" type="text" name="TITLE" value="<?=htmlspecialcharsbx(
					$arResult['ROW']['TITLE'] ?? ''
				)?>" class="bx-sender-letter-form-control bx-sender-letter-field-input"
					<? if (!$arParams['CAN_EDIT']): ?>disabled="disabled"<? endif; ?>
				>
			</div>
		</div>

		<div class="sender-toloka-input-container">
			<?
			$APPLICATION->IncludeComponent(
				"bitrix:sender.message.editor",
				"",
				[
					"MESSAGE_CODE"  => $arResult['MESSAGE_CODE'],
					"MESSAGE_ID"    => $arResult['MESSAGE_ID'],
					"MESSAGE"       => $arResult['MESSAGE'],
					"TEMPLATE_TYPE" => $arResult['ROW']['TEMPLATE_TYPE'] ?? '',
					"TEMPLATE_ID"   => $arResult['ROW']['TEMPLATE_ID'] ?? '',
					"CAN_EDIT"      => $arParams['CAN_EDIT'],
				],
				false
			);
			?>
		</div>

		<div data-role="letter-buttons" style="<?=($arResult['SHOW_TEMPLATE_SELECTOR']? 'display: none;' : '')?>">
			<?
			$APPLICATION->IncludeComponent(
				'bitrix:ui.button.panel',
				'',
				[
					'HIDE'    => false,
					'BUTTONS' => [
						[
							'TYPE'    => 'save',
							'ONCLICK' => 'return false;',

						],
						[
							'TYPE' => 'cancel',
							'LINK' => $arParams['PATH_TO_LIST'] ?? ''
						],
					],
				]
			);
			?>
		</div>

</form>


<script>
	BX.ready(function() {
		window.Toloka = BX.Sender.Toloka.create(<?=Json::encode(
			[
				'containerId'      => 'workarea-content',
				'actionUri'        => $arResult['ACTION_URL'],
				'isAvailable'      => $arResult['IS_AVAILABLE'] ?? '',
				'isSaved'          => $arResult['IS_SAVED'] ?? '',
				'isOutside'        => $arParams['IS_OUTSIDE'] ?? '',
				'isTemplateShowed' => $arResult['SHOW_TEMPLATE_SELECTOR'] ?? '',
				'letterTile'       => $arResult['LETTER_TILE'] ?? '',
				'isRegistered'     => $arResult['IS_REGISTERED'] ?? '',
				'prettyDateFormat' => PrettyDate::getDateFormat(),
				'preset'           => json_encode(
					$arResult['ROW']['TEMPLATE']['FIELDS'] ?? []
				),
				'mess'             => [
					'patternTitle'       => Loc::getMessage('SENDER_COMP_TMPL_TOLOKA_PATTERN_TITLE'),
					'name'               => $arResult['MESSAGE_NAME'],
					'outsideSaveSuccess' => $component->getLocMessage(
						'SENDER_TOLOKA_EDIT_OUTSIDE_ADD_SUCCESS',
						['%path%' => $arParams['PATH_TO_LIST']]
					),
					'required'        => Loc::getMessage('SENDER_FIELD_REQUIRED'),
					'errorAction'        => Loc::getMessage('SENDER_TOLOKA_ERROR_ACTION'),
					'dlgBtnClose'        => Loc::getMessage('SENDER_TOLOKA_CLOSE'),
					'dlgBtnCreate'       => Loc::getMessage('SENDER_TOLOKA_CREATE'),
					'dlgBtnApply'        => Loc::getMessage('SENDER_TOLOKA_APPLY'),
					'dlgBtnCancel'       => Loc::getMessage('SENDER_TOLOKA_CANCEL_ALT'),
				]
			]
		)?>);
	});
</script>



