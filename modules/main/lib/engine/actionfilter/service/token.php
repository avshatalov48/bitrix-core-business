<?php

namespace Bitrix\Main\Engine\ActionFilter\Service;

use Bitrix\Main\Security;
use Bitrix\Main\ArgumentOutOfRangeException;

/**
 * Class Token
 *
 * @package Bitrix\Main\Engine\ActionFilter\Service
 */
class Token
{
	protected const SALT_PREFIX = 'token_actionfilter';
	protected const TTL = 60 * 60 * 3;

	protected const HEADER_ENTITY = 'X-Bitrix-Sign-Entity';
	protected const HEADER_TOKEN = 'X-Bitrix-Sign-Token';

	/** @var Security\Sign\Signer */
	protected $signer;
	/** @var int */
	protected $userId;

	public static function getEntityHeader(): string
	{
		return self::HEADER_ENTITY;
	}

	public static function getTokenHeader(): string
	{
		return self::HEADER_TOKEN;
	}

	public function __construct(int $userId = 0)
	{
		static $signerInstance = null;

		if ($userId <= 0)
		{
			throw new ArgumentOutOfRangeException('Invalid user ID');
		}

		if ($signerInstance === null)
		{
			$signerInstance = new Security\Sign\TimeSigner();
		}

		$this->signer = $signerInstance;
		$this->userId = $userId;
	}

	/**
	 * @param $value string
	 * @return string
	 */
	public function generate(string $value = ''): string
	{
		return $this->getSigner()->sign($value, (time() + self::TTL), $this->getSalt($value));
	}

	/**
	 * @param $signedValue string
	 * @param $userId int
	 * @return string
	 */
	public function unsign(string $signedValue = '', string $payloadEntityValue = ''): string
	{
		return $this->getSigner()->unsign($signedValue, $this->getSalt($payloadEntityValue));
	}

	protected function getSigner(): Security\Sign\TimeSigner
	{
		return $this->signer;
	}

	protected function getSalt(string $value = ''): string
	{
		return mb_substr(self::SALT_PREFIX . '_' . $this->getCurrentUserId() . '_' . $value, -50);
	}

	protected function getCurrentUserId(): int
	{
		return $this->userId;
	}
}
