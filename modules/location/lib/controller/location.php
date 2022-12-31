<?php

namespace Bitrix\Location\Controller;

use Bitrix\Location\Entity\Location\Parents;
use Bitrix\Location\Infrastructure\Service\ErrorService;
use Bitrix\Location\Service;
use Bitrix\Main\Engine\ActionFilter\Cors;
use \Bitrix\Location\Entity;
use Bitrix\Main\Engine\Response\AjaxJson;

/**
 * Class Location
 * @package Bitrix\Location\Controller
 * Facade
 */
class Location extends \Bitrix\Main\Engine\Controller
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

		if($location)
		{
			$result = $location->toArray();
		}
		elseif($location === false)
		{
			if(ErrorService::getInstance()->hasErrors())
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
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function findParentsAction(array $location)
	{
		$result = new Parents();
		$entity = Entity\Location::fromArray($location);

		if($entity)
		{
			$parents = $entity->getParents();

			if($parents)
			{
				$result = \Bitrix\Location\Entity\Location\Converter\ArrayConverter::convertParentsToArray($parents);
			}
			else if($parents === false)
			{
				if(ErrorService::getInstance()->hasErrors())
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
		if($location && $location->getId() > 0)
		{
			$externalLocation = Service\LocationService::getInstance()->findByExternalId(
				$externalId,
				$sourceCode,
				$languageId,
				LOCATION_SEARCH_SCOPE_EXTERNAL
			);

			if($externalLocation)
			{
				$location->setAddress($externalLocation->getAddress());
			}
		}
		/* */

		if($location)
		{
			$result = \Bitrix\Location\Entity\Location\Converter\ArrayConverter::convertToArray($location);
		}
		else
		{
			if(ErrorService::getInstance()->hasErrors())
			{
				$result = AjaxJson::createError(
					ErrorService::getInstance()->getErrors()
				);
			}
		}

		return $result;
	}

	public static function saveAction(array $location)
	{
		$entity = Entity\Location::fromArray($location);
		$result = $entity->save();

		return [
			'isSuccess' => $result->isSuccess(),
			'errors' => $result->getErrorMessages(),
			'location' => \Bitrix\Location\Entity\Location\Converter\ArrayConverter::convertToArray($entity)
		];
	}

	/**
	 * @param array $location
	 * @return \Bitrix\Main\Result
	 */
	public function deleteAction(array $location)
	{
		return Service\LocationService::getInstance()->delete(
			Entity\Location::fromArray($location)
		);
	}
}
