<?php

namespace Bitrix\Location\Controller;

use Bitrix\Location\Entity\Location\Parents;
use Bitrix\Location\Infrastructure\Service\ErrorService;
use Bitrix\Location\Repository\FormatRepository;
use Bitrix\Location\Service;
use Bitrix\Location\Entity;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Location\Entity\Location\Converter\ArrayConverter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Result;

/**
 * Class Location
 * @package Bitrix\Location\Controller
 * Facade
 */
class Location extends Controller
{
	protected function getDefaultPreFilters()
	{
		return [];
	}

	/**
	 * @param int $locationId
	 * @param string $languageId
	 * @return array|null|AjaxJson
	 */
	public function findByIdAction(int $locationId, string $languageId)
	{
		$result = null;
		$location = Service\LocationService::getInstance()->findById($locationId, $languageId);

		if ($location)
		{
			$result = $location->toArray();
		}
		elseif ($location === false)
		{
			if (ErrorService::getInstance()->hasErrors())
			{
				$result = AjaxJson::createError(
					ErrorService::getInstance()->getErrors()
				);
			}
		}

		return $result;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function autocompleteAction(array $params)
	{
		return Service\LocationService::getInstance()->autocomplete($params, LOCATION_SEARCH_SCOPE_EXTERNAL);
	}

	/**
	 * @param array $location
	 * @return array|AjaxJson
	 */
	public function findParentsAction(array $location)
	{
		$result = new Parents();

		$entity = Entity\Location::fromArray($location);

		if ($entity)
		{
			$parents = $entity->getParents();

			if ($parents)
			{
				$result = ArrayConverter::convertParentsToArray($parents);
			}
			else if($parents === false)
			{
				if (ErrorService::getInstance()->hasErrors())
				{
					$result = AjaxJson::createError(
						ErrorService::getInstance()->getErrors()
					);
				}
			}
		}

		return $result;
	}

	/**
	 * array $fields
	 * @return array|null|AjaxJson
	 */
	public function findByExternalIdAction(string $externalId, string $sourceCode, string $languageId)
	{
		$result = null;
		$location = Service\LocationService::getInstance()->findByExternalId($externalId, $sourceCode, $languageId);

		/* Temporary. To decrease the usage of the Google API */
		if ($location && $location->getId() > 0)
		{
			$externalLocation = Service\LocationService::getInstance()->findByExternalId(
				$externalId,
				$sourceCode,
				$languageId,
				LOCATION_SEARCH_SCOPE_EXTERNAL
			);

			if ($externalLocation)
			{
				$location->setAddress($externalLocation->getAddress());
			}
		}

		if ($location)
		{
			$result = ArrayConverter::convertToArray($location);
		}
		else
		{
			if (ErrorService::getInstance()->hasErrors())
			{
				$result = AjaxJson::createError(
					ErrorService::getInstance()->getErrors()
				);
			}
		}

		return $result;
	}

	public function findByCoordsAction(float $lat, float $lng, int $zoom, string $languageId)
	{
		$result = null;

		$location = Service\LocationService::getInstance()->findByCoords($lat, $lng, $zoom, $languageId);

		if ($location)
		{
			$result = ArrayConverter::convertToArray($location);
		}
		else
		{
			if (ErrorService::getInstance()->hasErrors())
			{
				$result = AjaxJson::createError(
					ErrorService::getInstance()->getErrors()
				);
			}
		}

		return $result;
	}

	public static function saveAction(array $location): array
	{
		$entity = Entity\Location::fromArray($location);
		$result = $entity->save();

		return [
			'isSuccess' => $result->isSuccess(),
			'errors' => $result->getErrorMessages(),
			'location' => ArrayConverter::convertToArray($entity),
		];
	}

	public function deleteAction(array $location): Result
	{
		return Service\LocationService::getInstance()->delete(
			Entity\Location::fromArray($location)
		);
	}
}
