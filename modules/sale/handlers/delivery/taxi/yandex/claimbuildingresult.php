<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Result;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Claim;

/**
 * Class ClaimBuildingResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class ClaimBuildingResult extends Result
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
	 * @return ClaimBuildingResult
	 */
	public function setClaim(Claim $claim): ClaimBuildingResult
	{
		$this->claim = $claim;

		return $this;
	}
}
