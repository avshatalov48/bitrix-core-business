<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult;

use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;
use Bitrix\Main;

/**
 * Class MultiClaimResult
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 * @internal
 */
final class MultiClaimResult extends Main\Result
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
