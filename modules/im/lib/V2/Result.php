<?php

namespace Bitrix\Im\V2;

/**
 * @template T
 */
class Result extends \Bitrix\Main\Result
{
	protected bool $hasResult = false;

	/**
	 * Sets only the result.
	 * @param T $result
	 * @return self
	 */
	public function setResult($result): self
	{
		$this->hasResult = true;
		return parent::setData(['RESULT' => $result]);
	}

	/**
	 * Returns a single result.
	 * @return T|null
	 */
	public function getResult()
	{
		return parent::getData()['RESULT'] ?? null;
	}

	/**
	 * We have a result.
	 * @return bool
	 */
	public function hasResult(): bool
	{
		return $this->isSuccess() && $this->hasResult;
	}
}