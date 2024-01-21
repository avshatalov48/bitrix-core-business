<?php

namespace Bitrix\Socialnetwork\Internals\Registry;


use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Main\Data\Cache;

class UserRegistry
{
	private const CACHE_PREFIX = 'sonet_user2group_U';
	private const CACHE_DIR = '/sonet/userregistry';
	private const CACHE_TTL = 3 * 60 * 60;

	public const MODE_GROUP_ALL = 'all';
	public const MODE_GROUP = 'group';
	public const MODE_PROJECT = 'project';
	public const MODE_SCRUM = 'scrum';
	public const MODE_EXCLUDE_SCRAM = 'ex_scram';

	private static array $instance = [];

	private int $userId;
	private array $userGroups = [];
	private array $userProjects = [];
	private array $userWorkgroups = [];
	private array $userScrum = [];

	public static function getInstance(int $userId): self
	{
		if (!array_key_exists($userId, self::$instance))
		{
			self::$instance[$userId] = new self($userId);
		}
		return self::$instance[$userId];
	}

	public function getUserGroups(string $mode = self::MODE_GROUP_ALL): array
	{
		switch ($mode)
		{
			case self::MODE_GROUP:
				$groups = $this->userWorkgroups;
				break;
			case self::MODE_PROJECT:
				$groups = $this->userProjects;
				break;
			case self::MODE_SCRUM:
				$groups = $this->userScrum;
				break;
			case self::MODE_EXCLUDE_SCRAM:
				$groups = array_replace($this->userProjects, $this->userWorkgroups);
				break;
			default:
				$groups = $this->userGroups;
		}

		return $groups;
	}

	private function __construct(int $userId)
	{
		$this->userId = $userId;
		$this->loadInfo();
	}

	private function loadInfo(): void
	{
		$this->loadGroupInfo();
	}

	private function loadGroupInfo(): void
	{
		$cache = Cache::createInstance();

		if ($cache->initCache(self::CACHE_TTL, $this->getCacheId(), $this->getCacheDir()))
		{
			$res = $cache->getVars();
		}
		else
		{
			$res = UserToGroupTable::query()
				->addSelect('GROUP_ID')
				->addSelect('ROLE')
				->addSelect('WORKGROUP.PROJECT', 'PROJECT')
				->addSelect('WORKGROUP.SCRUM_MASTER_ID', 'SCRUM_MASTER')
				->registerRuntimeField(
					new Reference(
						'WORKGROUP',
						WorkgroupTable::class,
						Join::on('this.GROUP_ID', 'ref.ID'),
						['join_type' => 'LEFT']
					)
				)
				->setFilter([
					'=USER_ID' => $this->userId,
					'@ROLE' => [UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER]
				])
				->fetchAll();

			$taggedCache = Application::getInstance()->getTaggedCache();
			$taggedCache->StartTagCache($this->getCacheDir());
			$taggedCache->RegisterTag($this->getCacheTag());

			$cache->startDataCache();
			$cache->endDataCache($res);
			$taggedCache->EndTagCache();
		}

		foreach ($res as $row)
		{
			$this->userGroups[$row['GROUP_ID']] = $row['ROLE'];
			if ((int)$row['SCRUM_MASTER'] > 0)
			{
				$this->userScrum[$row['GROUP_ID']] = $row['ROLE'];
			}
			elseif ($row['PROJECT'] === 'Y')
			{
				$this->userProjects[$row['GROUP_ID']] = $row['ROLE'];
			}
			else
			{
				$this->userWorkgroups[$row['GROUP_ID']] = $row['ROLE'];
			}
		}
	}

	private function getCacheTag(): string
	{
		return self::CACHE_PREFIX . $this->userId;
	}

	private function getCacheDir(): string
	{
		return self::CACHE_DIR . '/' . substr(md5($this->userId),2,2) . '/';
	}

	private function getCacheId(): string
	{
		return $this->getCacheTag();
	}
}