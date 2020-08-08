<?php

namespace Sale\Handlers\Delivery\Taxi\Status;

/**
 * Class Searching
 * @package Sale\Handlers\Delivery\Taxi\Status
 */
class Searching implements StatusContract
{
	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return 'searching';
	}
}
