<?php
namespace Bitrix\Iblock\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Element extends CopyImplementer
{
	const ELEMENT_COPY_ERROR = "ELEMENT_COPY_ERROR";

	/**
	 * Adds entity.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool return entity id or false.
	 */
	public function add(Container $container, array $fields)
	{
		$elementObject = new \CIBlockElement;
		$elementId = $elementObject->add($fields, false, true, true);
		if ($elementId)
		{
			return $elementId;
		}
		else
		{
			if ($elementObject->LAST_ERROR)
			{
				$this->result->addError(new Error($elementObject->LAST_ERROR, self::ELEMENT_COPY_ERROR));
			}
			else
			{
				$this->result->addError(new Error("Unknown error", self::ELEMENT_COPY_ERROR));
			}
			return false;
		}
	}

	/**
	 * Returns element fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$fields = [];

		$filter = [
			"ID" => $entityId,
			"CHECK_PERMISSIONS" => "N"
		];
		$queryObject = \CIBlockElement::getList([], $filter, false, false);
		if ($element = $queryObject->fetch())
		{
			$fields = $element;
			$propertyValuesObject = \CIblockElement::getPropertyValues(
				$element["IBLOCK_ID"], ["ID" => $entityId]);
			while ($propertyValues = $propertyValuesObject->fetch())
			{
				foreach ($propertyValues as $propertyId => $propertyValue)
				{
					if ($propertyId == "IBLOCK_ELEMENT_ID")
						continue;
					$fields["PROPERTY_".$propertyId] = $propertyValue;
				}
			}
		}

		return $fields;
	}

	/**
	 * Preparing data before creating a new entity.
	 *
	 * @param Container $container
	 * @param array $inputFields List element fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $inputFields)
	{
		$fields = [
			"PROPERTY_VALUES" => []
		];

		foreach ($inputFields as $fieldId => $fieldValue)
		{
			if (mb_substr($fieldId, 0, 9) == "PROPERTY_")
			{
				$propertyId = mb_substr($fieldId, mb_strlen("PROPERTY_"));
				$fields["PROPERTY_VALUES"][$propertyId] = $this->getPropertyFieldValue(
					$container, $fieldId, $fieldValue);
			}
			else
			{
				$fields[$fieldId] = $this->getFieldValue($fieldId, $fieldValue);
			}
		}

		unset($fields["DATE_CREATE"]);
		unset($fields["TIMESTAMP_X"]);
		unset($fields["XML_ID"]);

		$dictionary = $container->getDictionary();

		if (array_key_exists($fields["IBLOCK_SECTION_ID"], $dictionary["sectionsRatio"]))
		{
			$fields["IBLOCK_SECTION_ID"] = $dictionary["sectionsRatio"][$fields["IBLOCK_SECTION_ID"]];
		}

		$fields["RIGHTS"] = $this->getRights($fields["IBLOCK_ID"], $fields["ID"]);

		if (!empty($dictionary["targetIblockId"]))
		{
			$fields["IBLOCK_ID"] = $dictionary["targetIblockId"];
			$fields = $this->convertPropertyId($fields, $dictionary["targetIblockId"]);
		}

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $elementId Entity id.
	 * @param int $copiedElementId Copied entity id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $elementId, $copiedElementId)
	{
		return $this->getResult();
	}

	private function getFieldValue($fieldId, $fieldValue)
	{
		switch ($fieldId)
		{
			case "PREVIEW_PICTURE":
			case "DETAIL_PICTURE":
				return $this->getPictureValue($fieldValue);
				break;
			default:
				return $this->getBaseValue($fieldValue);
		}
	}

	private function getPropertyFieldValue(Container $container, $fieldId, $fieldValue)
	{
		$propertyId = mb_substr($fieldId, mb_strlen("PROPERTY_"));
		$fieldValue = (is_array($fieldValue) ? $fieldValue : [$fieldValue]);

		$queryObject = \CIBlockProperty::getList([], ["ID" => $propertyId]);
		if ($property = $queryObject->fetch())
		{
			if (!empty($property["USER_TYPE"]))
			{
				$userType = \CIBlockProperty::getUserType($property["USER_TYPE"]);
				if ($userType["ConvertFromDB"] && is_callable($userType["ConvertFromDB"]))
				{
					$fieldValue = $this->getValueFromPropertyClass($fieldValue, $userType["ConvertFromDB"]);
				}
				else
				{
					$fieldValue = $this->getPropertyValue($fieldValue);
				}
			}
			else
			{
				switch ($property["PROPERTY_TYPE"])
				{
					case "F":
						$fieldValue = $this->getFileValue($fieldValue);
						break;
					case "N":
						$fieldValue = $this->getIntegerValue($fieldValue);
						break;
					case "L":
						$fieldValue = $this->getListValue($container, $fieldValue);
						break;
					default:
						$fieldValue = $this->getPropertyValue($fieldValue);
				}
			}
		}

		return $fieldValue;
	}

	private function getPictureValue($fieldValue)
	{
		return \CFile::makeFileArray($fieldValue);
	}

	private function getBaseValue($fieldValue)
	{
		return (is_array($fieldValue) ? current($fieldValue) : $fieldValue);
	}

	private function getFileValue(array $fieldValue)
	{
		array_walk($fieldValue, function(&$value) {
			$value = ["VALUE" => \CFile::makeFileArray($value)];
		});
		return $fieldValue;
	}

	private function getListValue(Container $container, array $inputValue)
	{
		$values = [];

		$dictionary = $container->getDictionary();
		$enumRatio = $dictionary["enumRatio"];

		if ($enumRatio)
		{
			foreach ($inputValue as $value)
			{
				if ($value && array_key_exists($value, $enumRatio))
				{
					$values[] = $enumRatio[$value];
				}
			}
		}

		return $values;
	}

	private function getPropertyValue(array $inputValue)
	{
		$values = [];
		foreach ($inputValue as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $k => $v)
					$values[$k]["VALUE"] = $v;
			}
			else
			{
				$values[$key]["VALUE"] = $value;
			}
		}
		return $values;
	}

	private function getIntegerValue(array $fieldValue)
	{
		array_walk($fieldValue, function(&$value) {
			$value = [
				"VALUE" => ($value === false ? "" : floatval(str_replace(" ", "", str_replace(",", ".", $value))))
			];
		});
		return $fieldValue;
	}

	private function convertPropertyId(array $fields, $targetIblockId)
	{
		$targetProperties = [];
		$queryObject = \CIBlockProperty::getList([], ["IBLOCK_ID" => $targetIblockId]);
		while ($property = $queryObject->fetch())
		{
			$targetProperties[$property["ID"]] = $property["CODE"];
		}

		foreach ($fields["PROPERTY_VALUES"] as $propertyId => $propertyValue)
		{
			$queryObject = \CIBlockProperty::getList([], ["ID" => $propertyId]);
			if ($property = $queryObject->fetch())
			{
				foreach ($targetProperties as $targetPropertyId => $targetPropertyCode)
				{
					if ($targetPropertyCode == $property["CODE"])
					{
						$fields["PROPERTY_VALUES"][$targetPropertyId] = $propertyValue;
					}
				}
				unset($fields["PROPERTY_VALUES"][$propertyId]);
			}
		}

		return $fields;
	}

	private function getValueFromPropertyClass(array $fieldValue, $callback)
	{
		$listValues = [];
		foreach ($fieldValue as $value)
		{
			$listValues[] = call_user_func_array($callback, [[], ["VALUE" => $value]]);
		}
		$fieldValue = $listValues;

		return $fieldValue;
	}

	private function getRights(int $iblockId, int $elementId)
	{
		$rights = [];

		$objectRights = new \CIBlockElementRights($iblockId, $elementId);

		$groupCodeIgnoreList = $this->getGroupCodeIgnoreList($iblockId);

		foreach ($objectRights->getRights() as $right)
		{
			if (!in_array($right["GROUP_CODE"], $groupCodeIgnoreList))
			{
				$rights["n".(count($rights))] = [
					"GROUP_CODE" => $right["GROUP_CODE"],
					"DO_CLEAN" => "N",
					"TASK_ID" => $right["TASK_ID"],
				];
			}
		}

		return $rights;
	}

	private function getGroupCodeIgnoreList(int $iblockId): array
	{
		$groupCodeIgnoreList = [];

		$rightObject = new \CIBlockRights($iblockId);
		foreach ($rightObject->getRights() as $right)
		{
			$groupCodeIgnoreList[] = $right["GROUP_CODE"];
		}

		return $groupCodeIgnoreList;
	}
}