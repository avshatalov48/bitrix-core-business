<?php

namespace Bitrix\Calendar\Sharing\Crm;

use Bitrix\Calendar\Sharing\Link\CrmDealLink;
use Bitrix\Calendar\Sharing\Link\Factory;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class CrmSharing
{
	protected int $userId;
	protected int $entityId;

	/**
	 * @param int $userId
	 * @param int $entityId
	 */
	public function __construct(int $userId, int $entityId)
	{
		$this->userId = $userId;
		$this->entityId = $entityId;
	}

	/**
	 * @param array $memberIds
	 * @param int|null $contactId
	 * @param int|null $contactType
	 * @param string|null $channelId
	 * @param string|null $senderId
	 * @return Result
	 */
	public function generateCrmDealJointLink(
		array $memberIds,
		?int $contactId = null,
		?int $contactType = null,
		?string $channelId = null,
		?string $senderId = null,
	): Result
	{
		$result = new Result();

		if (
			(!is_null($contactId) && $contactId <= 0)
			|| (!is_null($contactType) && $contactType <= 0)
		)
		{
			$result->addError(new Error('Invalid data was provided', 100070));
		}

		if ($result->isSuccess())
		{
			$crmDealLink = (new CrmDealLink())
				->setOwnerId($this->userId)
				->setEntityId($this->entityId)
				->setContactType($contactType)
				->setContactId($contactId)
				->setChannelId($channelId)
				->setSenderId($senderId)
			;
			$crmDealJointLink = Factory::getInstance()->createCrmDealJointLink($crmDealLink, $memberIds);
			$result->setData(['link' => $crmDealJointLink]);
		}

		return $result;
	}
}
