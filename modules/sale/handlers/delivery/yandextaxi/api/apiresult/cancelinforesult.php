<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult;

use Bitrix\Main\Result;

/**
 * Class CancelInfoResult
 *
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 */
class CancelInfoResult extends Result
{
	/** @var string */
	private $cancelState;

	/**
	 * @return string|null
	 */
	public function getCancelState(): ?string
	{
		return $this->cancelState;
	}

	/**
	 * @param string $cancelState
	 * @return CancelInfoResult
	 */
	public function setCancelState(string $cancelState): CancelInfoResult
	{
		$this->cancelState = $cancelState;
		return $this;
	}
}
