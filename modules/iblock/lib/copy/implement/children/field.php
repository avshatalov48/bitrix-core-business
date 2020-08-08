<?php
namespace Bitrix\Iblock\Copy\Implement\Children;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Field implements Child
{
	const FIELD_COPY_ERROR = "FIELD_COPY_ERROR";

	private $enumRatio = [];
	private $enumTmpMap = [];

	/**
	 * @var Result
	 */
	protected $result;

	public function __construct()
	{
		$this->result = new Result();
	}

	public function getEnumRatio()
	{
		return $this->enumRatio;
	}

	public function copy($iblockId, $copiedIblockId): Result
	{
		$fields = $this->getFieldsToCopy($iblockId);
		$this->addFields($copiedIblockId, $fields);

		$properties = $this->getProperty($iblockId);
		$this->addProperties($copiedIblockId, $properties);

		return $this->result;
	}

	private function getFieldsToCopy($iblockId)
	{
		return \CIBlock::getFields($iblockId);
	}

	private function addFields($copiedIblockId, $fields)
	{
		\CIBlock::setFields($copiedIblockId, $fields);
		/** @global \CStackCacheManager $stackCacheManager */
		global $stackCacheManager;
		if (is_object($stackCacheManager))
		{
			$stackCacheManager->clear("b_iblock");
		}
	}

	private function getProperty($iblockId)
	{
		$fields = [];

		$queryObject = \CIBlock::getProperties($iblockId);
		while ($property = $queryObject->fetch())
		{
			$fields[] = $property;
		}

		return $fields;
	}

	private function addProperties($copiedIblockId, array $properties)
	{
		foreach ($properties as $propertyField)
		{
			$propertyField["IBLOCK_ID"] = $copiedIblockId;

			$property = new \CIBlockProperty;
			$propertyId = $property->add($propertyField);
			if ($propertyId)
			{
				if ($propertyField["PROPERTY_TYPE"] == "L" && is_array($propertyField["LIST"]))
				{
					$this->addPropertyList($propertyId, $propertyField["LIST"]);
				}
			}

			if (!empty($property->LAST_ERROR))
			{
				$this->result->addError(new Error($property->LAST_ERROR, self::FIELD_COPY_ERROR));
			}
		}
	}

	private function addPropertyList($propertyId, $list)
	{
		foreach ($list as $id => $enum)
		{
			if (is_array($enum))
			{
				$value = trim($enum["VALUE"], " \t\n\r");
				if($value <> '')
				{
					$enum["PROPERTY_ID"] = $propertyId;
					\CIBlockPropertyEnum::add($enum);
				}
			}
		}
	}

	protected function getEnumValues($fieldId)
	{
		$values = [];

		$this->enumTmpMap[$fieldId] = [];

		$propertyId = mb_substr($fieldId, mb_strlen("PROPERTY_"));
		$enum = \CIBlockPropertyEnum::getList([], ["PROPERTY_ID" => $propertyId]);
		while ($listData = $enum->fetch())
		{
			$values[] = [
				"VALUE" => $listData["VALUE"],
				"DEF" => $listData["DEF"],
				"SORT" => $listData["SORT"]
			];

			$this->enumTmpMap[$fieldId][$listData["VALUE"]] = $listData["ID"];
		}

		return $values;
	}

	protected function setEnumRatio($iblockId, $fieldId, $copiedFieldId)
	{
		if (array_key_exists($fieldId, $this->enumTmpMap))
		{
			$enumTmpMap = $this->enumTmpMap[$fieldId];
			if (!is_array($this->enumRatio[$iblockId]))
			{
				$this->enumRatio[$iblockId] = [];
			}
			$propertyId = mb_substr($copiedFieldId, mb_strlen("PROPERTY_"));
			$enum = \CIBlockPropertyEnum::getList([], ["PROPERTY_ID" => $propertyId]);
			while ($listData = $enum->fetch())
			{
				if (array_key_exists($listData["VALUE"], $enumTmpMap))
				{
					$this->enumRatio[$iblockId][$enumTmpMap[$listData["VALUE"]]] = $listData["ID"];
				}
			}
		}
	}
}