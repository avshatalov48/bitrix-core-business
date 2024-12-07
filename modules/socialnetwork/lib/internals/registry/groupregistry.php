<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Internals\Registry;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\Integration\Im\Chat;

final class GroupRegistry
{
	private const UF_ENTITY_ID = 'SONET_GROUP';

	private static ?self $instance = null;
	private array $storage = [];

	public static function getInstance(): self
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct()
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

		if (isset($this->storage[$groupId]))
		{
			return $this->storage[$groupId];
		}

		$this->load($groupId);

		return $this->storage[$groupId];
	}

	public function invalidate(int $groupId): self
	{
		unset($this->storage[$groupId]);

		return $this;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private function load(int $groupId): void
	{
		$fields = WorkgroupTable::getById($groupId)->fetch();

		if (!$fields)
		{
			$this->storage[$groupId] = null;

			return;
		}

		$groupFields = $fields;

		$this->fillDates($groupFields);
		$this->fillUserFields($groupFields);
		$this->fillChatId($groupFields);
		$this->fillUserMembers($groupFields);

		$this->storage[$groupId] = new Workgroup($groupFields);
	}

	private function fillDates(array &$groupFields): void
	{
		if ($groupFields['DATE_CREATE'] instanceof DateTime)
		{
			$groupFields['DATE_CREATE'] = $groupFields['DATE_CREATE']->toString();
		}
		if ($groupFields['DATE_UPDATE'] instanceof DateTime)
		{
			$groupFields['DATE_UPDATE'] = $groupFields['DATE_UPDATE']->toString();
		}
		if ($groupFields['DATE_ACTIVITY'] instanceof DateTime)
		{
			$groupFields['DATE_ACTIVITY'] = $groupFields['DATE_ACTIVITY']->toString();
		}
	}

	private function fillUserFields(array &$groupFields): void
	{
		$id = (int)$groupFields['ID'];

		global $USER_FIELD_MANAGER;
		$uf = $USER_FIELD_MANAGER->getUserFields(self::UF_ENTITY_ID, $id, false, 0);
		if (is_array($uf))
		{
			$groupFields = array_merge($groupFields, $uf);
		}
	}

	private function fillChatId(array &$groupFields): void
	{
		$id = (int)$groupFields['ID'];

		$chat = Chat\Workgroup::getChatData([
			'group_id' => $id,
			'skipAvailabilityCheck' => true,
		]);

		$chatId = (int)($chat[$id] ?? 0);

		$groupFields['CHAT_ID'] = $chatId;
	}

	private function fillUserMembers(array &$groupFields): void
	{
		$members = UserToGroupTable::query()
			->setSelect(['USER_ID'])
			->where('GROUP_ID', $groupFields['ID'])
			->exec()
			->fetchAll();

		$memberIds = array_column($members, 'USER_ID');

		$groupFields['USER_MEMBERS'] = $memberIds;
	}
}