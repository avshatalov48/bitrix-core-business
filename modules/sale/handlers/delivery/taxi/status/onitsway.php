<?php

namespace Sale\Handlers\Delivery\Taxi\Status;

/**
 * Class OnItsWay
 * @package Sale\Handlers\Delivery\Taxi\Status
 */
class OnItsWay implements StatusContract
{
	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return 'on_its_way';
	}
}
