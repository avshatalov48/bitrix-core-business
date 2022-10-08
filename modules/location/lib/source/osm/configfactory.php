<?php

namespace Bitrix\Location\Source\Osm;

use Bitrix\Location\Entity\Source\Config;
use Bitrix\Location\Entity\Source\ConfigItem;
use Bitrix\Location\Source\IConfigFactory;
use Bitrix\Main\Type\DateTime;

class ConfigFactory implements IConfigFactory
{
	protected $serviceUrl;
	protected $token;

	public function __construct(string $serviceUrl, string $token = null)
	{
		$this->serviceUrl = $serviceUrl;
		$this->token = $token;
	}

	public function createConfig(): Config
	{
		$token = null;

		if($this->token !== null)
		{
			$token = new Token(
				(string)$this->token,
				(int)((new DateTime())->getTimestamp() + 31536000) //year
			);

			$token = serialize($token->convertToArray());
		}

		$sourceConfig = new Config();

		$sourceConfig
			->addItem(
				(new ConfigItem('SERVICE_URL', 'string'))
					->setSort(10)
					->setValue($this->serviceUrl)
					->setIsVisible(false)
			)
			->addItem(
				(new ConfigItem('TOKEN', 'string'))
					->setSort(20)
					->setValue($token)
					->setIsVisible(false)
			)
		;

		return $sourceConfig;
	}
}