<?php

namespace Sale\Handlers\Delivery\Taxi\Status;

/**
 * Class Success
 * @package Sale\Handlers\Delivery\Taxi\Status
 */
class Success implements StatusContract
{
	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return 'success';
	}
}
