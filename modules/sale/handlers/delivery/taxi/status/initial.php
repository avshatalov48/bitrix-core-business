<?php

namespace Sale\Handlers\Delivery\Taxi\Status;

/**
 * Class Initial
 * @package Sale\Handlers\Delivery\Taxi\Status
 */
class Initial implements StatusContract
{
	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return 'initial';
	}
}
