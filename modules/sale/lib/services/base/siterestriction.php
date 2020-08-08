<?php
namespace Bitrix\Sale\Services\Base;

use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Delivery\Restrictions\Base;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;

Loc::loadMessages(__FILE__);

/**
 * Class SiteRestriction
 * @package Bitrix\Sale\Services\Base
 */
abstract class SiteRestriction extends Base
{
	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_SRV_RSTR_BY_SITE_NAME");
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_RV_RSTR_BY_SITE_DESCRIPT");
	}

	/**
	 * @param $siteId
	 * @param array $restrictionParams
	 * @param int $deliveryId
	 * @return bool
	 */
	public static function check($siteId, array $restrictionParams, $deliveryId = 0)
	{
		if(empty($restrictionParams))
			return true;

		$result = true;

		if($siteId <> '' && isset($restrictionParams["SITE_ID"]) && is_array($restrictionParams["SITE_ID"]))
			$result = in_array($siteId, $restrictionParams["SITE_ID"]);

		return $result;
	}

	/**
	 * @param Entity $entity
	 * @throws NotImplementedException
	 */
	protected static function getOrder(Entity $entity)
	{
		throw new NotImplementedException('Method '.__METHOD__.' must be overload');
	}

	/**
	 * @param Entity $entity
	 * @return bool|mixed|null|string
	 * @throws NotImplementedException
	 */
	protected static function extractParams(Entity $entity)
	{
		/** @var Order $order */
		$order = static::getOrder($entity);

		if (!$order)
			return false;

		return $order->getSiteId();
	}

	/**
	 * @param int $entityId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getParamsStructure($entityId = 0)
	{
		$siteList = array();

		$rsSite = \Bitrix\Main\SiteTable::getList();

		while ($site = $rsSite->fetch())
			$siteList[$site["LID"]] = $site["NAME"]." (".$site["LID"].")";

		return array(
			"SITE_ID" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"DEFAULT" => SITE_ID,
				"LABEL" => Loc::getMessage("SALE_DLVR_RSTR_BY_SITE_SITE_ID"),
				"OPTIONS" => $siteList
			)
		);
	}

	/**
	 * @param int $mode
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		if($mode == RestrictionManager::MODE_MANAGER)
			return RestrictionManager::SEVERITY_STRICT;

		return parent::getSeverity($mode);
	}

	/**
	 * @return bool
	 */
	public static function isAvailable()
	{
		return IsModuleInstalled('crm') ? false : true;
	}

} 