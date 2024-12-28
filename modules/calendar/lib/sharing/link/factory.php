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
	protected static ?UserLinkMapper $userLinkMapper = null;
	protected static ?EventLinkMapper $eventLinkMapper = null;
	protected static ?CrmDealLinkMapper $crmDealLinkMapper = null;
	protected static ?GroupLinkMapper $groupLinkMapper = null;

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
			return $this->getCrmDealLinkMapper()->convertToArray($sharingLink);
		}

		if ($sharingLink instanceof UserLink)
		{
			return $this->getUserLinkMapper()->convertToArray($sharingLink);
		}

		if ($sharingLink instanceof EventLink)
		{
			return $this->getEventLinkMapper()->convertToArray($sharingLink);
		}

		if ($sharingLink instanceof GroupLink)
		{
			return $this->getGroupLinkMapper()->convertToArray($sharingLink);
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

		$mapper = match ($sharingLinkEO->getObjectType())
		{
			Helper::USER_SHARING_TYPE => $this->getUserLinkMapper(),
			Helper::EVENT_SHARING_TYPE => $this->getEventLinkMapper(),
			Helper::CRM_DEAL_SHARING_TYPE => $this->getCrmDealLinkMapper(),
			Helper::GROUP_SHARING_TYPE => $this->getGroupLinkMapper(),
			default => null,
		};

		return $mapper?->getByEntityObject($sharingLinkEO);
	}

	/**
	 * @param int $userId
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAllUserLinks(int $userId): array
	{
		return $this->getUserLinkMapper()->getMap([
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
		return $this->getUserLinkMapper()->getMap([
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
		return $this->getUserLinkMapper()->getMap([
			'=OBJECT_ID' => $userId,
			'=OBJECT_TYPE' => Helper::USER_SHARING_TYPE,
			'=ACTIVE' => 'Y',
			'!=MEMBERS_HASH' => null,
		])->getCollection();
	}

	public function getGroupLinks(int $groupId, int $userId): array
	{
		return $this->getGroupLinkMapper()->getMap([
			'=OBJECT_ID' => $groupId,
			'=HOST_ID' => $userId,
			'=OBJECT_TYPE' => Helper::GROUP_SHARING_TYPE,
			'=ACTIVE' => 'Y',
			'=MEMBERS_HASH' => null,
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

		$this->getUserLinkMapper()->create($userLink);

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

		$this->getEventLinkMapper()->create($eventLink);

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
		?array $memberIds = [],
		?int $contactId = null,
		?int $contactType = null,
		?string $channelId = null,
		?string $senderId = null,
	): CrmDealLink
	{
		$memberHash = $this->generateMembersHash($ownerId, $memberIds);

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

		if ($memberHash !== null)
		{
			$crmDealLink
				->setMembers($this->getMembersFromIds($memberIds))
				->setMembersHash($memberHash)
			;
		}

		$rule = (new Rule\Factory())->getRuleBySharingLink($crmDealLink);
		$crmDealLink->setSharingRule($rule);

		$this->getCrmDealLinkMapper()->create($crmDealLink);

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
			->setFrequentUse(1)
		;

		if ($memberHash !== null)
		{
			$userJointLink
				->setMembers($this->getMembersFromIds($memberIds))
				->setMembersHash($memberHash)
			;
		}

		$this->getUserLinkMapper()->create($userJointLink);

		return $userJointLink;
	}

	public function createCrmDealJointLink(CrmDealLink $crmDealLink, array $memberIds): CrmDealLink
	{
		$crmDealLink
			->setActive(true)
			->setMembers($this->getMembersFromIds($memberIds))
			->setFrequentUse(1)
		;

		$this->getCrmDealLinkMapper()->create($crmDealLink);

		return $crmDealLink;
	}

	public function createGroupLink(int $groupId, int $userId): self
	{
		$groupLink = (new GroupLink())
			->setObjectId($groupId)
			->setHostId($userId)
			->setActive(true)
			->setFrequentUse(1)
		;

		$rule = (new Rule\Factory())->getRuleBySharingLink($groupLink);
		$groupLink->setSharingRule($rule);

		$this->getGroupLinkMapper()->create($groupLink);

		return $this;
	}

	public function createGroupJointLink(int $groupId, array $memberIds): \Bitrix\Calendar\Core\Base\EntityInterface
	{
		$memberHash = $this->generateMembersHash($groupId, $memberIds, 'group');

		if ($existJointLink = $this->getGroupJointLinkByMembersHash($groupId, $memberHash))
		{
			SharingLinkTable::update($existJointLink->getId(), [
				'DATE_EXPIRE' => Sharing\Helper::createSharingLinkExpireDate(
					new DateTime(),
					Sharing\Link\Helper::GROUP_SHARING_TYPE
				),
			]);

			return $existJointLink;
		}

		$groupJointLink = (new GroupLink())
			->setObjectId($groupId)
			->setActive(true)
			->setFrequentUse(1)
			->setDateExpire(
				Sharing\Helper::createSharingLinkExpireDate(
					new DateTime(),
					Sharing\Link\Helper::GROUP_SHARING_TYPE
				)
			)
		;

		if ($memberHash !== null)
		{
			$groupJointLink
				->setMembers($this->getMembersFromIds($memberIds))
				->setMembersHash($memberHash)
			;
		}

		$this->getGroupLinkMapper()->create($groupJointLink);

		return $groupJointLink;
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
			->setSelect(['NAME', 'LAST_NAME', 'ID', 'PERSONAL_PHOTO'])
			->exec()
			->fetchCollection()
		;

		foreach ($users as $user)
		{
			$avatar = '';
			if (!empty($user->getPersonalPhoto()))
			{
				$file = \CFile::ResizeImageGet(
					$user->getPersonalPhoto(),
					['width' => 100, 'height' => 100],
					BX_RESIZE_IMAGE_EXACT,
				);
				$avatar = !empty($file['src']) ? $file['src'] : '';
			}

			$member = new Member();
			$member
				->setId($user->getId())
				->setName($user->getName())
				->setLastName($user->getLastName())
				->setAvatar($avatar)
			;
			$result[] = $member;
		}

		return $result;
	}

	public function generateMembersHash(int $userId, array $memberIds, string $prefix = ''): ?string
	{
		if (empty($memberIds))
		{
			return null;
		}

		$memberIds = array_map(static function ($memberId) {
			return (int)$memberId;
		}, $memberIds);

		sort($memberIds);
		$implodedUsers = $prefix . implode('|', $memberIds) . '|' .  $userId;

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
	public function getEventLinkByEventId(int $eventId, bool $searchActiveOnly = true): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$query = SharingLinkTable::query()
			->setSelect(['*'])
			->where('OBJECT_ID', $eventId)
			->where('OBJECT_TYPE', Helper::EVENT_SHARING_TYPE)
		;

		if ($searchActiveOnly)
		{
			$query->where('ACTIVE', 'Y');
		}

		$sharingLinkEO = $query->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return $this->getEventLinkMapper()->getByEntityObject($sharingLinkEO);
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

		return $this->getEventLinkMapper()->getByEntityObject($sharingLinkEO);
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
		?array $memberIds = [],
		?int $contactId = null,
		?int $contactType = null,
	): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$memberHash = $this->generateMembersHash($ownerId, $memberIds);

		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(self::SELECT)
			->where('OBJECT_ID', $entityId)
			->where('OBJECT_TYPE', Helper::CRM_DEAL_SHARING_TYPE)
			->where('ACTIVE', 'Y')
			->where('OWNER_ID', $ownerId)
			->where('CONTACT_ID', $contactId)
			->where('CONTACT_TYPE', $contactType)
			->where('DATE_CREATE', '>=', (new DateTime())->setTime(0, 0))
			->where('MEMBERS_HASH', $memberHash)
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return $this->getCrmDealLinkMapper()->getByEntityObject($sharingLinkEO);
	}

	public function getLastSentCrmDealLink(int $ownerId): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(['*'])
			->where('OBJECT_TYPE', Helper::CRM_DEAL_SHARING_TYPE)
			->where('OWNER_ID', $ownerId)
			->whereNotNull('CONTACT_ID')
			->whereNotNull('CONTACT_TYPE')
			->setOrder(['ID' => 'desc'])
			->setLimit(1)
			->exec()->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return $this->getCrmDealLinkMapper()->getByEntityObject($sharingLinkEO);
	}

	public function getJointLinkByMembersHash(string $membersHash): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(self::SELECT)
			->where('MEMBERS_HASH', $membersHash)
			->where('ACTIVE', 'Y')
			->where('OBJECT_TYPE', Helper::USER_SHARING_TYPE)
			->exec()->fetchObject()
		;

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return $this->getUserLinkMapper()->getByEntityObject($sharingLinkEO);
	}

	public function getGroupJointLinkByMembersHash(int $groupId, string $membersHash): ?\Bitrix\Calendar\Core\Base\EntityInterface
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(self::SELECT)
			->where('OBJECT_ID', $groupId)
			->where('MEMBERS_HASH', $membersHash)
			->where('ACTIVE', 'Y')
			->where('OBJECT_TYPE', Helper::GROUP_SHARING_TYPE)
			->exec()->fetchObject()
		;

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return $this->getGroupLinkMapper()->getByEntityObject($sharingLinkEO);
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

		return $this->getCrmDealLinkMapper()->getByEntityObject($sharingLinkEO);
	}

	private function getUserLinkMapper(): UserLinkMapper
	{
		if (self::$userLinkMapper === null)
		{
			self::$userLinkMapper = new UserLinkMapper();
		}

		return self::$userLinkMapper;
	}

	private function getEventLinkMapper(): EventLinkMapper
	{
		if (self::$eventLinkMapper === null)
		{
			self::$eventLinkMapper = new EventLinkMapper();
		}

		return self::$eventLinkMapper;
	}

	private function getCrmDealLinkMapper(): CrmDealLinkMapper
	{
		if (self::$crmDealLinkMapper === null)
		{
			self::$crmDealLinkMapper = new CrmDealLinkMapper();
		}

		return self::$crmDealLinkMapper;
	}

	private function getGroupLinkMapper(): GroupLinkMapper
	{
		if (self::$groupLinkMapper === null)
		{
			self::$groupLinkMapper = new GroupLinkMapper();
		}

		return self::$groupLinkMapper;
	}
}
