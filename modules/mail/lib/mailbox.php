<?php

namespace Bitrix\Mail;

use Bitrix\Mail\Internals\MailboxAccessTable;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\DB\ArrayResult;
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

	private const CACHE_TTL = 86400;
	public const SHARED_MAILBOX_KEY = 'mailbox_shared_mailboxes';
	public const OWNER_MAILBOX_KEY = 'mailbox_owners_mailboxes';
	public const SHARED_CACHE_DIR = '/mail/shared/';
	public const OWNER_CACHE_DIR = '/mail/owner/';
	private static array $ownerCache = [];
	private static array $onlyIdOwnerCache = [];
	private static array $sharedCache = [];
	private static array $onlyIdSharedCache = [];

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_mailbox';
	}

	/**
	 * ( A user can connect the same mailbox only once )
	 *
	 * @param $email
	 * @return mixed
	 */
	public static function getUserMailboxWithEmail($email): mixed
	{
		foreach (static::getUserMailboxes() as $mailbox)
		{
			if ($mailbox['EMAIL'] == $email)
			{
				return $mailbox;
			}
		}

		return null;
	}

	/**
	 * @param $email
	 * @return ArrayResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMailboxesWithEmail($email)
	{
		$result = [];
		$list = self::getList(([
			'select' => [
				'ID',
				'USER_ID',
			],
			'filter' => [
				'=EMAIL' => $email,
			],
		]));

		while ($item = $list->fetch())
		{
			$result[] = $item;
		}

		$dbResult = new ArrayResult($result);
		$dbResult->setCount($list->getSelectedRowsCount());

		return $dbResult;
	}

	public static function getOwnerId($mailboxId): int
	{
		$mailbox = self::getList([
			'select' => [
				'USER_ID',
			],
			'filter' => [
				'=ID' => $mailboxId,
			],
			'limit' => 1,
		])->fetch();

		if (isset($mailbox['USER_ID']))
		{
			return (int) $mailbox['USER_ID'];
		}

		return 0;
	}

	public static function getUserMailbox($mailboxId, $userId = null)
	{
		$mailboxes = static::getUserMailboxes($userId);

		return array_key_exists($mailboxId, $mailboxes) ? $mailboxes[$mailboxId] : false;
	}

	public static function getTheOwnersMailboxes($userId = null, bool $onlyIds = false): array
	{
		global $USER;

		if (!($userId > 0 || (is_object($USER) && $USER->isAuthorized())))
		{
			return [];
		}

		if (!($userId > 0))
		{
			$userId = $USER->getId();
		}

		if ($onlyIds && isset(self::$onlyIdOwnerCache[$userId]))
		{
			return self::$onlyIdOwnerCache[$userId];
		}

		if (!$onlyIds && isset(self::$ownerCache[$userId]))
		{
			return self::$ownerCache[$userId];
		}

		$cacheManager = Cache::createInstance();
		$cacheKey = self::getOwnerMailboxCacheKey($userId);
		if ($cacheManager->initCache(self::CACHE_TTL, $cacheKey,self::OWNER_CACHE_DIR))
		{
			$result = $cacheManager->getVars();
			//cache stores only id values, but empty value also works for full request
			if ($onlyIds || $result === [])
			{
				return $result;
			}
		}

		self::$onlyIdOwnerCache[$userId] = [];
		if (!$onlyIds)
		{
			self::$ownerCache[$userId] = [];
		}

		$getListParams = [
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
		];

		if ($onlyIds)
		{
			$getListParams['select'] = ['ID'];
		}

		$res = static::getList($getListParams);
		while ($mailbox = $res->fetch())
		{
			static::normalizeEmail($mailbox);
			$mailboxId = $mailbox['ID'] ?? null;
			self::$onlyIdOwnerCache[$userId][$mailboxId] = [
				'ID' => $mailboxId,
			];

			if (!$onlyIds)
			{
				self::$ownerCache[$userId][$mailboxId] = $mailbox;
			}
		}

		if (empty(self::$onlyIdOwnerCache[$userId]))
		{
			self::$ownerCache[$userId] = [];
		}

		if ($cacheManager->startDataCache(self::CACHE_TTL, $cacheKey,self::OWNER_CACHE_DIR))
		{
			$cacheManager->endDataCache(self::$onlyIdOwnerCache[$userId]);
		}

		return $onlyIds ? self::$onlyIdOwnerCache[$userId] : self::$ownerCache[$userId];
	}

	public static function getTheSharedMailboxes($userId = null, bool $onlyIds = false): array
	{
		global $USER;

		if (!($userId > 0 || (is_object($USER) && $USER->isAuthorized())))
		{
			return [];
		}

		if (!($userId > 0))
		{
			$userId = $USER->getId();
		}

		if ($onlyIds && isset(self::$onlyIdSharedCache[$userId]))
		{
			return self::$onlyIdSharedCache[$userId];
		}

		if (!$onlyIds && isset(self::$sharedCache[$userId]))
		{
			return self::$sharedCache[$userId];
		}

		$cacheManager = Cache::createInstance();
		$cacheKey = self::getSharedMailboxCacheKey($userId);
		if ($cacheManager->initCache(self::CACHE_TTL, $cacheKey, self::SHARED_CACHE_DIR))
		{
			$result = $cacheManager->getVars();
			//cache stores only id values, but empty value also works for full request
			if ($onlyIds || $result === [])
			{
				return $result;
			}
		}

		self::$onlyIdSharedCache[$userId] = [];
		if (!$onlyIds)
		{
			self::$sharedCache[$userId] = [];
		}

		(new \CAccess)->updateCodes(['USER_ID' => $userId]);

		$getListParams = [
			'runtime' => [
				new Entity\ReferenceField(
					'ACCESS',
					'Bitrix\Mail\Internals\MailboxAccessTable',
					[
						'=this.ID' => 'ref.MAILBOX_ID',
					],
					[
						'join_type' => 'LEFT',
					],
				),
				new Entity\ReferenceField(
					'USER_ACCESS',
					'Bitrix\Main\UserAccess',
					[
						'this.ACCESS.ACCESS_CODE' => 'ref.ACCESS_CODE',
					],
					[
						'join_type' => 'LEFT',
					],
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
		];

		if ($onlyIds)
		{
			$getListParams['select'] = ['ID'];
		}

		$res = static::getList($getListParams);

		while ($mailbox = $res->fetch())
		{
			static::normalizeEmail($mailbox);

			$mailboxId = $mailbox['ID'] ?? null;
			self::$onlyIdSharedCache[$userId][$mailboxId] = [
				'ID' => $mailboxId,
			];

			if (!$onlyIds)
			{
				self::$sharedCache[$userId][$mailboxId] = $mailbox;
			}
		}

		if (empty(self::$onlyIdSharedCache[$userId]))
		{
			self::$sharedCache[$userId] = [];
		}

		if ($cacheManager->startDataCache(self::CACHE_TTL, $cacheKey,self::SHARED_CACHE_DIR))
		{
			$cacheManager->endDataCache(self::$onlyIdSharedCache[$userId]);
		}

		return $onlyIds ? self::$onlyIdSharedCache[$userId] : self::$sharedCache[$userId];
	}

	/**
	 * Returns ACTIVE mailboxes that the user has access to
	 *
	 * @param $userId
	 * @return array
	 */
	public static function getUserMailboxes($userId = null, bool $onlyIds = false): array
	{
		global $USER;

		if (!($userId > 0 || (is_object($USER) && $USER->isAuthorized())))
		{
			return [];
		}

		if (!($userId > 0))
		{
			$userId = $USER->getId();
		}

		$sharedMailboxes = static::getTheSharedMailboxes($userId, $onlyIds);
		$ownersMailboxes = static::getTheOwnersMailboxes($userId, $onlyIds);

		return $ownersMailboxes + $sharedMailboxes;
	}

	public static function onAfterAdd(Entity\Event $event): void
	{
		$mailbox = $event->getParameter('fields');
		if (isset($mailbox['USER_ID']))
		{
			self::cleanOwnerCacheByUserId($mailbox['USER_ID']);
		}
	}

	public static function onAfterUpdate(Entity\Event $event): void
	{
		$mailbox = $event->getParameter('fields');
		if (isset($mailbox['USER_ID']))
		{
			unset(self::$ownerCache[$mailbox['USER_ID']]);
		}
	}

	public static function onAfterDelete(Entity\Event $event): void
	{
		self::cleanAllCache();
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
				'required'  => true,
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
						},
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function ($value)
						{
							return static::cryptoEnabled('PASSWORD') ? $value : \CMailUtil::decrypt($value);
						},
					);
				},
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
				'values'    => array('smtp', 'pop3', 'imap', 'controller', 'domain', 'crdomain'),
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
						},
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function ($values)
						{
							return unserialize($values, ['allowed_classes' => false]);
						},
					);
				},
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Main\Site',
				'reference' => array('=this.LID' => 'ref.LID'),
			),
		);
	}

	public static function cleanOwnerCacheByUserId(int $userId): void
	{
		unset(self::$ownerCache[$userId]);
		unset(self::$onlyIdOwnerCache[$userId]);
		Cache::createInstance()
			 ->clean(self::getOwnerMailboxCacheKey($userId),MailboxTable::OWNER_CACHE_DIR)
		;
	}

	private static function getOwnerMailboxCacheKey(int $userId): string
	{
		return MailboxTable::OWNER_MAILBOX_KEY . '_' . $userId;
	}

	private static function cleanAllCache(): void
	{
		self::$onlyIdOwnerCache = [];
		self::$ownerCache = [];
		self::$onlyIdSharedCache = [];
		self::$sharedCache = [];

		$cacheManager = Cache::createInstance();
		$cacheManager->cleanDir(self::SHARED_CACHE_DIR);
		$cacheManager->cleanDir(self::OWNER_CACHE_DIR);
	}

	public static function cleanUserSharedCache(int $userId): void
	{
		unset(self::$sharedCache[$userId]);
		unset(self::$onlyIdSharedCache[$userId]);

		Cache::createInstance()
			 ->clean(self::getSharedMailboxCacheKey($userId),MailboxTable::SHARED_CACHE_DIR)
		;
	}

	private static function getSharedMailboxCacheKey(int $userId): string
	{
		return self::SHARED_MAILBOX_KEY . '_' . $userId;
	}

	public static function cleanAllSharedCache(): void
	{
		self::$onlyIdSharedCache = [];
		self::$sharedCache = [];

		$cacheManager = Cache::createInstance();
		$cacheManager->cleanDir(self::SHARED_CACHE_DIR);
	}
}
