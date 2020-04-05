<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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

$this->setViewTarget('pagetitle_icon');

?>

<span class="mail-msg-title-icon mail-msg-title-icon-outcome"></span>
<span class="mail-msg-title-icon-placeholder ">&nbsp;</span>

<?

$this->endViewTarget();

$message = $arResult['MESSAGE'];

$rcptList = array(
	'users' => array(),
	'emails' => $arResult['EMAILS'],
	'mailContacts' => $arResult['LAST_RCPT'],
	'companies' => array(),
	'contacts' => array(),
	'deals' => array(),
	'leads' => array(),
);
$rcptLast = array(
	'users' => array(),
	'emails' => array(),
	'mailContacts' => array_combine(array_keys($arResult['LAST_RCPT']), array_keys($arResult['LAST_RCPT'])),
	'companies' => array(),
	'contacts' => array(),
	'deals' => array(),
	'leads' => array(),
);

$prepareReply = function($__field) use (&$message, &$rcptList, &$rcptLast)
{
	$result = array();

	foreach ((array) $__field as $item)
	{
		if (!empty($item['email']))
		{
			if ('reply' == $message['__type'] && $message['__email'] == $item['email'])
			{
				continue;
			}

			$id = 'U'.md5($item['email']);
			$type = 'users';

			$rcptList['emails'][$id] = $rcptList[$type][$id] = array(
				'id'         => $id,
				'entityId'   => count($rcptList['emails'])+1,
				'name'       => $item['name'] ?: $item['email'],
				'desc'       => $item['email'],
				'email'      => $item['email'],
				'isEmail'    => 'Y',
			);
			$rcptLast['emails'][$id] = $rcptLast[$type][$id] = $id;

			$result[$id] = $type;
		}
	}

	return $result;
};

$rcptSelected = array();
$rcptCcSelected = array();

if ('reply' == $message['__type'])
{
	$rcptSelected = $prepareReply($message['__is_outcome'] ? $message['__to'] : $message['__reply_to']);
	$rcptCcSelected = $prepareReply($message['__cc']);
}
else
{
	$rcptSelected = $prepareReply($message['__rcpt']);
}

$messageHtml = trim($message['BODY_HTML']) ? $message['BODY_HTML'] : preg_replace('/(\s*(\r\n|\n|\r))+/', '<br>', htmlspecialcharsbx($message['BODY']));

$isCrmEnabled = ($arResult['CRM_ENABLE'] === 'Y');

?>

<div class="mail-msg-view-wrapper">
	<div data-id="<?=intval($message['ID']) ?>" id="mail-msg-view-details-<?=intval($message['ID']) ?>">
		<?

		$formId = 'mail_msg_new_form';
		$actionUrl = '/bitrix/services/main/ajax.php?c=bitrix%3Amail.client&action=sendMessage&mode=ajax';

		?>
		<form action="<?= $actionUrl ?>" method="POST" id="<?= htmlspecialcharsbx($formId) ?>">
			<?= bitrix_sessid_post() ?>
			<? if ('reply' == $message['__type'] && $message['__parent'] > 0): ?>
				<input type="hidden" name="data[IN_REPLY_TO]" value="<?= htmlspecialcharsbx($message['MSG_ID']) ?>">
				<input type="hidden" name="data[MAILBOX_ID]" value="<?= $message['MAILBOX_ID'] ?>">
			<? endif ?>
			<?

			$inlineFiles = array();
			$quote = preg_replace_callback(
				'#(\?|&)__bxacid=(n?\d+)#i',
				function ($matches) use (&$inlineFiles)
				{
					$inlineFiles[] = $matches[2];
					return $matches[0];
				},
				$messageHtml
			);
			$quote = $messageHtml;

			$attachedFiles = array();
			foreach ((array) $message['__files'] as $item)
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

			$selectorParams = array(
				//'pathToAjax' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?soc_net_log_dest=search_email_comms',
				'extranetUser'             => false,
				'isCrmFeed'                => $isCrmEnabled,
				'CrmTypes'                 => array('CRMCONTACT', 'CRMCOMPANY', 'CRMLEAD'),
				'useClientDatabase'        => true,
				'allowSearchEmailContacts' => true,
				'allowAddUser'             => true,
				'allowAddCrmContact'       => false,
				'allowSearchEmailUsers'    => true,
				'allowSearchCrmEmailUsers' => false,
				'allowUserSearch'          => true,
				'items'                    => $rcptList,
				'itemsLast'                => $rcptLast,
				'emailDescMode'            => true,
				'searchOnlyWithEmail'      => true,
			);

			$APPLICATION->includeComponent(
				'bitrix:main.mail.form', '',
				array(
					'FORM_ID' => $formId,
					'LAYOUT_ONLY' => true,
					'SUBMIT_AJAX' => true,
					'FOLD_QUOTE' => !empty($message['MSG_ID']),
					'FOLD_FILES' => !empty($message['MSG_ID']),
					'EDITOR_TOOLBAR' => true,
					'USE_SIGNATURES' => true,
					'FIELDS' => array(
						array(
							'name'     => 'data[from]',
							'title'    => Loc::getMessage('MAIL_MESSAGE_NEW_FROM'),
							'type'     => 'from',
							'value'    => $message['__email'],
							'isFormatted' => true,
							'required' => true,
						),
						array(
							'type' => 'separator',
						),
						array(
							'name'        => 'data[to]',
							'title'       => Loc::getMessage('MAIL_MESSAGE_NEW_TO'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_ADD_RCPT'),
							'type'        => 'rcpt',
							//'value'       => $rcptSelected,
							'selector'    => array_merge(
								$selectorParams,
								array('itemsSelected' => $rcptSelected)
							),
							'required' => true,
						),
						array(
							'name'        => 'data[cc]',
							'title'       => Loc::getMessage('MAIL_MESSAGE_NEW_CC'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_ADD_RCPT'),
							'type'        => 'rcpt',
							'folded'      => empty($rcptCcSelected),
							//'value'       => $rcptCcSelected,
							'selector'    => array_merge(
								$selectorParams,
								array('itemsSelected' => $rcptCcSelected)
							),
						),
						array(
							'name'        => 'data[bcc]',
							'title'       => Loc::getMessage('MAIL_MESSAGE_NEW_BCC'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_ADD_RCPT'),
							'type'        => 'rcpt',
							'folded'      => true,
							'selector'    => $selectorParams,
						),
						array(
							'name'        => 'data[subject]',
							'title'       => Loc::getMessage('MAIL_MESSAGE_NEW_SUBJECT'),
							'placeholder' => Loc::getMessage('MAIL_MESSAGE_NEW_SUBJECT_PH'),
							'value'       => $message['SUBJECT'],
						),
						array(
							'name'   => 'data[message]',
							'type'   => 'editor',
							'value'  => !empty($message['MSG_ID']) ? sprintf(
								'<br><br>%s, %s:<br><blockquote style="margin: 0 0 0 5px; padding: 5px 5px 5px 8px; border-left: 4px solid #e2e3e5; ">%s</blockquote>',
								formatDate(
									preg_replace('/[\/.,\s:][s]/', '', $GLOBALS['DB']->dateFormatToPhp(FORMAT_DATETIME)),
									$message['FIELD_DATE']->getTimestamp() + \CTimeZone::getOffset(),
									time() + \CTimeZone::getOffset()
								),
								htmlspecialcharsbx(reset($message['__from'])['name'] ?: reset($message['__from'])['email']),
								$quote
							) : '',
						),
						array(
							'name'  => 'data[__diskfiles]',
							'type'  => 'files',
							'value' => $attachedFiles,
						),
					),
					'BUTTONS' => array(
						'submit' => array(
							'class' => 'ui-btn-primary',
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_SEND'),
						),
						'cancel' => array(
							'title' => Loc::getMessage('MAIL_MESSAGE_NEW_CANCEL'),
						),
					),
				)
			);

			?>

		</form>

	</div>
</div>

<script type="text/javascript">

BX.message({
	MAIL_MESSAGE_AJAX_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_AJAX_ERROR')) ?>',
	MAIL_MESSAGE_NEW_EMPTY_RCPT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_NEW_EMPTY_RCPT')) ?>',
	MAIL_MESSAGE_NEW_UPLOADING: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_NEW_UPLOADING')) ?>',
	MAIL_MESSAGE_SEND_SUCCESS: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_SEND_SUCCESS')) ?>'
});

BX.ready(function ()
{
	BXMailMessageController.init({
		messageId: <?=intval($message['ID']) ?>,
		type: 'edit',
		pathList: '<?=\CUtil::jsEscape(\CComponentEngine::makePathFromTemplate(
			$message['MAILBOX_ID'] > 0 ? $arParams['~PATH_TO_MAIL_MSG_LIST'] : $arParams['~PATH_TO_MAIL_HOME'],
			array(
				'id' => $message['MAILBOX_ID'],
			)
		)) ?>'
	});

	new BXMailMessage({
		messageId: <?=intval($message['ID']) ?>,
		formId: '<?=\CUtil::jsEscape($formId) ?>'
	});

	var mailForm = BXMainMailForm.getForm('<?=\CUtil::jsEscape($formId) ?>');
	mailForm.init();
	<? if($arResult['SELECTED_EMAIL_CODE'] && !empty($arResult['LAST_RCPT'][$arResult['SELECTED_EMAIL_CODE']])): ?>
	mailForm.getField('data[to]').setValue({'<?= CUtil::JSEscape($arResult['SELECTED_EMAIL_CODE']) ?>': 'mailContacts'});
	<? endif;?>
});

</script>
