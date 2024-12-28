<?php

namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Sharing\Link\Joint\JointLinkMapper;
use Bitrix\Calendar\Sharing\Link;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\Json;

final class GroupLinkMapper extends JointLinkMapper
{
	protected const DEFAULT_SELECT = ['*', 'MEMBERS', 'MEMBERS.USER', 'MEMBERS.IMAGE'];

	/**
	 * @param GroupLink $sharingLink
	 */
	public function convertToArray($sharingLink): array
	{
		$baseArray = parent::convertToArray($sharingLink);

		if (!($baseArray['isJoint'] ?? null))
		{
			$baseArray['members'][] = $this->getMemberByUserId($sharingLink->getHostId())->toArray();
		}

		return array_merge($baseArray, [
			'groupId' => $sharingLink->getGroupId(),
			'slotSize' => $sharingLink->getSlotSize(),
			'rule' => (new Rule\Mapper())->convertToArray($sharingLink->getSharingRule()),
			'hostId' => $sharingLink->getHostId(),
			// rewrite field, cause ownerId for group link is group ID, but we need hostId here (creator of link)
			'userIds' => array_merge(
				[$sharingLink->getHostId()],
				array_map(static fn ($member) => $member['id'], $baseArray['members'])
			),
		]);
	}

	protected function getOptionsArray($entity): array
	{
		$options = [];

		if (!empty($entity->getSlotSize()))
		{
			$options['slotSize'] = $entity->getSlotSize();
		}

		return $options;
	}

	protected function getSpecificFields($entity): array
	{
		return [
			'MEMBERS_HASH' => $entity->getMembersHash(),
			'HOST_ID' => $entity->getHostId(),
		];
	}

	protected function getEntityClass(): string
	{
		return GroupLink::class;
	}

	protected function convertToObject($objectEO): ?GroupLink
	{
		$sharingGroupLink = (new GroupLink())
			->setId($objectEO->getId())
			->setGroupId($objectEO->getObjectId())
			->setDateCreate($objectEO->getDateCreate())
			->setDateExpire($objectEO->getDateExpire())
			->setActive($objectEO->getActive())
			->setHash($objectEO->getHash())
			->setMembersHash($objectEO->getMembersHash())
			->setFrequentUse($objectEO->getFrequentUse())
			->setHostId($objectEO->getHostId())
		;

		if ($objectEO->getMembers()?->count() > 0)
		{
			$sharingGroupLink->setMembers((new Member\Manager())->createMembersFromEntityObject($objectEO->getMembers()));
		}

		$options = Json::decode($objectEO->getOptions() ?? '');
		if (!empty($options['slotSize']))
		{
			$sharingGroupLink->setSlotSize($options['slotSize']);
		}

		$rule = (new Rule\Factory())->getRuleBySharingLink($sharingGroupLink);
		$sharingGroupLink->setSharingRule($rule);

		return $sharingGroupLink;
	}

	private function getMemberByUserId($userId): ?Link\Member\Member
	{
		$user = UserTable::query()
			->where('ID', $userId)
			->where('IS_REAL_USER', 'Y')
			->setSelect(['NAME', 'LAST_NAME', 'ID', 'PERSONAL_PHOTO'])
			->exec()
			->fetchObject()
		;
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

		$member = new Link\Member\Member();
		$member
			->setId($user->getId())
			->setName($user->getName())
			->setLastName($user->getLastName())
			->setAvatar($avatar)
		;

		return $member;
	}

	protected function getEntityName(): string
	{
		return 'Calendar sharing group link';
	}
}
