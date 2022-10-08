<?php

namespace Bitrix\Location\Source\Google;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Entity\Generic\Collection;
use Bitrix\Location\Entity\Location\Parents;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Repository\Location\Capability\IFindByExternalId;
use Bitrix\Location\Repository\Location\Capability\IFindByText;
use Bitrix\Location\Repository\Location\Capability\IFindParents;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Location\Repository\Location\ISource;
use Bitrix\Location\Service\LocationService;
use Bitrix\Location\Source\BaseRepository;
use Bitrix\Location\Source\Google\Converters;
use Bitrix\Location\Source\Google\Converters\BaseConverter;
use Bitrix\Location\Source\Google\Requesters;
use Bitrix\Location\Source\Google\Requesters\BaseRequester;
use \Bitrix\Location\Common\CachedPool;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;

Loc::loadMessages(__FILE__);

/**
 * Class Google
 * @package Bitrix\Location\Source
 */
class Repository extends BaseRepository implements IRepository, IFindByExternalId, IFindByText, IFindParents, ISource
{
	/** @var string  */
	protected $apiKey = '';
	/** @var string  */
	protected static $sourceCode = 'GOOGLE';
	/** @var HttpClient  */
	protected $httpClient = null;
	/** @var CachedPool */
	protected $cachePool = null;
	/** @var GoogleSource  */
	protected $googleSource = null;

	public function __construct(
		string $apiKey,
		HttpClient $httpClient,
		GoogleSource $googleSource,
		CachedPool $cachePool = null
	)
	{
		$this->apiKey = $apiKey;
		$this->httpClient = $httpClient;
		$this->cachePool = $cachePool;
		$this->googleSource = $googleSource;
	}

	/** @inheritDoc */
	public function findByExternalId(string $locationExternalId, string $sourceCode, string $languageId)
	{
		if($sourceCode !== self::$sourceCode || $locationExternalId === '')
		{
			return null;
		}

		return $this->find(
			new Requesters\ByIdRequester($this->httpClient, $this->cachePool),
			new Converters\ByIdConverter($languageId),
			[
				'placeid' => $locationExternalId,
				'language' => $this->googleSource->convertLang($languageId)
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function findByText(string $query, string $languageId)
	{
		if($query == '')
		{
			return null;
		}

		return $this->find(
			new Requesters\ByQueryRequester($this->httpClient, $this->cachePool),
			new Converters\ByQueryConverter($languageId),
			[
				'query' => $query,
				'language' => $this->googleSource->convertLang($languageId)
			]
		);
	}

	protected function isCollectionContainLocation(Location $location, Collection $collection): bool
	{
		foreach ($collection->getItems() as $item)
		{
			if($location->getExternalId() === $item->getExternalId())
			{
				return true;
			}
		}

		return false;
	}

	protected function chooseParentFromCollection(
		Location $location,
		Collection $collection,
		Parents $parentResultCollection,
		array $parentTypes
	): ?Location
	{
		if($collection->count() <= 0)
		{
			return null;
		}

		$candidatesTypes = [];
		$result = null;

		for($i = 0, $l = $collection->count(); $i < $l; $i++)
		{
			$candidate = $collection[$i];

			if($location->getExternalId() === $candidate->getExternalId())
			{
				continue;
			}

			$candidateType = $candidate->getType();

			if($candidateType === Location\Type::UNKNOWN)
			{
				continue;
			}

			if($location->getType() !== Location\Type::UNKNOWN && $candidate->getType() >= $location->getType())
			{
				continue;
			}

			// check if we already have the same location in result parents collection
			if($this->isCollectionContainLocation($candidate, $parentResultCollection))
			{
				continue;
			}

			if(in_array($candidateType, $parentTypes, true))
			{
				return $candidate;
			}

			$candidatesTypes[] = [$i, $candidateType];
		}

		if(count($candidatesTypes) <= 0)
		{
			return null;
		}

		if(count($candidatesTypes) > 1)
		{
			$typeColumn = array_column($candidatesTypes, 1);
			array_multisort($typeColumn, SORT_ASC, $candidatesTypes);
		}

		return $collection[$candidatesTypes[0][0]];
	}

	/** @inheritDoc */
	/*
	 * Needs tests
	 */
	public function findParents(Location $location, string $languageId): ?Parents
	{
		if($location->getSourceCode() !== self::$sourceCode || $location->getExternalId() == '')
		{
			return null;
		}

		$result = (new Parents())
			->setDescendant($location);

		/* Temporary. To decrease the usage of the Google API */
		return $result;
		/* */

		//We need full information about the location
		$rawData = $this->find(
			new Requesters\ByIdRequester($this->httpClient, $this->cachePool),
			null,
			[
				'placeid' => $location->getExternalId(),
				'language' => $languageId
			]
		);

		$ancestorDataConverter = new Converters\AncestorDataConverter();
		$ancestorsRawData = $ancestorDataConverter->convert($rawData, $location->getType());

		//is it always available?
		$latLon = $location->getLatitude().','.$location->getLongitude();

		foreach ($ancestorsRawData as $data)
		{
			//Just searching by query taking into account lat and lon
			$res = $this->find(
				new Requesters\ByQueryRequester($this->httpClient, $this->cachePool),
				new Converters\ByQueryConverter($languageId),
				[
					'query' => $data['NAME'],
					//todo: may be restrict by several types?
					'location' => $latLon,
					'language' => $languageId
				]
			);

			if($res instanceof Collection && $res->count() > 0)
			{
				if(!($parentSource = $this->chooseParentFromCollection($location, $res, $result, $data['TYPES'])))
				{
					continue;
				}

				$localParent = $this->findLocalLocationByExternalId($parentSource);

				//the parent location have already been saved
				if ($localParent)
				{
					$result->addItem($localParent);

					if ($llParents = $localParent->getParents())
					{
						foreach ($llParents as $localParent)
						{
							$result->addItem($localParent);
						}
					}

					break;
				}
				else
				{
					//we need detailed info
					$detailedParent = $this->findByExternalId(
						$parentSource->getExternalId(),
						self::$sourceCode,
						$languageId
					);

					if($detailedParent)
					{
						$result->addItem($detailedParent);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param Location $location
	 * @return Location|bool|null
	 * todo: maybe carry out?
	 */
	protected function findLocalLocationByExternalId(Location $location)
	{
		return LocationService::getInstance()->findByExternalId(
			$location->getExternalId(),
			$location->getSourceCode(),
			$location->getLanguageId(),
			LOCATION_SEARCH_SCOPE_INTERNAL
		);
	}

	/**
	 * @param Requesters\BaseRequester $requester
	 * @param Converters\BaseConverter $converter
	 * @return Finder
	 */
	protected function buildFinder($requester, $converter)
	{
		return new Finder($requester, $converter);
	}

	/**
	 * @param BaseRequester $requester`
	 * @param BaseConverter $converter
	 * @param array $findParams
	 * @return Location|Collection|false|null|array
	 */
	protected function find($requester,  $converter = null, array $findParams = [])
	{
		if($this->apiKey === '')
		{
			throw new RuntimeException(
				Loc::getMessage('LOCATION_ADDRESS_REPOSITORY_API_KEY_ERROR'),
				ErrorCodes::REPOSITORY_FIND_API_KEY_ERROR
			);
		}

		$finder = $this->buildFinder($requester, $converter);
		$findParams['key'] = $this->apiKey;
		return $finder->find($findParams);
	}

	/** @inheritDoc */
	public static function getSourceCode(): string
	{
		return self::$sourceCode;
	}
}
