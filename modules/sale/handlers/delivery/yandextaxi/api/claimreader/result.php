<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ClaimReader;

use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;

/**
 * Class Result
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ClaimReader
 * @internal
 */
final class Result extends \Bitrix\Main\Result
{
	/** @var Claim */
	private $claim;

	/**
	 * @return Claim
	 */
	public function getClaim()
	{
		return $this->claim;
	}

	/**
	 * @param Claim $claim
	 * @return Result
	 */
	public function setClaim(Claim $claim): Result
	{
		$this->claim = $claim;

		return $this;
	}
}
