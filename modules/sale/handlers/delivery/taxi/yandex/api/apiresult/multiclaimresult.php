<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult;

use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Claim;
use Bitrix\Main;

/**
 * Class MultiClaimResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult
 */
class MultiClaimResult extends Main\Result
{
	/** @var Claim[] */
	private $claims = [];

	/**
	 * @param Claim $claim
	 * @return $this
	 */
	public function addClaim(Claim $claim)
	{
		$this->claims[] = $claim;

		return $this;
	}

	/**
	 * @return Claim[]
	 */
	public function getClaims(): array
	{
		return $this->claims;
	}
}
