<?php

namespace Bitrix\Mail;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class MailboxTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Mailbox_Query query()
 * @method static EO_Mailbox_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Mailbox_Result getById($id)
 * @method static EO_Mailbox_Result getList(array $parameters = array())
 * @method static EO_Mailbox_Entity getEntity()
 * @method static \Bitrix\Mail\EO_Mailbox createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\EO_Mailbox_Collection createCollection()
 * @method static \Bitrix\Mail\EO_Mailbox wakeUpObject($row)
 * @method static \Bitrix\Mail\EO_Mailbox_Collection wakeUpCollection($rows)
 */
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

	public static function getTheOwnersMailboxes($userId = null)
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

		static $mailboxes = [];
		static $userMailboxes = [];

		if (!array_key_exists($userId, $userMailboxes))
		{
			$userMailboxes[$userId] = [];

			(new \CAccess)->updateCodes(['USER_ID' => $userId]);

			$res = static::getList([
				'filter' => [
					[
						'=USER_ID' => $userId,
					],
					'=ACTIVE' => 'Y',
					'=SERVER_TYPE' => 'imap',
				],
				'order' => [
					'ID' => 'DESC',
				],
			]);

			while ($mailbox = $res->fetch())
			{
				static::normalizeEmail($mailbox);

				$mailboxes[$mailbox['ID']] = $mailbox;
				$userMailboxes[$userId][] = $mailbox['ID'];
			}
		}

		$result = [];

		foreach ($userMailboxes[$userId] as $mailboxId)
		{
			$result[$mailboxId] = $mailboxes[$mailboxId];
		}

		return $result;
	}

	public static function getTheSharedMailboxes($userId = null)
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

		static $mailboxes = [];
		static $userMailboxes = [];

		if (!array_key_exists($userId, $userMailboxes))
		{
			$userMailboxes[$userId] = [];

			(new \CAccess)->updateCodes(['USER_ID' => $userId]);

			$res = static::getList([
				'runtime' => [
					new Entity\ReferenceField(
						'ACCESS',
						'Bitrix\Mail\Internals\MailboxAccessTable',
						[
							'=this.ID' => 'ref.MAILBOX_ID',
						],
						[
							'join_type' => 'LEFT',
						]
					),
					new Entity\ReferenceField(
						'USER_ACCESS',
						'Bitrix\Main\UserAccess',
						[
							'this.ACCESS.ACCESS_CODE' => 'ref.ACCESS_CODE',
						],
						[
							'join_type' => 'LEFT',
						]
					),
				],
				'filter' => [
					[
						'LOGIC' => 'AND',
						'!=USER_ID' => $userId,
						'=USER_ACCESS.USER_ID' => $userId,
					],
					'=ACTIVE' => 'Y',
					'=SERVER_TYPE' => 'imap',
				],
				'order' => [
					'ID' => 'DESC',
				],
			]);

			while ($mailbox = $res->fetch())
			{
				static::normalizeEmail($mailbox);

				$mailboxes[$mailbox['ID']] = $mailbox;
				$userMailboxes[$userId][] = $mailbox['ID'];
			}
		}

		$result = [];

		foreach ($userMailboxes[$userId] as $mailboxId)
		{
			$result[$mailboxId] = $mailboxes[$mailboxId];
		}

		return $result;
	}

	public static function getUserMailboxes($userId = null)
	{
		global $USER;

		if (!($userId > 0 || is_object($USER) && $USER->isAuthorized()))
		{
			return false;
		}

		$sharedMailboxes = static::getTheSharedMailboxes($userId);
		$ownersMailboxes = static::getTheOwnersMailboxes($userId);

		return $ownersMailboxes + $sharedMailboxes;
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
				'data_type' => (static::cryptoEnabled('PASSWORD') ? 'crypto' : 'string'),
				'save_data_modification' => function()
				{
					return array(
						function ($value)
						{
							return static::cryptoEnabled('PASSWORD') ? $value : \CMailUtil::crypt($value);
						}
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function ($value)
						{
							return static::cryptoEnabled('PASSWORD') ? $value : \CMailUtil::decrypt($value);
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
							return serialize($options);
						}
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function ($values)
						{
							return unserialize($values);
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
