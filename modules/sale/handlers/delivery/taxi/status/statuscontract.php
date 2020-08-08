<?php

namespace Sale\Handlers\Delivery\Taxi\Status;

/**
 * Interface StatusContract
 * @package Sale\Handlers\Delivery\Taxi\Status
 */
interface StatusContract
{
	/**
	 * @return string
	 */
	public function getCode(): string;
}
