<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Services\PaySystem\Restrictions\Manager;

Loc::loadMessages(__FILE__);

/**
 * Class DeliveryPaySystemTable
 *
 * Fields:
 * <ul>
 * <li> DELIVERY_ID string(35) mandatory
 * <li> PAYSYSTEM_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Sale
 **/

class DeliveryPaySystemTable extends \Bitrix\Main\Entity\DataManager
{
	const LINK_DIRECTION_DELIVERY_PAYSYSTEM = "D";
	const LINK_DIRECTION_PAYSYSTEM_DELIVERY = "P";

	const ENTITY_TYPE_DELIVERY = "DELIVERY_ID";
	const ENTITY_TYPE_PAYSYSTEM = "PAYSYSTEM_ID";

	protected static $unLinked = null;
	protected static $entityItemsFullList = array();
	protected static $entityItemsFieldsList = array();

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_delivery2paysystem';
	}

	public static function getMap()
	{
		return array(
			'DELIVERY_ID' => array(
				'primary' => true,
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('DELIVERY_PAYSYSTEM_ENTITY_DELIVERY_ID_FIELD'),
			),
			'DELIVERY' => array(
				'data_type' => '\Bitrix\Sale\Delivery\Services\Table',
				'reference' => array(
					'=this.DELIVERY_ID' => 'ref.ID'
				)
			),
			'LINK_DIRECTION' => array(
				'primary' => true,
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateLinkDirection'),
				'required' => true,
				'title' => Loc::getMessage('DELIVERY_PAYSYSTEM_ENTITY_LINK_DIRECTION'),
			),
			'PAYSYSTEM_ID' => array(
				'primary' => true,
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('DELIVERY_PAYSYSTEM_ENTITY_PAYSYSTEM_ID_FIELD'),
			),
			'PAYSYSTEM' => array(
				'data_type' => '\Bitrix\Sale\Internals\PaySystemActionTable',
				'reference' => array(
					'=this.PAYSYSTEM_ID' => 'ref.ID'
				)
			)
		);
	}

	public static function validateLinkDirection()
	{
		return array(
			new \Bitrix\Main\Entity\Validator\Length(1, 1),
		);
	}

	/**
	 * @param int $entityId
	 * @param string $entityType self::ENTITY_TYPE_DELIVERY || self::ENTITY_TYPE_PAYSYSTEM
	 * @param int[] $linkedIds Empty means all
	 * @return \Bitrix\Main\Entity\Result
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function  setLinks($entityId, $entityType, array $linkedIds = array(), $actualizeRestrictions = true)
	{
		if(intval($entityId) <= 0)
			throw new ArgumentNullException("entityId");

		if($entityType != self::ENTITY_TYPE_DELIVERY && $entityType != self::ENTITY_TYPE_PAYSYSTEM)
			throw new ArgumentOutOfRangeException("entityType");

		$result = new \Bitrix\Main\Entity\Result();
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();
		$entityId = $sqlHelper->forSql($entityId);
		$reverseParentLinks = array();

		if($entityType == self::ENTITY_TYPE_DELIVERY)
		{
			$linkDirection = self::LINK_DIRECTION_DELIVERY_PAYSYSTEM ;
			$reverseLinkDirection = self::LINK_DIRECTION_PAYSYSTEM_DELIVERY;
			$reverseEntityType = self::ENTITY_TYPE_PAYSYSTEM;
			$parentId = self::getDeliveryParentId($entityId);
		}
		else
		{
			$linkDirection =  self::LINK_DIRECTION_PAYSYSTEM_DELIVERY;
			$reverseLinkDirection = self::LINK_DIRECTION_DELIVERY_PAYSYSTEM;
			$reverseEntityType = self::ENTITY_TYPE_DELIVERY;
			$parentId = 0;

			if(!empty($linkedIds)) // for delivery profiles
			{
				$reverseFieldsList = self::getEntityItemsFieldsList($reverseEntityType);

				foreach($linkedIds as $id)
					if(isset($reverseFieldsList[$id]['PARENT_ID']) && self::isValidParent($reverseFieldsList[$id]['PARENT_ID'], $reverseEntityType))
						if(!in_array($reverseFieldsList[$id]['PARENT_ID'], $linkedIds))
							$reverseParentLinks[] = $reverseFieldsList[$id]['PARENT_ID'];

				if(!empty($reverseParentLinks))
				{
					$linkedIds = array_unique(array_merge($linkedIds, $reverseParentLinks));
				}
			}
		}

		//delete current entity links
		$con->queryExecute(
			"DELETE FROM ".self::getTableName().
			" WHERE ".$entityType."=".$entityId ." AND LINK_DIRECTION='".$linkDirection."'"
		);

		//insert new links
		if(!empty($linkedIds))
			self::insertLinks($entityId, $linkDirection, $entityType, $linkedIds);

		$glParams = array(
			'filter' => array(
				'=LINK_DIRECTION' => $reverseLinkDirection,
			)
		);

		if(!empty($linkedIds))
			$glParams['filter'][$reverseEntityType] = $linkedIds;

		$res = self::getList($glParams);

		$linkedToEntity = array();
		$linkedToOther = array();

		while($rec = $res->fetch())
		{
			if($rec[$entityType] == $entityId)
			{
				if(!in_array($rec[$reverseEntityType], $linkedToEntity))
					$linkedToEntity[] = $rec[$reverseEntityType];
			}
			else
			{
				if(!in_array($rec[$reverseEntityType], $linkedToOther))
					$linkedToOther[] = $rec[$reverseEntityType];
			}
		}

		$reverseIdsToAdd = array_diff($linkedToOther, $linkedToEntity);

		//set reverse links to current entity
		if(!empty($reverseIdsToAdd))
			self::insertLinks($entityId, $reverseLinkDirection, $entityType, $reverseIdsToAdd);

		//delete reverse links we didn't choose
		$glParams = array(
			'filter' => array(
				'=LINK_DIRECTION' => $reverseLinkDirection,
				'='.$entityType => $entityId,
			)
		);

		if(!empty($linkedIds))
			$glParams['filter']['!='.$reverseEntityType] = $linkedIds;

		$res = self::getList($glParams);

		while($rec = $res->fetch())
		{
			self::delete(array(
				'DELIVERY_ID' => $rec['DELIVERY_ID'],
				'PAYSYSTEM_ID' => $rec['PAYSYSTEM_ID'],
				'LINK_DIRECTION' => $rec['LINK_DIRECTION']
			));
		}

		self::$unLinked = null;

		//Modify delivery parent links for working profile links.
		if(!empty($linkedIds))
		{
			$unlinked = self::getUnlinkedEnityItems($entityType);
			if($entityType == self::ENTITY_TYPE_DELIVERY && self::isValidParent($parentId, $entityType) && !in_array($parentId, $unlinked))
			{
				$parentLinks = self::getLinks($parentId, $entityType, array(), false);

				self::setLinks(
					$parentId,
					$entityType,
					array_unique(array_merge($parentLinks, $linkedIds)),
					false
				);
			}
			elseif($entityType == self::ENTITY_TYPE_PAYSYSTEM)
			{
				$reverseFieldsList = self::getEntityItemsFieldsList($reverseEntityType);
				$unlinkedReverse = self::getUnlinkedEnityItems($reverseEntityType);
				$entityList = self::getEntityItemsFullList($entityType);
				$entityList = array_diff($entityList, array($entityId));

				foreach($reverseFieldsList as $id => $fields)
				{
					if(intval($fields['PARENT_ID']) > 0 && in_array($fields['PARENT_ID'], $reverseParentLinks) && in_array($id, $unlinkedReverse))
					{
						self::setLinks(
							$id,
							$reverseEntityType,
							$entityList,
							false
						);
					}
				}
			}
		}

		if($actualizeRestrictions)
		{
			self::actualizeDeliveriesRestrictionByPS();
			self::actualizePaySystemRestrictionByDelivery();
		}

		return $result;
	}

	protected static function isValidParent($parentId, $entityType)
	{
		$parentId = intval($parentId);

		if($parentId <= 0)
			return false;

		if($entityType == self::ENTITY_TYPE_DELIVERY)
		{
			$activeDeliveryData = self::getActiveDeliveryData();

			if(!in_array($parentId, array_keys($activeDeliveryData)))
				return false;

			if($activeDeliveryData[$parentId]['PARENT_CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\Group')
				return false;
		}

		return true;
	}

	protected static function actualizeDeliveriesRestrictionByPS()
	{
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$restrictions = array();
		$dbR = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
			'filter' => array(
				'=CLASS_NAME' => '\Bitrix\Sale\Delivery\Restrictions\ByPaySystem'
			),
			'select' => array('SERVICE_ID')
		));

		while($restr = $dbR->fetch())
			$restrictions[] = $restr['SERVICE_ID'];

		$deliveryList = self::getEntityItemsFullList(self::ENTITY_TYPE_DELIVERY);
		$dLinkedToP = array();
		$deliveriesToPs = array();
		$linkedPS = array();

		$dbP2S = DeliveryPaySystemTable::getList();

		while($d2p = $dbP2S->fetch())
		{
			if($d2p["LINK_DIRECTION"] == self::LINK_DIRECTION_DELIVERY_PAYSYSTEM && !in_array($d2p["DELIVERY_ID"], $dLinkedToP))
				$dLinkedToP[] = $d2p["DELIVERY_ID"];

			if($d2p["LINK_DIRECTION"] == self::LINK_DIRECTION_PAYSYSTEM_DELIVERY )
			{
				if(!isset($deliveriesToPs[$d2p["DELIVERY_ID"]]))
					$deliveriesToPs[$d2p["DELIVERY_ID"]] = array();

				$linkedPS[] = $d2p["PAYSYSTEM_ID"];
				$deliveriesToPs[$d2p["DELIVERY_ID"]][] = $d2p["PAYSYSTEM_ID"];
			}
		}

		$notLinkedToPS = array_diff($deliveryList, $dLinkedToP);
		$existLinkedPs = !empty($linkedPS);
		$notNeedRestriction = array();
		$needRestriction = array();

		foreach($deliveryList as $id)
		{
			$need = true;

			//DS not linked to PS and (All PS having links linked to current DS
			if(in_array($id, $notLinkedToPS))
			{
				if(isset($deliveriesToPs[$id]))
					$diff = array_diff($linkedPS, $deliveriesToPs[$id]);
				else
					$diff = $linkedPS;

				if(!$existLinkedPs || empty($diff))
				{
					$notNeedRestriction[] = $id;
					$need = false;
				}
			}

			// DS linked to PS or exist linked PS but not linked to current DS
			if($need)
				$needRestriction[] = $id;
		}

		$notNeedRestriction = array_intersect($notNeedRestriction, $restrictions);

		if(!empty($notNeedRestriction))
		{
			$sql = "";

			foreach($notNeedRestriction as $deliveryId)
				$sql .= " ".($sql == "" ? "WHERE CLASS_NAME='".$sqlHelper->forSql('\Bitrix\Sale\Delivery\Restrictions\ByPaySystem')."' AND (" : "OR " )."SERVICE_ID=".$sqlHelper->forSql($deliveryId)." AND SERVICE_TYPE=".Restrictions\Manager::SERVICE_TYPE_SHIPMENT;

			$sql = "DELETE FROM ".\Bitrix\Sale\Internals\ServiceRestrictionTable::getTableName().$sql.")";
			$con->queryExecute($sql);
		}

		$needRestriction = array_diff($needRestriction, $restrictions);

		//let's... add missing
		if(!empty($needRestriction))
		{
			$sql = "";

			foreach($needRestriction as $deliveryId)
				$sql .= ($sql == "" ? " " : ", ")."(".$sqlHelper->forSql($deliveryId).", '".$sqlHelper->forSql('\Bitrix\Sale\Delivery\Restrictions\ByPaySystem')."', ".Restrictions\Manager::SERVICE_TYPE_SHIPMENT.")";

			$sql = "INSERT INTO ".\Bitrix\Sale\Internals\ServiceRestrictionTable::getTableName()."(SERVICE_ID, CLASS_NAME, SERVICE_TYPE) VALUES".$sql;
			$con->queryExecute($sql);
		}
	}

	protected static function actualizePaySystemRestrictionByDelivery()
	{
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$restrictions = array();
		$dbR = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
			'filter' => array(
				'=CLASS_NAME' => '\\'.\Bitrix\Sale\Services\PaySystem\Restrictions\Delivery::class
			),
			'select' => array('SERVICE_ID')
		));

		while($restr = $dbR->fetch())
			$restrictions[] = $restr['SERVICE_ID'];

		$deliveryList = self::getEntityItemsFullList(self::ENTITY_TYPE_PAYSYSTEM);
		$dLinkedToP = array();
		$deliveriesToPs = array();
		$linkedPS = array();

		$dbP2S = DeliveryPaySystemTable::getList();

		while($d2p = $dbP2S->fetch())
		{
			if($d2p["LINK_DIRECTION"] == self::LINK_DIRECTION_PAYSYSTEM_DELIVERY && !in_array($d2p["PAYSYSTEM_ID"], $dLinkedToP))
				$dLinkedToP[] = $d2p["PAYSYSTEM_ID"];

			if($d2p["LINK_DIRECTION"] == self::LINK_DIRECTION_DELIVERY_PAYSYSTEM)
			{
				if(!isset($deliveriesToPs[$d2p["PAYSYSTEM_ID"]]))
					$deliveriesToPs[$d2p["PAYSYSTEM_ID"]] = array();

				$linkedPS[] = $d2p["DELIVERY_ID"];
				$deliveriesToPs[$d2p["PAYSYSTEM_ID"]][] = $d2p["DELIVERY_ID"];
			}
		}

		$notLinkedToPS = array_diff($deliveryList, $dLinkedToP);
		$existLinkedPs = !empty($linkedPS);
		$notNeedRestriction = array();
		$needRestriction = array();

		foreach($deliveryList as $id)
		{
			$need = true;

			//DS not linked to PS and (All PS having links linked to current DS
			if(in_array($id, $notLinkedToPS))
			{
				if(isset($deliveriesToPs[$id]))
					$diff = array_diff($linkedPS, $deliveriesToPs[$id]);
				else
					$diff = $linkedPS;

				if(!$existLinkedPs || empty($diff))
				{
					$notNeedRestriction[] = $id;
					$need = false;
				}
			}

			// DS linked to PS or exist linked PS but not linked to current DS
			if($need)
				$needRestriction[] = $id;
		}

		$notNeedRestriction = array_intersect($notNeedRestriction, $restrictions);

		if(!empty($notNeedRestriction))
		{
			$sql = "";

			foreach($notNeedRestriction as $deliveryId)
				$sql .= " ".($sql == "" ? "WHERE CLASS_NAME='".$sqlHelper->forSql('\Bitrix\Sale\Services\PaySystem\Restrictions\Delivery')."' AND (" : "OR " )."SERVICE_ID=".$sqlHelper->forSql($deliveryId)." AND SERVICE_TYPE=".Manager::SERVICE_TYPE_PAYMENT;

			$sql = "DELETE FROM ".\Bitrix\Sale\Internals\ServiceRestrictionTable::getTableName().$sql.")";
			$con->queryExecute($sql);
		}

		$needRestriction = array_diff($needRestriction, $restrictions);

		//let's... add missing
		if(!empty($needRestriction))
		{
			$sql = "";

			foreach($needRestriction as $deliveryId)
				$sql .= ($sql == "" ? " " : ", ")."(".$sqlHelper->forSql($deliveryId).", '".$sqlHelper->forSql('\\'.\Bitrix\Sale\Services\PaySystem\Restrictions\Delivery::class)."', ".Manager::SERVICE_TYPE_PAYMENT.")";

			$sql = "INSERT INTO ".\Bitrix\Sale\Internals\ServiceRestrictionTable::getTableName()."(SERVICE_ID, CLASS_NAME, SERVICE_TYPE) VALUES".$sql;
			$con->queryExecute($sql);
		}
	}

	protected static function insertLinks($entityId, $linkDirection, $entityType, $linkedIds)
	{
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();
		$entityId = (int)$entityId;
		$linkDirection = $sqlHelper->forSql($linkDirection);

		$sql = "INSERT INTO ".
			self::getTableName().
			"(DELIVERY_ID, PAYSYSTEM_ID, LINK_DIRECTION) ".
			"VALUES";

		$first = true;

		foreach($linkedIds as $id)
		{
			if(!$first)
				$sql .= ",";
			else
				$first = false;

			$id = (int)$id;

			if($entityType == self::ENTITY_TYPE_DELIVERY)
				$sql .= " (".$entityId.", ".$id;
			else
				$sql .= " (".$id.", ".$entityId;

			$sql .= ", '".$linkDirection."')";
		}

		$con->queryExecute($sql);
	}

	/**
	 * @param int $entityId
	 * @param string $entityType self::ENTITY_TYPE_DELIVERY || self::ENTITY_TYPE_PAYSYSTEM
	 * @return int[]
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getLinks($entityId, $entityType, array $preparedData = array(), $considerParent = true)
	{
		$result = array();

		if(intval($entityId) <= 0)
			return array();

		if($entityType != self::ENTITY_TYPE_DELIVERY && $entityType != self::ENTITY_TYPE_PAYSYSTEM)
			throw new ArgumentOutOfRangeException("entityType");

		if($entityType == self::ENTITY_TYPE_DELIVERY)
		{
			$linkDirection = self::LINK_DIRECTION_DELIVERY_PAYSYSTEM;
			$reverseLinkDirection = self::LINK_DIRECTION_PAYSYSTEM_DELIVERY;
			$reverseEntityType = self::ENTITY_TYPE_PAYSYSTEM;
			$parentId = self::getDeliveryParentId($entityId);
		}
		else
		{
			$linkDirection =  self::LINK_DIRECTION_PAYSYSTEM_DELIVERY;
			$reverseLinkDirection = self::LINK_DIRECTION_DELIVERY_PAYSYSTEM;
			$reverseEntityType = self::ENTITY_TYPE_DELIVERY;
			$parentId = 0;
		}

		if(isset($preparedData[$entityId]["DIRECT"]))
		{
			$result = $preparedData[$entityId]["DIRECT"];
		}
		else
		{
			$glParams = array(
				'filter' => array(
					"=".$entityType => $entityId,
					"=LINK_DIRECTION" => $linkDirection
				),
				'select' => array($reverseEntityType)
			);

			$res = self::getList($glParams);

			while($rec = $res->fetch())
				$result[] = $rec[$reverseEntityType];
		}

		//if entity has links they must be actual
		if(!empty($result))
		{
			if($considerParent)
			{
				if($entityType == self::ENTITY_TYPE_PAYSYSTEM)
				{
					$result = self::includeDeliveryByParent($result);
					$result = self::excludeDeliveryByParent($result);
				}
				elseif($entityType == self::ENTITY_TYPE_DELIVERY && $parentId > 0)
				{
					$result = self::considerDeliveryParent($result, $entityId, $parentId, $preparedData);
				}
			}

			return $result;
		}

		if(isset($preparedData[$entityId]["REVERSE"]))
		{
			$result = $preparedData[$entityId]["REVERSE"];
		}
		else
		{
			$glParams = array(
				'filter' => array(
					"=".$entityType => $entityId,
					"=LINK_DIRECTION" => $reverseLinkDirection
				),
				'select' => array($reverseEntityType)
			);

			$res = self::getList($glParams);

			while($rec = $res->fetch())
				$result[] = $rec[$reverseEntityType];
		}

		$result = array_merge($result, self::getUnlinkedEnityItems($reverseEntityType, $reverseLinkDirection));

		if($considerParent)
		{
			if($entityType == self::ENTITY_TYPE_DELIVERY && $parentId > 0)
			{
				$result = self::considerDeliveryParent($result, $entityId, $parentId, $preparedData);
			}
			elseif($entityType == self::ENTITY_TYPE_PAYSYSTEM && !empty($result))
			{
				$result = self::excludeDeliveryByParent($result);
			}
		}

		return $result;
	}

	protected static function getActiveDeliveryData()
	{
		$result = array();
		self::getEntityItemsFullList(self::ENTITY_TYPE_DELIVERY);

		if(is_array(self::$entityItemsFieldsList[self::ENTITY_TYPE_DELIVERY]))
		{
			foreach(self::$entityItemsFieldsList[self::ENTITY_TYPE_DELIVERY] as $fields)
			{
				$result[$fields['ID']] = $fields;
			}
		}

		return $result;
	}

	protected static function excludeDeliveryByParent(array $dlvIds)
	{
		$result = array();
		$activeDeliveryData = self::getActiveDeliveryData();

		foreach($dlvIds as $id)
		{
			if(intval($activeDeliveryData[$id]['PARENT_ID']) <= 0)
				$result[] = $id;
			elseif($activeDeliveryData[$id]['PARENT_CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\Group')
				$result[] = $id;
			elseif(in_array($activeDeliveryData[$id]['PARENT_ID'], $dlvIds))
				$result[] = $id;
		}

		return $result;
	}

	protected static function includeDeliveryByParent(array $dlvIds)
	{
		$result = $dlvIds;
		$unlinkedDlvIds = self::getUnlinkedEnityItems(self::ENTITY_TYPE_DELIVERY);

		foreach(self::getActiveDeliveryData() as $id => $fields)
		{
			if(in_array($fields['PARENT_ID'], $dlvIds)) //is profile
				if(in_array($id, $unlinkedDlvIds) && !in_array($id, $result))  //profile doesn't have own restriction by PS
					$result[] = $id;
		}

		return $result;
	}

	protected static function considerDeliveryParent(array $profilePsIds, $profileId, $parentId, $preparedData)
	{
		if(intval($parentId) <= 0)
			return $profilePsIds;

		$result = $profilePsIds;
		$unlinkedIds = self::getUnlinkedEnityItems(self::ENTITY_TYPE_DELIVERY);
		$parentPSIds = self::getLinks($parentId, self::ENTITY_TYPE_DELIVERY, $preparedData);

		if(!in_array($parentId, $unlinkedIds) && in_array($profileId, $unlinkedIds))
			$result = $parentPSIds;
		elseif(!in_array($parentId, $unlinkedIds) && !in_array($profileId, $unlinkedIds))
			$result = array_intersect($profilePsIds, $parentPSIds);

		return $result;
	}
	protected static function getDeliveryParentId($deliveryId)
	{
		$activeData = self::getActiveDeliveryData();

		if(empty($activeData[$deliveryId]))
			return 0;

		$parentId = intval($activeData[$deliveryId]['PARENT_ID']);

		if($parentId <= 0 || empty($activeData[$parentId]))
			return 0;

		if($activeData[$parentId]['CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\Group')
			return 0;

		return $parentId;
	}

	protected static function getUnlinkedEnityItems($entityType, $linkDirection = null)
	{
		if($entityType != self::ENTITY_TYPE_DELIVERY && $entityType != self::ENTITY_TYPE_PAYSYSTEM)
			throw  new ArgumentOutOfRangeException('entityType');

		if($linkDirection != null)
			if($linkDirection != self::LINK_DIRECTION_DELIVERY_PAYSYSTEM && $linkDirection != self::LINK_DIRECTION_PAYSYSTEM_DELIVERY)
				throw  new ArgumentOutOfRangeException('linkDirection');

		if(!isset(self::$unLinked[$entityType]))
		{
			$entityList = array_flip(self::getEntityItemsFullList($entityType));

			self::$unLinked[$entityType] = array(
				self::LINK_DIRECTION_DELIVERY_PAYSYSTEM => $entityList,
				self::LINK_DIRECTION_PAYSYSTEM_DELIVERY => $entityList
			);

			$glParams = array(
				'group' => array($entityType, 'LINK_DIRECTION'),
				'select' => array($entityType, 'LINK_DIRECTION')
			);

			$res = DeliveryPaySystemTable::getList($glParams);

			while($row = $res->fetch())
			{
				if(isset(self::$unLinked[$entityType][$row['LINK_DIRECTION']][$row[$entityType]]))
				{
					unset(self::$unLinked[$entityType][$row['LINK_DIRECTION']][$row[$entityType]]);
				}
			}

			self::$unLinked[$entityType][self::LINK_DIRECTION_DELIVERY_PAYSYSTEM] = array_keys(self::$unLinked[$entityType][self::LINK_DIRECTION_DELIVERY_PAYSYSTEM]);
			self::$unLinked[$entityType][self::LINK_DIRECTION_PAYSYSTEM_DELIVERY] = array_keys(self::$unLinked[$entityType][self::LINK_DIRECTION_PAYSYSTEM_DELIVERY]);
		}

		if($linkDirection == null)
		{
			$result = array_intersect(self::$unLinked[$entityType][self::LINK_DIRECTION_DELIVERY_PAYSYSTEM], self::$unLinked[$entityType][self::LINK_DIRECTION_PAYSYSTEM_DELIVERY]);
		}
		else
		{
			$result = self::$unLinked[$entityType][$linkDirection];
		}

		return $result;
	}

	protected static function getEntityItemsFieldsList($entityType)
	{
		self::getEntityItemsFullList($entityType);
		return self::$entityItemsFieldsList[$entityType];
	}

	protected static function getEntityItemsFullList($entityType)
	{
		if(isset(self::$entityItemsFullList[$entityType]))
			return self::$entityItemsFullList[$entityType];

		self::$entityItemsFullList[$entityType] = array();

		if($entityType == self::ENTITY_TYPE_DELIVERY)
		{
			\Bitrix\Sale\Delivery\Services\Manager::getActiveList();
			$res = Services\Table::getList(array(
				'filter' => array(
					'ACTIVE' => 'Y'
				),
				'select' => array('*', 'PARENT_CLASS_NAME' => 'PARENT.CLASS_NAME')
			));
			
			while($dsrv = $res->fetch())
			{
				$obj = Services\Manager::createObject($dsrv);

				if ($obj && $obj->canHasChildren()) //groups
					continue;

				self::$entityItemsFullList[$entityType][] = $dsrv["ID"];
				self::$entityItemsFieldsList[$entityType][$dsrv["ID"]] = $dsrv;
			}
		}
		else
		{
			$dbRes = PaySystemActionTable::getList(array(
				'filter' => array("ACTIVE" =>  "Y"),
				'select' => array("ID")
			));

			while($ps = $dbRes->fetch())
			{
				self::$entityItemsFullList[$entityType][] = $ps["ID"];
				self::$entityItemsFieldsList[$entityType][$ps["ID"]] = $ps;
			}
		}

		return self::$entityItemsFullList[$entityType];
	}

	public static function prepareData(array $entityIds, $entityType)
	{
		static $preparedData = array();

		if(!isset($preparedData[$entityType]))
			$preparedData[$entityType] = array();

		if($entityType == self::ENTITY_TYPE_DELIVERY)
		{
			$linkDirection = self::LINK_DIRECTION_DELIVERY_PAYSYSTEM;
			$reverseLinkDirection = self::LINK_DIRECTION_PAYSYSTEM_DELIVERY;
			$reverseEntityType = self::ENTITY_TYPE_PAYSYSTEM;
		}
		else
		{
			$linkDirection =  self::LINK_DIRECTION_PAYSYSTEM_DELIVERY;
			$reverseLinkDirection = self::LINK_DIRECTION_DELIVERY_PAYSYSTEM;
			$reverseEntityType = self::ENTITY_TYPE_DELIVERY;
		}

		if(empty($entityIds))
			$entityIds = self::getEntityItemsFullList($entityType);

		$arrdif = array_diff($entityIds, array_keys($preparedData[$entityType]));

		if(is_array($arrdif) && empty($arrdif))
			return array_intersect_key($preparedData[$entityType], $entityIds);

		$glParams = array(
			'filter' => array(
				"=".$entityType => $arrdif
			)
		);

		$res = DeliveryPaySystemTable::getList($glParams);

		foreach($arrdif as $id)
		{
			$preparedData[$entityType][$id] = array(
				"DIRECT" => array(),
				"REVERSE" => array()
			);
		}

		while($rec = $res->fetch())
		{
			if($rec["LINK_DIRECTION"] == $linkDirection)
				$preparedData[$entityType][$rec[$entityType]]["DIRECT"][] = $rec[$reverseEntityType];
			elseif($rec["LINK_DIRECTION"] == $reverseLinkDirection)
				$preparedData[$entityType][$rec[$entityType]]["REVERSE"][] = $rec[$reverseEntityType];
		}

		return $preparedData[$entityType];
	}
}