<?php
namespace Bitrix\Sale\Cashbox\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale;
use Bitrix\Sale\Services\Base\Restriction;

Loc::loadMessages(__FILE__);

/**
 * Class Company
 * @package Bitrix\Sale\Cashbox\Restrictions
 */
class Company extends Restriction
{
	public static $easeSort = 200;
	protected static $preparedData = array();

	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_CASHBOX_RSTR_BY_COMPANY_TITLE");
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_CASHBOX_RSTR_BY_COMPANY_DESC");
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if (is_array($restrictionParams) && isset($restrictionParams['COMPANY']))
		{
			$diff = array_diff($params, $restrictionParams['COMPANY']);
			return empty($diff);
		}

		return true;
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	protected static function extractParams(Entity $entity)
	{
		$result = array();

		if ($entity instanceof Sale\Payment ||
			$entity instanceof Sale\Shipment ||
			$entity instanceof Sale\Order
		)
		{
			$result[] = $entity->getField('COMPANY_ID');
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	protected static function getCompanyList()
	{
		static $result = null;

		if($result !== null)
			return $result;

		$result = array();

		$dbResultList = Sale\Services\Company\Manager::getList(array(
			'select' => array("ID", "NAME", "ACTIVE"),
			'filter' => array("ACTIVE" => "Y"),
			'order' => array("SORT"=>"ASC", "NAME"=>"ASC")
		));

		while ($item = $dbResultList->fetch())
			$result[$item["ID"]] = $item["NAME"];

		return $result;
	}

	/**
	 * @param int $entityId
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0)
	{
		$result =  array(
			"COMPANY" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_CASHBOX_RSTR_BY_COMPANY"),
				"OPTIONS" => self::getCompanyList()
			)
		);

		return $result;
	}

	/**
	 * @param int $mode - RestrictionManager::MODE_CLIENT | RestrictionManager::MODE_MANAGER
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		return Manager::SEVERITY_STRICT;
	}

}