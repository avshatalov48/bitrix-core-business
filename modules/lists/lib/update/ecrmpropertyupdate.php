<?
namespace Bitrix\Lists\Update;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Update\Stepper;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;

class EcrmPropertyUpdate extends Stepper
{
	protected static $moduleId = "lists";
	protected $deleteFile = true;

	public function execute(array &$result)
	{
		if(!Loader::includeModule("lists"))
			return false;

		$className = get_class($this);
		$option = Option::get("lists", $className, 0);
		$result["steps"] = $option;

		$limit = 20;
		$result["steps"] = isset($result["steps"]) ? $result["steps"] : 0;

		$queryObject = PropertyTable::getList(array(
			"select" => array("ID", "IBLOCK_ID", "USER_TYPE_SETTINGS"),
			"filter" => array("=USER_TYPE" => "ECrm")
		));
		$listIblockId = array();
		$listPropertyId = array();
		while($property = $queryObject->fetch())
		{
			if(is_string($property["USER_TYPE_SETTINGS"]) && CheckSerializedData($property["USER_TYPE_SETTINGS"]))
			{
				$property["USER_TYPE_SETTINGS"] = unserialize($property["USER_TYPE_SETTINGS"]);
			}
			if(is_array($property["USER_TYPE_SETTINGS"]))
			{
				if(array_key_exists("VISIBLE", $property["USER_TYPE_SETTINGS"]))
					unset($property["USER_TYPE_SETTINGS"]["VISIBLE"]);
				$tmpArray = array_filter($property["USER_TYPE_SETTINGS"], function($mark) { return $mark == "Y"; });
				if(count($tmpArray) == 1)
				{
					$listIblockId[] = intval($property["IBLOCK_ID"]);
					$listPropertyId[$property["IBLOCK_ID"]][] = intval($property["ID"]);
				}
			}
		}

		$connection = Application::getInstance()->getConnection();
		$listIblockIdS = implode(",", $listIblockId);
		if(empty($listIblockIdS))
		{
			return false;
		}

		$sqlString = "SELECT ID, IBLOCK_ID FROM b_iblock_element WHERE IBLOCK_ID IN (".$listIblockIdS
			.") ORDER BY ID ASC LIMIT ".$limit." OFFSET ".$result["steps"];
		$queryObject = $connection->query($sqlString);
		$listElement = $queryObject->fetchAll();
		$selectedRowsCount = $queryObject->getSelectedRowsCount();
		$listElementData = array();
		foreach($listElement as $element)
		{
			$listElementData[$element["IBLOCK_ID"]][] = $element["ID"];
		}

		foreach($listElementData as $iblockId => $listElementId)
		{
			$queryObject = \CIblockElement::getPropertyValues(
				$iblockId, array("ID" => $listElementId), false, array("ID" => $listPropertyId[$iblockId]));
			while($propertyValues = $queryObject->fetch())
			{

				foreach($propertyValues as $propertyId => $propertyValue)
				{
					if($propertyId == "IBLOCK_ELEMENT_ID" || empty($propertyValue))
						continue;

					$isDamaged = false;
					if(is_array($propertyValue))
					{
						$listPropertyValues = array();
						foreach ($propertyValue as $value)
						{
							if(!intval($value))
							{
								$explode = explode('_', $value);
								$listPropertyValues[] = intval($explode[1]);
								$isDamaged = true;
							}
						}
						$propertyValue = $listPropertyValues;
					}
					else
					{
						if(!intval($propertyValue))
						{
							$explode = explode('_', $propertyValue);
							$propertyValue = intval($explode[1]);
							$isDamaged = true;
						}
					}
					if($isDamaged && $propertyId)
					{
						\CIBlockElement::setPropertyValues(
							$propertyValues["IBLOCK_ELEMENT_ID"], $iblockId, $propertyValue, $propertyId);
					}
				}
			}
		}

		if($selectedRowsCount < $limit)
		{
			Option::delete("lists", array("name" => $className));
			return false;
		}
		else
		{
			$result["steps"] = $result["steps"] + $selectedRowsCount;
			$option = $result["steps"];
			Option::set("lists", $className, $option);
			return true;
		}
	}
}