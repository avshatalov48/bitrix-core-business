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
	 * @return static
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

	public static function merge(Result ...$results): Result
	{
		$mergedResult = new Result();
		$mergedData = [];

		foreach ($results as $result)
		{
			if (!$result->isSuccess())
			{
				$mergedResult->addErrors($result->getErrors());
			}
			elseif ($result->hasResult)
			{
				$mergedData = array_merge($mergedData, $result->getResult());
			}
		}

		if (!empty($mergedData))
		{
			$mergedResult->setResult($mergedData);
		}

		return $mergedResult;
	}
}