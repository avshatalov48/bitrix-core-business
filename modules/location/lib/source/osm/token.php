<?php

namespace Bitrix\Location\Source\Osm;

/**
 * Class Token
 * @package Bitrix\Location\Source\Osm
 * @inernal
 */
final class Token
{
	/** @var string */
	private $token;

	/** @var int */
	private $expiry;

	/**
	 * Token constructor.
	 * @param string $token
	 * @param int $expiry
	 */
	public function __construct(string $token, int $expiry)
	{
		$this->token = $token;
		$this->expiry = $expiry;
	}

	/**
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * @return int
	 */
	public function getExpiry(): int
	{
		return $this->expiry;
	}

	/**
	 * @return array
	 */
	public function convertToArray(): array
	{
		return [
			'token' => $this->token,
			'expire' => $this->expiry,
		];
	}

	/**
	 * @param array $token
	 * @return Token|null
	 */
	public static function makeFromArray(array $token): ?Token
	{
		if(empty($token['token']) || empty($token['expire']))
		{
			return null;
		}

		return new Token(
			$token['token'],
			$token['expire']
		);
	}
}
