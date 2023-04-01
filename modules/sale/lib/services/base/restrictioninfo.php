<?php

namespace Bitrix\Sale\Services\Base;

final class RestrictionInfo
{
	private string $restrictionType;
	private array $options;

	public function __construct(string $restrictionType, array $options = [])
	{
		$this->options = $options;
		$this->restrictionType = $restrictionType;
	}

	public function getType(): string
	{
		return $this->restrictionType;
	}

	public function getOption(string $optionId)
	{
		return $this->options[$optionId] ?? null;
	}

	public function setOption(string $optionId, $value): void
	{
		$this->options[$optionId] = $value;
	}

	public function getOptions(): array
	{
		return $this->options;
	}
}