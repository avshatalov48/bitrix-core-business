<?php

namespace Bitrix\Calendar\Internals;

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
