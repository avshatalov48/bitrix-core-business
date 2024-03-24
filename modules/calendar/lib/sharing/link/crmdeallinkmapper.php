<?php

namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Sharing\Link\Joint\JointLinkMapper;
use Bitrix\Main\Web\Json;

class CrmDealLinkMapper extends JointLinkMapper
{
	/**
	 * @param CrmDealLink $entity
	 * @return array
	 */
	protected function getOptionsArray($entity): array
	{
		/** @var CrmDealLink $entity */
		$options = [];

		if (!empty($entity->getSlotSize()))
		{
			$options['slotSize'] = $entity->getSlotSize();
		}

		if (!empty($entity->getChannelId()))
		{
			$options['channelId'] = $entity->getChannelId();
		}

		if (!empty($entity->getSenderId()))
		{
			$options['senderId'] = $entity->getSenderId();
		}

		if (!empty($entity->getLastStatus()))
		{
			$options['lastStatus'] = $entity->getLastStatus();
		}

		return $options;
	}

	protected function getEntityClass(): string
	{
		return CrmDealLink::class;
	}

	protected function convertToObject($objectEO): ?CrmDealLink
	{
		$crmDealLink = (new CrmDealLink())
			->setId($objectEO->getId())
			->setEntityId($objectEO->getObjectId())
			->setDateCreate($objectEO->getDateCreate())
			->setDateExpire($objectEO->getDateExpire())
			->setActive($objectEO->getActive())
			->setHash($objectEO->getHash())
			->setContactId($objectEO->getContactId())
			->setContactType($objectEO->getContactType())
			->setOwnerId($objectEO->getOwnerId())
		;

		$options = Json::decode($objectEO->getOptions() ?? '');
		if (!empty($options['slotSize']))
		{
			$crmDealLink->setSlotSize($options['slotSize']);
		}

		if (!empty($options['channelId']))
		{
			$crmDealLink->setChannelId($options['channelId']);
		}

		if (!empty($options['senderId']))
		{
			$crmDealLink->setSenderId($options['senderId']);
		}

		if (!empty($options['lastStatus']))
		{
			$crmDealLink->setLastStatus($options['lastStatus']);
		}
		//backward compatibility
		if (empty($crmDealLink->getContactId()) && !empty($options['contactId']))
		{
			$crmDealLink->setContactId($options['contactId']);
		}
		if (empty($crmDealLink->getContactType()) && !empty($options['contactType']))
		{
			$crmDealLink->setContactType($options['contactType']);
		}
		if (empty($crmDealLink->getOwnerId()) && !empty($options['ownerId']))
		{
			$crmDealLink->setOwnerId($options['ownerId']);
		}

		$rule = (new Rule\Factory())->getRuleBySharingLink($crmDealLink);
		$crmDealLink->setSharingRule($rule);

		return $crmDealLink;
	}

	/**
	 * @param CrmDealLink $sharingLink
	 */
	public function convertToArray($sharingLink): array
	{
		$baseArray = parent::convertToArray($sharingLink);

		return array_merge($baseArray, [
			'slotSize' => $sharingLink->getSlotSize(),
			'channelId' => $sharingLink->getChannelId(),
			'senderId' => $sharingLink->getSenderId(),
			'entityId' => $sharingLink->getEntityId(),
			'contactId' => $sharingLink->getContactId(),
			'contactType' => $sharingLink->getContactType(),
			'ownerId' => $sharingLink->getOwnerId(),
			'lastStatus' => $sharingLink->getLastStatus(),
			'rule' => (new Rule\Mapper())->convertToArray($sharingLink->getSharingRule()),
		]);
	}

	protected function getSpecificFields($entity): array
	{
		return [
			'OWNER_ID' => $entity->getOwnerId(),
			'CONTACT_ID' => $entity->getContactId(),
			'CONTACT_TYPE' => $entity->getContactType(),
		];
	}

	protected function getEntityName(): string
	{
		return 'Crm deal sharing link';
	}
}