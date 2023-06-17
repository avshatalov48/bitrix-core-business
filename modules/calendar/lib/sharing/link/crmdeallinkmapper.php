<?php

namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Main\Web\Json;

class CrmDealLinkMapper extends LinkMapper
{
	/**
	 * @param CrmDealLink $entity
	 * @return array
	 */
	protected function getOptionsArray($entity): array
	{
		$options = [];

		if (!empty($entity->getSlotSize()))
		{
			$options['slotSize'] = $entity->getSlotSize();
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
			'entityId' => $sharingLink->getEntityId(),
			'contactId' => $sharingLink->getContactId(),
			'contactType' => $sharingLink->getContactType(),
			'ownerId' => $sharingLink->getOwnerId(),
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