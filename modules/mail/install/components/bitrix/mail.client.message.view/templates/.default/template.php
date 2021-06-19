<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load(['ui.icons.b24']);

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view')));

$message = $arResult['MESSAGE'];

$this->setViewTarget('pagetitle_icon');

?>

<span class="mail-msg-title-icon mail-msg-title-icon-<?=($message['__is_outcome'] ? 'outcome' : 'income') ?>"></span>

<?

$this->endViewTarget();

if (SITE_TEMPLATE_ID == 'bitrix24' || $_REQUEST['IFRAME'] == 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER')
{
	$this->setViewTarget('inside_pagetitle');
}

?>

<div class="pagetitle-container mail-pagetitle-flexible-space"></div>
<div class="mail-msg-header-group">
	<? if (!empty($message['BIND_LINKS']) && !empty(@call_user_func_array('array_merge', array_values((array) $message['BIND_LINKS'])))): ?>
		<div class="mail-msg-header-control-item mail-msg-header-control-select" id="mail-msg-additional-switch">
			<div class="mail-msg-header-control-text"><?=Loc::getMessage('MAIL_MESSAGE_EXT_BLOCK_LINK') ?></div>
			<div class="mail-msg-header-control-triangle"></div>
		</div>
	<? endif ?>

	<? $APPLICATION->includeComponent(
		'bitrix:mail.message.actions',
		'',
		array(
			'MESSAGE' => $message,
			'PATH_TO_USER_TASKS_TASK' => $arParams['PATH_TO_USER_TASKS_TASK'],
		)
	); ?>
</div>

<?

if (SITE_TEMPLATE_ID == 'bitrix24' || $_REQUEST['IFRAME'] == 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER')
{
	$this->endViewTarget();
}

?>

<script type="text/javascript">

BX.ready(function ()
{
	BXMailMessageController.init({
		messageId: <?=intval($message['ID']) ?>,
		ajaxUrl: '/bitrix/services/main/ajax.php?c=<?=rawurlencode($this->__component->getName()) ?>&mode=class',
		pageSize: <?=intval($arParams['PAGE_SIZE']) ?>,
		<? if (isset($_REQUEST['mail_uf_message_token']) && is_string($_REQUEST['mail_uf_message_token'])): ?>
			mail_uf_message_token: '<?=\CUtil::jsEscape($_REQUEST['mail_uf_message_token']) ?>',
		<? endif ?>
		pathNew: '<?=\CUtil::jsEscape(\CHTTP::urlAddParams(
			$arParams['~PATH_TO_MAIL_MSG_NEW'],
			array(
				'IFRAME' => $_REQUEST['IFRAME'],
				'IFRAME_TYPE' => $_REQUEST['IFRAME_TYPE'],
			)
		)) ?>',
		pathList: '<?=\CUtil::jsEscape(\CComponentEngine::makePathFromTemplate(
			$arParams['~PATH_TO_MAIL_MSG_LIST'],
			array(
				'id' => $message['MAILBOX_ID'],
			)
		)) ?>'
	});

	BX.bind(
		BX('mail-msg-additional-switch'),
		'click',
		function ()
		{
			var block = BX('mail-msg-additional-block');

			if (block.offsetHeight > 0 && !BX.hasClass(block, 'mail-msg-close-animation'))
			{
				block.style.maxHeight = (block.offsetHeight*1.5)+'px';
				block.style.transition = 'max-height .12s ease-in';

				setTimeout(function () {
					block.style.display = 'none';
				}, 120);
				block.offsetHeight;
				block.style.maxHeight = '0px';

				BX.removeClass(block, 'mail-msg-show-animation');
				BX.addClass(block, 'mail-msg-close-animation');
			}
			else
			{
				BX.removeClass(block, 'mail-msg-close-animation');
				BX.addClass(block, 'mail-msg-show-animation');

				block.style.display = '';
				block.style.transition = '';
				block.style.maxHeight = '';
			}
		}
	);
});

</script>

<?

$renderBindLink = function ($item)
{
	return sprintf(
		'<a href="%s" class="mail-additional-item-value" onclick="%s">%s</a>',
		htmlspecialcharsbx($item['href']),
		empty($item['onclick']) ? '' : htmlspecialcharsbx($item['onclick']),
		htmlspecialcharsbx($item['title'])
	);
};

?>

<div id="mail-msg-additional-block" class="mail-additional" style="display: none; ">
	<div class="mail-additional-inner">
		<div class="mail-additional-title-block">
			<span class="mail-additional-title"><?=Loc::getMessage('MAIL_MESSAGE_EXT_BLOCK_TITLE') ?></span>
		</div>
		<div class="mail-additional-options">
			<div class="mail-additional-options-inner">
				<? foreach ((array) $message['BIND_LINKS'] as $typeTitle => $linksList): ?>
					<? if (!empty($linksList)): ?>
						<div class="mail-additional-item">
							<div class="mail-additional-item-name-block">
								<span class="mail-additional-item-name"><?=htmlspecialcharsbx($typeTitle) ?></span>
							</div>
							<div class="mail-additional-item-value-block">
								<?=join(', ', array_map($renderBindLink, (array) $linksList)) ?>
							</div>
						</div>
					<? endif ?>
				<? endforeach ?>
			</div>
		</div>
	</div>
</div>

<div class="mail-msg-view-wrapper" data-uid-key="<?= htmlspecialcharsbx($arResult['MESSAGE_UID_KEY']); ?>">

	<div class="mail-msg-view-log-separator"
		style="margin-bottom: 1px; <? if (count($arResult['LOG']['A']) < $arParams['PAGE_SIZE']): ?> display: none; <? endif ?>">
		<a class="mail-msg-view-log-more mail-msg-view-log-more-a" href="#"><?=Loc::getMessage('MAIL_MESSAGE_LOG_MORE') ?></a>
	</div>

	<?

	$list = $arResult['LOG']['A'];
	include __DIR__ . '/__log.php';

	?>

	<div style="display: none; "></div>
	<?php if (isset($arResult['iCalEvent'])): ?>
	 <div class="mail-msg-view-header-set ical-event-container">
		<div class="mail-msg-view-header-set-info">
			<div class="mail-msg-view-header-set-icon"></div>
			<div class="mail-msg-view-header-set-param">
				<div class="mail-msg-view-header-set-title">
					<?php echo Loc::getMessage('MAIL_MESSAGE_ICAL_INVITATION'), ': ', $arResult['iCalEvent']['NAME']; ?>
				</div>
				<div class="mail-msg-view-header-set-prev">
					<?php echo FormatDateFromDB($arResult['iCalEvent']['DATE_FROM'], 'D, d F Y'); ?>
				</div>
			</div>
		</div>
		<div
			class="mail-msg-view-header-set-controls ical-event-control"
			data-messageid="<?php echo intval($message['ID']) ?>"
		>
		</div>
	</div>
	<?php endif ?>
	<div class="mail-msg-view-details" data-id="<?=intval($message['ID']) ?>"
		id="mail-msg-view-details-<?=intval($message['ID']) ?>">
		<? include __DIR__ . '/__body.php'; ?>
	</div>

	<?

	$list = $arResult['LOG']['B'];
	include __DIR__ . '/__log.php';

	?>

	<div class="mail-msg-view-log-separator"
		style="margin-top: 1px; <? if (count($arResult['LOG']['B']) < $arParams['PAGE_SIZE']): ?> display: none; <? endif ?>">
		<a class="mail-msg-view-log-more mail-msg-view-log-more-b" href="#"><?=Loc::getMessage('MAIL_MESSAGE_LOG_MORE') ?></a>
	</div>

</div>

<script type="text/javascript">

<? $emailMaxSize = (int) \Bitrix\Main\Config\Option::get('main', 'max_file_size', 0); ?>

BX.message({
	MAIL_MESSAGE_AJAX_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_AJAX_ERROR')) ?>',
	MAIL_MESSAGE_NEW_EMPTY_RCPT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_NEW_EMPTY_RCPT')) ?>',
	MAIL_MESSAGE_NEW_UPLOADING: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_NEW_UPLOADING')) ?>',
	MAIL_MESSAGE_MAX_SIZE: <?=$emailMaxSize ?>,
	MAIL_MESSAGE_MAX_SIZE_EXCEED: '<?=\CUtil::jsEscape(Loc::getMessage(
		'MAIL_MESSAGE_MAX_SIZE_EXCEED',
		['#SIZE#' => \CFile::formatSize($emailMaxSize)]
	)) ?>',
	MAIL_MESSAGE_READ_CONFIRMED_SHORT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_READ_CONFIRMED_SHORT')) ?>',
	MAIL_MESSAGE_DELETE_CONFIRM: '<?=\CUtil::jsEscape(Loc::getMessage('CRM_ACT_EMAIL_DELETE_CONFIRM')) ?>',
	MAIL_MESSAGE_SPAM_CONFIRM: '<?=\CUtil::jsEscape(Loc::getMessage('CRM_ACT_EMAIL_SPAM_CONFIRM')) ?>',
	MAIL_MESSAGE_LIST_CONFIRM_DELETE: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE')) ?>',
	MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN')) ?>',
	MAIL_MESSAGE_LIST_CONFIRM_TITLE: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_TITLE')) ?>',
	MAIL_MESSAGE_LIST_CONFIRM_CANCEL_BTN: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_CANCEL_BTN')) ?>',
	MAIL_MESSAGE_SEND_SUCCESS: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_SEND_SUCCESS')) ?>',
	MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM')) ?>',
	MAIL_MESSAGE_LIST_NOTIFY_ADD_TO_CRM_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADD_TO_CRM_ERROR')) ?>',
	MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM')) ?>',
	MAIL_MESSAGE_ICAL_NOTIFY_ACCEPT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ICAL_NOTIFY_ACCEPT')) ?>',
	MAIL_MESSAGE_ICAL_NOTIFY_REJECT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ICAL_NOTIFY_REJECT')) ?>',
	MAIL_MESSAGE_ICAL_NOTIFY_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ICAL_NOTIFY_ERROR')) ?>'
});

BX.bindDelegate(document.body, 'click', {className: 'ical-event-control-button'}, function ()
{
	var messageId = this.parentNode.dataset.messageid;
	var action = this.dataset.action;
	var button = this;

	button.classList.add('ui-btn-wait');

	BX.ajax.runComponentAction('bitrix:mail.client', 'ical', {
		mode: 'ajax',
		data: {messageId, action}
	}).then(
		function ()
		{
			button.classList.remove('ui-btn-wait');
			notify(BX.message(action === 'cancelled' ? 'MAIL_MESSAGE_ICAL_NOTIFY_REJECT' : 'MAIL_MESSAGE_ICAL_NOTIFY_ACCEPT'));
		},
		function ()
		{
			button.classList.remove('ui-btn-wait');
			notify(BX.message('MAIL_MESSAGE_ICAL_NOTIFY_ERROR'));
		}
	);
});

function notify(message)
{
	top.BX.UI.Notification.Center.notify({
		autoHideDelay: 2000,
		content: message
	});
}

</script>
