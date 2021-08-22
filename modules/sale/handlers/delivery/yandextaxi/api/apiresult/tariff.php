<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult;

/**
 * Class Tariff
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 * @internal
 */
final class Tariff
{
	/** @var string */
	private $code;

	/** @var array */
	private $supportedRequirements = [];

	/**
	 * Tariff constructor.
	 * @param string $code
	 */
	public function __construct(string $code)
	{
		$this->code = $code;
	}

	/**
	 * @param string $code
	 * @return $this
	 */
	public function addSupportedRequirement(string $code): Tariff
	{
		$this->supportedRequirements[] = $code;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return array
	 */
	public function getSupportedRequirements(): array
	{
		return $this->supportedRequirements;
	}
}
