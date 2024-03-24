<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Sharing\Link\Joint\JointLinkMapper;
use Bitrix\Calendar\Sharing\Link\Member;
use Bitrix\Main\Web\Json;

class UserLinkMapper extends JointLinkMapper
{
	protected const DEFAULT_SELECT = ['*', 'MEMBERS', 'MEMBERS.USER', 'MEMBERS.IMAGE'];

	protected function convertToObject($objectEO): ?UserLink
	{
		$sharingUserLink = (new UserLink())
			->setId($objectEO->getId())
			->setUserId($objectEO->getObjectId())
			->setDateCreate($objectEO->getDateCreate())
			->setDateExpire($objectEO->getDateExpire())
			->setActive($objectEO->getActive())
			->setHash($objectEO->getHash())
			->setMembersHash($objectEO->getMembersHash())
			->setFrequentUse($objectEO->getFrequentUse())
		;

		if ($objectEO->getMembers()?->count() > 0)
		{
			$sharingUserLink->setMembers((new Member\Manager())->createMembersFromEntityObject($objectEO->getMembers()));
		}

		$options = Json::decode($objectEO->getOptions() ?? '');
		if (!empty($options['slotSize']))
		{
			$sharingUserLink->setSlotSize($options['slotSize']);
		}

		$rule = (new Rule\Factory())->getRuleBySharingLink($sharingUserLink);
		$sharingUserLink->setSharingRule($rule);

		return $sharingUserLink;
	}

	/**
	 * @param UserLink $sharingLink
	 */
	public function convertToArray($sharingLink): array
	{
		$baseArray = parent::convertToArray($sharingLink);

		return array_merge($baseArray, [
			'userId' => $sharingLink->getUserId(),
			'slotSize' => $sharingLink->getSlotSize(),
			'rule' => (new Rule\Mapper())->convertToArray($sharingLink->getSharingRule()),
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
		];
	}

	protected function getEntityClass(): string
	{
		return UserLink::class;
	}

	protected function getEntityName(): string
	{
		return 'Calendar sharing user link';
	}
}