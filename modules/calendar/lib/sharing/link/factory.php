<?php
namespace Bitrix\Calendar\Sharing\Link;

class Factory
{
	private const EVENT_TYPE = 'event';
	private const USER_TYPE = 'user';

	public function getLinkArrayByHash(string $hash): ?array
	{
		$sharingLink = $this->getLinkByHash($hash);

		if ($sharingLink instanceof UserLink)
		{
			return (new UserLinkMapper())->convertToArray($sharingLink);
		}

		if ($sharingLink instanceof EventLink)
		{
			return (new EventLinkMapper())->convertToArray($sharingLink);
		}

		return null;
	}

	public function getLinkByHash(string $hash): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(['*'])
			->where('HASH', $hash)
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		if ($sharingLinkEO->getObjectType() === self::USER_TYPE)
		{
			return (new UserLinkMapper())->getByEntityObject($sharingLinkEO);
		}

		if ($sharingLinkEO->getObjectType() === self::EVENT_TYPE)
		{
			return (new EventLinkMapper())->getByEntityObject($sharingLinkEO);
		}

		return null;
	}

	public function getUserLinksArray($userId): array
	{
		$userLinksCollection = $this->getUserLinks($userId);

		$userLinks = [];
		foreach ($userLinksCollection as $userLink)
		{
			$userLinks[] = (new UserLinkMapper())->convertToArray($userLink);
		}

		return $userLinks;
	}

	public function getUserLinks($userId): array
	{
		return (new UserLinkMapper())->getMap([
			'=OBJECT_ID' => $userId,
		])->getCollection();
	}

	public function createUserLink(int $userId, bool $isSharingOn): self
	{
		$userLink = (new UserLink())
			->setUserId($userId)
			->setSlotSize(60)
			->setActive($isSharingOn)
		;

		(new UserLinkMapper())->create($userLink);

		return $this;
	}

	public function createEventLink(int $eventId, int $ownerId, int $hostId, string $userLinkHash): EventLink
	{
		$eventLink = (new EventLink())
			->setEventId($eventId)
			->setOwnerId($ownerId)
			->setHostId($hostId)
			->setUserLinkHash($userLinkHash)
			->setActive(true)
		;

		(new EventLinkMapper())->create($eventLink);

		return $eventLink;
	}

	public function getEventLinkByEventId(int $eventId): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(['*'])
			->where('OBJECT_ID', $eventId)
			->where('OBJECT_TYPE', self::EVENT_TYPE)
			->where('ACTIVE', 'Y')
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return (new EventLinkMapper())->getByEntityObject($sharingLinkEO);
	}

}