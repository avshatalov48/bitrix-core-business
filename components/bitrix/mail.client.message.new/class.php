<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail;

Loc::loadMessages(__DIR__ . '/../mail.client/class.php');

Main\Loader::includeModule('mail');

class CMailClientMessageNewComponent extends CBitrixComponent
{
	/** @var bool */
	private $isCrmEnable = false;

	/**
	 * @return mixed|void
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function executeComponent($level = 1)
	{
		global $USER, $APPLICATION;

		if (!is_object($USER) || !$USER->isAuthorized())
		{
			$APPLICATION->authForm('');
			return;
		}

		$this->isCrmEnable = Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled();
		$this->arResult['CRM_ENABLE'] = ($this->isCrmEnable ? 'Y' : 'N');

		$messageId = 0;
		if (!empty($_REQUEST['forward']) && $_REQUEST['forward'] > 0)
		{
			$messageType = 'forward';
			$messageId = (int) $_REQUEST['forward'];
			$subjectPrefix = 'Fwd';
		}
		else if (!empty($_REQUEST['reply']) && $_REQUEST['reply'] > 0)
		{
			$messageType = 'reply';
			$messageId = (int) $_REQUEST['reply'];
			$subjectPrefix = 'Re';
		}

		$message = array();

		$this->arResult['TO_PLUG_EXTENSION_SALES_LETTER_TEMPLATE'] = false;

		if (!empty($_REQUEST['sales_letter']) && $_REQUEST['sales_letter'])
		{
			$this->arResult['TO_PLUG_EXTENSION_SALES_LETTER_TEMPLATE'] = true;
		}

		if (!empty($_REQUEST['id']) && $_REQUEST['id'] > 0)
		{
			if ($mailbox = Mail\MailboxTable::getUserMailbox($_REQUEST['id']))
			{
				$message = array(
					'MAILBOX_ID' => $mailbox['ID'],
					'MAILBOX_EMAIL' => $mailbox['EMAIL'],
					'MAILBOX_NAME' => 'MAILBOX.NAME',
					'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
				);
			}
		}

		if ($messageId > 0)
		{
			$message = Mail\MailMessageTable::getList(array(
				'runtime' => array(
					new Main\ORM\Fields\Relations\Reference(
						'MESSAGE_UID',
						'Bitrix\Mail\MailMessageUidTable',
						array(
							'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
							'=this.ID' => 'ref.MESSAGE_ID',
						),
						array(
							'join_type' => 'INNER',
						)
					),
				),
				'select' => array(
					'*',
					'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
					'MAILBOX_NAME' => 'MAILBOX.NAME',
					'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
					'DIR_MD5' => 'MESSAGE_UID.DIR_MD5',
					'MSG_UID' => 'MESSAGE_UID.MSG_UID',
				),
				'filter' => array(
					'=ID' => $messageId,
				),
			))->fetch();

			if (empty($message))
			{
				showError(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
				return;
			}

			if (!Mail\Helper\Message::hasAccess($message))
			{
				showError(Loc::getMessage('MAIL_CLIENT_ELEMENT_DENIED'));
				return;
			}

			if (!empty($subjectPrefix))
			{
				$message['SUBJECT'] = preg_replace(
					sprintf('/^(%s:\s*)?/i', preg_quote($subjectPrefix)),
					sprintf('%s: ', $subjectPrefix),
					$message['SUBJECT']
				);
			}

			if ($level <= 1 && Mail\Helper\Message::ensureAttachments($message) > 0)
			{
				return $this->executeComponent($level + 1);
			}

			$message['__files'] = array();
			if ($message['ATTACHMENTS'] > 0)
			{
				$message['__files'] = Mail\Internals\MailMessageAttachmentTable::getList(array(
					'select' => array(
						'ID', 'FILE_ID', 'FILE_NAME', 'FILE_SIZE', 'CONTENT_TYPE',
					),
					'filter' => array(
						'=MESSAGE_ID' => $message['ID'],
					),
				))->fetchAll();
			}

			$message['ID'] = 0;

			$message['__type'] = $messageType;
			$message['__parent'] = $messageId;
		}

		if (!empty($_REQUEST['email']))
		{
			$message['FIELD_RCPT'] = $_REQUEST['email'];
		}

		Mail\Helper\Message::prepare($message);

		$this->arResult['MESSAGE'] = $message;
		$this->arResult['LAST_RCPT'] = Mail\Helper\Recipient::loadLastRcpt();

		$this->arResult['EMAILS'] = array();//Mail\Helper\Recipient::loadMailContacts();

		$this->includeComponentTemplate();
	}
}