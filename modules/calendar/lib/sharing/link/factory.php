<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Calendar\Sharing;

class Factory
{
	/**
	 * returns public link data in array by hash
	 *
	 * @param string $hash
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getLinkArrayByHash(string $hash): ?array
	{
		$sharingLink = $this->getLinkByHash($hash);

		if ($sharingLink instanceof CrmDealLink)
		{
			return (new CrmDealLinkMapper())->convertToArray($sharingLink);
		}

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

	/**
	 * gets public link object by hash
	 *
	 * @param string $hash
	 * @return \Bitrix\Calendar\Core\Base\EntityInterface|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
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

		if ($sharingLinkEO->getObjectType() === Helper::USER_SHARING_TYPE)
		{
			return (new UserLinkMapper())->getByEntityObject($sharingLinkEO);
		}

		if ($sharingLinkEO->getObjectType() === Helper::EVENT_SHARING_TYPE)
		{
			return (new EventLinkMapper())->getByEntityObject($sharingLinkEO);
		}

		if ($sharingLinkEO->getObjectType() === Helper::CRM_DEAL_SHARING_TYPE)
		{
			return (new CrmDealLinkMapper())->getByEntityObject($sharingLinkEO);
		}

		return null;
	}

	/**
	 * gets all user public links data in array by user id
	 *
	 * @param $userId
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
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

	/**
	 * gets all user public links by user id
	 *
	 * @param $userId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getUserLinks($userId): array
	{
		return (new UserLinkMapper())->getMap([
			'=OBJECT_ID' => $userId,
			'=OBJECT_TYPE' => Helper::USER_SHARING_TYPE,
			'=ACTIVE' => 'Y',
		])->getCollection();
	}

	/**
	 * creates user public link for calendar sharing by user id
	 *
	 * @param int $userId
	 * @return $this
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function createUserLink(int $userId): self
	{
		$userLink = (new UserLink())
			->setUserId($userId)
			->setActive(true);

		(new UserLinkMapper())->create($userLink);

		return $this;
	}

	/**
	 * creates public link for sharing event
	 *
	 * @param int $eventId
	 * @param int $ownerId
	 * @param int $hostId
	 * @param string $parentLinkHash
	 * @param DateTime|null $expireDate
	 * @return EventLink
	 * @throws ArgumentException
	 */
	public function createEventLink(array $params): EventLink
	{
		$eventLink = (new EventLink())
			->setEventId($params['eventId'])
			->setOwnerId($params['ownerId'])
			->setHostId($params['hostId'])
			->setParentLinkHash($params['parentLinkHash'])
			->setActive(true)
			->setDateExpire($params['expiryDate'] ?? null)
			->setExternalUserName($params['externalUserName'] ?? null)
		;

		(new EventLinkMapper())->create($eventLink);

		return $eventLink;
	}

	/**
	 * creates crm deal public link for calendar sharing
	 *
	 * @param int $ownerId
	 * @param int $entityId
	 * @param int|null $contactId
	 * @param int|null $contactType
	 * @return CrmDealLink
	 * @throws ArgumentException
	 */
	public function createCrmDealLink(
		int  $ownerId,
		int  $entityId,
		?int $contactId = null,
		?int $contactType = null
	): CrmDealLink
	{
		$crmDealLink = (new CrmDealLink())
			->setOwnerId($ownerId)
			->setEntityId($entityId)
			->setContactType($contactType)
			->setContactId($contactId)
			->setActive(true)
			->setDateExpire(
				Sharing\Helper::createSharingLinkExpireDate(
					new DateTime(),
					Sharing\Link\Helper::CRM_DEAL_SHARING_TYPE
				)
			)
		;

		(new CrmDealLinkMapper())->create($crmDealLink);

		return $crmDealLink;
	}

	/**
	 * gets calendar sharing event public link by enetId
	 *
	 * @param int $eventId
	 * @return \Bitrix\Calendar\Core\Base\EntityInterface|null
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getEventLinkByEventId(int $eventId): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(['*'])
			->where('OBJECT_ID', $eventId)
			->where('OBJECT_TYPE', Helper::EVENT_SHARING_TYPE)
			->where('ACTIVE', 'Y')
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return (new EventLinkMapper())->getByEntityObject($sharingLinkEO);
	}

	/**
	 * gets calendar sharing event public link by enetId
	 *
	 * @param int $eventId
	 * @return \Bitrix\Calendar\Core\Base\EntityInterface|null
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getDeletedEventLinkByEventId(int $eventId): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(['*'])
			->where('OBJECT_ID', $eventId)
			->where('OBJECT_TYPE', Helper::EVENT_SHARING_TYPE)
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return (new EventLinkMapper())->getByEntityObject($sharingLinkEO);
	}

	/**
	 * gets crm deal public link for calendar sharing
	 *
	 * @param int $entityId
	 * @param int $ownerId
	 * @param int|null $contactId
	 * @param int|null $contactType
	 * @return \Bitrix\Calendar\Core\Base\EntityInterface|null
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getCrmDealLink(
		int $entityId,
		int $ownerId,
		?int $contactId = null,
		?int $contactType = null
	): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(['*'])
			->where('OBJECT_ID', $entityId)
			->where('OBJECT_TYPE', Helper::CRM_DEAL_SHARING_TYPE)
			->where('ACTIVE', 'Y')
			->where('OWNER_ID', $ownerId)
			->where('CONTACT_ID', $contactId)
			->where('CONTACT_TYPE', $contactType)
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return (new CrmDealLinkMapper())->getByEntityObject($sharingLinkEO);
	}
}