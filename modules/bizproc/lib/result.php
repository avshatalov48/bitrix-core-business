<?php

namespace Bitrix\Bizproc;

class Result extends \Bitrix\Main\Result
{
	public static function createFromErrorCode(string $code, $customData = null): self
	{
		return static::createError(Error::fromCode($code, $customData));
	}

	public static function createError(Error $error): static
	{
		$res = new static();
		$res->addError($error);

		return $res;
	}

	public static function createOk(?array $data = null): static
	{
		$res = new static();

		if (is_array($data))
		{
			$res->setData($data);
		}

		return $res;
	}

	public function map(callable $callback): self
	{
		if ($this->isSuccess())
		{
			return $callback($this->getData());
		}

		return $this;
	}

	/**
	 * @return \Bitrix\Bizproc\Error[]
	 */
	public function getErrors(): array
	{
		return $this->errors->toArray();
	}
}