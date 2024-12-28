<?php

declare(strict_types=1);

namespace Bitrix\Main;

use Bitrix\Main\Security\Random;

class UuidGenerator
{
	public static function generateV4(): string
	{
		$data = Random::getBytes(16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}
