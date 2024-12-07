<?php

namespace Bitrix\Location\Source\Osm;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Geometry\Converter\Manager;
use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService;
use Bitrix\Location\Repository\Location\Capability\IFindByCoords;
use Bitrix\Location\Repository\Location\Capability\IFindByExternalId;
use Bitrix\Location\Repository\Location\Capability\ISupportAutocomplete;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Location\Repository\Location\ISource;
use Bitrix\Location\Source\BaseRepository;
use Bitrix\Location\Source\Osm\Api\Api;
use Bitrix\Location\Source\Osm\Converters\Factory;

/**
 * Class Repository
 * @package Bitrix\Location\Source\Osm
 * @internal
 */
final class Repository extends BaseRepository implements
	IRepository,
	IFindByExternalId,
	IFindByCoords,
	ISupportAutocomplete,
	ISource
{
	/** @var string  */
	protected static $sourceCode = 'OSM';

	/** @var Api */
	protected $api;

	/** @var OsmSource */
	protected $osmSource;

	/**
	 * Repository constructor.
	 * @param Api $api
	 * @param OsmSource $osmSource
	 */
	public function __construct(Api $api, OsmSource $osmSource)
	{
		$this->api = $api;
		$this->osmSource = $osmSource;
	}

	/**
	 * @inheritDoc
	 */
	public function findByExternalId(string $externalId, string $sourceCode, string $languageId)
	{
		$osmType = ExternalIdBuilder::getOsmTypeByExternalId($externalId);
		$osmId = ExternalIdBuilder::getOsmIdByExternalId($externalId);

		if ($sourceCode !== self::$sourceCode || is_null($osmType) || is_null($osmId))
		{
			return null;
		}

		$details = $this->api->details(
			[
				'osm_type' => $osmType,
				'osm_id' => $osmId,
				'addressdetails' => 1,
				'accept-language' => $this->osmSource->convertLang($languageId),
			]
		);

		$location = Factory::make($details)->convert(
			$languageId, $details
		);

		if (
			$location
			&& isset($details['centroid'])
		)
		{
			$centroid = Manager::makeConverter(Manager::FORMAT_ARRAY)->read($details['centroid']);

			if ($centroid instanceof Point)
			{
				$customFieldsScenario = CustomFieldsService::getInstance()->getCustomFieldsByPoint($centroid);
				if ($customFieldsScenario)
				{
					$customFieldsScenario->adjustLocation($location);
				}
			}
		}

		return $location;
	}

	public function findByCoords(
		float $lat,
		float $lng,
		int $zoom,
		string $languageId
	): ?Location
	{
		$reverse = $this->api->reverse(
			[
				'lat' => $lat,
				'lng' => $lng,
				'zoom' => $zoom,
				'addressdetails' => 0,
				'accept-language' => $this->osmSource->convertLang($languageId),
			]
		);

		if (
			!(
				isset($reverse['osm_type'])
				&& isset($reverse['osm_id'])
			)
		)
		{
			return null;
		}

		$externalId = ExternalIdBuilder::buildExternalId(
			NodeTypeMap::getShortNodeTypeCode($reverse['osm_type']),
			$reverse['osm_id']
		);

		if (!$externalId)
		{
			return null;
		}

		return $this->findByExternalId(
			$externalId,
			self::$sourceCode,
			$languageId
		);
	}

	/**
	 * @inheritDoc
	 */
	public function autocomplete(array $params): array
	{
		$result = $this->api->autocomplete([
			'q' => $params['q'],
			'lang' => $this->osmSource->convertLang($params['lang']),
			'lat' => $params['lat'] ?? null,
			'lon' => $params['lon'] ?? null,
		]);

		if (
			is_array($result)
			&& isset($result['features'])
			&& is_array($result['features'])
		)
		{
			foreach ($result['features'] as $key => $feature)
			{
				if (!isset($feature['geometry']))
				{
					continue;
				}

				$geometry = Manager::makeConverter(Manager::FORMAT_ARRAY)->read($feature['geometry']);
				if (!$geometry instanceof Point)
				{
					continue;
				}

				$customFieldsScenario = CustomFieldsService::getInstance()->getCustomFieldsByPoint($geometry);
				if (!$customFieldsScenario)
				{
					continue;
				}
				$customFieldsScenario->adjustAutocompleteItem($result['features'][$key]['properties']);
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public static function getSourceCode(): string
	{
		return self::$sourceCode;
	}
}
