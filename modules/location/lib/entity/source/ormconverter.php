<?php

namespace Bitrix\Location\Entity\Source;

use Bitrix\Location\Entity\Source;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Model\EO_Source;
use Bitrix\Location\Model\SourceTable;

/**
 * Class OrmConverter
 * @package Bitrix\Location\Entity\Source
 * @internal
 */
final class OrmConverter
{
	/**
	 * @param Source $source
	 * @return EO_Source
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertToOrm(Source $source): EO_Source
	{
		/**
		 * @TODO revisit it later. There should be a better way to init an existing ORM object without accessing database here
		 */
		$result = SourceTable::getById($source->getCode())->fetchObject();
		if (!$result)
		{
			$result = (new EO_Source())
				->setCode($source->getCode());
		}

		return $result
			->setName($source->getName())
			->setConfig($this->buildConfigString($source->getConfig()));
	}

	/**
	 * @param EO_Source $ormSource
	 * @return Source
	 */
	public function convertFromOrm(EO_Source $ormSource): Source
	{
		return Factory::makeSource($ormSource->getCode())
			->setName($ormSource->getName())
			->setConfig($this->buildConfig($ormSource->getConfig()));
	}

	/**
	 * @param string $configString
	 * @return Config|null
	 */
	private function buildConfig(string $configString): ?Config
	{
		if (!$configString)
		{
			return null;
		}

		if (!CheckSerializedData($configString))
		{
			return null;
		}

		$result = new Config();

		$configArray = unserialize($configString, ['allowed_classes' => false]);
		foreach ($configArray as $configArrayItem)
		{
			$result->addItem(
				$this->convertArrayToConfigItem($configArrayItem)
			);
		}

		return $result;
	}

	/**
	 * @param Config|null $config
	 * @return string
	 */
	private function buildConfigString(?Config $config)
	{
		if (is_null($config))
		{
			return '';
		}

		$configArray = [];
		/** @var ConfigItem $configItem */
		foreach ($config as $configItem)
		{
			$configArray[] = $this->convertConfigItemToArray($configItem);
		}

		return serialize($configArray);
	}

	/**
	 * @param ConfigItem $configItem
	 * @return array
	 */
	private function convertConfigItemToArray(ConfigItem $configItem)
	{
		return [
			'code' => $configItem->getCode(),
			'type' => $configItem->getType(),
			'is_visible' => $configItem->isVisible(),
			'sort' => $configItem->getSort(),
			'value' => $configItem->getValue(),
		];
	}

	/**
	 * @param array $array
	 * @return ConfigItem
	 */
	private function convertArrayToConfigItem(array $array)
	{
		if (!isset($array['code']))
		{
			throw new RuntimeException('code is not specified');
		}

		$result = new ConfigItem($array['code'], $array['type']);

		if (isset($array['is_visible']))
		{
			$result->setIsVisible($array['is_visible']);
		}
		if (isset($array['sort']))
		{
			$result->setSort((int)$array['sort']);
		}
		if (isset($array['value']))
		{
			$result->setValue(isset($array['value']) ? (string)$array['value'] : null);
		}

		return $result;
	}
}
