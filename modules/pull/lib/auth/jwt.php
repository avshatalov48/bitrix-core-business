<?php

namespace Bitrix\Pull\Auth;

use Bitrix\Main;
use Bitrix\Pull\Config;

final class Jwt
{
	public const TTL = 12 * 3600;

	public static function create(array $channels = [], int $userId = 0): string
	{
		if (empty($channels) && $userId === 0)
		{
			throw new Main\ArgumentException("Either channel list or user id must be specified");
		}

		$time = time();
		$data = [
			'iss' => (string)Config::getHostId(),
			'iat' => $time,
			'exp' => $time + self::TTL,
		];
		if ($userId > 0)
		{
			$data['sub'] = (string)$userId;
		}
		if (!empty($channels))
		{
			$data['chan'] = implode(',', $channels);
		}
		$secret = Config::getSignatureKey();

		return Main\Web\JWT::encode($data, $secret);
	}

	public static function decode(string $jwt, string $secret)
	{
		return Main\Web\JWT::decode($jwt, $secret);
	}
}