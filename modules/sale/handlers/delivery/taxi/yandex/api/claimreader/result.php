<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\ClaimReader;

use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Claim;

/**
 * Class Result
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\ClaimReader
 */
class Result extends \Bitrix\Main\Result
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
