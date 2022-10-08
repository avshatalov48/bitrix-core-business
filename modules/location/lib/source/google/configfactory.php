<?php

namespace Bitrix\Location\Source\Google;

use Bitrix\Location\Entity\Source\Config;
use Bitrix\Location\Entity\Source\ConfigItem;
use Bitrix\Location\Source\IConfigFactory;

class ConfigFactory implements IConfigFactory
{
	protected $frontendKey;
	protected $backendKey;

	public function __construct(string $frontendKey, string $backendKey)
	{
		$this->frontendKey = $frontendKey;
		$this->backendKey = $backendKey;
	}

	public function createConfig(): Config
	{
		$sourceConfig = new Config();
		$sourceConfig->addItem(
			(new ConfigItem('API_KEY_FRONTEND', 'string'))
				->setSort(10)
				->setValue($this->frontendKey)
			)
			->addItem(
				(new ConfigItem('API_KEY_BACKEND', 'string'))
					->setSort(20)
					->setValue($this->backendKey)
			)
			->addItem(
				(new ConfigItem('SHOW_PHOTOS_ON_MAP', 'bool'))
					->setSort(30)
					->setValue(false)
			)
			->addItem(
				(new ConfigItem('USE_GEOCODING_SERVICE', 'bool'))
					->setSort(40)
					->setValue(false)
			)
		;

		return $sourceConfig;
	}
}