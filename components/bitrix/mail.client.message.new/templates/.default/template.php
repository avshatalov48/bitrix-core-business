<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Mail\Helper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var \CMailClientMessageNewComponent $component */

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
]);

if ($arResult['TO_PLUG_EXTENSION_SALES_LETTER_TEMPLATE'])
{
	\Bitrix\Main\UI\Extension::load('mail.saleslettertemplate');
}

$this->setViewTarget('inside_pagetitle'); ?>
<div></div>
<?php
$this->endViewTarget();

$this->setViewTarget('above_pagetitle'); ?>

<div class="mail-message-new-head">
	<span class="mail-msg-title-icon mail-msg-title-icon-outcome"></span>
	<span class="mail-message-new-title-text"><?=Loc::getMessage('MAIL_NEW_MESSAGE_TITLE')?></span>
</div>

<?php

$emailsLimitToSendMessage = Helper\LicenseManager::getEmailsLimitToSendMessage();

$this->endViewTarget();

$message = $arResult['MESSAGE'];

$rcptList = [
	'users' => [],
	'emails' => $arResult['EMAILS'],
	'mailContacts' => $arResult['LAST_RCPT'],
	'companies' => [],
	'contacts' => [],
	'deals' => [],
	'leads' => [],
];
$rcptLast = [
	'users' => [],
	'emails' => [],
	'mailContacts' => array_combine(array_keys($arResult['LAST_RCPT']), array_keys($arResult['LAST_RCPT'])),
	'companies' => [],
	'contacts' => [],
	'deals' => [],
	'leads' => [],
];

$prepareReply = function($__field) use (&$message, &$rcptList, &$rcptLast) {
	$result = [];

	foreach ((array)$__field as $item)
	{
		if (!empty($item['email']))
		{
			if ('reply' == $message['__type'] && $message['__email'] == $item['email'])
			{
				continue;
			}

			//			$id = 'U'.md5($item['email']);
			//			$type = 'users';
			$id = 'MC'.$item['email'];
			$type = 'mailcontacts';

			$rcptList['emails'][$id] = $rcptList[$type][$id] = [
				'id' => $id,
				'entityId' => count($rcptList['emails']) + 1,
				'name' => $item['name'] ? : $item['email'],
				'desc' => $item['email'],
				'email' => $item['email'],
				'isEmail' => 'Y',
			];
			$rcptLast['emails'][$id] = $rcptLast[$type][$id] = $id;

			$result[$id] = $type;
		}
	}

	return $result;
};

$rcptSelected = [];
$rcptCcSelected = [];

if ('reply' == $message['__type'])
{
	$rcptSelected = $prepareReply($message['__is_outcome'] ? $message['__to'] : $message['__reply_to']);
	$rcptCcSelected = $prepareReply($message['__cc']);
}
else
{
	$rcptSelected = $prepareReply($message['__rcpt']);
}

$messageHtml = trim($message['BODY_HTML'])
	? $message['BODY_HTML']
	: preg_replace(
		'/(\s*(\r\n|\n|\r))+/',
		'<br>',
		htmlspecialcharsbx($message['BODY'])
	);

$isCrmEnabled = ($arResult['CRM_ENABLE'] === 'Y');

?>

<div class="mail-msg-view-wrapper">
	<div data-id="<?=intval($message['ID'])?>" id="mail-msg-view-details-<?=intval($message['ID'])?>">
		<?

		$formId = 'mail_msg_new_form';
		$actionUrl = '/bitrix/services/main/ajax.php?c=bitrix%3Amail.client&action=sendMessage&mode=ajax';

		?>
		<form action="<?=$actionUrl?>" method="POST" id="<?=htmlspecialcharsbx($formId)?>">
			<?=bitrix_sessid_post()?>
			<? if ('reply' == $message['__type'] && $message['__parent'] > 0): ?>
				<input type="hidden" name="data[IN_REPLY_TO]" value="<?=htmlspecialcharsbx($message['MSG_ID'])?>">
				<input type="hidden" name="data[MAILBOX_ID]" value="<?=$message['MAILBOX_ID']?>">
			<? endif ?>
			<?

			$inlineFiles = [];
			$quote = preg_replace_callback(
				'#(\?|&)__bxacid=(n?\d+)#i',
				function($matches) use (&$inlineFiles) {
					$inlineFiles[] = $matches[2];

					return $matches[0];
				},
				$messageHtml
			);
			$quote = $messageHtml;

			$attachedFiles = [];
			foreach ((array)$message['__files'] as $item)
			{
				if (preg_match('/^n\d+$/i', $item['id']))
				{
					$attachedFiles[] = $item['id'];
				}
			}

			if ('reply' == $message['__type'] && $message['__parent'] > 0)
			{
				$attachedFiles = array_intersect($attachedFiles, $inlineFiles);
			}

			$selectorParams = [
				//'pathToAjax' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?soc_net_log_dest=search_email_comms',
				'extranetUser' => false,
				'isCrmFeed' => $isCrmEnabled,
				'CrmTypes' => ['CRMCONTACT', 'CRMCOMPANY', 'CRMLEAD'],
				'enableUsers' => true,
				'useClientDatabase' => true,
				'allowSearchEmailContacts' => true,
				'allowAddUser' => true,
				'allowAddCrmContact' => false,
				'allowSearchEmailUsers' => true,
				'allowSearchCrmEmailUsers' => false,
				'allowUserSearch' => true,
				'items' => $rcptList,
				'itemsLast' => $rcptLast,
				'emailDescMode' => true,
				'searchOnlyWithEmail' => true,
			];

			$APPLICATION->includeComponent(
				'bitrix:main.mail.form',
				'',
				[
					'VERSION' => 2,
					'FORM_ID' => $formId,
					'LAYOUT_ONLY' => true,
					'SUBMIT_AJAX' => true,
					'FOLD_QUOTE' => !empty($message['MSG_ID']),
					'FOLD_FILES' => !empty($message['MSG_ID']),
					'EDITOR_TOOLBAR' => true,
					'USE_SIGNATURES' => true,
					'FIELDS' => [
						[
							'name' => 'data[from]',
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_FROM'),
							'type' => 'from',
							'value' => $message['__email'],
							'isFormatted' => true,
							'required' => true,
						],
						[
							'type' => 'separator',
						],
						[
							'name' => 'data[to]',
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_TO'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_ADD_RCPT'),
							'type' => 'rcpt',
							//'value'       => $rcptSelected,
							'selector' => array_merge(
								$selectorParams,
								['itemsSelected' => $rcptSelected]
							),
							'required' => true,
						],
						[
							'name' => 'data[cc]',
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_CC'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_ADD_RCPT'),
							'type' => 'rcpt',
							'folded' => empty($rcptCcSelected),
							//'value'       => $rcptCcSelected,
							'selector' => array_merge(
								$selectorParams,
								['itemsSelected' => $rcptCcSelected]
							),
						],
						[
							'name' => 'data[bcc]',
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_BCC'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_ADD_RCPT'),
							'type' => 'rcpt',
							'folded' => true,
							'selector' => $selectorParams,
						],
						[
							'name' => 'data[subject]',
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_SUBJECT'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_SUBJECT_PH'),
							'value' => $message['SUBJECT'],
						],
						[
							'name' => 'data[message]',
							'type' => 'editor',
							'value' => !empty($message['MSG_ID']) ? sprintf(
								'<br><br>%s, %s:<br><blockquote style="margin: 0 0 0 5px; padding: 5px 5px 5px 8px; border-left: 4px solid #e2e3e5; ">%s</blockquote>',
								formatDate(
									preg_replace(
										'/[\/.,\s:][s]/',
										'',
										$GLOBALS['DB']->dateFormatToPhp(FORMAT_DATETIME)
									),
									$message['FIELD_DATE']->getTimestamp() + \CTimeZone::getOffset(),
									time() + \CTimeZone::getOffset()
								),
								htmlspecialcharsbx(reset($message['__from'])['formated']),
								$quote
							) : '',
						],
						[
							'name' => 'data[__diskfiles]',
							'type' => 'files',
							'value' => $attachedFiles,
						],
					],
					'BUTTONS' => [
						'submit' => [
							'class' => 'ui-btn-primary',
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_SEND'),
						],
						'cancel' => [
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_CANCEL'),
						],
					],
				]
			);

			?>

		</form>

	</div>
</div>

<script type="text/javascript">

	BX.message({
		EMAILS_LIMIT_TO_SEND_MESSAGE: '<?=$emailsLimitToSendMessage?>',
		MAIL_MESSAGE_AJAX_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_AJAX_ERROR')) ?>',
		MAIL_MESSAGE_NEW_EMPTY_RCPT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_NEW_EMPTY_RCPT')) ?>',
		MAIL_MESSAGE_NEW_TARIFF_RESTRICTION: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_NEW_TARIFF_RESTRICTION', ['#COUNT#'=> $emailsLimitToSendMessage])) ?>',
		MAIL_MESSAGE_NEW_UPLOADING: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_NEW_UPLOADING')) ?>',
		MAIL_MESSAGE_MAX_SIZE: <?=Helper\Message::getMaxAttachedFilesSize()?>,
		MAIL_MESSAGE_MAX_SIZE_EXCEED: '<?=\CUtil::jsEscape(
			Loc::getMessage(
				'MAIL_MESSAGE_MAX_SIZE_EXCEED',
				['#SIZE#' => \CFile::formatSize(Helper\Message::getMaxAttachedFilesSizeAfterEncoding(),1)]
			)
		) ?>',
		MAIL_MESSAGE_SEND_SUCCESS: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_SEND_SUCCESS')) ?>',
	});

	BX.ready(function() {
		BXMailMessageController.init({
			messageId: <?=intval($message['ID']) ?>,
			type: 'edit',
			pathList: '<?=\CUtil::jsEscape(
				\CComponentEngine::makePathFromTemplate(
					$message['MAILBOX_ID'] > 0 ? $arParams['~PATH_TO_MAIL_MSG_LIST'] : $arParams['~PATH_TO_MAIL_HOME'],
					[
						'id' => $message['MAILBOX_ID'],
					]
				)
			) ?>',
		});

		new BXMailMessage({
			messageId: <?=intval($message['ID']) ?>,
			formId: '<?=\CUtil::jsEscape($formId) ?>',
		});

		var mailForm = BXMainMailForm.getForm('<?=\CUtil::jsEscape($formId) ?>');
		mailForm.init();
		<? if($arResult['SELECTED_EMAIL_CODE'] && !empty($arResult['LAST_RCPT'][$arResult['SELECTED_EMAIL_CODE']])): ?>
		mailForm.getField('data[to]').setValue({
			'<?= CUtil::JSEscape(
				$arResult['SELECTED_EMAIL_CODE']
			) ?>': 'mailContacts',
		});
		<? endif;?>
	});

</script>
