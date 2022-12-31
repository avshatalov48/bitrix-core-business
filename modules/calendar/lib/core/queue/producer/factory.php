<?php

namespace Bitrix\Calendar\Core\Queue\Producer;

use Bitrix\Calendar\Core\Queue\Interfaces;

class Factory
{
	public const PRODUCER_TYPES = [
		'delayed' => 'delayed', // TODO: implement it
		'immediate' => 'immediate',
	];

	public static function getProduser(?string $type = self::PRODUCER_TYPES['immediate']): Interfaces\Producer
	{
		switch ($type)
		{
			// case self::PRODUCER_TYPES['delayed']: // TODO: implement it
			// 	return new Producer();
			default:
				return new Producer();
		}
	}
}