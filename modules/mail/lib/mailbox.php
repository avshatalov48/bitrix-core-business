<?php

namespace Bitrix\Mail;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class MailboxTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_mailbox';
	}

	public static function getUserMailbox($mailboxId, $userId = null)
	{
		$mailboxes = static::getUserMailboxes($userId);

		return array_key_exists($mailboxId, $mailboxes) ? $mailboxes[$mailboxId] : false;
	}

	public static function getUserMailboxes($userId = null)
	{
		global $USER;

		if (!($userId > 0 || is_object($USER) && $USER->isAuthorized()))
		{
			return false;
		}

		if (!($userId > 0))
		{
			$userId = $USER->getId();
		}

		static $mailboxes = array();
		static $userMailboxes = array();

		if (!array_key_exists($userId, $userMailboxes))
		{
			$userMailboxes[$userId] = array();

			(new \CAccess)->updateCodes(array('USER_ID' => $userId));

			$accessSubquery = new Entity\Query(Internals\MailboxAccessTable::getEntity());
			$accessSubquery->registerRuntimeField(
				new Entity\ReferenceField(
					'USER_ACCESS',
					'Bitrix\Main\UserAccess',
					array(
						'=this.ACCESS_CODE' => 'ref.ACCESS_CODE',
					),
					array(
						'join_type' => 'INNER',
					)
				)
			);
			$accessSubquery->addFilter('=MAILBOX_ID', new \Bitrix\Main\DB\SqlExpression('%s'));
			$accessSubquery->addFilter('=USER_ACCESS.USER_ID', $userId);

			$res = static::getList(array(
				'runtime' => array(
					new Entity\ExpressionField(
						'IS_OWNED',
						sprintf('IF(%%s=%u, 1, 0)', $userId),
						'USER_ID'
					),
					new Entity\ExpressionField(
						'IS_ACCESS',
						sprintf('EXISTS(%s)', $accessSubquery->getQuery()),
						'ID'
					),
				),
				'filter' => array(
					array(
						'LOGIC' => 'OR',
						'=USER_ID' => $userId,
						'==IS_ACCESS' => true,
					),
					'=ACTIVE' => 'Y',
					'=SERVER_TYPE' => 'imap',
				),
				'order' => array(
					'IS_OWNED' => 'DESC',
					'ID' => 'DESC',
				),
			));

			while ($mailbox = $res->fetch())
			{
				static::normalizeEmail($mailbox);

				$mailboxes[$mailbox['ID']] = $mailbox;
				$userMailboxes[$userId][] = $mailbox['ID'];
			}
		}

		$result = array();
		foreach ($userMailboxes[$userId] as $mailboxId)
		{
			$result[$mailboxId] = $mailboxes[$mailboxId];
		}

		return $result;
	}

	public static function normalizeEmail(&$mailbox)
	{
		foreach (array($mailbox['EMAIL'], $mailbox['NAME'], $mailbox['LOGIN']) as $item)
		{
			$address = new \Bitrix\Main\Mail\Address($item);
			if ($address->validate())
			{
				$mailbox['EMAIL'] = $address->getEmail();
				break;
			}
		}

		return $mailbox;
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'LID' => array(
				'data_type' => 'string',
				'required'  => true
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'SERVICE_ID' => array(
				'data_type' => 'integer',
			),
			'EMAIL' => array(
				'data_type' => 'string',
			),
			'USERNAME' => array(
				'data_type' => 'string',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'SERVER' => array(
				'data_type' => 'string',
			),
			'PORT' => array(
				'data_type' => 'integer',
			),
			'LINK' => array(
				'data_type' => 'string',
			),
			'LOGIN' => array(
				'data_type' => 'string',
			),
			'CHARSET' => array(
				'data_type' => 'string',
			),
			'PASSWORD' => array(
				'data_type' => 'string',
				'fetch_data_modification' => function()
				{
					return array(
						function ($value)
						{
							return \CMailUtil::decrypt($value);
						}
					);
				}
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
			),
			'USE_MD5' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'DELETE_MESSAGES' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'PERIOD_CHECK' => array(
				'data_type' => 'integer',
			),
			'MAX_MSG_COUNT' => array(
				'data_type' => 'integer',
			),
			'MAX_MSG_SIZE' => array(
				'data_type' => 'integer',
			),
			'MAX_KEEP_DAYS' => array(
				'data_type' => 'integer',
			),
			'USE_TLS' => array(
				'data_type' => 'enum',
				'values'    => array('N', 'Y', 'S'),
			),
			'SERVER_TYPE' => array(
				'data_type' => 'enum',
				'values'    => array('smtp', 'pop3', 'imap', 'controller', 'domain', 'crdomain')
			),
			'DOMAINS' => array(
				'data_type' => 'string',
			),
			'RELAY' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'AUTH_RELAY' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'SYNC_LOCK' => array(
				'data_type' => 'integer',
			),
			'OPTIONS' => array(
				'data_type'  => 'text',
				'save_data_modification' => function()
				{
					return array(
						function ($options)
						{
							if (!empty($options['imap']['dirsMd5']) && is_array($options['imap']['dirsMd5']))
							{
								unset($options['imap']['dirsMd5']);
							}
							return serialize($options);
						}
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function ($values)
						{
							$values = unserialize($values);
							if (!empty($values['imap']['dirs']) && is_array($values['imap']['dirs']))
							{
								foreach ($values['imap']['dirs'] as $name => $dir)
								{
									$values['imap']['dirsMd5'][$name] = md5($name);
								}
							}

							return $values;
						}
					);
				}
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Main\Site',
				'reference' => array('=this.LID' => 'ref.LID'),
			),
		);
	}

}
