<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Main\Web\Json;

class EventLinkMapper extends LinkMapper
{
	protected function convertToObject($objectEO): ?EventLink
	{
		$sharingEventLink = (new EventLink())
			->setId($objectEO->getId())
			->setEventId($objectEO->getObjectId())
			->setDateCreate($objectEO->getDateCreate())
			->setActive($objectEO->getActive())
			->setHash($objectEO->getHash())
		;

		$options = Json::decode($objectEO->getOptions() ?? '');
		if (!empty($options['ownerId']))
		{
			$sharingEventLink->setOwnerId($options['ownerId']);
		}
		if (!empty($options['hostId']))
		{
			$sharingEventLink->setHostId($options['hostId']);
		}
		if (!empty($options['conferenceId']))
		{
			$sharingEventLink->setConferenceId($options['conferenceId']);
		}
		if (!empty($options['userLinkHash']))
		{
			$sharingEventLink->setUserLinkHash($options['userLinkHash']);
		}

		return $sharingEventLink;
	}

	/**
	 * @param EventLink $sharingLink
	 */
	public function convertToArray($sharingLink): array
	{
		$baseArray = parent::convertToArray($sharingLink);

		return array_merge($baseArray, [
			'eventId' => $sharingLink->getEventId(),
			'ownerId' => $sharingLink->getOwnerId(),
			'hostId' => $sharingLink->getHostId(),
			'conferenceId' => $sharingLink->getConferenceId(),
			'userLinkHash' => $sharingLink->getUserLinkHash(),
		]);
	}

	protected function getOptionsArray($entity): array
	{
		$options = [];

		if (!empty($entity->getOwnerId()))
		{
			$options['ownerId'] = $entity->getOwnerId();
		}

		if (!empty($entity->getHostId()))
		{
			$options['hostId'] = $entity->getHostId();
		}

		if (!empty($entity->getConferenceId()))
		{
			$options['conferenceId'] = $entity->getConferenceId();
		}

		if (!empty($entity->getUserLinkHash()))
		{
			$options['userLinkHash'] = $entity->getUserLinkHash();
		}

		return $options;
	}

	protected function getEntityClass(): string
	{
		return EventLink::class;
	}

	protected function getEntityName(): string
	{
		return 'Calendar sharing user link';
	}
}