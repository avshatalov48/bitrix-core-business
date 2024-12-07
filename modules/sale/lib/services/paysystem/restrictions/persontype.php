<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\ClientType;
use Bitrix\Sale\PaySystem\Service;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Services\Base;

Loc::loadMessages(__FILE__);

class PersonType extends Base\Restriction
{
	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if (is_array($restrictionParams) && isset($restrictionParams['PERSON_TYPE_ID']))
		{
			return in_array($params, $restrictionParams['PERSON_TYPE_ID']);
		}

		return true;
	}

	/**
	 * @param Entity $entity
	 * @return int
	 */
	public static function extractParams(Entity $entity)
	{
		if ($entity instanceof CollectableEntity)
		{
			/** @var PaymentCollection $collection */
			$collection = $entity->getCollection();

			/** @var Order $order */
			$order = $collection->getOrder();
		}
		elseif ($entity instanceof Order)
		{
			$order = $entity;
		}

		if (!$order)
			return false;

		$personTypeId = $order->getPersonTypeId();
		return $personTypeId;
	}

	/**
	 * @return mixed
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PERSON_TYPE');
	}

	/**
	 * @return mixed
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PERSON_TYPE_DESC');
	}

	public static function getOnApplyErrorMessage(): string
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PERSON_TYPE_ON_APPLY_ERROR_MSG');
	}

	/**
	 * @param $entityId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getParamsStructure($entityId = 0)
	{
		$personTypeList = array();

		$dbRes = \Bitrix\Sale\PersonType::getList();

		while ($personType = $dbRes->fetch())
			$personTypeList[$personType["ID"]] = $personType["NAME"]." (".$personType["ID"].")";

		return array(
			"PERSON_TYPE_ID" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_SALE_PS_RESTRICTIONS_BY_PERSON_TYPE_NAME"),
				"OPTIONS" => $personTypeList
			)
		);
	}

	/**
	 * @param $mode
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		return Manager::SEVERITY_STRICT;
	}
}
