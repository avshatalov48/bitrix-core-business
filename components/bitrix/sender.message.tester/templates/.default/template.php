<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

\Bitrix\Main\UI\Extension::load("sender.error_handler");
\Bitrix\Main\UI\Extension::load("bitrix24.phoneverify");

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
/** @var CAllUser $USER */

$arParams['ID'] = 'def-tester';
$containerId = 'bx-sender-message-tester-' . $arParams['ID'];

$hint = Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_HINT_'.mb_strtoupper($arResult['MESSAGE_CODE']));
$hint = $hint ?: Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_HINT_'.mb_strtoupper($arResult['TYPE_CODE']));
$hint = $hint ?: Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_HINT');

$enablePhoneVerification =
	(! (bool)$arParams['IS_PHONE_CONFIRMED'])
	&& $arParams['IS_BX24_INSTALLED']
	&& in_array($arParams['MESSAGE_CODE'], [null, \Bitrix\Sender\Transport\iBase::CODE_MAIL], true)
;
?>
<? if($arParams['CAN_EDIT']): ?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-message-tester">
	<div class="sender-message-tester-left">
		<div class="sender-message-tester-icon sender-message-tester-icon-<?=htmlspecialcharsbx($arResult['TYPE_CODE'])?>"></div>
	</div>
	<div class="sender-message-tester-right">
		<div class="sender-message-tester-control">
			<div class="sender-message-tester-control-title">
				<?=Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_RECIPIENTS')?>
				<div data-hint="<?=htmlspecialcharsbx($hint)?>"></div>
			</div>

			<?
			$APPLICATION->IncludeComponent('bitrix:sender.ui.tile.selector', '', array(
				'ID' => $arParams['ID'],
				'LIST' => $arResult['DEFAULT_RECIPIENTS'],
				'SHOW_BUTTON_ADD' => false,
				'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_SPECIFY')
			))?>
		</div>
		<div class="sender-test-container">
			<div class="sender-test-button-container">
				<div class="sender-test-button-bottom">
					<span data-role="test-button" class="ui-btn ui-btn-primary">
						<?=Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_SEND')?>
					</span>

				</div>
				<?php if($arResult['VALIDATION_TEST']):?>
					<div class="sender-test-button-bottom">
					<span data-role="test-validation-button" class="ui-btn ui-btn-light-border">
						<?=Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_VALIDATION_TEST_SEND')?>
					</span>
					</div>
				<?php endif;?>
			</div>
			<div class="result-container">
				<span data-role="test-result" class="sender-test-result-line">
					<!--
					<span class="sender-test-result-line-icon"></span>
					-->
				</span>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	BX.ready(function () {
		<?php if ($enablePhoneVerification): ?>
		BX.Bitrix24.PhoneVerify
			.setVerified(false)
			.setMandatory(false);
		<?php endif; ?>

		BX.Sender.Message.Tester.init(<?=Json::encode(array(
			'id' => $arParams['ID'],
			'containerId' => $containerId,
			'actionUri' => $arResult['ACTION_URI'],
			'messageCode' => $arResult['MESSAGE_CODE'],
			'type' => $arResult['TYPE_ID'],
			'types' => $arResult['TYPES'],
			'lastRecipients' => $arResult['LAST_RECIPIENTS'],
			'enablePhoneVerification' => $enablePhoneVerification,
			'mess' => array(
				'testSuccess' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_SUCCESS'),
				'testSuccessPhone' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_SUCCESS_PHONE'),
				'testEmpty' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_EMPTY'),
				'categoryLast' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_CAT_LAST'),
				'searchTitleMail' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_TITLE_MAIL'),
				'searchTitlePhone' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_TEST_TITLE_PHONE'),
				'consentSuccess' => Loc::getMessage('SENDER_MESSAGE_TESTER_TMPL_VALIDATION_TEST_SUCCESS'),
			)
		))?>);
	});
</script>
<? endif; ?>