<?php

namespace Bitrix\MessageService\Providers\Base;

use Bitrix\Main;
use Bitrix\MessageService\Providers\OptionManager;

class Option implements OptionManager
{
	protected ?array $options;

	protected string $providerType;
	protected string $providerId;
	protected string $dbOptionName;

	public function __construct(string $providerType, string $providerId)
	{
		$this->options = null;

		$this->providerType = mb_strtolower($providerType);
		$this->providerId = $providerId;

		$this->dbOptionName = 'sender.' . $this->providerType . '.' . $this->providerId;
	}

	public function setOptions(array $options): OptionManager
	{
		$this->options = $options;

		Main\Config\Option::set('messageservice', $this->dbOptionName, serialize($options));

		return $this;
	}

	public function setOption(string $optionName, $optionValue): OptionManager
	{
		$options = $this->getOptions();

		if (!isset($options[$optionName]) || $options[$optionName] !== $optionValue)
		{
			$options[$optionName] = $optionValue;
			$this->setOptions($options);
		}

		return $this;
	}

	public function getOptions(): array
	{
		$this->options ??= $this->loadOptions();

		return $this->options;
	}

	public function getOption(string $optionName, $defaultValue = null)
	{
		$this->getOptions();

		return $this->options[$optionName] ?? $defaultValue;
	}

	public function clearOptions(): OptionManager
	{
		$this->options = [];
		Main\Config\Option::delete('messageservice', array('name' => $this->dbOptionName));

		return $this;
	}

	protected function loadOptions(): array
	{
		$serializedOptions = Main\Config\Option::get('messageservice', $this->dbOptionName);
		$options = unserialize($serializedOptions, ['allowed_classes' => false]);

		if (!is_array($options))
		{
			$options = [];
		}

		return $options;
	}
}