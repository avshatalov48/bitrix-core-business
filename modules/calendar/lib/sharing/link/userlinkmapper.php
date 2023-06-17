<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Main\Web\Json;

class UserLinkMapper extends LinkMapper
{
	protected function convertToObject($objectEO): ?UserLink
	{
		$sharingUserLink = (new UserLink())
			->setId($objectEO->getId())
			->setUserId($objectEO->getObjectId())
			->setDateCreate($objectEO->getDateCreate())
			->setActive($objectEO->getActive())
			->setHash($objectEO->getHash())
		;

		$options = Json::decode($objectEO->getOptions() ?? '');
		if (!empty($options['slotSize']))
		{
			$sharingUserLink->setSlotSize($options['slotSize']);
		}

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
		return [];
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