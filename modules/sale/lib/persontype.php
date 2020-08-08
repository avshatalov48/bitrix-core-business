<?php

namespace Bitrix\Sale;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Internals;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PersonType
 * @package Bitrix\Sale
 */
class PersonType
{
	/** @var int */
	protected $siteId;

	/** @var array  */
	private $personTypeList = array();

	/**
	 * PersonType constructor.
	 */
	protected function __construct() {}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return mixed
	 * @throws ArgumentException
	 */
	private static function createPersonTypeObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$personTypeClassName = $registry->getPersonTypeClassName();

		return new $personTypeClassName();
	}

	/**
	 * @param null $siteId
	 * @param null $id
	 * @return mixed
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function load($siteId = null, $id = null)
	{
		if (strval($siteId) == "" && intval($id) <= 0)
		{
			throw new ArgumentException();
		}

		$personType = static::createPersonTypeObject();
		$personType->siteId = $siteId;

		$filter = array("=ACTIVE" => "Y");

		if (strval($siteId) != "")
		{
			$filter['=PERSON_TYPE_SITE.SITE_ID'] = $siteId;
		}

		if ($id > 0)
		{
			$filter['ID'] = $id;
		}

		$personTypeList = static::getList(['order'=>["SORT" => "ASC", "ID"=>"ASC"], 'filter' => $filter])
			->fetchAll();

		if ($personTypeList)
		{
			foreach($personTypeList as $personTypeData)
			{
				$personType->personTypeList[$personTypeData['ID']] = $personTypeData;
			}
		}

		return $personType->personTypeList;
	}

	/**
	 * @param array $parameters
	 * @return \Bitrix\Main\ORM\Query\Result
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getList(array $parameters = [])
	{
		if (!isset($parameters['filter']))
		{
			$parameters['filter'] = [];
		}

		$parameters['filter']['=ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		return Internals\PersonTypeTable::getList($parameters);
	}

	/**
	 * @param OrderBase $order
	 * @return Result
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function doCalculate(OrderBase $order)
	{
		$result = new Result();

		if ($order->getPersonTypeId() !== null)
		{
			if (!($personTypeList = static::load($order->getSiteId(), $order->getPersonTypeId())))
			{
				$result->addError(new Entity\EntityError(GetMessage('SKGP_PERSON_TYPE_NOT_FOUND'), 'PERSON_TYPE_ID'));
			}

			return $result;
		}

		if (($personTypeList = static::load($order->getSiteId())) && !empty($personTypeList) && is_array($personTypeList))
		{
			$firstPersonType = reset($personTypeList);
			$order->setPersonTypeId($firstPersonType["ID"]);
		}
		else
		{
			$result->addError(new Entity\EntityError(GetMessage('SKGP_PERSON_TYPE_EMPTY'), 'PERSON_TYPE_ID'));
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function generateXmlId()
	{
		return uniqid('bx_');
	}

}