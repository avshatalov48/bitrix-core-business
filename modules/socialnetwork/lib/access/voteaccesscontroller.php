<?php

namespace Bitrix\Socialnetwork\Access;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogRightTable;
use Bitrix\Socialnetwork\LogSiteTable;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Tasks\Access\ActionDictionary;
use Bitrix\Tasks\Access\TaskAccessController;
use Bitrix\Socialnetwork\Livefeed\Provider;

class VoteAccessController
{
	private const STEP_LIMIT = 200;

	private $userId;
	private static $accessCodes = [];
	private static $userGroups = [];

	/**
	 * @param array $info
	 * @return array
	 */
	public static function beforeGetVoteList(array $info): array
	{
		return self::checkEvent($info);
	}

	/**
	 * @param array $param
	 * @param array $items
	 * @return array
	 */
	public static function afterGetVoteList(array $param, array $items): array
	{
		if (
			!array_key_exists('CHECK_RIGHTS', $param)
			|| $param['CHECK_RIGHTS'] !== 'Y'
		)
		{
			return [
				'ITEMS' => $items,
			];
		}

		if (array_key_exists('CURRENT_USER_ID', $param))
		{
			$userId = (int) $param['CURRENT_USER_ID'];
		}
		else
		{
			global $USER;
			$userId = (int) $USER->getId();
		}

		$userIds = array_column($items, 'ID');

		$controller = new self($userId);
		$filtered = $controller->filterUsers($userIds);

		if (empty($filtered))
		{
			return [
				'ITEMS' => [],
			];
		}

		foreach ($items as $k => $item)
		{
			if (!in_array($item['ID'], $filtered))
			{
				unset($items[$k]);
			}
		}

		return [
			'ITEMS' => $items,
		];
	}

	/**
	 * @param array $info
	 * @return array
	 */
	public static function checkEvent(array $info = []): array
	{
		$result = new VoteAccessResult();
		$result
			->setResult(true)
			->setMessage('')
			->setErrorType('');

		if (
			!array_key_exists('CHECK_RIGHTS', $info)
			|| $info['CHECK_RIGHTS'] !== 'Y'
		)
		{
			return $result->toArray();
		}

		if (array_key_exists('CURRENT_USER_ID', $info))
		{
			$userId = (int) $info['CURRENT_USER_ID'];
		}
		else
		{
			global $USER;
			$userId = (int) $USER->getId();
		}

		if (!array_key_exists('ENTITY_TYPE_ID', $info))
		{
			return $result->toArray();
		}
		$entityTypeId = (string) $info['ENTITY_TYPE_ID'];

		if (!array_key_exists('ENTITY_ID', $info))
		{
			return $result->toArray();
		}
		$entityId = (int) $info['ENTITY_ID'];

		$controller = new self($userId);
		if (!$controller->check($entityTypeId, $entityId))
		{
			return (new VoteAccessResult())->toArray();
		}

		return $result->toArray();
	}

	public function __construct(int $userId = 0)
	{
		$this->userId = $userId;
	}

	/**
	 * @param string $typeId
	 * @param int $entityId
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function check(string $typeId, int $entityId): bool
	{
		if (!$this->userId)
		{
			return false;
		}

		// $types = Provider::getEntityTypes();

		$logId = $this->getLogId($typeId, $entityId);
		// if (
		// 	!in_array($typeId, $types)
		// 	&& !$logId
		// )
		// {
		// 	// do nothing if there is no record
		// 	return true;
		// }

		if (!$logId)
		{
			return true;
		}

		$logRights = $this->getLogRights($logId);
		if (empty($logRights))
		{
			// this mean that socnet haven't got control for access right for log entry
			return true;
		}

		if ($this->isExtranetUser($this->userId))
		{
			$extranetSiteId = \CExtranet::GetExtranetSiteID();
			$logSites = $this->getLogSites($logId);

			if (!in_array($extranetSiteId, $logSites))
			{
				return false;
			}
		}

		if (in_array("UA", $logRights))
		{
			return true;
		}

		if (
			in_array("AU", $logRights)
			&& $this->userId
		)
		{
			return true;
		}

		$accessCodes = $this->getAccessCodes();
		$isAccess = !empty(array_intersect($accessCodes, $logRights));

		if (
			$typeId === 'TASK'
			&& Loader::includeModule('tasks')
		)
		{
			$isAccess = $isAccess || TaskAccessController::can($this->userId, ActionDictionary::ACTION_TASK_READ, $entityId);
		}

		return $isAccess;
	}

	/**
	 * @param array $userIds
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function filterUsers(array $userIds): array
	{
		if (!$this->isExtranetUser($this->userId))
		{
			return $userIds;
		}

		$userIds = array_map(function($el) {
			return (int) $el;
		}, $userIds);

		$userGroups = $this->getUserGroups();
		if (empty($userGroups))
		{
			return [];
		}

		$chunks = array_chunk($userIds, self::STEP_LIMIT);

		$result = [];
		foreach ($chunks as $chunk)
		{
			$groupUsers = UserToGroupTable::getList([
				'select' => ['USER_ID'],
				'filter' => [
					'@USER_ID' => $chunk,
					'@GROUP_ID' => $userGroups,
					'@ROLE' => UserToGroupTable::getRolesMember(),
				],
			])->fetchAll();
			$groupUsers = array_column($groupUsers, 'USER_ID');
			$groupUsers = array_intersect($userIds, $groupUsers);

			$result = array_merge($result, $groupUsers);
		}

		return array_unique($result);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getUserGroups(): array
	{
		if (array_key_exists($this->userId, self::$userGroups))
		{
			return self::$userGroups[$this->userId];
		}

		self::$userGroups[$this->userId] = [];

		$groups = UserToGroupTable::getList([
			'select' => ['GROUP_ID'],
			'filter' => [
				'=USER_ID' => $this->userId,
				'@ROLE' => UserToGroupTable::getRolesMember(),
			],
		])->fetchAll();

		self::$userGroups[$this->userId] = array_column($groups, 'GROUP_ID');

		return self::$userGroups[$this->userId];
	}

	/**
	 * @param int $logId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getLogRights(int $logId): array
	{
		$rights = LogRightTable::getList([
			'select' => ['GROUP_CODE'],
			'filter' => [
				'=LOG_ID' => $logId,
			],
		])->fetchAll();

		$rights = array_column($rights, 'GROUP_CODE');
		$rights[] = "SA";

		return $rights;
	}

	/**
	 * @param int $logId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getLogSites(int $logId): array
	{
		$sites = LogSiteTable::getList([
			'select' => ['SITE_ID'],
			'filter' => [
				'=LOG_ID' => $logId,
			],
		])->fetchAll();

		$sites = array_column($sites, 'SITE_ID');

		return $sites;
	}

	/**
	 * @return array|mixed
	 */
	private function getAccessCodes()
	{
		if (array_key_exists($this->userId, self::$accessCodes))
		{
			return self::$accessCodes[$this->userId];
		}

		self::$accessCodes[$this->userId] = [];

		$accessCodes = \CAccess::GetUserCodesArray($this->userId);
		foreach ($accessCodes as $code)
		{
			self::$accessCodes[$this->userId][] = $code;
			$signature = (new AccessCode($code))->getSignature();
			if (
				$signature
				&& $signature !== $code
			)
			{
				self::$accessCodes[$this->userId][] = $signature;
			}
		}

		return self::$accessCodes[$this->userId];
	}

	/**
	 * @param string $typeId
	 * @param int $entityId
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getLogId(string $typeId, int $entityId): ?int
	{
		$log = LogTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=RATING_ENTITY_ID' => $entityId,
				'=RATING_TYPE_ID' => $typeId,
			],
			'limit' => 1,
		])->fetch();

		if (!$log)
		{
			return null;
		}

		return (int) $log['ID'];
	}

	/**
	 * @param int $userId
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function isExtranetUser(int $userId): bool
	{
		if (!Loader::includeModule('extranet'))
		{
			return false;
		}

		return !\CExtranet::IsIntranetUser(SITE_ID, $userId);
	}
}