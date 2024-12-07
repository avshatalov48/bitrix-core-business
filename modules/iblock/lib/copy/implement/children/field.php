<?php
namespace Bitrix\Iblock\Copy\Implement\Children;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Field implements Child
{
	const FIELD_COPY_ERROR = "FIELD_COPY_ERROR";

	private $enumRatio = [];
	private $enumTmpMap = [];

	private array $fieldRatio = [];

	/**
	 * @var Result
	 */
	protected $result;

	public function __construct()
	{
		$this->result = new Result();
	}

	/**
	 * Returns lists values map from old iblock to new iblock
	 *
	 * @return array
	 */
	public function getEnumRatio()
	{
		return $this->enumRatio;
	}

	/**
	 * Copy iblock fields and properties.
	 *
	 * @param int $iblockId Source iblock id.
	 * @param int $copiedIblockId Destination iblock id.
	 * @return Result
	 */
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
		$this->fieldRatio[$iblockId] ??= [];

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
			$iblockId = $propertyField["IBLOCK_ID"];
			$propertyField["IBLOCK_ID"] = $copiedIblockId;

			$property = new \CIBlockProperty;
			$propertyId = $property->add($propertyField);
			if ($propertyId)
			{
				if (
					$propertyField['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST
					&& !empty($propertyField['LIST'])
					&& is_array($propertyField['LIST'])
				)
				{
					$this->addPropertyList($propertyId, $propertyField["LIST"]);
				}
				$this->fieldRatio[$iblockId][$propertyField['ID']] = $propertyId;
			}

			$error = $property->getLastError();
			if ($error !== '')
			{
				$this->result->addError(new Error($error, self::FIELD_COPY_ERROR));
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
			$this->enumRatio[$iblockId] ??= [];
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

	/**
	 * Returns properties map from old iblock to new iblock.
	 *
	 * @return array
	 */
	public function getFieldRatio(): array
	{
		return $this->fieldRatio;
	}

	/**
	 * Add property relation from old iblock to new iblock.
	 *
	 * @param int $iblockId Old iblock id.
	 * @param int $fieldId Old property id.
	 * @param int $newFieldId New property id.
	 * @return void
	 */
	public function setFieldRatio(int $iblockId, int $fieldId, int $newFieldId): void
	{
		$this->fieldRatio[$iblockId] ??= [];
		$this->fieldRatio[$iblockId][$fieldId] = $newFieldId;
	}
}
