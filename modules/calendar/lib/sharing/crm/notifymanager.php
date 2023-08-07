<?php

namespace Bitrix\Calendar\Sharing\Crm;

use Bitrix\Calendar\Integration\Crm\EventData;
use Bitrix\Calendar\Integration\Crm\EventHandlerService;
use Bitrix\Calendar\Sharing\Link\CrmDealLink;
use Bitrix\Main\Type\DateTime;

final class NotifyManager
{
	public const NOTIFY_TYPE_NOT_VIEWED = 'notViewed';
	public const NOTIFY_TYPE_VIEWED = 'viewed';
	public const NOTIFY_TYPE_EVENT_CONFIRMED = 'eventConfirmed';

	public const NOTIFY_TYPES = [
		self::NOTIFY_TYPE_NOT_VIEWED,
		self::NOTIFY_TYPE_VIEWED,
		self::NOTIFY_TYPE_EVENT_CONFIRMED,
	];

	private const SHARING_CRM_ACTIONS_EVENT = 'onSharedCrmActions';

	private CrmDealLink $link;
	private string $notifyType;

	/**
	 * @param CrmDealLink $link
	 * @param string $notifyType
	 */
	public function __construct(CrmDealLink $link, string $notifyType)
	{
		$this->link = $link;
		$this->notifyType = $notifyType;
	}

	/**
	 * sends event about crm-sharing actions
	 * @param int|null $timestamp
	 * @param int $associatedEntityId
	 * @param int $associatedEntityTypeId
	 * @return void
	 */
	public function sendSharedCrmActionsEvent(
		?int $timestamp = null,
		int $associatedEntityId = 0,
		int $associatedEntityTypeId = 0
	): void
	{
		$timestamp = $this->prepareTimestamp($timestamp);

		$eventData = [
			'EVENT_TYPE' => $this->notifyType,
			'OWNER_ID' => $this->link->getOwnerId(),
			'LINK_ID' => $this->link->getId(),
			'LINK_TYPE' => $this->link->getObjectType(),
			'LINK_ENTITY_ID' => $this->link->getObjectId(),
			'CONTACT_ID' => $this->link->getContactId(),
			'CONTACT_TYPE_ID' => $this->link->getContactType(),
			'LINK_HASH' => $this->link->getHash(),
			'ASSOCIATED_ENTITY_ID' => $associatedEntityId,
			'ASSOCIATED_ENTITY_TYPE_ID' => $associatedEntityTypeId,
			'TIMESTAMP' => $timestamp,
		];

		(new \Bitrix\Main\Event(
			'calendar',
			self::SHARING_CRM_ACTIONS_EVENT,
			$eventData
		))->send();
	}

	/**
	 * @param $timestamp
	 * @return int|mixed|null
	 */
	private function prepareTimestamp($timestamp)
	{
		$result = $timestamp;

		if ($this->notifyType === self::NOTIFY_TYPE_NOT_VIEWED || $this->notifyType === self::NOTIFY_TYPE_VIEWED)
		{
			$result = $this->link->getDateCreate() ? $this->link->getDateCreate()->getTimestamp() : null;
		}

		if (!$result)
		{
			$result = (new DateTime())->getTimestamp();
		}

		return $result;
	}
}