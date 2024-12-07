<?php

namespace Bitrix\Location\Service;

use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Common\BaseService;
use Bitrix\Main\Result;
use Bitrix\Location\Entity;
use Bitrix\Location\Repository\LocationRepository;
use Bitrix\Location\Infrastructure\Service\Config;
use Bitrix\Location\Common\RepositoryTrait;

/**
 * Class LocationService
 *
 * Service to work with locations
 *
 * @package Bitrix\Location\Service
 */
final class LocationService extends BaseService
{
	use RepositoryTrait;

	/** @var LocationService */
	protected static $instance;

	/** @var LocationRepository  */
	protected $repository = null;

	/**
	 * Find Location by locationId
	 *
	 * @param int $locationId
	 * @param string $languageId
	 * @param int $searchScope
	 * @return Entity\Location|null|bool
	 */
	public function findById(int $locationId, string $languageId, int $searchScope = LOCATION_SEARCH_SCOPE_ALL)
	{
		$result = false;

		try
		{
			$result = $this->repository->findById($locationId, $languageId, $searchScope);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Find location by externalId
	 *
	 * @param string $externalId
	 * @param string $sourceCode
	 * @param string $languageId
	 * @param int $searchScope
	 * @return Entity\Location|bool|null
	 */
	public function findByExternalId(string $externalId, string $sourceCode, string $languageId, int $searchScope = LOCATION_SEARCH_SCOPE_ALL)
	{
		$result = false;

		try
		{
			$result = $this->repository->findByExternalId($externalId, $sourceCode, $languageId, $searchScope);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Find location by coordinates
	 *
	 * @param float $lat
	 * @param float $lng
	 * @param int $zoom
	 * @param string $languageId
	 * @return Entity\Location|null
	 */
	public function findByCoords(
		float $lat,
		float $lng,
		int $zoom,
		string $languageId
	): ?Entity\Location
	{
		try
		{
			return $this->repository->findByCoords(
				$lat,
				$lng,
				$zoom,
				$languageId,
				LOCATION_SEARCH_SCOPE_EXTERNAL
			);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return null;
	}

	/**
	 * @param array $params
	 * @param int $searchScope
	 * @return array
	 */
	public function autocomplete(array $params, int $searchScope = LOCATION_SEARCH_SCOPE_ALL)
	{
		$result = [];

		try
		{
			$result = $this->repository->autocomplete($params, $searchScope);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Find Location parents
	 *
	 * @param Entity\Location $location
	 * @param string $languageId
	 * @param int $searchScope
	 * @return Entity\Location\Parents|bool
	 * @internal
	 */
	public function findParents(Entity\Location $location, string $languageId, int $searchScope = LOCATION_SEARCH_SCOPE_ALL)
	{
		$result = false;

		try
		{
			$result = $this->repository->findParents($location, $languageId, $searchScope);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Save Location
	 *
	 * @param Entity\Location $location
	 * @return Result
	 */
	public function save(Entity\Location $location): Result
	{
		return $this->repository->save($location);
	}

	/**
	 * Delete Location
	 *
	 * @param Entity\Location $location
	 * @return Result
	 */
	public function delete(Entity\Location $location): Result
	{
		return $this->repository->delete($location);
	}

	/**
	 * LocationService constructor.
	 *
	 * @param Config\Container $config
	 */
	protected function __construct(Config\Container $config)
	{
		$this->setRepository($config->get('repository'));

		parent::__construct($config);
	}

	/**
	 * Save parents from the location
	 *
	 * @param Entity\Location\Parents $parents
	 * @return Result
	 * @internal
	 */
	public function saveParents(Entity\Location\Parents $parents): Result
	{
		return $this->repository->saveParents($parents);
	}
}
