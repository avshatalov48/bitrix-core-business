<?php

namespace Bitrix\MessageService\Providers\Base;

use Bitrix\Main;
use Bitrix\Main\Security\Cipher;
use Bitrix\MessageService\Providers\Encryptor;
use Bitrix\MessageService\Providers\OptionManager;

class Option implements OptionManager
{
	use Encryptor;

	protected ?array $options;

	protected string $providerType;
	protected string $providerId;
	protected string $dbOptionName;

	protected int $socketTimeout = 10;
	protected int $streamTimeout = 30;

	public function __construct(string $providerType, string $providerId)
	{
		$this->options = null;

		$this->providerType = mb_strtolower($providerType);
		$this->providerId = $providerId;

		$this->dbOptionName = 'sender.' . $this->providerType . '.' . $this->providerId;
	}

	/**
	 * @return int
	 */
	public function getSocketTimeout(): int
	{
		return $this->socketTimeout;
	}

	/**
	 * @param int $socketTimeout
	 * @return Option
	 */
	public function setSocketTimeout(int $socketTimeout): OptionManager
	{
		$this->socketTimeout = $socketTimeout;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getStreamTimeout(): int
	{
		return $this->streamTimeout;
	}

	/**
	 * @param int $streamTimeout
	 * @return Option
	 */
	public function setStreamTimeout(int $streamTimeout): OptionManager
	{
		$this->streamTimeout = $streamTimeout;
		return $this;
	}

	public function setOptions(array $options): OptionManager
	{
		$this->options = $options;
		$data = serialize($options);

		$encryptedData = [
			'crypto' => 'Y',
			'data' => self::encrypt($data, $this->providerType . '-' . $this->providerId)
		];

		Main\Config\Option::set('messageservice', $this->dbOptionName, serialize($encryptedData));

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
		$data = Main\Config\Option::get('messageservice', $this->dbOptionName);
		$data = unserialize($data, ['allowed_classes' => false]);

		if (!isset($data['crypto']) && !isset($data['data']))
		{
			return is_array($data) ? $data : [];
		}

		$decryptedData = self::decrypt($data['data'], $this->providerType . '-' . $this->providerId);
		$options = unserialize($decryptedData, ['allowed_classes' => false]);

		return is_array($options) ? $options : [];
	}

	public function getProviderId(): string
	{
		return $this->providerId;
	}
}