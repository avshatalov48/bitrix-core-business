<?php

namespace Bitrix\Mail;

use Bitrix\Main;

class Helper
{

	const SYNC_TIMEOUT = 300;

	public static function syncMailboxAgent($id)
	{
		$result = self::syncMailbox($id, $error);

		if ($result === false)
			return '';

		return sprintf('Bitrix\Mail\Helper::syncMailboxAgent(%u);', $id);
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
				\CMailDomain2::getDomainUsers($item['TOKEN'], $item['SERVER'], $error, true);
			if ($item['SERVICE_TYPE'] == 'crdomain')
				\CControllerClient::executeEvent('OnMailControllerResyncMemberUsers', array('DOMAIN' => $item['SERVER']));
		}

		return 'Bitrix\Mail\Helper::resyncDomainUsersAgent();';
	}

	public static function syncMailbox($id, &$error)
	{
		global $DB;

		$error = null;

		$id = (int) $id;

		$mailbox = MailboxTable::getList(array(
			'filter' => array('ID' => $id, 'ACTIVE' => 'Y'),
			'select' => array('*', 'LANG_CHARSET' => 'SITE.CULTURE.CHARSET')
		))->fetch();

		if (empty($mailbox))
		{
			$error = 'no mailbox';
			return false;
		}

		if (!in_array($mailbox['SERVER_TYPE'], array('imap', 'controller', 'domain', 'crdomain')))
		{
			$error = 'unsupported mailbox type';
			return false;
		}

		if ($mailbox['SYNC_LOCK'] > time()-Helper::SYNC_TIMEOUT)
			return 0;

		if ($mailbox['USER_ID'] > 0)
			\CUserOptions::setOption('global', 'last_mail_sync_'.$mailbox['LID'], time(), false, $mailbox['USER_ID']);

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

		$mailbox['SYNC_LOCK'] = time();
		$res = $DB->query(sprintf(
			'UPDATE b_mail_mailbox SET SYNC_LOCK = %u WHERE ID = %u AND (SYNC_LOCK IS NULL OR SYNC_LOCK < %u)',
			$mailbox['SYNC_LOCK'], $id, $mailbox['SYNC_LOCK']-Helper::SYNC_TIMEOUT
		));

		if (!$res->affectedRowsCount())
			return 0;

		$result = static::syncImapMailbox($mailbox, $error);
		$success = $result !== false && empty($error);

		$DB->query(sprintf(
			'UPDATE b_mail_mailbox SET SYNC_LOCK = 0 WHERE ID = %u AND SYNC_LOCK = %u',
			$id, $mailbox['SYNC_LOCK']
		));

		if ($mailbox['USER_ID'] > 0)
		{
			\CUserOptions::setOption('global', 'last_mail_check_'.$mailbox['LID'], time(), false, $mailbox['USER_ID']);
			\CUserOptions::setOption('global', 'last_mail_sync_'.$mailbox['LID'], time(), false, $mailbox['USER_ID']);
			\CUserOptions::setOption('global', 'last_mail_check_success_'.$mailbox['LID'], $success, false, $mailbox['USER_ID']);
		}
		else
		{
			\Bitrix\Main\Config\Option::set('mail', 'last_mail_check', time(), $mailbox['LID']);
			\Bitrix\Main\Config\Option::set('mail', 'last_mail_sync', time(), $mailbox['LID']);
			\Bitrix\Main\Config\Option::set('mail', 'last_mail_check_success', $success ? 'Y' : 'N', $mailbox['LID']);
		}

		return $result;
	}

	protected static function syncImapMailbox($mailbox, &$error)
	{
		$error = null;
		$count = 0;

		if (empty($mailbox['OPTIONS']['imap']) || !is_array($mailbox['OPTIONS']['imap']))
			return false;

		$imapOptions = $mailbox['OPTIONS']['imap'];
		if (empty($imapOptions['income']) || !is_array($imapOptions['income']))
			return false;

		$imapOptions['outcome'] = !empty($imapOptions['outcome']) && is_array($imapOptions['income'])
			? array_diff($imapOptions['outcome'], $imapOptions['income']) : array();

		$client = new Imap(
			$mailbox['SERVER'], $mailbox['PORT'],
			$mailbox['USE_TLS'] == 'Y' || $mailbox['USE_TLS'] == 'S',
			$mailbox['USE_TLS'] == 'Y',
			$mailbox['LOGIN'], $mailbox['PASSWORD'],
			$mailbox['LANG_CHARSET']
		);

		if (!$client->singin($error))
			return 0;

		$localList = array();
		$localSeen = array();
		$res = MailMessageUidTable::getList(array(
			'filter' => array('MAILBOX_ID' => $mailbox['ID']),
			'select' => array('ID', 'HASH' => 'HEADER_MD5', 'IS_SEEN')
		));
		while ($item = $res->fetch())
		{
			$localList[$item['ID']]   = $item['HASH'];
			$localSeen[$item['HASH']] = $item['IS_SEEN'];
		}

		$obsoleteList = $localList;
		$modifiedList = array();

		$blacklist = array(
			'domain' => array(),
			'email'  => array(),
		);

		$res = BlacklistTable::getList(array(
			'select' => array('ITEM_TYPE', 'ITEM_VALUE'),
			'filter' => array(
				array(
					'LOGIC' => 'OR',
					array(
						'MAILBOX_ID' => $mailbox['ID'],
					),
					array(
						'MAILBOX_ID' => 0,
						'SITE_ID'    => $mailbox['LID'],
					)
				),
			),
		));
		while ($item = $res->fetch())
		{
			if (Blacklist\ItemType::DOMAIN == $item['ITEM_TYPE'])
				$blacklist['domain'][] = $item['ITEM_VALUE'];
			else
				$blacklist['email'][] = $item['ITEM_VALUE'];
		}

		$domains = array();
		$defaultDomain = \COption::getOptionString('main', 'server_name', '');
		$res = Main\SiteTable::getList(array('select' => array('LID', 'SERVER_NAME')));
		while ($site = $res->fetch())
		{
			$domains[$site['LID']] = $site['SERVER_NAME'] ?: $defaultDomain;

			if (preg_match('/^(?<domain>.+):(?<port>\d+)$/', $domains[$site['LID']], $matches))
				$domains[$site['LID']] = $matches['domain'];
		}

		if ($mailbox['USER_ID'] > 0)
		{
			$res = UserRelationsTable::getList(array('filter' => array('=USER_ID' => $mailbox['USER_ID'], '=ENTITY_ID' => null)));
			while ($relation = $res->fetch())
				$blacklist['email'][] = sprintf('fwd%s@%s', $relation['TOKEN'], $domains[$relation['SITE_ID']]);
		}

		$blacklist['domain'] = array_map('strtolower', $blacklist['domain']);
		$blacklist['email']  = array_map('strtolower', $blacklist['email']);

		$nouidv = \Bitrix\Main\Config\Option::get('mail', sprintf('imap_mailbox_no_uidv_%u', $mailbox['ID']), 'N');

		$session = md5(uniqid(''));

		foreach (array_merge($imapOptions['income'], $imapOptions['outcome']) as $name)
		{
			$list = $client->listMessages($name, $uidtoken, $error);

			if ($list === false) // an error occurred
			{
				$obsoleteList = array();
				continue;
			}

			if (empty($list))
				continue;

			if (!($uidtoken > 0) && $nouidv != 'Y')
			{
				addMessage2Log(
					sprintf(
						'IMAP: UIDV not found (%s:%s/%s)',
						$mailbox['SERVER'], $mailbox['PORT'], $mailbox['LOGIN']
					),
					'mail', 0, false
				);

				\Bitrix\Main\Config\Option::set('mail', sprintf('imap_mailbox_no_uidv_%u', $mailbox['ID']), $nouidv = 'Y');
			}

			foreach ($list as $item)
			{
				$skip = false;

				$item['seen'] = (bool) preg_match('/ ( ^ | \x20 ) \x5c ( Seen ) ( \x20 | $ ) /ix', $item['flags']);

				if (!is_null($item['uid']))
				{
					$item['uid'] = md5(sprintf('%s:%u:%u', $name, $uidtoken, $item['uid']));

					unset($obsoleteList[$item['uid']]);
					if (array_key_exists($item['uid'], $localList))
					{
						$item['hash'] = $localList[$item['uid']];

						$skip = true;
					}
				}

				if ($skip === false)
				{
					$header = $client->getMessage($name, $item['id'], 'header', $error);

					if ($header === false) // an error occurred
					{
						$obsoleteList = array();
						$skip = true;
					}
					else
					{
						$item['hash'] = md5(sprintf('%s:%s:%u', trim($header), $item['date'], $item['size']));

						if (is_null($item['uid']))
							$item['uid'] = $item['hash'];

						if ($uid = array_search($item['hash'], $localList))
						{
							unset($obsoleteList[$uid]);
							if ($uid != $item['hash'])
							{
								MailMessageUidTable::update(
									array('ID' => $uid, 'MAILBOX_ID' => $mailbox['ID']),
									array('ID' => $item['uid'])
								);
							}

							$skip = true;
						}
					}
				}

				if ($skip === true)
				{
					if ($item['seen'] != in_array($localSeen[$item['hash']], array('Y', 'S')))
					{
						if (in_array($localSeen[$item['hash']], array('S', 'U')))
						{
							$item['seen'] = $localSeen[$item['hash']] == 'S';

							$result = $client->updateMessageFlags($name, $item['id'], array(
								'\Seen' => $item['seen'],
							), $err);

							if ($result !== false)
							{
								MailMessageUidTable::update(
									array('ID' => $item['uid'], 'MAILBOX_ID' => $mailbox['ID']),
									array('IS_SEEN' => $item['seen'] ? 'Y' : 'N')
								);
							}
						}
						else
						{
							$modifiedList[$item['uid']] = array(
								'hash' => $item['hash'],
								'seen' => $item['seen'],
							);
						}
					}

					continue;
				}

				if (\CMail::option('attachment_failure'))
				{
					if ($item['size'] > 200000)
						continue;
				}

				if ($mailbox['SYNC_LOCK'] < time()-Helper::SYNC_TIMEOUT*0.9)
					return $count;

				MailMessageUidTable::add(array(
					'ID'          => $item['uid'],
					'MAILBOX_ID'  => $mailbox['ID'],
					'HEADER_MD5'  => $item['hash'],
					'IS_SEEN'     => $item['seen'] ? 'Y' : 'N',
					'SESSION_ID'  => $session,
					'DATE_INSERT' => new Main\Type\DateTime(),
					'MESSAGE_ID'  => 0,
				));
				$localList[$item['uid']] = $item['hash'];

				if (!empty($mailbox['OPTIONS']['sync_from']))
				{
					$syncFrom = (int) $mailbox['OPTIONS']['sync_from'];
					if (strtotime($item['date']) < $syncFrom)
						continue;
				}

				$item['outcome'] = in_array($name, $imapOptions['outcome']);

				$parsedHeader = \CMailMessage::parseHeader($header, $mailbox['LANG_CHARSET']);

				$parsedFrom = array_unique(array_map('strtolower', array_filter(array_merge(
					\CMailUtil::extractAllMailAddresses($parsedHeader->getHeader('FROM')),
					\CMailUtil::extractAllMailAddresses($parsedHeader->getHeader('REPLY-TO'))
				), 'trim')));
				$parsedTo = array_unique(array_map('strtolower', array_filter(array_merge(
					\CMailUtil::extractAllMailAddresses($parsedHeader->getHeader('TO')),
					\CMailUtil::extractAllMailAddresses($parsedHeader->getHeader('CC')),
					\CMailUtil::extractAllMailAddresses($parsedHeader->getHeader('BCC')),
					\CMailUtil::extractAllMailAddresses($parsedHeader->getHeader('X-Original-Rcpt-to'))
				), 'trim')));

				if (!empty($blacklist['email']))
				{
					if (!$item['outcome'] && array_intersect($parsedFrom, $blacklist['email']))
						continue;

					if ($item['outcome'] && !array_diff($parsedTo, $blacklist['email']))
						continue;
				}

				if (!empty($blacklist['domain']))
				{
					$skip = false;

					$haystack = $item['outcome'] ? $parsedTo : $parsedFrom;
					foreach ($haystack as $email)
					{
						$domain = substr($email, strrpos($email, '@'));
						if ($domain != $email)
						{
							if (in_array($domain, $blacklist['domain']))
							{
								$skip = true;
								if (!$item['outcome'])
									break;
							}
							else
							{
								$skip = false;
								if ($item['outcome'])
									break;
							}
						}
					}

					if ($skip)
						continue;
				}

				$messageId = 0;

				$body = $client->getMessage($name, $item['id'], null, $error);
				if ($body !== false)
				{
					if (!preg_match('/\r\n$/', $body))
						$body .= "\r\n";

					$messageId = \CMailMessage::addMessage(
						$mailbox['ID'], $body,
						$mailbox['CHARSET'] ?: $mailbox['LANG_CHARSET'],
						array(
							'outcome' => $item['outcome'],
							'seen'    => $item['seen'],
							'hash'    => $item['hash'],
						)
					);
				}

				if ($messageId > 0)
				{
					$count++;

					MailMessageUidTable::update(
						array('ID' => $item['uid'], 'MAILBOX_ID' => $mailbox['ID']),
						array('MESSAGE_ID' => $messageId)
					);
				}
				else
				{
					MailMessageUidTable::delete(
						array('ID' => $item['uid'], 'MAILBOX_ID' => $mailbox['ID'])
					);
				}
			}
		}

		if (!empty($obsoleteList))
		{
			foreach ($obsoleteList as $msgUid => $dummy)
			{
				MailMessageUidTable::delete(array(
					'ID' => $msgUid, 'MAILBOX_ID' => $mailbox['ID']
				));
			}

			foreach ($obsoleteList as $msgHash)
			{
				$event = new Main\Event(
					'mail', 'OnMessageObsolete',
					array(
						'user' => $mailbox['USER_ID'],
						'hash' => $msgHash,
					)
				);
				$event->send();
			}
		}

		if (!empty($modifiedList))
		{
			foreach ($modifiedList as $msgUid => $msgData)
			{
				MailMessageUidTable::update(
					array('ID' => $msgUid, 'MAILBOX_ID' => $mailbox['ID']),
					array('IS_SEEN' => $msgData['seen'] ? 'Y' : 'N')
				);
			}

			foreach ($modifiedList as $msgData)
			{
				$event = new Main\Event(
					'mail', 'OnMessageModified',
					array(
						'user' => $mailbox['USER_ID'],
						'hash' => $msgData['hash'],
						'seen' => $msgData['seen'],
					)
				);
				$event->send();
			}
		}

		return $count;
	}

	public static function listImapDirs($mailbox, &$error, &$errors = null)
	{
		$error  = null;
		$errors = null;

		$client = new Imap(
			$mailbox['SERVER'], $mailbox['PORT'],
			$mailbox['USE_TLS'] == 'Y' || $mailbox['USE_TLS'] == 'S',
			$mailbox['USE_TLS'] == 'Y',
			$mailbox['LOGIN'], $mailbox['PASSWORD'],
			LANG_CHARSET
		);

		$list   = $client->listMailboxes('*', $error);
		$errors = $client->getErrors();

		if ($list === false)
			return false;

		$flat = function($list, $prefix = '', $level = 0) use (&$flat)
		{
			$k = count($list);
			for ($i = 0; $i < $k; $i++)
			{
				$item = $list[$i];

				$list[$i] = array(
					'level' => $level,
					'name'  => preg_replace(sprintf('/^%s/', preg_quote($prefix, '/')), '', $item['name']),
					'path'  => $item['name']
				);

				if (preg_match('/ ( ^ | \x20 ) \x5c Noselect ( \x20 | $ ) /ix', $item['flags']))
				{
					$list[$i]['disabled'] = true;
				}
				else
				{
					if (strtolower($item['name']) == 'inbox')
						$list[$i]['income'] = true;

					if (preg_match('/ ( ^ | \x20 ) \x5c Sent ( \x20 | $ ) /ix', $item['flags']))
						$list[$i]['outcome'] = true;
				}

				if (!empty($item['children']))
				{
					$children = $flat($item['children'], $item['name'].$item['delim'], $level+1);

					array_splice($list, $i+1, 0, $children);

					$i += count($children);
					$k += count($children);
				}
			}

			return $list;
		};

		return $flat($list);
	}

	public static function getImapUnseen($mailbox, $dir = 'inbox', &$error, &$errors = null)
	{
		$error  = null;
		$errors = null;

		$client = new Imap(
			$mailbox['SERVER'], $mailbox['PORT'],
			$mailbox['USE_TLS'] == 'Y' || $mailbox['USE_TLS'] == 'S',
			$mailbox['USE_TLS'] == 'Y',
			$mailbox['LOGIN'], $mailbox['PASSWORD'],
			LANG_CHARSET
		);

		$result = $client->getUnseen($dir, $error);
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

		$client = new Imap(
			$mailbox['SERVER'], $mailbox['PORT'],
			$mailbox['USE_TLS'] == 'Y' || $mailbox['USE_TLS'] == 'S',
			$mailbox['USE_TLS'] == 'Y',
			$mailbox['LOGIN'], $mailbox['PASSWORD'],
			$mailbox['LANG_CHARSET'] ?: $mailbox['CHARSET']
		);

		$imapOptions = $mailbox['OPTIONS']['imap'];
		if (empty($imapOptions['outcome']) || !is_array($imapOptions['outcome']))
			return;

		return $client->addMessage(reset($imapOptions['outcome']), $data, $error);
	}

	public static function updateImapMessage($userId, $hash, $data, &$error)
	{
		$error = null;

		$res = MailMessageUidTable::getList(array(
			'select' => array('ID', 'MAILBOX_ID', 'IS_SEEN'),
			'filter' => array(
				'=HEADER_MD5'     => $hash,
				'MAILBOX.USER_ID' => array($userId, 0),
			),
		));

		while ($msgUid = $res->fetch())
		{
			if (in_array($msgUid['IS_SEEN'], array('Y', 'S')) != $data['seen'])
			{
				MailMessageUidTable::update(
					array('ID' => $msgUid['ID'], 'MAILBOX_ID' => $msgUid['MAILBOX_ID']),
					array('IS_SEEN' => $data['seen'] ? 'S' : 'U')
				);
			}
		}
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
	}

	public static function getMailEol()
	{
		return "\r\n";
	}

	public function __toString()
	{
		return sprintf("%s\r\n\r\n%s", $this->getHeaders(), $this->getBody());
	}

}
