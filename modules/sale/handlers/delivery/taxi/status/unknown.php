<?php

namespace Sale\Handlers\Delivery\Taxi\Status;

/**
 * Class Unknown
 * @package Sale\Handlers\Delivery\Taxi\Status
 */
class Unknown implements StatusContract
{
	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return 'unknown';
	}
}
