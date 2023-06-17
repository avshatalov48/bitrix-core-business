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
			->setDateExpire($objectEO->getDateExpire())
			->setActive($objectEO->getActive())
			->setHash($objectEO->getHash())
			->setOwnerId($objectEO->getOwnerId())
			->setHostId($objectEO->getHostId())
			->setConferenceId($objectEO->getConferenceId())
			->setParentLinkHash($objectEO->getParentLinkHash())
		;

		//backward compatibility
		$options = $objectEO->getOptions();
		if (!empty($options))
		{
			$options = Json::decode($options);
		}
		if (empty($sharingEventLink->getOwnerId()) && !empty($options['ownerId']))
		{
			$sharingEventLink->setOwnerId($options['ownerId']);
		}
		if (empty($sharingEventLink->getHostId()) && !empty($options['hostId']))
		{
			$sharingEventLink->setHostId($options['hostId']);
		}
		if (empty($sharingEventLink->getConferenceId()) && !empty($options['conferenceId']))
		{
			$sharingEventLink->setConferenceId($options['conferenceId']);
		}
		if (empty($sharingEventLink->getParentLinkHash()) && !empty($options['userLinkHash']))
		{
			$sharingEventLink->setParentLinkHash($options['userLinkHash']);
		}
		if (!empty($options['canceledTimestamp']))
		{
			$sharingEventLink->setCanceledTimestamp($options['canceledTimestamp']);
		}

		if (!empty($options['externalUserName']))
		{
			$sharingEventLink->setExternalUserName($options['externalUserName']);
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
			'parentLinkHash' => $sharingLink->getParentLinkHash(),
			'canceledTimestamp' => $sharingLink->getCanceledTimestamp(),
			'externalUserName' => $sharingLink->getExternalUserName(),
		]);
	}

	protected function getOptionsArray($entity): array
	{
		$options = [];

		if (!empty($entity->getCanceledTimestamp()))
		{
			$options['canceledTimestamp'] = $entity->getCanceledTimestamp();
		}

		if (!empty($entity->getExternalUserName()))
		{
			$options['externalUserName'] = $entity->getExternalUserName();
		}

		return $options;
	}

	protected function getSpecificFields($entity): array
	{
		return [
			'HOST_ID' => $entity->getHostId(),
			'OWNER_ID' => $entity->getOwnerId(),
			'CONFERENCE_ID' => $entity->getConferenceId(),
			'PARENT_LINK_HASH' => $entity->getParentLinkHash(),
		];
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