<?
namespace Bitrix\Main\Update;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\UI\Filter\DateType;

/**
 * Class FilterOption
 * The class is designed to convert the settings of the old administrative filter into a new one.
 *
 * An example of how this miracle works can be seen here: sale,18.5.7; iblock,18.5.5; catalog,18.5.6; main,17.0.11;
 *
 * @package Bitrix\Main\Dev\Converter
 */
class AdminFilterOption extends Stepper
{
	protected static $moduleId = "main";

	protected $limit = 100;

	/**
	 * The method records the necessary data for conversion into an option.
	 *
	 * @param string $filterId Filter id.
	 * @param string $tableId Grid id.
	 * @param array $ratioFields Fields of the old and new filter.
	 *    array(
	 *      "find_name" => "NAME",
	 *      "find_lang" => "LID",
	 *      ...
	 *    )
	 *
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function setFilterToConvert($filterId, $tableId, array $ratioFields)
	{
		$listFilter = Option::get(self::$moduleId, "listFilterToConvert", "");
		if ($listFilter !== "" )
			$listFilter = unserialize($listFilter);
		$listFilter = is_array($listFilter) ? $listFilter : array();
		if (!array_key_exists($filterId, $listFilter))
		{
			$listFilter[$filterId] = array(
				"offset" => 0,
				"tableId"=> $tableId,
				"ratioFields" => $ratioFields
			);
			Option::set(self::$moduleId, "listFilterToConvert", serialize($listFilter));
		}
	}

	public function execute(array &$option)
	{
		$listFilter = Option::get(self::$moduleId, "listFilterToConvert", "");
		if ($listFilter !== "" )
			$listFilter = unserialize($listFilter);
		$listFilter = is_array($listFilter) ? $listFilter : array();
		if (empty($listFilter))
		{
			Option::delete(self::$moduleId, array("name" => "listFilterToConvert"));
			$GLOBALS["CACHE_MANAGER"]->cleanDir("user_option");
			return false;
		}

		$connection = Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		foreach ($listFilter as $filterId => $filter)
		{
			$queryObject = $connection->query("SELECT * FROM `b_filters` WHERE `FILTER_ID` = '".$sqlHelper->forSql(
				$filterId)."' ORDER BY ID ASC LIMIT ".$this->limit." OFFSET ".$filter["offset"]);
			$selectedRowsCount = $queryObject->getSelectedRowsCount();
			while ($oldFilter = $queryObject->fetch())
			{
				$filters = array();
				$listNewPresetName = [];
				$oldFields = unserialize($oldFilter["FIELDS"]);
				if (is_array($oldFields))
				{
					list($newFields, $newRows) = $this->convertOldFieldsToNewFields(
						$oldFields, $filter["ratioFields"]);
					$presetId = "filter_".(time()+(int)$oldFilter["ID"]);
					$filters[$presetId] = array(
						"name" => $oldFilter["NAME"],
						"fields" => $newFields,
						"filter_rows" => implode(",", $newRows)
					);
					$listNewPresetName[$presetId] = $oldFilter["NAME"];
				}

				if (empty($filters))
				{
					continue;
				}

				$queryOptionCurrentFilter = $connection->query(
					"SELECT * FROM `b_user_option` WHERE 
					`CATEGORY` = 'main.ui.filter' AND 
					`USER_ID` = '".$sqlHelper->forSql($oldFilter["USER_ID"])."' AND 
					`NAME` = '".$sqlHelper->forSql($filter["tableId"])."'"
				);
				if ($optionCurrentFilter = $queryOptionCurrentFilter->fetch())
				{
					$optionCurrentFilterValue = unserialize($optionCurrentFilter["VALUE"]);
					if (is_array($optionCurrentFilterValue))
					{
						if (!empty($optionCurrentFilterValue["filters"]))
						{
							// This is a check whether presets exist with that name.
							foreach ($optionCurrentFilterValue["filters"] as $currentFilter)
							{
								$name = (!empty($currentFilter["name"]) ? $currentFilter["name"] : "");
								$listNewPresetName = array_filter($listNewPresetName, function($oldName) use ($name) {
									return ($oldName !== $name);
								});
							}

							$filters = array_intersect_key($filters, $listNewPresetName);

							$optionCurrentFilterValue["filters"] = array_merge(
								$optionCurrentFilterValue["filters"], $filters);
							$optionCurrentFilterValue["update_default_presets"] = true;

							$connection->query(
								"UPDATE `b_user_option` SET 
								`VALUE` = '" . $sqlHelper->forSql(serialize($optionCurrentFilterValue)) . "' WHERE 
								`ID` = '" . $sqlHelper->forSql($optionCurrentFilter["ID"]) . "'"
							);
						}
					}
				}
				else
				{
					$optionNewFilter = array();
					$optionNewFilter["filters"] = $filters;
					$optionNewFilter["update_default_presets"] = true;

					$connection->query(
						"INSERT INTO `b_user_option` 
						(`ID`, `USER_ID`, `CATEGORY`, `NAME`, `VALUE`, `COMMON`) VALUES 
						(NULL, '".$sqlHelper->forSql($oldFilter["USER_ID"])."', 'main.ui.filter', '".
						$sqlHelper->forSql($filter["tableId"])."', '".$sqlHelper->forSql(serialize($optionNewFilter)).
						"', '".$sqlHelper->forSql($oldFilter["COMMON"])."')"
					);
				}
			}

			if ($selectedRowsCount < $this->limit)
			{
				unset($listFilter[$filterId]);
			}
			else
			{
				$listFilter[$filterId]["offset"] = $listFilter[$filterId]["offset"] + $selectedRowsCount;
			}
		}

		$GLOBALS["CACHE_MANAGER"]->cleanDir("user_option");

		if (!empty($listFilter))
		{
			Option::set(self::$moduleId, "listFilterToConvert", serialize($listFilter));
			return true;
		}
		else
		{
			Option::delete(self::$moduleId, array("name" => "listFilterToConvert"));
			return false;
		}
	}

	protected function convertOldFieldsToNewFields(array $oldFields, array $ratioFields)
	{
		$newFields = [];
		$newRows = [];
		foreach ($oldFields as $fieldId => $field)
		{
			if ($field["hidden"] !== "false" || (array_key_exists($fieldId, $ratioFields) &&
					array_key_exists($ratioFields[$fieldId], $newFields)))
				continue;

			if (preg_match("/_FILTER_PERIOD/", $fieldId, $matches,  PREG_OFFSET_CAPTURE))
			{
				$searchResult = current($matches);
				$oldDateType = $field["value"];
				$dateFieldId = mb_substr($fieldId, 0, $searchResult[1]);
				$dateValue = array_key_exists($dateFieldId."_FILTER_DIRECTION", $oldFields) ?
					$oldFields[$dateFieldId."_FILTER_DIRECTION"]["value"] : "";
				$newDateType = $this->getNewDateType($oldDateType, $dateValue);

				$custom = false;
				if (mb_substr($dateFieldId, -2) == "_1")
				{
					$custom = true;
					$fieldId = mb_substr($dateFieldId, 0, mb_strlen($dateFieldId) - 2);
				}
				else
				{
					$fieldId = $dateFieldId;
				}

				if (!$custom)
				{
					if ((mb_substr($fieldId, -5) == "_from"))
					{
						$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 5);
					}
					elseif ((mb_substr($fieldId, -3) == "_to"))
					{
						$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 3);
					}
				}

				$from = "";
				$to = "";
				if ($newDateType == DateType::EXACT || $newDateType == DateType::RANGE)
				{
					if (array_key_exists($fieldId."_1", $oldFields))
					{
						$from = $oldFields[$fieldId."_1"]["value"];
						$to = $oldFields[$fieldId."_2"]["value"];
					}
					elseif (array_key_exists($fieldId."_from", $oldFields))
					{
						$from = $oldFields[$fieldId."_from"]["value"];
						$to = $oldFields[$fieldId."_to"]["value"];
					}
				}

				$newFields[$ratioFields[$fieldId]."_datesel"] = $newDateType;
				$newFields[$ratioFields[$fieldId]."_from"] = $from;
				$newFields[$ratioFields[$fieldId]."_to"] = $to;
				$newFields[$ratioFields[$fieldId]."_days"] = "";
				$newFields[$ratioFields[$fieldId]."_month"] = "";
				$newFields[$ratioFields[$fieldId]."_quarter"] = "";
				$newFields[$ratioFields[$fieldId]."_year"] = "";
			}
			elseif (mb_substr($fieldId, -2) === "_1")
			{
				$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 2);
				if (array_key_exists($fieldId, $ratioFields) && array_key_exists($fieldId."_2", $oldFields) &&
					!array_key_exists($fieldId."_FILTER_PERIOD", $oldFields))
				{
					$newFields[$ratioFields[$fieldId]."_numsel"] = "range";
					$newFields[$ratioFields[$fieldId]."_from"] = $field["value"];
					$newFields[$ratioFields[$fieldId]."_to"] = $oldFields[$fieldId."_2"]["value"];
				}
			}
			elseif (isset($ratioFields[$fieldId."_integer"]))
			{
				$fieldId = $fieldId."_integer";
				$newFields[$ratioFields[$fieldId]."_numsel"] = "exact";
				$newFields[$ratioFields[$fieldId]."_from"] = $field["value"];
				$newFields[$ratioFields[$fieldId]."_to"] = $field["value"];
			}
			elseif (mb_substr($fieldId, -6) === "_start")
			{
				$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 6);
				if (array_key_exists($fieldId, $ratioFields) && array_key_exists($fieldId."_end", $oldFields) &&
					!array_key_exists($fieldId."_FILTER_PERIOD", $oldFields))
				{
					$newFields[$ratioFields[$fieldId]."_numsel"] = "range";
					$newFields[$ratioFields[$fieldId]."_start"] = $field["value"];
					$newFields[$ratioFields[$fieldId]."_end"] = $oldFields[$fieldId."_end"]["value"];
				}
			}
			elseif ((bool)strtotime($field["value"]))
			{
				if ((mb_substr($fieldId, -5) == "_from"))
				{
					$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 5);
				}
				elseif ((mb_substr($fieldId, -3) == "_to"))
				{
					$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 3);
				}
				$from = "";
				$to = "";
				if (array_key_exists($fieldId."_from", $oldFields))
				{
					$from = $oldFields[$fieldId."_from"]["value"];
					$to = $oldFields[$fieldId."_to"]["value"];
				}
				if ($from || $to)
				{
					$newFields[$ratioFields[$fieldId]."_datesel"] = DateType::RANGE;
					$newFields[$ratioFields[$fieldId]."_from"] = $from;
					$newFields[$ratioFields[$fieldId]."_to"] = $to;
					$newFields[$ratioFields[$fieldId]."_days"] = "";
					$newFields[$ratioFields[$fieldId]."_month"] = "";
					$newFields[$ratioFields[$fieldId]."_quarter"] = "";
					$newFields[$ratioFields[$fieldId]."_year"] = "";
				}
			}
			elseif (mb_substr($fieldId, -5) == "_from" && !array_key_exists($fieldId."_FILTER_DIRECTION", $oldFields))
			{
				$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 5);
				$rangeType = (($oldFields[$fieldId."_from"] === $oldFields[$fieldId."_to"]) ? "exact" : "range");
				$newFields[$ratioFields[$fieldId]."_numsel"] = $rangeType;
				$newFields[$ratioFields[$fieldId]."_from"] = $field["value"];
			}
			elseif (mb_substr($fieldId, -3) == "_to")
			{
				$fieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 3);
				if (!array_key_exists($fieldId."_from"."_FILTER_DIRECTION", $oldFields))
				{
					$rangeType = (($oldFields[$fieldId."_from"] === $oldFields[$fieldId."_to"]) ? "exact" : "range");
					$newFields[$ratioFields[$fieldId]."_numsel"] = $rangeType;
					$newFields[$ratioFields[$fieldId]."_to"] = $field["value"];
				}
			}
			elseif (array_key_exists($fieldId, $ratioFields))
			{
				$newFields[$ratioFields[$fieldId]] = $field["value"];
			}

			if (!in_array($ratioFields[$fieldId], $newRows) && $ratioFields[$fieldId])
			{
				$newRows[] = $ratioFields[$fieldId];
			}
		}

		return array($newFields, $newRows);
	}

	protected function getNewDateType($oldDateType, $oldDateValue)
	{
		$newDateType = DateType::EXACT;

		switch ($oldDateType)
		{
			case "day":
				switch ($oldDateValue)
				{
					case "previous":
						$newDateType = DateType::YESTERDAY;
						break;
					case "current":
						$newDateType = DateType::CURRENT_DAY;
						break;
					case "next":
						$newDateType = DateType::TOMORROW;
						break;
				}
				break;
			case "week":
				switch ($oldDateValue)
				{
					case "previous":
						$newDateType = DateType::LAST_WEEK;
						break;
					case "current":
						$newDateType = DateType::CURRENT_WEEK;
						break;
					case "next":
						$newDateType = DateType::NEXT_WEEK;
						break;
				}
				break;
			case "month":
				switch ($oldDateValue)
				{
					case "previous":
						$newDateType = DateType::LAST_MONTH;
						break;
					case "current":
						$newDateType = DateType::CURRENT_MONTH;
						break;
					case "next":
						$newDateType = DateType::NEXT_MONTH;
						break;
				}
				break;
			case "quarter":
				switch ($oldDateValue)
				{
					case "current":
						$newDateType = DateType::CURRENT_QUARTER;
						break;
					case "previous":
					case "next":
						$newDateType = DateType::RANGE;
						break;
				}
				break;
			case "year":
				$newDateType = DateType::RANGE;
				break;
			case "exact":
				$newDateType = DateType::EXACT;
				break;
			case "before":
			case "after":
			case "interval":
				$newDateType = DateType::RANGE;
				break;
		}

		return $newDateType;
	}
}