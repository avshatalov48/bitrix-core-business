<?php

namespace Bitrix\Calendar\Sync\Builders;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Internals\EO_Push;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Main\ObjectException;

class BuilderPushFromDM implements Builder
{
	private EO_Push $push;

	/**
	 * @param EO_Push $push
	 */
	public function __construct(EO_Push $push)
	{
		$this->push = $push;
	}

	/**
	 * @return Push
	 *
	 * @throws ObjectException
	 */
	public function build(): Push
	{
		return ( new Push())
			->setEntityType($this->push->getEntityType())
			->setEntityId($this->push->getEntityId())
			->setChannelId($this->push->getChannelId())
			->setResourceId($this->push->getResourceId())
			->setExpireDate($this->getExpireDate())
			->setProcessStatus($this->push->getNotProcessed() ?? Dictionary::PUSH_STATUS_PROCESS['unblocked'])
			->setFirstPushDate($this->getFirstPushDate())
			;
	}

	/**
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	private function getExpireDate(): Date
	{
		return new Date($this->push->getExpires());
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	private function getFirstPushDate(): ?Date
	{
		return $this->push->getFirstPushDate()
			? new Date($this->push->getFirstPushDate())
			: null;
	}
}
