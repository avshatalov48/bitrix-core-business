<?

namespace Bitrix\Main\UI\Filter;

use Bitrix\Main\Localization\Loc;


class FieldAdapter
{
	public static function adapt($sourceField)
	{
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
							$selectItem["VALUE"] = $selectItemValue;
							$listItem = $selectItem;
						}
						else
						{
							$listItem = array("NAME" => $selectItem, "VALUE" => $selectItemValue);
						}

						$items[] = $listItem;
					}
				}

				if ($sourceField["params"]["multiple"] === "Y")
				{
					$field = Field::multiSelect(
						$sourceField["id"],
						$items, array(),
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
					DateType::NONE, array(),
					$sourceField["name"],
					$sourceField["placeholder"],
					$sourceField["time"],
					$sourceField["exclude"],
					$sourceField["include"],
					$sourceField["allow_years_switcher"],
					$sourceField["messages"]
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
					$sourceField["style"]
				);
				break;

			case "custom_entity" :
				$multiple = $sourceField["params"]["multiple"] === "Y" || $sourceField["params"]["multiple"] === true;
				$field = Field::customEntity(
					$sourceField["id"],
					$sourceField["name"],
					$sourceField["placeholder"],
					$multiple
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
					(!empty($sourceField["params"]) && !empty($sourceField["params"]["multiple"]) && $sourceField["params"]["multiple"] == "Y"),
					(!empty($sourceField["params"]) && is_array($sourceField["params"]) ? $sourceField["params"] : array()),
					(isset($sourceField["lightweight"]) ? $sourceField["lightweight"] : false)
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

		return $field;
	}
}