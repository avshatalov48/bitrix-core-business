<?php

namespace Bitrix\Calendar\Internals;

interface HasStatusInterface
{
	/**
	 * @return ObjectStatus
	 */
	public function getStatus(): ObjectStatus;
}
