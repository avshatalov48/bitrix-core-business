<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult;

use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Claim;
use Bitrix\Main;

/**
 * Class SingleClaimResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult
 */
class SingleClaimResult extends Main\Result
{
	/** @var Claim */
	private $claim;

	/**
	 * @param Claim $claim
	 * @return $this
	 */
	public function setClaim(Claim $claim)
	{
		$this->claim = $claim;

		return $this;
	}

	/**
	 * @return Claim
	 */
	public function getClaim()
	{
		return $this->claim;
	}
}
