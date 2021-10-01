<?

namespace Bitrix\Main\UI\Filter;

use Bitrix\Main\Localization\Loc;


class FieldAdapter
{
	/**
	 * @param array $sourceField
	 * @return array
	 */
	public static function adapt(array $sourceField, $filterId = '')
	{
		$sourceField = static::normalize($sourceField);
		switch ($sourceField["type"])
		{
			case "list" :
				$items = array();

				if (isset($sourceField["items"]) && !empty($sourceField["items"]) && is_array($sourceField["items"]))
				{
					foreach ($sourceField["items"] as $selectItemValue => $selectItem)
					{
						if (is_array($selectItem))
						{
							$selectItem["VALUE"] = (string)$selectItemValue;
							$listItem = $selectItem;
						}
						else
						{
							$listItem = ["NAME" => $selectItem, "VALUE" => (string)$selectItemValue];
						}

						$items[] = $listItem;
					}
				}

				if ($sourceField["params"]["multiple"])
				{
					$field = Field::multiSelect(
						$sourceField["id"],
						$items,
						array(),
						$sourceField["name"],
						$sourceField["placeholder"]
					);
				}
				else
				{
					if (empty($items[0]["VALUE"]) && empty($items[0]["NAME"]))
					{
						$items[0]["NAME"] = Loc::getMessage("MAIN_UI_FILTER__NOT_SET");
					}

					if (!empty($items[0]["VALUE"]) && !empty($items[0]["NAME"]))
					{
						array_unshift($items, array("NAME" => Loc::getMessage("MAIN_UI_FILTER__NOT_SET"), "VALUE" => ""));
					}

					$field = Field::select(
						$sourceField["id"],
						$items,
						array(),
						$sourceField["name"],
						$sourceField["placeholder"]
					);
				}

				break;

			case "date" :
				$field = Field::date(
					$sourceField["id"],
					DateType::NONE,
					array(),
					$sourceField["name"],
					$sourceField["placeholder"],
					(isset($sourceField["time"]) ? $sourceField["time"] : false),
					(isset($sourceField["exclude"]) ? $sourceField["exclude"] : array()),
					(isset($sourceField["include"]) ? $sourceField["include"] : array()),
					(isset($sourceField["allow_years_switcher"]) ? $sourceField["allow_years_switcher"] : false),
					(isset($sourceField["messages"]) ? $sourceField["messages"] : array())
				);
				break;

			case "number" :
				$field = Field::number(
					$sourceField["id"],
					NumberType::SINGLE,
					array(),
					$sourceField["name"],
					$sourceField["placeholder"]
				);

				$subTypes = array();
				$subType = is_array($field["SUB_TYPE"]) ? $field["SUB_TYPE"]["VALUE"] : $field["SUB_TYPE"];
				$dateTypesList = NumberType::getList();

				foreach ($dateTypesList as $key => $type)
				{
					$subTypes[] = array(
						"NAME" => Loc::getMessage("MAIN_UI_FILTER__NUMBER_".$key),
						"PLACEHOLDER" => "",
						"VALUE" => $type
					);

					if ($type === $subType)
					{
						$field["SUB_TYPE"] = array(
							"NAME" => Loc::getMessage("MAIN_UI_FILTER__NUMBER_".$key),
							"PLACEHOLDER" => "",
							"VALUE" => $subType
						);
					}
				}

				$field["SUB_TYPES"] = $subTypes;

				break;

			case "custom" :
				$field = Field::custom(
					$sourceField["id"],
					$sourceField["value"],
					$sourceField["name"],
					$sourceField["placeholder"],
					(isset($sourceField["style"]) ? $sourceField["style"] : false)
				);
				break;

			case "custom_entity" :
				$field = Field::customEntity(
					$sourceField["id"],
					$sourceField["name"],
					$sourceField["placeholder"],
					$sourceField["params"]["multiple"]
				);
				break;

			case "checkbox" :
				$values = isset($sourceField["valueType"]) && $sourceField["valueType"] === "numeric"
					? array("1", "0")
					: array("Y", "N");

				$items = array(
					array("NAME" => Loc::getMessage("MAIN_UI_FILTER__NOT_SET"), "VALUE" => ""),
					array("NAME" => Loc::getMessage("MAIN_UI_FILTER__YES"), "VALUE" => $values[0]),
					array("NAME" => Loc::getMessage("MAIN_UI_FILTER__NO"), "VALUE" => $values[1])
				);

				$field = Field::select(
					$sourceField["id"],
					$items,
					$items[0],
					$sourceField["name"],
					$sourceField["placeholder"]
				);

				break;

			case "custom_date" :
				$field = Field::customDate($sourceField);
				break;

			case "dest_selector" :
				$field = Field::destSelector(
					$sourceField["id"],
					$sourceField["name"],
					$sourceField["placeholder"],
					$sourceField["params"]["multiple"],
					$sourceField["params"],
					(isset($sourceField["lightweight"]) ? $sourceField["lightweight"] : false),
					$filterId
				);
				break;

			case 'entity_selector' :
				$field = Field::entitySelector(
					isset($sourceField['id']) ? (string)$sourceField['id'] : '',
					isset($sourceField['name']) ? (string)$sourceField['name'] : '',
					isset($sourceField['placeholder']) ? (string)$sourceField['placeholder'] : '',
					(isset($sourceField['params']) && is_array($sourceField['params'])) ? $sourceField['params'] : [],
					(string)$filterId
				);
				break;

			case "textarea" :
				$field = Field::textarea(
					$sourceField["id"],
					"",
					$sourceField["name"],
					$sourceField["placeholder"]
				);
				break;

			default :
				$field = Field::string(
					$sourceField["id"],
					"",
					$sourceField["name"],
					$sourceField["placeholder"]
				);
				break;
		}

		if (!empty($sourceField["html"]))
		{
			$field["HTML"] = $sourceField["html"];
		}
		if (!empty($sourceField["additionalFilter"]))
		{
			$field["ADDITIONAL_FILTER_ALLOWED"] = $sourceField["additionalFilter"];
		}

		if (isset($sourceField["sectionId"]) && $sourceField["sectionId"] !== '')
		{
			$field["SECTION_ID"] = $sourceField["sectionId"];
		}
		if (!empty($sourceField["icon"]))
		{
			$field["ICON"] = $sourceField["icon"];
		}

		return $field;
	}

	/**
	 * @param array $sourceField
	 * @return array
	 */
	public static function normalize(array $sourceField)
	{
		if (!isset($sourceField["type"]))
		{
			$sourceField["type"] = "string";
		}
		if (!isset($sourceField["placeholder"]))
		{
			$sourceField["placeholder"] = "";
		}
		if (!isset($sourceField["params"]) || !is_array($sourceField["params"]))
		{
			$sourceField["params"] = array();
		}
		if (!isset($sourceField["params"]["multiple"]))
		{
			$sourceField["params"]["multiple"] = false;
		}
		else
		{
			$sourceField["params"]["multiple"] = (
				$sourceField["params"]["multiple"] === 'Y'
				|| $sourceField["params"]["multiple"] === true
			);
		}

		return $sourceField;
	}
}