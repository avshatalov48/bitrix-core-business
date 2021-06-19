<?php

namespace Bitrix\Mail;

use Bitrix\Main;

class Helper
{

	const SYNC_TIMEOUT = 300;

	public static function syncMailboxAgent($id)
	{
		$mailboxHelper = Helper\Mailbox::createInstance($id, false);

		if (empty($mailboxHelper))
		{
			return '';
		}

		$mailbox = $mailboxHelper->getMailbox();

		if ($mailbox['OPTIONS']['next_sync'] <= time())
		{
			$mailboxHelper->sync();

			$mailbox = $mailboxHelper->getMailbox();
		}

		global $pPERIOD;

		$pPERIOD = min($pPERIOD, max($mailbox['OPTIONS']['next_sync'] - time(), 60));

		return sprintf('Bitrix\Mail\Helper::syncMailboxAgent(%u);', $id);
	}

	public static function syncOutgoingAgent($id)
	{
		$mailboxHelper = Helper\Mailbox::createInstance($id, false);

		$result = empty($mailboxHelper) ? false : $mailboxHelper->syncOutgoing();

		return '';
	}

	public static function cleanupMailboxAgent($id)
	{
		$mailboxHelper = Helper\Mailbox::rawInstance($id, false);

		if (empty($mailboxHelper))
		{
			return '';
		}

		$mailboxHelper->setCheckpoint();

		$stage1 = $mailboxHelper->dismissOldMessages();
		$stage2 = $mailboxHelper->dismissDeletedUidMessages();
		$stage3 = $mailboxHelper->cleanup();

		global $pPERIOD;

		$pPERIOD = min($pPERIOD, max($stage1 && $stage2 && $stage3 ? $pPERIOD : 600, 60));

		if ($pPERIOD === null)
		{
			$pPERIOD = 60;
		}

		return sprintf('Bitrix\Mail\Helper::cleanupMailboxAgent(%u);', $id);
	}

	/**
	 * @deprecated
	 */
	public static function resortTreeAgent($id)
	{
		$mailboxHelper = Helper\Mailbox::createInstance($id, false);

		$result = empty($mailboxHelper) ? false : $mailboxHelper->resortTree();

		return '';
	}

	public static function deleteMailboxAgent($id)
	{
		return \CMailbox::delete($id) ? '' : sprintf('Bitrix\Mail\Helper::deleteMailboxAgent(%u);', $id);
	}

	public static function resyncDomainUsersAgent()
	{
		$res = MailServicesTable::getList(array(
			'filter' => array(
				'=ACTIVE'       => 'Y',
				'@SERVICE_TYPE' => array('domain', 'crdomain'),
			)
		));
		while ($item = $res->fetch())
		{
			if ($item['SERVICE_TYPE'] == 'domain')
			{
				$lockName = sprintf('domain_users_sync_lock_%u', $item['ID']);
				$syncLock = \Bitrix\Main\Config\Option::get('mail', $lockName, 0);

				if ($syncLock < time()-3600)
				{
					\Bitrix\Main\Config\Option::set('mail', $lockName, time());
					\CMailDomain2::getDomainUsers($item['TOKEN'], $item['SERVER'], $error, true);
					\Bitrix\Main\Config\Option::set('mail', $lockName, 0);
				}
			}
			else if ($item['SERVICE_TYPE'] == 'crdomain')
			{
				\CControllerClient::executeEvent('OnMailControllerResyncMemberUsers', array('DOMAIN' => $item['SERVER']));
			}
		}

		return 'Bitrix\Mail\Helper::resyncDomainUsersAgent();';
	}

	public static function syncMailbox($id, &$error)
	{
		$mailboxHelper = Helper\Mailbox::createInstance($id, false);

		return empty($mailboxHelper) ? false : $mailboxHelper->sync();
	}

	public static function listImapDirs($mailbox, &$error, &$errors = null)
	{
		$error  = null;
		$errors = null;

		$client = static::createClient($mailbox);

		$list   = $client->listMailboxes('*', $error, true);
		$errors = $client->getErrors();

		if ($list === false)
			return false;

		$k = count($list);
		for ($i = 0; $i < $k; $i++)
		{
			$item = $list[$i];

			$list[$i] = array(
				'path' => $item['name'],
				'name' => $item['title'],
				'level' => $item['level'],
				'disabled' => (bool) preg_grep('/^ \x5c Noselect $/ix', $item['flags']),
				'income' => mb_strtolower($item['name']) == 'inbox',
				'outcome' => (bool) preg_grep('/^ \x5c Sent $/ix', $item['flags']),
			);
		}

		return $list;
	}

	public static function getImapUnseen($mailbox, $dirPath = 'inbox', &$error, &$errors = null)
	{
		$error  = null;
		$errors = null;

		$client = static::createClient($mailbox);

		$result = $client->getUnseen($dirPath, $error);
		$errors = $client->getErrors();

		return $result;
	}

	public static function addImapMessage($id, $data, &$error)
	{
		$error = null;

		$id = (int) (is_array($id) ? $id['ID'] : $id);

		$mailbox = MailboxTable::getList(array(
			'filter' => array('ID' => $id, 'ACTIVE' => 'Y'),
			'select' => array('*', 'LANG_CHARSET' => 'SITE.CULTURE.CHARSET')
		))->fetch();

		if (empty($mailbox))
			return;

		if (!in_array($mailbox['SERVER_TYPE'], array('imap', 'controller', 'domain', 'crdomain')))
			return;

		if (in_array($mailbox['SERVER_TYPE'], array('controller', 'crdomain')))
		{
			// @TODO: request controller
			$result = \CMailDomain2::getImapData();

			$mailbox['SERVER']  = $result['server'];
			$mailbox['PORT']    = $result['port'];
			$mailbox['USE_TLS'] = $result['secure'];
		}
		elseif ($mailbox['SERVER_TYPE'] == 'domain')
		{
			$result = \CMailDomain2::getImapData();

			$mailbox['SERVER']  = $result['server'];
			$mailbox['PORT']    = $result['port'];
			$mailbox['USE_TLS'] = $result['secure'];
		}

		$client = static::createClient($mailbox, $mailbox['LANG_CHARSET'] ?: $mailbox['CHARSET']);

		$dir = MailboxDirectory::fetchOneOutcome($mailbox['ID']);
		$path = $dir ? $dir->getPath() : 'INBOX';

		return $client->addMessage($path, $data, $error);
	}

	public static function updateImapMessage($userId, $hash, $data, &$error)
	{
		$error = null;

		$res = MailMessageUidTable::getList(array(
			'select' => array(
				'ID', 'MAILBOX_ID', 'IS_SEEN',
				'MAILBOX_USER_ID' => 'MAILBOX.USER_ID',
				'MAILBOX_OPTIONS' => 'MAILBOX.OPTIONS',
			),
			'filter' => array(
				'=HEADER_MD5'  => $hash,
				'=DELETE_TIME' => 'IS NULL',
			),
		));

		while ($item = $res->fetch())
		{
			$isOwner = $item['MAILBOX_USER_ID'] == $userId;
			$isPublic = in_array('crm_public_bind', (array) $item['MAILBOX_OPTIONS']['flags']);
			$inQueue = in_array($userId, (array) $item['MAILBOX_OPTIONS']['crm_lead_resp']);
			if (!$isOwner && !$isPublic && !$inQueue)
			{
				continue;
			}

			if (in_array($item['IS_SEEN'], array('Y', 'S')) != $data['seen'])
			{
				MailMessageUidTable::update(
					array(
						'ID' => $item['ID'],
						'MAILBOX_ID' => $item['MAILBOX_ID'],
					),
					array(
						'IS_SEEN' => $data['seen'] ? 'S' : 'U',
					)
				);
			}
		}
	}

	private static function createClient($mailbox, $langCharset = null)
	{
		return new Imap(
			$mailbox['SERVER'], $mailbox['PORT'],
			$mailbox['USE_TLS'] == 'Y' || $mailbox['USE_TLS'] == 'S',
			$mailbox['USE_TLS'] == 'Y',
			$mailbox['LOGIN'], $mailbox['PASSWORD'],
			$langCharset ? $langCharset : LANG_CHARSET
		);
	}
}

class DummyMail extends Main\Mail\Mail
{

	public function initSettings()
	{
		parent::initSettings();

		$this->settingServerMsSmtp = false;
		$this->settingMailFillToEmail = false;
		$this->settingMailConvertMailHeader = true;
		$this->settingConvertNewLineUnixToWindows = true;
		$this->useBlacklist = false;
	}

	public static function getMailEol()
	{
		return "\r\n";
	}

	public function __toString()
	{
		return sprintf("%s\r\n\r\n%s", $this->getHeaders(), $this->getBody());
	}

	/**
	 * @deprecated
	 */
	public static function overwriteMessageHeaders(Main\Mail\Mail $message, array $headers)
	{
		foreach ($headers as $name => $value)
		{
			$message->headers[$name] = $value;
		}
	}

}
