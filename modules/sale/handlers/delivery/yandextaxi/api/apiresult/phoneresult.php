<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult;

use Bitrix\Main\Result;

/**
 * Class PhoneResult
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 * @internal
 */
final class PhoneResult extends Result
{
	/** @var string */
	private $phone;

	/** @var string */
	private $ext;

	/** @var int */
	private $ttlSeconds;

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone
	 */
	public function setPhone(string $phone)
	{
		$this->phone = $phone;
	}

	/**
	 * @return string
	 */
	public function getExt()
	{
		return $this->ext;
	}

	/**
	 * @param string $ext
	 */
	public function setExt(string $ext)
	{
		$this->ext = $ext;
	}

	/**
	 * @return int
	 */
	public function getTtlSeconds()
	{
		return $this->ttlSeconds;
	}

	/**
	 * @param int $ttlSeconds
	 * @return PhoneResult
	 */
	public function setTtlSeconds(int $ttlSeconds): PhoneResult
	{
		$this->ttlSeconds = $ttlSeconds;

		return $this;
	}
}
