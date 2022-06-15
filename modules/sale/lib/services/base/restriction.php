<?php
namespace Bitrix\Sale\Services\Base;

use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Internals\ServiceRestrictionTable;

/**
 * Class RestrictionBase.
 * Base class for payment and delivery services restrictions.
 * @package Bitrix\Sale\Services
 */
abstract class Restriction {

	/** @var int
	 * 100 - lightweight - just compare with params
	 * 200 - middleweight - may be use base queries
	 * 300 - hardweight - use base, and/or hard calculations
	 * */
	public static $easeSort = 100;

	/**
	 * @return string
	 * @throws NotImplementedException
	 */
	public static function getClassTitle()
	{
		throw new NotImplementedException;
	}

	/**
	 * @return string
	 * @throws NotImplementedException
	 */
	public static function getClassDescription()
	{
		throw new NotImplementedException;
	}

	/**
	 * Checking the service parameters for compliance with the restriction.
	 * 
	 * To check of the constraint itself, use method self::validateRestriction
	 * 
	 * @param mixed $params Params to check.
	 * @param array $restrictionParams Restriction params.
	 * @param int $serviceId Service identifier.
	 * @return bool
	 * @throws NotImplementedException
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		throw new NotImplementedException;
	}

	/**
	 * Checking the service parameters for compliance with the restriction by entity.
	 * 
	 * @param Entity $entity
	 * @param array $restrictionParams
	 * @param int $mode
	 * @param int $serviceId
	 * @return int
	 * @throws NotImplementedException
	 */
	public static function checkByEntity(Entity $entity, array $restrictionParams, $mode, $serviceId = 0)
	{
		$severity = static::getSeverity($mode);

		if($severity == RestrictionManager::SEVERITY_NONE)
			return RestrictionManager::SEVERITY_NONE;

		$entityRestrictionParams = static::extractParams($entity);
		$res = static::check($entityRestrictionParams, $restrictionParams, $serviceId);
		return $res ? RestrictionManager::SEVERITY_NONE : $severity;
	}
	
	/**
	 * Checking the restriction for compliance with business rules.
	 * 
	 * For example, for the restriction "currency" in this method,
	 * you can compare which currencies the payment system works with which the restriction is linked.
	 *
	 * @param array $fields restriction fields
	 * @return Result
	 */
	public static function validateRestriction($fields)
	{
		return new Result();
	}

	/**
	 * @param Entity $entity
	 * @return mixed
	 * @throws NotImplementedException
	 */
	protected static function extractParams(Entity $entity)
	{
		throw new NotImplementedException;
	}

	/**
	 * Returns params structure to show it to user
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0)
	{
		return array();
	}

	/**
	 * @param array $paramsValues
	 * @param int $entityId
	 * @return array
	 */
	public static function prepareParamsValues(array $paramsValues, $entityId = 0)
	{
		return $paramsValues;
	}

	/**
	 * @param array $fields
	 * @param int $restrictionId
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	public static function save(array $fields, $restrictionId = 0)
	{
		$fields["CLASS_NAME"] = '\\'.get_called_class();

		if($restrictionId > 0)
			$res = ServiceRestrictionTable::update($restrictionId, $fields);
		else
			$res = ServiceRestrictionTable::add($fields);

		return $res;
	}

	/**
	 * @param $restrictionId
	 * @param int $entityId
	 * @return \Bitrix\Main\Entity\DeleteResult
	 * @throws \Exception
	 */
	public static function delete($restrictionId, $entityId = 0)
	{
		return ServiceRestrictionTable::delete($restrictionId);
	}

	/**
	 * @param int $mode - RestrictionManager::MODE_CLIENT | RestrictionManager::MODE_MANAGER
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		$result = RestrictionManager::SEVERITY_STRICT;

		if($mode == RestrictionManager::MODE_MANAGER)
			return RestrictionManager::SEVERITY_SOFT;

		return $result;
	}

	/**
	 * @param array $servicesIds
	 * @return bool
	 */
	public static function prepareData(array $servicesIds)
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public static function isAvailable()
	{
		return true;
	}

	/*
	 * Children can have also this method
	 * for performance purposes.
	 *
	 * @return int[]
	 * public static function filterServicesArray(Shipment $shipment, array $restrictionFields)
	 * {
	 *  ...
	 * }
	*/
}