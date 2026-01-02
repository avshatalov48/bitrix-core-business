<?php
declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Text;

abstract class BaseBbCode implements BbCode
{
	abstract protected function getValue(): ?string;

	abstract protected function getAdditionalParams(): array;

	public function compile(): string
	{
		$value = $this->getValue();
		$result = '[' . static::getName();
		if ($value !== null)
		{
			$result .= '=' . $value;
		}

		foreach ($this->getAdditionalParams() as $paramName => $paramValue)
		{
			$result .= " {$paramName}={$paramValue}";
		}

		$result .= ']';

		return $result;
	}

	public function toPlaceholder(): string
	{
		return $this->toPlain();
	}
}
