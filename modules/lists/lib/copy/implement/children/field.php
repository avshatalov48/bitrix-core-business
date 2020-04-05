<?php
namespace Bitrix\Lists\Copy\Implement\Children;

use Bitrix\Iblock\Copy\Implement\Children\Field as BaseField;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

Loc::loadMessages(__FILE__);

class Field extends BaseField
{
	/**
	 * Copies lists fields.
	 * @param int $iblockId Iblock id.
	 * @param int $copiedIblockId Copied iblock id.
	 * @return Result
	 */
	public function copy($iblockId, $copiedIblockId): Result
	{
		$fields = $this->getFieldsToCopy($iblockId, $copiedIblockId);
		$this->addListsFields($iblockId, $copiedIblockId, $fields);

		return $this->result;
	}

	private function getFieldsToCopy($iblockId, $copiedIblockId)
	{
		$fields = [];

		$object = new \CList($iblockId);

		foreach($object->getFields() as $fieldId => $field)
		{
			$copiedField = [
				"ID" => $fieldId,
				"NAME" => $field["NAME"],
				"SORT" => $field["SORT"],
				"MULTIPLE" => $field["MULTIPLE"],
				"IS_REQUIRED" => $field["IS_REQUIRED"],
				"IBLOCK_ID" => $copiedIblockId,
				"SETTINGS" => $field["SETTINGS"],
				"DEFAULT_VALUE" => $field["DEFAULT_VALUE"],
				"TYPE" => $field["TYPE"],
				"PROPERTY_TYPE" => $field["PROPERTY_TYPE"],
			];

			if (!$object->is_field($fieldId))
			{
				if ($field["TYPE"] == "L")
				{
					$copiedField["VALUES"] = $this->getEnumValues($fieldId);
				}

				$copiedField["CODE"] = $field["CODE"];
				$copiedField["LINK_IBLOCK_ID"] = $field["LINK_IBLOCK_ID"];
				if (!empty($field["PROPERTY_USER_TYPE"]["USER_TYPE"]))
					$copiedField["USER_TYPE"] = $field["PROPERTY_USER_TYPE"]["USER_TYPE"];
				if (!empty($field["ROW_COUNT"]))
					$copiedField["ROW_COUNT"] = $field["ROW_COUNT"];
				if (!empty($field["COL_COUNT"]))
					$copiedField["COL_COUNT"] = $field["COL_COUNT"];
				if (!empty($field["USER_TYPE_SETTINGS"]))
					$copiedField["USER_TYPE_SETTINGS"] = $field["USER_TYPE_SETTINGS"];
			}
			$fields[] = $copiedField;
		}

		return $fields;
	}

	private function addListsFields($iblockId, $copiedIblockId, array $fields)
	{
		$object = new \CList($copiedIblockId);

		foreach ($fields as $field)
		{
			if ($field["ID"] == "NAME")
			{
				$result = $object->updateField("NAME", $field);
			}
			else
			{
				$result = $object->addField($field);
			}

			if ($result)
			{
				$object->save();

				$this->setEnumRatio($iblockId, $field["ID"], $result);
			}
			else
			{
				$this->result->addError(
					new Error(
						Loc::getMessage("COPY_FIELD_ERROR", ["#NAME#" => $field["NAME"]]),
						self::FIELD_COPY_ERROR
					)
				);
			}
		}
	}
}