<?php
namespace Bitrix\UI\EntitySelector;

abstract class BaseFilter
{
	protected $options = [];

	protected function __construct()
	{
		// You have to validate $options in a derived class constructor
	}

	abstract public function isAvailable(): bool;

	/**
	 * @param array $items
	 * @param Dialog $dialog
	 */
	abstract public function apply(array $items, Dialog $dialog): void;

	public function getOptions(): array
	{
		return $this->options;
	}

	public function getOption(string $option, $defaultValue = null)
	{
		return array_key_exists($option, $this->options) ? $this->options[$option] : $defaultValue;
	}
}