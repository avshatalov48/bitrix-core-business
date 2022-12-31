<?php

namespace Bitrix\Location\Source\Osm;

use Bitrix\Location\Entity\Source\Factory;
use Bitrix\Location\Entity\Source\OrmConverter;
use Bitrix\Location\Repository\SourceRepository;
use Bitrix\Location\Entity\Source\Config;
use Bitrix\Location\Entity\Source\ConfigItem;
use Bitrix\Main\Application;

class Configurer
{
	private const SERVICE_DATACENTER_RU = 'ru';
	private const SERVICE_URL_RU = 'https://osm-ru-002.bitrix.info';
	private const MAP_SERVICE_URL_RU = 'https://osm-ru-001.bitrix.info';

	private const SERVICE_DATACENTER_DE = 'de';
	private const SERVICE_URL_DE = 'https://osm-de-002.bitrix.info';
	private const MAP_SERVICE_URL_DE = 'https://osm-de-001.bitrix.info';

	public static function configure(): void
	{
		$sourceRepository = new SourceRepository(new OrmConverter());

		$osmSource = $sourceRepository->findByCode(Factory::OSM_SOURCE_CODE);
		if (!$osmSource)
		{
			return;
		}

		$osmConfig = $osmSource->getConfig() ?? new Config();

		$datacenterEndpointsMap = self::getDatacenterEndpointsMap();
		$datacenter = self::getServiceDatacenter();

		$osmConfig->setValue(
			'SERVICE_URL',
			$datacenterEndpointsMap[$datacenter]['SERVICE_URL']
		);

		$mapServiceUrlConfigItem = $osmConfig->getItem('MAP_SERVICE_URL');
		if (!$mapServiceUrlConfigItem)
		{
			$osmConfig->addItem(
				(new ConfigItem('MAP_SERVICE_URL', 'string'))
					->setIsVisible(true)
					->setSort(15)
					->setValue($datacenterEndpointsMap[$datacenter]['MAP_SERVICE_URL'])
			);
		}
		else
		{
			$osmConfig->setValue(
				'MAP_SERVICE_URL',
				$datacenterEndpointsMap[$datacenter]['MAP_SERVICE_URL']
			);
		}

		$sourceRepository->save($osmSource);
	}

	/**
	 * @return string
	 */
	private static function getServiceDatacenter(): string
	{
		$region = Application::getInstance()->getLicense()->getRegion();
		if (!$region)
		{
			return self::SERVICE_DATACENTER_RU;
		}

		if (in_array($region, ['ru', 'by', 'kz'], true))
		{
			return self::SERVICE_DATACENTER_RU;
		}

		return self::SERVICE_DATACENTER_DE;
	}

	/**
	 * @return \string[][]
	 */
	private static function getDatacenterEndpointsMap(): array
	{
		return [
			self::SERVICE_DATACENTER_RU => [
				'SERVICE_URL' => self::SERVICE_URL_RU,
				'MAP_SERVICE_URL' => self::MAP_SERVICE_URL_RU,
			],
			self::SERVICE_DATACENTER_DE => [
				'SERVICE_URL' => self::SERVICE_URL_DE,
				'MAP_SERVICE_URL' => self::MAP_SERVICE_URL_DE,
			],
		];
	}
}
