<?php

namespace Bitrix\Pull\Auth;

use Bitrix\Main;
use Bitrix\Pull\Config;

final class Jwt
{
	public const DEFAULT_TTL = 12 * 3600;

	/**
	 * Returns array with a pair of values: signed JWT token; token expiration timestamp
	 */
	public static function create(array $channels = [], int $userId = 0, array $options = []): array
	{
		if (empty($channels) && $userId === 0)
		{
			throw new Main\ArgumentException("Either channel list or user id must be specified");
		}

		$ttl = is_integer($options['ttl']) && $options['ttl'] > 0 ? $options['ttl'] :  self::DEFAULT_TTL;

		$time = time();
		$exp = $time + $ttl;
		$data = [
			'iss' => (string)Config::getHostId(),
			'iat' => $time,
			'exp' => $exp,
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

		return [Main\Web\JWT::encode($data, $secret), $exp];
	}

	public static function decode(string $jwt, string $secret)
	{
		return Main\Web\JWT::decode($jwt, $secret);
	}
}