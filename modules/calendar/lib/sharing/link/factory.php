<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Internals\SharingLinkTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Sharing\Link\Member\Member;
use Bitrix\Main\UserTable;

class Factory
{
	protected static ?Factory $instance = null;
	protected const SELECT = ['*', 'MEMBERS', 'MEMBERS.USER', 'MEMBERS.IMAGE'];

	/**
	 * @return Factory
	 */
	public static function getInstance(): Factory
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}


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
			->setSelect(self::SELECT)
			->where('HASH', $hash)
			->exec()->fetchObject()
		;

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
	 * @param int $userId
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAllUserLinks(int $userId): array
	{
		return (new UserLinkMapper())->getMap([
			'=OBJECT_ID' => $userId,
			'=OBJECT_TYPE' => Helper::USER_SHARING_TYPE,
			'=ACTIVE' => 'Y',
		])->getCollection();
	}

	/**
	 * gets user public links by user id
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
			'=MEMBERS_HASH' => null,
		])->getCollection();
	}

	/**
	 * gets user joint public links by user id
	 *
	 * @param $userId
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getUserJointLinks($userId): array
	{
		return (new UserLinkMapper())->getMap([
			'=OBJECT_ID' => $userId,
			'=OBJECT_TYPE' => Helper::USER_SHARING_TYPE,
			'=ACTIVE' => 'Y',
			'!=MEMBERS_HASH' => null,
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
			->setActive(true)
			->setFrequentUse(1)
		;

		$rule = (new Rule\Factory())->getRuleBySharingLink($userLink);
		$userLink->setSharingRule($rule);

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
	 * @param string|null $channelId
	 * @param string|null $senderId
	 * @return CrmDealLink
	 * @throws ArgumentException
	 */
	public function createCrmDealLink(
		int $ownerId,
		int $entityId,
		?int $contactId = null,
		?int $contactType = null,
		?string $channelId = null,
		?string $senderId = null
	): CrmDealLink
	{
		$crmDealLink = (new CrmDealLink())
			->setOwnerId($ownerId)
			->setEntityId($entityId)
			->setContactType($contactType)
			->setContactId($contactId)
			->setChannelId($channelId)
			->setSenderId($senderId)
			->setActive(true)
			->setDateExpire(
				Sharing\Helper::createSharingLinkExpireDate(
					new DateTime(),
					Sharing\Link\Helper::CRM_DEAL_SHARING_TYPE
				)
			)
			->setFrequentUse(1)
		;

		$rule = (new Rule\Factory())->getRuleBySharingLink($crmDealLink);
		$crmDealLink->setSharingRule($rule);

		(new CrmDealLinkMapper())->create($crmDealLink);

		return $crmDealLink;
	}

	public function createUserJointLink(int $userId, array $memberIds): \Bitrix\Calendar\Core\Base\EntityInterface
	{
		$memberHash = $this->generateMembersHash($userId, $memberIds);

		if ($existJointLink = $this->getJointLinkByMembersHash($memberHash))
		{
			SharingLinkTable::update($existJointLink->getId(), [
				'FREQUENT_USE' => $existJointLink->getFrequentUse() + 1,
			]);

			return $existJointLink;
		}

		$userJointLink = (new UserLink())
			->setUserId($userId)
			->setActive(true)
			->setMembers($this->getMembersFromIds($memberIds))
			->setMembersHash($memberHash)
			->setFrequentUse(1)
		;

		(new UserLinkMapper())->create($userJointLink);

		return $userJointLink;
	}

	public function createCrmDealJointLink(CrmDealLink $crmDealLink, array $memberIds): CrmDealLink
	{
		$crmDealLink
			->setActive(true)
			->setMembers($this->getMembersFromIds($memberIds))
			->setFrequentUse(1)
		;

		(new CrmDealLinkMapper())->create($crmDealLink);

		return $crmDealLink;
	}

	private function getMembersFromIds(array $memberIds): array
	{
		$memberIds = array_map(static function ($memberId) {
			return (int)$memberId;
		}, $memberIds);

		$result = [];
		$users = UserTable::query()
			->whereIn('ID', $memberIds)
			->where('IS_REAL_USER', 'Y')
			->setSelect(['NAME', 'LAST_NAME', 'ID'])
			->exec()
			->fetchCollection()
		;

		foreach ($users as $user)
		{
			$member = new Member();
			$member
				->setId($user->getId())
				->setName($user->getName())
				->setLastName($user->getLastName())
			;
			$result[] = $member;
		}

		return $result;
	}

	public function generateMembersHash(int $userId, array $memberIds): string
	{
		$memberIds = array_map(static function ($memberId) {
			return (int)$memberId;
		}, $memberIds);

		sort($memberIds);
		$implodedUsers = implode('|', $memberIds) . '|' .  $userId;

		return md5($implodedUsers);
	}

	/**
	 * gets calendar sharing event public link by eventId
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
			->exec()->fetchObject()
		;

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
			->setSelect(self::SELECT)
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
			->setSelect(self::SELECT)
			->where('OBJECT_ID', $entityId)
			->where('OBJECT_TYPE', Helper::CRM_DEAL_SHARING_TYPE)
			->where('ACTIVE', 'Y')
			->where('OWNER_ID', $ownerId)
			->where('CONTACT_ID', $contactId)
			->where('CONTACT_TYPE', $contactType)
			->where('DATE_CREATE', '>=', (new DateTime())->setTime(0, 0))
			->whereNull('MEMBERS.MEMBER_ID')
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return (new CrmDealLinkMapper())->getByEntityObject($sharingLinkEO);
	}

	public function getJointLinkByMembersHash(string $membersHash): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(self::SELECT)
			->where('MEMBERS_HASH', $membersHash)
			->where('ACTIVE', 'Y')
			->exec()->fetchObject()
		;

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return (new UserLinkMapper())->getByEntityObject($sharingLinkEO);
	}

	public function getParentLinkByConferenceId(string $conferenceId): ?Joint\JointLink
	{
		$entityObject = SharingLinkTable::query()
			->setSelect(['PARENT_LINK_HASH'])
			->where('CONFERENCE_ID', $conferenceId)
			->exec()->fetchObject()
		;

		if (is_null($entityObject))
		{
			return null;
		}

		$parentLink = $this->getLinkByHash($entityObject->getParentLinkHash());

		return $parentLink instanceof Joint\JointLink ? $parentLink : null;
	}

	public function getCrmDealJointLink(
		int $entityId,
		int $ownerId,
		?int $contactId = null,
		?int $contactType = null
	): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(self::SELECT)
			->where('OBJECT_ID', $entityId)
			->where('OBJECT_TYPE', Helper::CRM_DEAL_SHARING_TYPE)
			->where('ACTIVE', 'Y')
			->where('OWNER_ID', $ownerId)
			->where('CONTACT_ID', $contactId)
			->where('CONTACT_TYPE', $contactType)
			->whereNotNull('MEMBERS.MEMBER_ID')
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return (new CrmDealLinkMapper())->getByEntityObject($sharingLinkEO);
	}
}