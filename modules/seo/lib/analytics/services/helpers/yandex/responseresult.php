<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex;

use Bitrix\Main;

/** @template T */
final class ResponseResult extends Main\Result
{
	/** @var T|null */
	private mixed $response = null;

	/**
	 * @param T $response
	 * @return ResponseResult
	 */
	public function setResponse($response): self
	{
		$this->response = $response;

		return $this;
	}

	/** @return T|null */
	public function getResponse(): mixed
	{
		return $this->response;
	}
}
