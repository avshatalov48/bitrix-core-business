<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Internals\Registry;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Integration\IM\Dialog;
use Bitrix\Socialnetwork\Internals\Member\MemberEntityCollection;
use Bitrix\Socialnetwork\Internals\Registry\Event\GroupLoadedEvent;
use Bitrix\Socialnetwork\Internals\site\SiteEntityCollection;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Space\Member;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\Integration\Im\Chat;

class GroupRegistry
{
	protected const UF_ENTITY_ID = 'SONET_GROUP';

	protected static array $storage = [];

	private static ?self $instance = null;

	public static function getInstance(): static
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	protected function __construct()
	{

	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function get(int $groupId): ?Workgroup
	{
		if ($groupId <= 0)
		{
			return null;
		}

		if (isset(static::$storage[$groupId]))
		{
			$this->onObjectAlreadyLoaded(static::$storage[$groupId]);

			return static::$storage[$groupId];
		}

		$this->load($groupId);

		return static::$storage[$groupId];
	}

	public function invalidate(int $groupId): static
	{
		unset(static::$storage[$groupId]);

		return $this;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function load(int $groupId): void
	{
		$fields = $this->loadData($groupId);
		if (empty($fields))
		{
			static::$storage[$groupId] = null;

			return;
		}

		$this->fillStorage($fields);
	}

	protected function loadData(int $groupId): array
	{
		$select = [
			'select' => [
				'*',
				'SITES',
				'MEMBERS',
			],
		];

		$fields = WorkgroupTable::getByPrimary($groupId, $select)->fetchObject()?->collectValues();

		if (empty($fields))
		{
			return [];
		}
		
		$this->fillDates($fields);
		$this->fillUserFields($fields);
		$this->fillChatId($fields);
		$this->fillUserMembers($fields);
		$this->fillSites($fields);

		$this->remapBooleanFields($fields);

		return $fields;
	}

	protected function onObjectAlreadyLoaded(?Workgroup $group): void
	{

	}

	protected function onObjectLoaded(Workgroup $group): void
	{
		$event = new GroupLoadedEvent($group);

		$event->send();
	}

	protected function fillStorage(array $fields): void
	{
		$groupId = (int)$fields['ID'];
		$groupType = Type::tryFrom((string)$fields['TYPE']);

		if ($groupType === Type::Collab)
		{
			static::$storage[$groupId] = new Collab($fields);
		}
		else
		{
			static::$storage[$groupId] = new Workgroup($fields);
		}

		$this->onObjectLoaded(static::$storage[$groupId]);
	}

	protected function fillDates(array &$fields): void
	{
		if ($fields['DATE_CREATE'] instanceof DateTime)
		{
			$fields['DATE_CREATE'] = $fields['DATE_CREATE']->toString();
		}
		if ($fields['DATE_UPDATE'] instanceof DateTime)
		{
			$fields['DATE_UPDATE'] = $fields['DATE_UPDATE']->toString();
		}
		if ($fields['DATE_ACTIVITY'] instanceof DateTime)
		{
			$fields['DATE_ACTIVITY'] = $fields['DATE_ACTIVITY']->toString();
		}
	}

	protected function fillUserFields(array &$fields): void
	{
		$id = (int)$fields['ID'];

		global $USER_FIELD_MANAGER;
		$uf = $USER_FIELD_MANAGER->getUserFields(static::UF_ENTITY_ID, $id, false, 0);
		if (is_array($uf))
		{
			$fields = array_merge($fields, $uf);
		}
	}

	protected function fillChatId(array &$fields): void
	{
		$id = (int)$fields['ID'];

		$chat = Chat\Workgroup::getChatData([
			'group_id' => $id,
			'skipAvailabilityCheck' => true,
		]);

		$chatId = (int)($chat[$id] ?? 0);

		$fields['CHAT_ID'] = $chatId;

		$fields['DIALOG_ID'] = Dialog::getDialogId($chatId);
	}

	protected function fillUserMembers(array &$fields): void
	{
		$users = $fields['MEMBERS'];

		if (!$users instanceof MemberEntityCollection)
		{
			return;
		}

		if ($users->isEmpty())
		{
			return;
		}

		$users = array_map(static fn (Member $member): array => $member->collectValues(), iterator_to_array($users));

		$members = array_filter($users, static fn (array $member): bool => in_array($member['ROLE'], UserToGroupTable::getRolesMember(), true));
		$memberIds = array_column($members, 'USER_ID');

		$fields['MEMBERS'] = $memberIds;

		$ordinaryMembers = array_filter($users, static fn (array $member): bool => $member['ROLE'] === UserToGroupTable::ROLE_USER);
		$ordinaryMembersIds = array_column($ordinaryMembers, 'USER_ID');

		$fields['ORDINARY_MEMBERS'] = $ordinaryMembersIds;

		$requested = array_filter($users, static fn (array $member): bool => $member['ROLE'] === UserToGroupTable::ROLE_REQUEST);
		$requestedIds = array_column($requested, 'USER_ID');

		$fields['INVITED_MEMBERS'] = $requestedIds;

		$moderators = array_filter($users, static fn (array $member): bool => $member['ROLE'] === UserToGroupTable::ROLE_MODERATOR);
		$moderatorsIds = array_column($moderators, 'USER_ID');

		$fields['MODERATOR_MEMBERS'] = $moderatorsIds;
	}

	protected function fillSites(array &$fields): void
	{
		if (!$fields['SITES'] instanceof SiteEntityCollection)
		{
			return;
		}

		$fields['SITE_IDS'] = $fields['SITES']->getSiteIdList();
	}

	protected function remapBooleanFields(array &$fields): void
	{
		$entity = WorkgroupTable::getEntity();

		foreach ($fields as $key => $value)
		{
			try
			{
				$field = $entity->getField($key);
			}
			catch (ArgumentException)
			{
				continue;
			}

			if ($field->getDataType() === 'boolean')
			{
				$fields[$key] = $value ? 'Y' : 'N';
			}
		}
	}
}
