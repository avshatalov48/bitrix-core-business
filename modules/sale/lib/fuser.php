<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM;
use Bitrix\Main\Session\Session;
use Bitrix\Sale\Internals;

class Fuser
{
	protected const SESSION_USER_ID = 'SALE_USER_ID';
	protected const COOKIE_USER_ID = 'SALE_UID';

	function __construct()
	{

	}

	/**
	 * Returns fuser Id.
	 *
	 * @param bool $skipCreate Create, if not exist.
	 * @return int|null
	 */
	public static function getId($skipCreate = false): ?int
	{
		$id = static::getIdFromSession();
		if ($id !== null)
		{
			return $id;
		}

		$filter = static::getFilterFromCookie(static::getIdFromCookie());
		if ($filter !== null)
		{
			$id = static::getIdByFilter($filter);
		}
		if ($id === null)
		{
			$id = static::getIdByCurrentUser();
		}
		if ($id !== null)
		{
			$internalResult = static::update($id);
			if (!$internalResult->isSuccess())
			{
				$id = null;
			}
			unset($internalResult);
		}

		if ($id === null && !$skipCreate)
		{
			$options = [];
			if (
				self::getCurrentUserId() === null
				&& self::isSaveAnonymousUserCookie()
			)
			{
				$options['save'] = true;
			}
			$internalResult = static::add($options);
			if ($internalResult->isSuccess())
			{
				$id = $internalResult->getId();
			}
			unset($internalResult);
		}

		return $id;
	}

	public static function refreshSessionCurrentId(): ?int
	{
		$id = static::getIdFromSession();
		if ($id !== null)
		{
			return $id;
		}

		return static::getId(true);
	}

	/**
	 * @deprecated
	 *
	 * @return int
	 */
	public static function getCode(): int
	{
		$result = static::getRegeneratedId();

		return $result ?? 0;
	}

	/**
	 * Returns fuser id after change code.
	 *
	 * @return null|int
	 */
	public static function getRegeneratedId(): ?int
	{
		$userId = static::getCurrentUserId();
		if ($userId === null)
		{
			return null;
		}
		$id = static::getIdByFilter([
			'=USER_ID' => $userId,
		]);
		if ($id === null)
		{
			return null;
		}

		$userCode = static::generateCode();

		/** @var ORM\Data\UpdateResult $internalResult */
		$internalResult = static::save(
			$id,
			[
				'CODE' => $userCode,
			]
		);
		if (!$internalResult->isSuccess())
		{
			return null;
		}

		$cookieValue = (static::isEncodeCookie() ? $userCode : (string)$id);
		static::setIdToCookie($cookieValue);
		static::setIdToSession($id);

		return $id;
	}

	/**
	 * Return fuserId for user.
	 *
	 * @param int $userId			User Id.
	 * @return false|int
	 */
	public static function getIdByUserId($userId)
	{
		$userId = (int)$userId;
		$id = static::getIdByFilter([
			'=USER_ID' => (int)$userId,
		]);
		if ($id === null)
		{
			$internalResult = static::createForUserId($userId);
			if ($internalResult->isSuccess())
			{
				$id = (int)$internalResult->getId();
			}
			unset($internalResult);
		}

		return $id === null ? false : $id;
	}

	/**
	 * Return user by fuserId.
	 *
	 * @param int $fuserId		Fuser Id.
	 * @return int
	 * @throws Main\ArgumentException
	 */
	public static function getUserIdById($fuserId): int
	{
		$fuserId = (int)$fuserId;
		if ($fuserId <= 0)
		{
			return 0;
		}
		$row = Internals\FuserTable::getRow([
			'select' => [
				'ID',
				'USER_ID',
			],
			'filter' => [
				'=ID' => $fuserId,
			],
			'order' => [
				'ID' => 'DESC',
			],
		]);
		return (int)($row['USER_ID'] ?? 0);
	}

	/**
	 * Delete fuserId over several days.
	 *
	 * @param int $days			Interval.
	 * @return void
	 */
	public static function deleteOld($days): void
	{
		$days = (int)$days;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$query = "
			DELETE FROM b_sale_fuser
			WHERE
				b_sale_fuser.DATE_UPDATE < " . $helper->addDaysToDateTime(-$days) . "
				AND b_sale_fuser.USER_ID IS NULL
				AND b_sale_fuser.ID NOT IN (select FUSER_ID from b_sale_basket)
		";

		$connection->queryExecute($query);
	}

	/**
	 * Create new fuserId for user.
	 *
	 * @param int $userId User id.
	 * @return ORM\Data\AddResult
	 */
	protected static function createForUserId(int $userId): ORM\Data\AddResult
	{
		$currentTime = new Main\Type\DateTime();

		/** @var ORM\Data\AddResult $result */
		$result = static::save(
			null,
			[
				'DATE_INSERT' => $currentTime,
				'DATE_UPDATE' => $currentTime,
				'USER_ID' => $userId,
				'CODE' => static::generateCode(),
			]
		);

		return $result;
	}

	/**
	 * Session object.
	 *
	 * If session is not accessible, returns null.
	 *
	 * @return Session|null
	 */
	protected static function getSession(): ?Session
	{
		/** @var Session $session */
		$session = Application::getInstance()->getSession();
		if (!$session->isAccessible())
		{
			return null;
		}

		return $session;
	}

	protected static function isEncodeCookie(): bool
	{
		return Option::get('sale', 'encode_fuser_id') === 'Y';
	}

	protected static function isSecureCookie(): bool
	{
		return
			Option::get('sale', 'use_secure_cookies') === 'Y'
			&& Main\Context::getCurrent()->getRequest()->isHttps()
		;
	}

	protected static function isSaveAnonymousUserCookie(): bool
	{
		return Option::get('sale', 'save_anonymous_fuser_cookie') === 'Y';
	}

	protected static function getIdFromSession(): ?int
	{
		$session = static::getSession();
		if ($session === null)
		{
			return null;
		}
		if (!$session->has(self::SESSION_USER_ID))
		{
			return null;
		}
		$rawValue = $session->get(self::SESSION_USER_ID);
		unset($session);

		$value = (int)$rawValue;
		$rawValue = (string)$rawValue;
		if ((string)$value !== $rawValue)
		{
			$value = 0;
		}

		return ($value > 0 ? $value : null);
	}

	protected static function setIdToSession(int $id): void
	{
		$session = static::getSession();
		if ($session === null)
		{
			return;
		}
		if ($id <= 0)
		{
			return;
		}
		$session->set(self::SESSION_USER_ID, $id);
	}

	protected static function clearSession(): void
	{
		$session = static::getSession();
		if ($session === null)
		{
			return;
		}
		$session->remove(self::SESSION_USER_ID);
	}

	protected static function getIdFromCookie(): ?string
	{
		$request = Main\Context::getCurrent()->getRequest();

		return $request->getCookie(self::COOKIE_USER_ID);
	}

	protected static function setIdToCookie(string $id): void
	{
		$cookie = new Main\Web\Cookie(self::COOKIE_USER_ID, $id, null);
		$cookie
			->setSecure(static::isSecureCookie())
			->setHttpOnly(false)
			->setSpread(Main\Web\Cookie::SPREAD_DOMAIN | Main\Web\Cookie::SPREAD_SITES)
		;

		$response = Main\Context::getCurrent()->getResponse();

		$response->addCookie($cookie);

		unset($response);
		unset($cookie);
	}

	protected static function clearCookie(): void
	{
		static::setIdToCookie('0');
	}

	protected static function getFilterFromCookie(?string $cookie): ?array
	{
		if ($cookie === null || $cookie === '')
		{
			return null;
		}

		$filter = [];
		if (static::isEncodeCookie())
		{
			$filter = [
				'=CODE' => $cookie,
			];
		}
		else
		{
			$cookie = (int)$cookie;
			if ($cookie > 0)
			{
				$filter = [
					'=ID' => $cookie,
				];
			}
		}

		return (!empty($filter) ? $filter : null);
	}

	protected static function getIdByFilter(array $filter): ?int
	{
		$row = Internals\FuserTable::getRow([
			'select' => [
				'ID',
			],
			'filter' => $filter,
			'order' => [
				'ID' => 'DESC',
			]
		]);
		$result = (int)($row['ID'] ?? 0);

		return $result > 0 ? $result: null;
	}

	protected static function getIdByCurrentUser(): ?int
	{
		$userId = self::getCurrentUserId();
		if ($userId === null)
		{
			return null;
		}

		return static::getIdByFilter([
			'=USER_ID' => $userId,
		]);
	}

	private static function getCurrentUserId(): ?int
	{
		global $USER;

		if (!(
			isset($USER)
			&& $USER instanceof \CUser
		))
		{
			return null;
		}

		$userId = (int)$USER->GetID();

		return $userId > 0 ? $userId : null;
	}

	protected static function generateCode(): string
	{
		return md5(time() . Main\Security\Random::getString(10, true));
	}

	public static function add(array $options = []): Result
	{
		$result = new Result();

		$currentTime = new Main\Type\DateTime();
		$userCode = static::generateCode();
		$currentUserId = self::getCurrentUserId();

		$options['save'] ??= false;
		if (!is_bool($options['save']))
		{
			$options['save'] = false;
		}

		/** @var ORM\Data\AddResult $internalResult */
		$internalResult = static::save(
			null,
			[
				'DATE_INSERT' => $currentTime,
				'DATE_UPDATE' => $currentTime,
				'USER_ID' => $currentUserId,
				'CODE' => $userCode,
			]
		);
		if (!$internalResult->isSuccess())
		{
			$result->addErrors($internalResult->getErrors());

			return $result;
		}

		$id = (int)$internalResult->getId();
		if (
			$options['save']
			&& ($currentUserId !== null || self::isSaveAnonymousUserCookie())
		)
		{
			$cookieValue = (static::isEncodeCookie() ? $userCode : (string)$id);
			static::setIdToCookie($cookieValue);
		}
		static::setIdToSession($id);
		$result->setId($id);

		return $result;
	}

	public static function update(int $id, array $options = []): Result
	{
		$result = new Result();

		if ($id <= 0)
		{
			return $result;
		}

		$options['update'] ??= true;
		if (!is_bool($options['update']))
		{
			$options['update'] = true;
		}
		$options['save'] ??= false;
		if (!is_bool($options['save']))
		{
			$options['save'] = false;
		}

		$fuser = Internals\FuserTable::getRow([
			'select' => [
				'ID',
				'USER_ID',
				'CODE'
			],
			'filter' => [
				'=ID' => $id,
			]
		]);

		$databaseUpdate = $options['update'] && $fuser !== null;
		$encodeCookie = static::isEncodeCookie();

		$userCode = trim((string)($fuser['CODE'] ?? null));
		$currentUserId = self::getCurrentUserId();

		if ($databaseUpdate)
		{
			$fields = [
				'DATE_UPDATE' => new Main\Type\DateTime(),
			];
			if ($currentUserId !== null)
			{
				$userId = (int)$fuser['USER_ID'];
				if ($userId === 0 || $userId === $currentUserId)
				{
					$fields['USER_ID'] = $currentUserId;
				}
			}
			if ($encodeCookie && $userCode === '')
			{
				$userCode = static::generateCode();
				$fields['CODE'] = $userCode;
			}

			/** @var ORM\Data\UpdateResult $internalResult */
			$internalResult = static::save($id, $fields);
			if (!$internalResult->isSuccess())
			{
				$result->addErrors($internalResult->getErrors());

				return $result;
			}
		}
		else
		{
			if ($encodeCookie && $userCode === '')
			{
				$userCode = static::generateCode();
			}
		}

		if ($options['save'] && $currentUserId !== null)
		{
			$cookieValue = (static::isEncodeCookie() ? $userCode : (string)$id);
			static::setIdToCookie($cookieValue);
		}
		static::setIdToSession($id);

		return $result;
	}

	protected static function save(?int $id, array $fields): ORM\Data\Result
	{
		$pool = Application::getInstance()->getConnectionPool();
		$pool->useMasterOnly(true);
		if ($id === null)
		{
			$internalResult = Internals\FuserTable::add($fields);
		}
		else
		{
			$internalResult = Internals\FuserTable::update($id, $fields);
		}
		$pool->useMasterOnly(false);
		unset($pool);

		return $internalResult;
	}

	public static function handlerOnUserLogin($userId, array $params): void
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			return;
		}
		$options = [
			'update' => ($params['update'] ?? true) === true,
			'save' => ($params['save'] ?? false) === true,
		];
		if (!$options['update'])
		{
			return;
		}

		$id = static::getIdFromSession();
		if ($id === null)
		{
			$filter = static::getFilterFromCookie(static::getIdFromCookie());
			if ($filter !== null)
			{
				$id = static::getIdByFilter($filter);
			}
		}

		$filter = [
			'=USER_ID' => $userId
		];
		if ($id !== null)
		{
			$filter['!=ID'] = $id;
		}
		$row = Internals\FuserTable::getRow([
			'select' => [
				'ID',
			],
			'filter' => $filter,
			'order' => [
				'ID' => 'DESC',
			],
		]);
		if ($row !== null)
		{
			$newId = (int)$row['ID'];
			if ($id !== null)
			{
				if (\CSaleBasket::TransferBasket($id, $newId))
				{
					\CSaleUser::Delete($id);
				}
			}
			$id = $newId;
		}

		if ($id !== null)
		{
			static::update($id, $options);
		}
		else
		{
			unset($options['update']);
			static::add($options);
		}
	}

	public static function handlerOnUserLogout($userId)
	{
		static::clearSession();
		static::clearCookie();
	}
}
