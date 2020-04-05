<?php
namespace Bitrix\Sale\Services\Base;

use Bitrix\Main\GroupTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Internals\UserGroupRestrictionTable;
use Bitrix\Sale\Order;

Loc::loadMessages(__FILE__);

/**
 * Class UserGroupRestriction
 * Restricts entity by users groups
 * @package Bitrix\Sale\Services\Base
 */
class UserGroupRestriction extends Restriction
{
	public static $easeSort = 200;

	/**
	 * @inheritdoc
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_SRV_RSTR_BY_UG_NAME");
	}

	/**
	 * @inheritdoc
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_SRV_RSTR_BY_UG_DESC");
	}

	/**
	 * @param array $groups User groups
	 * @param array $restrictionParams Restriction params.
	 * @param int $entityId Service Identifier.
	 * @return bool
	 */
	public static function check($groups, array $restrictionParams, $entityId = 0)
	{
		if(intval($entityId) <= 0)
			return true;

		if(empty($groups) <= 0)
			return false;

		$commonGroups = array_intersect($groups, $restrictionParams['GROUP_IDS']);
		return !empty($commonGroups);
	}

	/**
	 * @param Entity $entity
	 * @return Order
	 * @throws NotImplementedException
	 */
	protected static function getOrder(Entity $entity)
	{
		throw new NotImplementedException('Method '.__METHOD__.' must be overload');
	}

	/**
	 * @return int
	 * @throws NotImplementedException
	 */
	protected static function getEntityTypeId()
	{
		throw new NotImplementedException('Method '.__METHOD__.' must be overload');
	}

	/**
	 * @param Entity $entity Delivery or
	 * @return array
	 */
	protected static function extractParams(Entity $entity)
	{
		$result = [];

		if($order = static::getOrder($entity))
		{
			$result = \Bitrix\Main\UserTable::getUserGroupIds($order->getUserId());
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	protected static function prepareParamsForSaving(array $params = array(), $entityId = 0)
	{
		$entityId = (int)$entityId;
		$entityTypeId = (int)static::getEntityTypeId();
		UserGroupRestrictionTable::deleteByEntity($entityTypeId, $entityId);

		if(is_array($params['GROUP_IDS']) && !empty($params['GROUP_IDS']))
		{
			foreach($params['GROUP_IDS'] as $groupId)
			{
				UserGroupRestrictionTable::add([
					'ENTITY_TYPE_ID' => $entityTypeId,
					'ENTITY_ID' => $entityId,
					'GROUP_ID' => (int)$groupId
				]);
			}
		}

		return [];
	}

	protected static function getUserGroups()
	{
		$result = [];
		$res = GroupTable::getList([
			'filter' => ['ACTIVE' => 'Y'],
			'order' => ['NAME' => 'ASC']
		]);

		while($group = $res->fetch())
		{
			$result[$group['ID']] = $group['NAME'];
		}

		return $result;
	}

	public static function getParamsStructure($entityId = 0)
	{
		return array(
			"GROUP_IDS" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_SRV_RSTR_BY_UG_LIST"),
				"OPTIONS" => static::getUserGroups()
			)
		);
	}

	public static function prepareParamsValues(array $paramsValues, $entityId = 0)
	{
		$result = [];

		$res = UserGroupRestrictionTable::getList(['filter' => [
			'=ENTITY_TYPE_ID' => static::getEntityTypeId(),
			'=ENTITY_ID' => $entityId
		]]);

		while($row = $res->fetch())
		{
			$result[] = $row['GROUP_ID'];
		}

		return array("GROUP_IDS" =>  $result);
	}

	public static function save(array $fields, $restrictionId = 0)
	{
		$fields["PARAMS"] = static::prepareParamsForSaving($fields["PARAMS"], $fields["SERVICE_ID"]);
		return parent::save($fields, $restrictionId);
	}

	public static function delete($restrictionId, $entityId = 0)
	{
		UserGroupRestrictionTable::deleteByEntity(static::getEntityTypeId(), $entityId);
		return parent::delete($restrictionId);
	}

	/**
	 * @param Entity $entity
	 * @param array $restrictionFields
	 * @return array
	 */
	public static function filterServicesArray(Entity $entity, array $restrictionFields)
	{
		if(empty($restrictionFields))
			return [];

		$groups = static::extractParams($entity);

		if(empty($groups))
		{
			return [];
		}

		$entityIds = array_keys($restrictionFields);

		$res = UserGroupRestrictionTable::getList(array(
			'filter' => array(
				'=ENTITY_TYPE_ID' => static::getEntityTypeId(),
				'=ENTITY_ID' => $entityIds,
				'=GROUP_ID' => $groups
			)
		));

		$result = [];

		while($row = $res->fetch())
		{
			if(!isset($result[$row['ENTITY_ID']]))
			{
				$result[$row['ENTITY_ID']] = true;
			}
		}

		return array_keys($result);
	}

	public static function isAvailable()
	{
		return true;
	}
}