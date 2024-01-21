<?php

namespace Bitrix\Calendar\Sharing\Link\Joint;

use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Internals\SharingLinkMemberTable;
use Bitrix\Calendar\Internals\SharingLinkTable;
use Bitrix\Calendar\Sharing\Helper;
use Bitrix\Calendar\Sharing\Link\LinkMapper;
use Bitrix\Calendar\Sharing\Link\Member;
use Bitrix\Calendar\Sharing\Link\Rule;
use Bitrix\Main\ORM\Query\Result;

abstract class JointLinkMapper extends LinkMapper
{
	protected const DEFAULT_SELECT = ['*', 'MEMBERS'];
	protected function createRelated($entity): void
	{
		if ($entity->isJoint())
		{
			$toAdd = [];
			$linkId = $entity->getId();
			$members = $entity->getMembers();
			foreach ($members as $member)
			{
				$toAdd[] = [
					'LINK_ID' => $linkId,
					'MEMBER_ID' => $member->getId(),
				];
			}
			SharingLinkMemberTable::addMulti($toAdd, true);
		}
	}

	protected function getDataManagerResult(array $params): Result
	{
		$params['select'] = static::DEFAULT_SELECT;

		return SharingLinkTable::getList($params);
	}

	/**
	 * @param JointLink $sharingLink
	 */
	public function convertToArray($sharingLink): array
	{
		$baseArray = parent::convertToArray($sharingLink);

		$members = array_map(static function($member) {
			return $member->toArray();
		}, $sharingLink->getMembers());

		return array_merge($baseArray, [
			'members' => $members,
			'membersHash' => $sharingLink->getMembersHash(),
			'userIds' => array_merge([$sharingLink->getOwnerId()], array_map(static fn ($member) => $member['id'], $members)),
			'isJoint' => $sharingLink->isJoint(),
			'shortUrl' => Helper::getShortUrl($sharingLink->getUrl()),
		]);
	}

	protected function deleteRelated(EntityInterface $entity): void
	{
		(new Rule\Mapper())->deleteLinkRule($entity->getId());
	}
}