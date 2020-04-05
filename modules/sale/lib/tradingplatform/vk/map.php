<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\SystemException;
use Bitrix\Sale\TradingPlatform\MapTable;
use Bitrix\Sale\TradingPlatform\MapEntityTable;


/**
 * Class Map
 * Work with map - create, update, delete entity end etc
 *
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class Map
{
	private static function getProductEntityCode($exportId)
	{
		return "VK_PRODUCTS_" . $exportId;
	}
	
	private static function getPhotoEntityCode($exportId)
	{
		return "VK_PHOTOS_" . $exportId;
	}
	
	private static function getAlbumEntityCode($exportId)
	{
		return "VK_ALBUMS_" . $exportId;
	}
	
	private static function getSectionsEntityCode($exportId)
	{
		return "VK_SECTIONS_" . $exportId;
	}
	
	private static function getGeneralCodePrefix()
	{
		return "VK_";
	}
	
	
	private static function getProductEntityId($exportId)
	{
		$productEntCode = self::getProductEntityCode($exportId);
		
		return self::getMapEntityId($productEntCode);
	}
	
	private static function getPhotoEntityId($exportId)
	{
		$photoEntCode = self::getPhotoEntityCode($exportId);
		
		return self::getMapEntityId($photoEntCode);
	}
	
	
	private static function getAlbumEntityId($exportId)
	{
		$albumEntCode = self::getAlbumEntityCode($exportId);
		
		return self::getMapEntityId($albumEntCode);
	}
	
	
	private static function getSectionsEntityId($exportId)
	{
		$sectionsEntCode = self::getSectionsEntityCode($exportId);
		
		return self::getMapEntityId($sectionsEntCode);
	}

//	--------------------------------------------------------
	public static function addProductMapping($values, $exportId)
	{
		$mapEntityCode = self::getProductEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::addEntityMapping($values, $mapEntityID);
	}
	
	public static function removeProductMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getProductEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::removeEntityMapping($values, $mapEntityID, $flagKeys);
	}
	
	public static function updateProductMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getProductEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::updateEntityMapping($values, $mapEntityID, $flagKeys);
	}

//	--------------------------------------------------------
	public static function addPhotoMapping($values, $exportId)
	{
		$mapEntityCode = self::getPhotoEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::addEntityMapping($values, $mapEntityID);
	}
	
	public static function removePhotoMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getPhotoEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::removeEntityMapping($values, $mapEntityID, $flagKeys);
	}
	
	public static function updatePhotoMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getPhotoEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::updateEntityMapping($values, $mapEntityID, $flagKeys);
	}

//	--------------------------------------------------------
	public static function addAlbumMapping($values, $exportId)
	{
		$mapEntityCode = self::getAlbumEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::addEntityMapping($values, $mapEntityID);
	}
	
	public static function removeAlbumMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getAlbumEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::removeEntityMapping($values, $mapEntityID, $flagKeys);
	}
	
	public static function updateAlbumMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getAlbumEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::updateEntityMapping($values, $mapEntityID, $flagKeys);
	}

//	--------------------------------------------------------
	public static function addSectionsMapping($values, $exportId)
	{
		$mapEntityCode = self::getSectionsEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::addEntityMapping($values, $mapEntityID);
	}
	
	public static function removeSectionsMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getSectionsEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::removeEntityMapping($values, $mapEntityID, $flagKeys);
	}
	
	public static function updateSectionsMapping($values, $exportId, $flagKeys = '')
	{
		$mapEntityCode = self::getSectionsEntityCode($exportId);
		$mapEntityID = self::getMapEntityId($mapEntityCode);
		
		return self::updateEntityMapping($values, $mapEntityID, $flagKeys);
	}
	
	
	/**
	 * Return ID of map entity code. If code not exist - create new item.
	 *
	 * @param string $mapEntityCode Map entity code
	 * @return int Map entity id.
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getMapEntityId($mapEntityCode)
	{
		$result = 0;
		$vk = Vk::getInstance();
		
		$fields = array(
			"TRADING_PLATFORM_ID" => $vk->getId(),
			"CODE" => $mapEntityCode,
		);
		
		$resMapEntity = MapEntityTable::getList(array(
			"filter" => $fields,
		));
		
		if ($mapEntity = $resMapEntity->fetch())
		{
			$result = $mapEntity["ID"];
		}
		else
		{
			$resAdd = MapEntityTable::add($fields);
			
			if ($resAdd->isSuccess())
				$result = $resAdd->getId();
		}
		
		if ($result <= 0)
			throw new SystemException("Can' t get map entity id for code: " . $mapEntityCode . ".");
		
		return $result;
	}
	
	
	/**
	 * Add item for mapping by entity ID
	 *
	 * @param $values
	 * @param $mapEntityID
	 * @return bool
	 * @throws \Exception
	 */
	private static function addEntityMapping($values, $mapEntityID)
	{
		$result = true;
//		todo: maybe we can use packed adding for acceleration
		foreach ($values as $item)
		{
			$item = array_change_key_case($item, CASE_UPPER);        // preserve low case keys
			$fields = array(
				"VALUE_EXTERNAL" => $item["VALUE_EXTERNAL"],
				"VALUE_INTERNAL" => $item["VALUE_INTERNAL"],
				"ENTITY_ID" => $mapEntityID,
			);
//			add params if not null
			if ($item["PARAMS"])
				$fields["PARAMS"] = $item["PARAMS"];
			
			$addRes = MapTable::add($fields);
			
			if (!$addRes->isSuccess() || !$result)
				$result = false;
		}
		
		return $result;
	}
	
	
	/**
	 * Update or create new entity mapping
	 *
	 * @param $values
	 * @param $mapEntityID
	 * @param $flagKeys
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	private static function updateEntityMapping($values, $mapEntityID, $flagKeys)
	{
		$result = true;
		
		foreach ($values as $item)
		{
			$fields = array_change_key_case($item, CASE_UPPER);        // preserve low case keys
			$fields["ENTITY_ID"] = intval($mapEntityID);
			$fields["VALUE_EXTERNAL"] = strval($item["VALUE_EXTERNAL"]);
			$fields["VALUE_INTERNAL"] = strval($item["VALUE_INTERNAL"]);
			$filterToId = $fields;
			
			if ($flagKeys == "ONLY_INTERNAL")
				unset($filterToId["VALUE_EXTERNAL"]);
			if ($flagKeys == "ONLY_EXTERNAL")
				unset($filterToId["VALUE_INTERNAL"]);
			unset($filterToId["PARAMS"]);

//			get ID for current element
			$id = MapTable::getList(
				array(
					"filter" => $filterToId,
					"select" => array("ID"),
				)
			);
			$id = $id->fetch();

//			update or create element
			if ($id = $id["ID"])
			{
				$upRes = MapTable::update($id, $fields);
				if (!$upRes->isSuccess())
					$result = false;
			}
			else
			{
				$addRes = MapTable::add($fields);
				if (!$addRes->isSuccess())
					$result = false;
			}
			
		}

//		if result == false - we have problem minimum in one item, maybe in all items
		return $result;
	}
	
	
	/**
	 * Remove item from mapping if exists
	 *
	 * @param $values
	 * @param $mapEntityID
	 * @param string $flagKey
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	private static function removeEntityMapping($values, $mapEntityID, $flagKey = '')
	{
		$result = true;
//		todo: maybe we can use packed adding for acceleration
		foreach ($values as $item)
		{
//			break empty items
			if(empty($item))
				continue;
			
//			preserve lowercase $item
			$item = array_change_key_case($item, CASE_UPPER);
			
			if ($flagKey == "ONLY_INTERNAL")
				unset($item["VALUE_EXTERNAL"]);
			elseif ($flagKey == "ONLY_EXTERNAL")
				unset($item["VALUE_INTERNAL"]);
			
			$fields = array("ENTITY_ID" => $mapEntityID);
			$fields = array_merge($fields, $item);
			
			$id = MapTable::getList(array(
					"filter" => $fields,
					"select" => array("ID"),
				)
			);
			$id = $id->fetch();
			
			if ($id)
			{
				$delRes = MapTable::delete($id["ID"]);
				if (!$delRes->isSuccess() || !$result)
					$result = false;
			}
			else
				$result = false;
			
		}

//		if result == false - we have problem minimum in one item, maybe in all items
		return $result;
	}
	
	
	/**
	 * Get albums, saved in mapping by export ID
	 *
	 * @param $exportId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getMappedAlbums($exportId)
	{
		$result = array();
		$albumEntityId = self::getAlbumEntityId($exportId);
		
		$catRes = MapTable::getList(array(
//			'select' => array('VALUE_INTERNAL'),
			'filter' => array('=ENTITY_ID' => $albumEntityId),
		));
		
		while ($album = $catRes->fetch())
			$result[$album["VALUE_INTERNAL"]] = array(
				"SECTION_ID" => $album["VALUE_INTERNAL"],
				"ALBUM_VK_ID" => $album["VALUE_EXTERNAL"],
			);
		
		return $result;
	}
	
	/**
	 * Get products, saved in mapping by export ID
	 *
	 * @param $exportId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getMappedProducts($exportId)
	{
		$result = array();
		$productEntId = self::getProductEntityId($exportId);
		
		$catRes = MapTable::getList(array(
//			'select' => array('VALUE_INTERNAL'),
			'filter' => array('=ENTITY_ID' => $productEntId),
		));
		
		while ($product = $catRes->fetch())
			$result[$product["VALUE_INTERNAL"]] = array(
				"BX_ID" => $product["VALUE_INTERNAL"],
				"VK_ID" => $product["VALUE_EXTERNAL"],
			);
		
		return $result;
	}
	
	/**
	 * Get sections, saved in mapping by export ID
	 *
	 * @param $exportId
	 * @param null $sectionId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getMappedSections($exportId, $sectionId = NULL)
	{
		$result = array();
		$catEntId = self::getSectionsEntityId($exportId);
		
		$filter = array('=ENTITY_ID' => $catEntId);
		if ($sectionId)
			$filter['=VALUE_INTERNAL'] = $sectionId;
		
//		todo: we can cached map. Clear cache if set setting in section
		$catRes = MapTable::getList(array(
			'filter' => $filter,
		));
		
		while ($product = $catRes->fetch())
			$result[$product["VALUE_INTERNAL"]] = array(
				"BX_ID" => $product["VALUE_INTERNAL"],
				"VK_ID" => $product["VALUE_EXTERNAL"],
				"PARAMS" => $product["PARAMS"],
			);
		
		return $result;
	}
	
	
	/**
	 * Matched items between site and VK.
	 * Set flag edit, if item was be exported and export is agressive.
	 * Remove items from mapping, if item was deleted.
	 *
	 * @param $data
	 * @param $dataFromVk
	 * @param $exportId
	 * @param $type
	 * @param $isAgressive
	 * @return array
	 * @throws SystemException
	 */
	public static function checkMappingMatches(array $data, array $dataFromVk, $exportId, $type, $isAgressive)
	{
//		todo: diff methods for albums and product, wrap over this method
		switch ($type)
		{
			case 'ALBUMS':
				$bxKey = "SECTION_ID";
				$vkKey = "ALBUM_VK_ID";
				$deleteMapMethod = "removeAlbumMapping";
				$dataFromMapping = self::getMappedAlbums($exportId);
				break;
			
			case 'PRODUCTS':
				$bxKey = "BX_ID";
				$vkKey = "VK_ID";
				$deleteMapMethod = "removeProductMapping";
				$dataFromMapping = self::getMappedProducts($exportId);
				break;
			
			default:
				throw new SystemException("Wrong VK-mapping type");
		}

//		FIND items, which exist in VK and map, but not exist on site (was be deleted, changed settings etc)
//		todo: now we can check only current data-chunk (about 25 items) and can't find element,
// 		todo: which exist in VK and MAP, but not added in VK. Must do this function for preserve duplication 
//		todo: old code see in the repo


//		MATCH items between VK and mapping
		$dataMappedToRemove = array();
		foreach ($data as &$item)
		{
			$itemMapped = $dataFromMapping[$item[$bxKey]];
			if (isset($itemMapped))
			{
				if (isset($dataFromVk[$itemMapped[$vkKey]]))
				{
					if ($isAgressive)
					{
//						editing albums/products which exists in VK
						$item[$vkKey] = $dataFromMapping[$item[$bxKey]][$vkKey];
						$item["FLAG_EDIT"] = true;
					}
					else
					{
//						if not agressive export we not editing products/albums
						unset($data[$item[$bxKey]]);
					}
				}
				
				else
				{
//				delete from map albums which not exist in vk
					$item["FLAG_EDIT"] = false;
					$dataMappedToRemove[] = array("VALUE_EXTERNAL" => $itemMapped[$vkKey]);
				}
			}
//			other albums not delete from map and not edit - only adding
			else
				$item["FLAG_EDIT"] = false;
		}

//		DELETE from mapping items which not exist in VK
		if (!empty($dataMappedToRemove))
			self::$deleteMapMethod($dataMappedToRemove, $exportId);
		
		return $data;
	}
	
	
	/**
	 * Remove mapping of ALL types and ALL exports IDs.
	 * Running where platform is deleted.
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public static function deleteAllMapping()
	{
//		GET all entity IDs
		$resEntityIds = MapEntityTable::getList(array(
			"filter" => array('%=CODE' => self::getGeneralCodePrefix() . '%'),
		));
		$entityIds = array();
		while ($entityId = $resEntityIds->fetch())
			$entityIds[] = $entityId['ID'];

//		DELETE all MAP ENTITY
		foreach ($entityIds as $entityId)
			MapEntityTable::delete($entityId);


//		GET all map items IDs
		$resMapIds = MapTable::getList(array(
			"filter" => array('=ENTITY_ID' => $entityIds),
		));
		$mapIds = array();
		while ($mapId = $resMapIds->fetch())
			$mapIds[] = $mapId['ID'];

//		DELETE all from MAP
		foreach ($mapIds as $mapId)
			MapTable::delete($mapId);
	}
}