<?php

use Bitrix\Mail\Message;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Viewer;
use Bitrix\Main\Web\Uri;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load([
	'ui.icons.b24',
	'ui.viewer',
	'ui.progressbar',
]);
\CJSCore::Init("loader");

Loc::loadMessages(__DIR__);

if (IsModuleInstalled('disk'))
{
	\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/disk/css/legacy_uf_common.css');
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
/** @var \CMailClientMessageViewComponent $component */
/** @var array $message */

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

	foreach ($__field as $item)
	{
		if (!empty($item['email']))
		{
			if ($message['__email'] == $item['email'])
			{
				continue;
			}

//			$id = 'U'.md5($item['email']);
//			$type = 'users';
			$id = 'MC'.$item['email'];
			$type = 'mailcontacts';

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

$rcptAllSelected = $prepareReply(array_merge($message['__to'], $message['__reply_to']));
$rcptSelected = $prepareReply($message['__is_outcome'] ? $message['__to'] : $message['__reply_to']);
$rcptCcSelected = $prepareReply($message['__cc']);

$datetimeFormat = \Bitrix\Main\Loader::includeModule('intranet') ? \CIntranetUtils::getCurrentDatetimeFormat() : false;
$datetimeFormatted = \CComponentUtil::getDateTimeFormatted(
	$message['FIELD_DATE']->getTimestamp()+\CTimeZone::getOffset(),
	$datetimeFormat,
	\CTimeZone::getOffset()
);
$readDatetimeFormatted = !empty($message['READ_CONFIRMED']) && $message['READ_CONFIRMED']
	? \CComponentUtil::getDateTimeFormatted(
		$message['READ_CONFIRMED']->getTimestamp()+\CTimeZone::getOffset(),
		$datetimeFormat,
		\CTimeZone::getOffset()
	) : null;


$isCrmEnabled = ($arResult['CRM_ENABLE'] === 'Y');

$isAjaxBody = isset($message['IS_AJAX_BODY_SANITIZE']) && $message['IS_AJAX_BODY_SANITIZE'];
$messageId = (int)$message['ID'];
$messageControlElementId = "mail-msg-view-control-$messageId";
$bodyElementId = "mail_msg_{$messageId}_body";
$fastReplyElementId = "mail-msg-fast-reply-$messageId";
$bodyLoaderElementId = "mail-msg-body-loader-$messageId";
$bodyDownloadLink = UrlManager::getInstance()
	->createByBitrixComponent($this->getComponent(), 'downloadHtmlBody', ['id' => $messageId]);
$warningWaitElementId = "mail-msg-warning-top-wait-$messageId";
$warningFailElementId = "mail-msg-warning-top-fail-$messageId";
$bodyLoaderMaxTime = ini_get('max_execution_time') ?: 60;
$fileRefreshButtonId = "mail_msg_{$messageId}_refresh_files_button";

?>
<div class="mail-msg-view-border-bottom">
	<div class="mail-msg-view-header <? if ($arParams['LOADED_FROM_LOG'] == 'Y'): ?> mail-msg-view-header-clickable mail-msg-view-item-open<? endif ?>">
		<span class="mail-msg-view-header-userpic">
			<? $APPLICATION->includeComponent(
				'bitrix:mail.contact.avatar',
				'',
				array_merge(
					!empty($arResult['avatarParams'][$message['SENDER_EMAIL']]) ? $arResult['avatarParams'][$message['SENDER_EMAIL']] : array(),
					array(
						'avatarSize' => 40,
					)
				),
				null,
				array(
					'HIDE_ICONS' => 'Y',
				)
			); ?>
		</span>
		<span class="mail-msg-view-header-info">
			<span class="mail-msg-view-sender-block">
				<div class="mail-msg-view-sender">
					<? $__from = reset($message['__from']); ?>
					<a class="mail-msg-view-sender-name js-mailto-link" href="mailto:<?=htmlspecialcharsbx($__from['email']) ?>"
						<? if ($__from['name']): ?>title="<?=htmlspecialcharsbx($__from['email']) ?>"<? endif ?>><?
						echo htmlspecialcharsbx($__from['name'] ?: $__from['email']);
					?></a>
					<? if (!empty($__from['name']) && !empty($__from['email']) && $__from['name'] != $__from['email']): ?>
						<a class="mail-msg-view-sender-email js-mailto-link" href="mailto:<?=htmlspecialcharsbx($__from['email']) ?>"><?
							echo htmlspecialcharsbx($__from['email']);
						?></a>
					<? endif ?>
				</div>
				<div class="mail-msg-view-date <? if ($arParams['LOADED_FROM_LOG'] == 'Y'): ?> mail-msg-view-arrow<? endif ?>">
					<span>
						<?=Loc::getMessage(
							$message['__is_outcome'] ? 'MAIL_MESSAGE_SENT' : 'MAIL_MESSAGE_RECEIVED',
							array('#DATETIME#' => $datetimeFormatted)
						) ?><!--
						--><? if ($message['__is_outcome'] && $message['OPTIONS']['trackable']): ?>,
							<span class="read-confirmed-datetime">
								<? if (!empty($readDatetimeFormatted)): ?>
									<?=Loc::getMessage('MAIL_MESSAGE_READ_CONFIRMED', array('#DATETIME#' => $readDatetimeFormatted)) ?>
								<? else: ?>
									<?=Loc::getMessage('MAIL_MESSAGE_READ_AWAITING') ?>
								<? endif ?>
							</span>
						<? endif ?>
					</span>
				</div>
			</span>
			<div class="mail-msg-view-rcpt-wrapper">
				<?

				$prepareRcpt = function($field) use (&$message, &$arResult)
				{
					$result = array();

					foreach (\Bitrix\Mail\Helper\Message::parseAddressList($field) as $item)
					{
						$address = new \Bitrix\Main\Mail\Address($item);
						$avatarParams = $address->validate() && !empty($arResult['avatarParams'][trim($address->getEmail())]) ? $arResult['avatarParams'][trim($address->getEmail())] : ['avatarSize' => 23];
						$result[] = array(
							'URL' => $address->validate() ? sprintf('mailto:%s', $address->getEmail()) : null,
							'TITLE' => $address->validate() ? $address->getEmail() : $item,
							'AVATAR_PARAMS' => $avatarParams,
							'HREF_TITLE' => $avatarParams['mailContact']['NAME'],
							'IMAGE' => $address->getEmail() == $message['__email'] ? $arResult['USER_IMAGE'] : '',
						);
					}

					return $result;
				};

				$rcpt = array(
					Loc::getMessage('MAIL_MESSAGE_RCPT') => $prepareRcpt($message['FIELD_TO']),
					Loc::getMessage('MAIL_MESSAGE_RCPT_CC') => $prepareRcpt($message['FIELD_CC']),
					Loc::getMessage('MAIL_MESSAGE_RCPT_BCC') => $prepareRcpt($message['FIELD_BCC']),
				);

				?>
				<? $k = 0; ?>
				<? foreach ($rcpt as $type => $list): ?>
					<? if (!empty($list)): ?>
						<? $count = count($list); ?>
						<? $limit = $count > ($k > 0 ? 2 : 4) ? ($k > 0 ? 1 : 3) : $count; ?>
						<span style="display: flex; align-items: center; margin-right: 5px; ">
							<span class="mail-msg-view-rcpt-list" <? if ($k > 0): ?> style="color: #000; "<? endif ?>><?=$type ?>:</span>
							<? foreach ($list as $item): ?>
								<? if ($limit == 0): ?>
									<a class="mail-msg-view-rcpt-more mail-msg-fake-link" href="#">
										<?=Loc::getMessage('MAIL_MESSAGE_RCPT_MORE', array('#NUM#' => $count)) ?>
									</a>
									<span class="mail-msg-view-rcpt-list-hidden">
								<? endif ?>
								<span class="mail-msg-view-rcpt-block mail-msg-list-cell-flex">
									<?
										$params = $item['AVATAR_PARAMS'];
										// for using initials from DB, not from message field
										if (isset($params['mailContact']))
										{
											unset($params['name'], $params['email']);
										}
										$APPLICATION->includeComponent(
											'bitrix:mail.contact.avatar',
											'',
											$params,
											null,
											array(
												'HIDE_ICONS' => 'Y',
											)
										);
									?>
									<? if ($item['URL']): ?>
										<a class="mail-msg-view-rcpt-link js-mailto-link"
											href="<?=htmlspecialcharsbx($item['URL']) ?>"
											title="<?= htmlspecialcharsbx($item['HREF_TITLE']); ?>"
											target="_blank"><?=htmlspecialcharsbx($item['TITLE']) ?></a>
									<? else: ?>
										<span class="mail-msg-view-rcpt"><?=htmlspecialcharsbx($item['TITLE']) ?></span>
									<? endif ?>
								</span>
								<? $count--; $limit--; ?>
							<? endforeach ?>
							<? if ($limit < -1): ?></span><? endif ?>
						</span>
						<? $k++; ?>
					<? endif ?>
				<? endforeach ?>
			</div>
		</span>
	</div>

	<? if (!$message['hideMailControlPanel']): ?>
		<div class="mail-msg-view-control-wrapper"
			 id="<?= htmlspecialcharsbx($messageControlElementId) ?>"
			<?php if($isAjaxBody): ?> style="display:none" <?php endif; ?>>
			<div class="mail-msg-view-control-block">
				<div class="mail-msg-view-control mail-msg-view-control-reply js-msg-view-control-reply"><?=Loc::getMessage('MAIL_MESSAGE_BTN_REPLY') ?></div>
				<div class="mail-msg-view-control mail-msg-view-control-replyall js-msg-view-control-replyall"><?=Loc::getMessage('MAIL_MESSAGE_BTN_REPLY_All') ?></div>
				<div class="mail-msg-view-control mail-msg-view-control-forward js-msg-view-control-forward"><?=Loc::getMessage('MAIL_MESSAGE_BTN_FWD') ?></div>
				<? if ($message['__access_level'] == 'full'): ?>
					<div class="mail-msg-view-control mail-msg-view-control-skip js-msg-view-control-skip"
						<? if (!preg_grep('/CRM_ACTIVITY-\d+/', $message['BIND']) || !$isCrmEnabled): ?> style="display: none; "<? endif ?>><?=Loc::getMessage('MAIL_MESSAGE_BTN_SKIP') ?></div>
					<? if (!$message['__is_outcome'] && !$message['isSpam']): ?>
						<div class="mail-msg-view-control mail-msg-view-control-spam js-msg-view-control-spam"><?=Loc::getMessage('MAIL_MESSAGE_BTN_SPAM') ?></div>
					<? endif ?>
					<div class="mail-msg-view-control mail-msg-view-control-delete js-msg-view-control-delete"
						<? if ($message['isTrash']): ?> data-is-trash="true" <? endif; ?>><?=Loc::getMessage('MAIL_MESSAGE_BTN_DEL') ?></div>
				<? endif ?>
			</div>
		</div>
	<? endif; ?>

	<? if ($message['isSyncError']):?>
		<div class="ui-alert ui-alert-warning mail-message-alert-warning">
		<span class="ui-alert-message">
			<? if (
				isset($message['MAILBOX'])
				&& isset($message['MAILBOX']['URI'])
				&& isset($message['MAILBOX']['HOST'])
			):?>
				<?=Loc::getMessage('MAIL_MESSAGE_WARNING_GO_TO_MAILBOX', [
					'#LINK#' => "<a href='". htmlspecialcharsbx($message['MAILBOX']['URI']) ."' target='_blank'>" . htmlspecialcharsbx($message['MAILBOX']['HOST']) . "</a>"
				])?>
			<? else: ?>
				<?=Loc::getMessage('MAIL_MESSAGE_WARNING_SYNC_ERROR');?>
			<? endif; ?>
		</span>
		</div>
	<? endif; ?>
	<?php if ($isAjaxBody): ?>
		<div class="ui-alert ui-alert-warning" id="<?= htmlspecialcharsbx($warningWaitElementId) ?>">
			<div class="ui-alert-message">
				<?= Loc::getMessage('MAIL_MESSAGE_WARNING_BODY_LOAD_WAIT', [
					'[download_link]' => "<a href=\"$bodyDownloadLink\" target=\"_blank\">",
					'[/download_link]' => '</a>',
				]) ?>
			</div>
			<div class="mail-message-alert-progress" id="<?= htmlspecialcharsbx($bodyLoaderElementId) ?>"></div>
		</div>
		<div class="ui-alert ui-alert-default"
			id="<?= htmlspecialcharsbx($warningFailElementId) ?>"
			style="display:none">
			<span class="ui-alert-message">
				<?= Loc::getMessage('MAIL_MESSAGE_WARNING_BODY_LOAD_FAIL', [
					'[download_link]' => "<a href=\"$bodyDownloadLink\" target=\"_blank\">",
					'[/download_link]' => '</a>',
				]) ?>
			</span>
			<span class="ui-alert-close-btn"></span>
		</div>
	<?php endif; ?>
	<div id="mail_msg_<?=$message['ID'] ?>_body" class="mail-msg-view-body"></div>
</div>

<?php
$diskFiles = $message['__diskFiles'] ?? [];
$ajaxAttachmentElementId = '';
?>
<?php if (!empty($message['__files']) || !empty($message['OPTIONS']['attachments'])) : ?>
<div class="mail-msg-view-file-block mail-msg-view-border-bottom">
	<div class="mail-msg-view-file-text"><?=getMessage('MAIL_MESSAGE_ATTACHES') ?>:</div>
	<?php
	if (empty($message['__files']) && !empty($message['OPTIONS']['attachments']))
	{
		$ajaxAttachmentElementId = 'bx-mail-message-ajax-attachment-'. ((int)$message['ID']);
	}
	?>
	<div class="mail-msg-view-file-inner"
		<?php if($ajaxAttachmentElementId): ?> id="<?=htmlspecialcharsbx($ajaxAttachmentElementId)?>" <?php endif; ?>>
		<?php
		if (!empty($message['__files']))
		{
			include __DIR__ . '/__files.php';
		}
		?>
	</div>
</div>
<?php endif; ?>


<? if (!$message['hideFastReplyPanel']):?>
	<div class="mail-msg-view-reply-panel mail-msg-view-border-bottom js-msg-view-reply-panel"
		 id="<?= htmlspecialcharsbx($fastReplyElementId) ?>"
		<?php if($isAjaxBody): ?> style="display:none" <?php endif; ?>>
		<div class="ui-icon ui-icon-common-user mail-msg-userpic">
			<i <? if (!empty($arResult['USER_IMAGE'])): ?> style="background: url('<?= Uri::urnEncode(htmlspecialcharsbx($arResult['USER_IMAGE'])) ?>'); background-size: 23px 23px; "<? endif ?>></i>
		</div>
		<div class="mail-msg-view-reply-panel-text"><?=Loc::getMessage('MAIL_MESSAGE_REPLY_Q') ?></div>
	</div>
<? endif; ?>

<?
$formId = sprintf('mail_msg_reply_%u_form', $message['ID']);

$actionUrl = '/bitrix/services/main/ajax.php?c=bitrix%3Amail.client&action=sendMessage&mode=ajax';

?>
<form action="<?= $actionUrl ?>" method="POST"
	class="mail-msg-view-border-bottom" id="<?=htmlspecialcharsbx($formId) ?>" style="display: none; margin-top: 10px; ">
	<?=bitrix_sessid_post() ?>
	<input type="hidden" name="data[IN_REPLY_TO]" value="<?=htmlspecialcharsbx($message['MSG_ID']) ?>">
	<input type="hidden" name="data[MAILBOX_ID]" value="<?=$message['MAILBOX_ID'] ?>">
	<?

	$inlineFiles = array();
	// there is no replace, only filling $inlineFiles array
	preg_replace_callback(
		'#(\?|&)__bxacid=(n?\d+)#i',
		function ($matches) use (&$inlineFiles)
		{
			$inlineFiles[] = $matches[2];
			return $matches[0];
		},
		$message['BODY_HTML']
	);
	$messageQuote = '';
	if (isset($message['MESSAGE_HTML']) && $message['MESSAGE_HTML'] && !$isAjaxBody)
	{
		$messageQuote = Message::wrapTheMessageWithAQuote(
			$message['MESSAGE_HTML'],
			$message['SUBJECT'],
			$message['FIELD_DATE'],
			$message['__from'],
			$message['__to'],
			$message['__cc'],
			true
		);
	}

    $attachedFiles = array_intersect(array_column($diskFiles, 'id'), $inlineFiles);

	$selectorParams = array(
		//'pathToAjax' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?soc_net_log_dest=search_email_comms';
		'extranetUser'             => false,
		'isCrmFeed'                => $isCrmEnabled,
		'CrmTypes'                 => array('CRMCONTACT', 'CRMCOMPANY', 'CRMLEAD'),
		'useClientDatabase'        => true,
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

	$formQuoteFieldName = 'data[message]';
	$APPLICATION->includeComponent(
		'bitrix:main.mail.form', '',
		array(
			'VERSION' => 2,
			'FORM_ID' => $formId,
			'LAYOUT_ONLY' => true,
			'SUBMIT_AJAX' => true,
			'FOLD_QUOTE' => true,
			'FOLD_FILES' => true,
			'USE_SIGNATURES' => true,
			'USE_CALENDAR_SHARING' => true,
			'COPILOT_PARAMS' => $arResult['COPILOT_PARAMS'],
			'FIELDS' => array(
				array(
					'name'     => 'data[from]',
					'title'    => Loc::getMessage('MAIL_MESSAGE_NEW_FROM'),
					'type'     => 'from',
					'value'    => $message['__email'],
					'isFormatted' => true,
					'required' => true,
					'folded'   => true,
				),
				//array(
				//	'type' => 'separator',
				//),
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
					'value'       => preg_replace(
						sprintf('/^(%s:\s*)?/i', preg_quote('Re')),
						sprintf('%s: ', 'Re'),
						$message['SUBJECT']
					),
					'folded'      => true,
				),
				array(
					'name'   => $formQuoteFieldName,
					'type'   => 'editor',
					'value'  => $messageQuote,
					'height' => 100,
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

<script>

var mailto = function ()
{
	top.BX.SidePanel.Instance.open(
		BX.util.add_url_param(
			'<?=\CUtil::jsEscape($arParams['PATH_TO_MAIL_MSG_NEW']) ?>',
			{
				id: <?=intval($message['MAILBOX_ID']) ?>,
				email: this.email
			}
		),
		{
			width: 960,
			cacheable: false,
			loader: 'create-mail-loader'
		}
	);

	BX.PreventDefault(event);
};

var emailLinks = BX.findChildrenByClassName(BX('mail-msg-view-details-<?=intval($message['ID']) ?>'), 'js-mailto-link', true);
for (var i in emailLinks)
{
	if (!emailLinks.hasOwnProperty(i))
		continue;

	if (emailLinks[i].href)
	{
		var matches = emailLinks[i].href.match(/^mailto:(.+)/);
		if (matches && matches[1])
		{
			BX.bind(emailLinks[i], 'click', mailto.bind({email: matches[1]}));
		}
	}
}
<?php if ($message['MESSAGE_HTML']): ?>
document.getElementById('<?= CUtil::JSescape($bodyElementId) ?>').innerHTML = '<div id="mail-message-wrapper">' + '<?= CUtil::jsEscape($message['MESSAGE_HTML']) ?>' + '</div>';
<?php endif; ?>
try
{
	top.BX.SidePanel.Instance.getSliderByWindow(window).closeLoader();
}
catch (err) {}

BX.ready(function()
{
	var message = new BXMailMessage({
		messageId: <?=intval($message['ID']) ?>,
		formId: '<?=\CUtil::jsEscape($formId) ?>',
		rcptSelected: <?=\Bitrix\Main\Web\Json::encode($rcptSelected) ?>,
		rcptAllSelected: <?=\Bitrix\Main\Web\Json::encode($rcptAllSelected) ?>,
		rcptCcSelected: <?=\Bitrix\Main\Web\Json::encode($rcptCcSelected) ?>
	});

	var mailView = new BXMailView({
		mailboxId: <?= (int)$message['MAILBOX_ID'] ?>,
		messageId: <?= (int)$message['ID'] ?>,
		isAjaxBody: <?= (int)$isAjaxBody ?>,
		formId: '<?= CUtil::JSescape($formId) ?>',
		messageBodyElementId: '<?= CUtil::JSescape($bodyElementId) ?>',
		bodyLoaderElementId: '<?= CUtil::JSescape($bodyLoaderElementId) ?>',
		bodyLoaderMaxTime: <?= (int)$bodyLoaderMaxTime ?>,
		mailUfMessageToken: '<?= CUtil::JSescape((string)($_REQUEST['mail_uf_message_token'] ?? '')) ?>',
		ajaxAttachmentElementId: '<?= CUtil::JSescape($ajaxAttachmentElementId) ?>',
		quoteFieldName: '<?= CUtil::JSescape($formQuoteFieldName) ?>',
		messageControlElementId: '<?= CUtil::JSescape($messageControlElementId) ?>',
		fastReplyElementId: '<?= CUtil::JSescape($fastReplyElementId) ?>',
		warningWaitElementId: '<?= CUtil::JSescape($warningWaitElementId) ?>',
		warningFailElementId: '<?= CUtil::JSescape($warningFailElementId) ?>',
		bxMailMessage: message,
		fileRefreshButtonId: '<?= CUtil::JSescape($fileRefreshButtonId) ?>',
	});

});

</script>
