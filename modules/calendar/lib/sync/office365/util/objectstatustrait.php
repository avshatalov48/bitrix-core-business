<?php

namespace Bitrix\Calendar\Sync\Office365\Util;

trait ObjectStatusTrait
{
	/** @var ObjectStatus */
	protected $objectStatus;

	/**
	 * @return ObjectStatus
	 */
	public function getStatus(): ObjectStatus
	{
		if (!$this->objectStatus)
		{
			$this->objectStatus = new ObjectStatus();
		}

		return $this->objectStatus;
	}
}
