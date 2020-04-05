<?

use Bitrix\Main\UI\Filter\Type;
use Bitrix\Main\UI\Filter\FieldAdapter;
use Bitrix\Main\UI\Filter\DateType;
use Bitrix\Main\UI\Filter\NumberType;
use Bitrix\Main\UI\Filter\Theme;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);


class CMainUiFilter extends CBitrixComponent
{
	protected $defaultViewSort = 500;
	protected $options;
	protected $jsFolder = "/js/";
	protected $blocksFolder = "/blocks/";
	protected $cssFolder = "/css/";
	protected $themesFolder = "/themes/";
	protected $configName = "config.json";
	protected $commonOptions;
	protected $theme;
	protected $themeInstance;


	protected function prepareResult()
	{
		$this->arResult["FILTER_ID"] = $this->arParams["FILTER_ID"];
		$this->arResult["GRID_ID"] = $this->arParams["GRID_ID"];
		$this->arResult["FIELDS"] = $this->prepareFields();
		$this->arResult["PRESETS"] = $this->preparePresets();
		$this->arResult["TARGET_VIEW_ID"] = $this->getViewId();
		$this->arResult["TARGET_VIEW_SORT"] = $this->getViewSort();
		$this->arResult["SETTINGS_URL"] = $this->prepareSettingsUrl();
		$this->arResult["FILTER_ROWS"] = $this->prepareFilterRows();
		$this->arResult["CURRENT_PRESET"] = $this->prepareDefaultPreset();
		$this->arResult["ENABLE_LABEL"] = $this->prepareEnableLabel();
		$this->arResult["ENABLE_LIVE_SEARCH"] = $this->prepareEnableLiveSearch();
		$this->arResult["DISABLE_SEARCH"] = $this->prepareDisableSearch();
		$this->arResult["COMPACT_STATE"] = $this->prepareCompactState();
		$this->arResult["MAIN_UI_FILTER__AND"] = Loc::getMessage('MAIN_UI_FILTER__AND');
		$this->arResult["MAIN_UI_FILTER__MORE"] = Loc::getMessage('MAIN_UI_FILTER__MORE');
		$this->arResult["MAIN_UI_FILTER__BEFORE"] = Loc::getMessage("MAIN_UI_FILTER__BEFORE");
		$this->arResult["MAIN_UI_FILTER__AFTER"] = Loc::getMessage("MAIN_UI_FILTER__AFTER");
		$this->arResult["MAIN_UI_FILTER__NUMBER_MORE"] = Loc::getMessage("MAIN_UI_FILTER__NUMBER_MORE");
		$this->arResult["MAIN_UI_FILTER__NUMBER_LESS"] = Loc::getMessage("MAIN_UI_FILTER__NUMBER_LESS");
		$this->arResult["MAIN_UI_FILTER__PLACEHOLDER_DEFAULT"] = Loc::getMessage("MAIN_UI_FILTER__PLACEHOLDER_DEFAULT");
		$this->arResult["MAIN_UI_FILTER__PLACEHOLDER_WITH_FILTER"] = Loc::getMessage("MAIN_UI_FILTER__PLACEHOLDER_WITH_FILTER");
		$this->arResult["MAIN_UI_FILTER__PLACEHOLDER"] = Loc::getMessage("MAIN_UI_FILTER__PLACEHOLDER");
		$this->arResult["MAIN_UI_FILTER__QUARTER"] = Loc::getMessage("MAIN_UI_FILTER__QUARTER");
		$this->arResult["MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET"] = Loc::getMessage("MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET");
		$this->arResult["MAIN_UI_FILTER__SET_AS_DEFAULT_PRESET"] = Loc::getMessage("MAIN_UI_FILTER__SET_AS_DEFAULT_PRESET");
		$this->arResult["MAIN_UI_FILTER__EDIT_PRESET_TITLE"] = Loc::getMessage("MAIN_UI_FILTER__EDIT_PRESET_TITLE");
		$this->arResult["MAIN_UI_FILTER__REMOVE_PRESET"] = Loc::getMessage("MAIN_UI_FILTER__REMOVE_PRESET");
		$this->arResult["MAIN_UI_FILTER__DRAG_TITLE"] = Loc::getMessage("MAIN_UI_FILTER__DRAG_TITLE");
		$this->arResult["MAIN_UI_FILTER__DRAG_FIELD_TITLE"] = Loc::getMessage("MAIN_UI_FILTER__DRAG_FIELD_TITLE");
		$this->arResult["MAIN_UI_FILTER__REMOVE_FIELD"] = Loc::getMessage("MAIN_UI_FILTER__REMOVE_FIELD");
		$this->arResult["MAIN_UI_FILTER__CONFIRM_MESSAGE_FOR_ALL"] = Loc::getMessage("MAIN_UI_FILTER__CONFIRM_MESSAGE_FOR_ALL");
		$this->arResult["MAIN_UI_FILTER__CONFIRM_APPLY_FOR_ALL"] = Loc::getMessage("MAIN_UI_FILTER__CONFIRM_APPLY_FOR_ALL");
		$this->arResult["MAIN_UI_FILTER__DATE_NEXT_DAYS_LABEL"] = Loc::getMessage("MAIN_UI_FILTER__DATE_NEXT_DAYS_LABEL");
		$this->arResult["MAIN_UI_FILTER__DATE_PREV_DAYS_LABEL"] = Loc::getMessage("MAIN_UI_FILTER__DATE_PREV_DAYS_LABEL");
		$this->arResult["MAIN_UI_FILTER__DATE_ERROR_TITLE"] = Loc::getMessage("MAIN_UI_FILTER__DATE_ERROR_TITLE");
		$this->arResult["MAIN_UI_FILTER__DATE_ERROR_LABEL"] = Loc::getMessage("MAIN_UI_FILTER__DATE_ERROR_LABEL");
		$this->arResult["CLEAR_GET"] = $this->prepareClearGet();
		$this->arResult["VALUE_REQUIRED_MODE"] = $this->prepareValueRequiredMode();
		$this->arResult["THEME"] = $this->getTheme();
		$this->arResult["RESET_TO_DEFAULT_MODE"] = $this->prepareResetToDefaultMode();
		$this->arResult["COMMON_PRESETS_ID"] = $this->arParams["COMMON_PRESETS_ID"];
		$this->arResult["IS_AUTHORIZED"] = $this->prepareIsAuthorized();
		$this->arResult["LAZY_LOAD"] = $this->arParams["LAZY_LOAD"];
		$this->arResult["VALUE_REQUIRED"] = $this->arParams["VALUE_REQUIRED"];

		if (isset($this->arParams["MESSAGES"]) && is_array($this->arParams["MESSAGES"]))
		{
			foreach ($this->arParams["MESSAGES"] as $key => $message)
			{
				if (
					strpos($key, "MAIN_UI_FILTER__") !== false
					&& isset($this->arResult[$key])
				)
				{
					$this->arResult[$key] = $message;
				}
			}
		}
	}

	protected static function prepareIsAuthorized()
	{
		global $USER;
		return $USER->isAuthorized();
	}

	protected function prepareResetToDefaultMode()
	{
		$result = true;

		if (isset($this->arParams["RESET_TO_DEFAULT_MODE"]) && is_bool($this->arParams["RESET_TO_DEFAULT_MODE"]))
		{
			$result = $this->arParams["RESET_TO_DEFAULT_MODE"];
		}

		return $result;
	}

	protected function prepareValueRequiredMode()
	{
		return ($this->arParams["VALUE_REQUIRED_MODE"] == true);
	}

	protected function prepareClearGet()
	{
		$apply = $this->request->get("apply_filter");
		return !empty($apply);
	}

	protected function prepareDisableSearch()
	{
		$result = false;

		if (isset($this->arParams["DISABLE_SEARCH"]) && $this->arParams["DISABLE_SEARCH"])
		{
			$result = true;
		}

		return $result;
	}

	protected function prepareEnableLiveSearch()
	{
		$this->arResult["ENABLE_LIVE_SEARCH"] = false;

		if (isset($this->arParams["ENABLE_LIVE_SEARCH"]) && is_bool($this->arParams["ENABLE_LIVE_SEARCH"]))
		{
			$this->arResult["ENABLE_LIVE_SEARCH"] = $this->arParams["ENABLE_LIVE_SEARCH"];
		}

		return $this->arResult["ENABLE_LIVE_SEARCH"];
	}

	protected function prepareCompactState()
	{
		$result = false;

		if (isset($this->arParams["COMPACT_STATE"]) && $this->arParams["COMPACT_STATE"])
		{
			$result = true;
		}

		return $result;
	}

	protected function prepareEnableLabel()
	{
		$this->arResult["ENABLE_LABEL"] = false;

		if (is_bool($this->arParams["ENABLE_LABEL"]) && $this->arParams["ENABLE_LABEL"] === true)
		{
			$this->arResult["ENABLE_LABEL"] = true;
		}

		return $this->arResult["ENABLE_LABEL"];
	}

	protected function getUserOptions()
	{
		if (!($this->options instanceof \Bitrix\Main\UI\Filter\Options))
		{
			$this->options = new \Bitrix\Main\UI\Filter\Options(
				$this->arParams["FILTER_ID"],
				$this->arParams["FILTER_PRESETS"],
				$this->arParams["COMMON_PRESETS_ID"]
			);
		}

		return $this->options;
	}

	protected function prepareParams()
	{
		$options = $this->getUserOptions();
		$presets = $this->arParams["FILTER_PRESETS"];

		foreach ($presets as $key => $preset)
		{
			if ($options->isDeletedPreset($key))
			{
				unset($this->arParams["FILTER_PRESETS"][$key]);
			}
		}

		$this->arParams["FILTER_ROWS"] = $this->prepareFilterRowsParam();
	}

	protected function prepareFilterRowsParam()
	{
		if (!isset($this->arParams["FILTER_ROWS"]) || !is_array($this->arParams["FILTER_ROWS"]))
		{
			$this->arParams["FILTER_ROWS"] = array();

			if (isset($this->arParams["FILTER"]) &&
				!empty($this->arParams["FILTER"]) &&
				is_array($this->arParams["FILTER"]))
			{
				foreach ($this->arParams["FILTER"] as $key => $field)
				{
					if ($field["default"])
					{
						$this->arParams["FILTER_ROWS"][$field["id"]] = true;
					}
				}
			}
		}

		return $this->arParams["FILTER_ROWS"];
	}



	protected static function prepareSelectValue(Array $items = array(), $value = "", $strictMode = false)
	{
		foreach ($items as $key => $item)
		{
			if (
				(!$strictMode && $item["VALUE"] == $value) ||
				($strictMode && $item["VALUE"] === $value)
			)
			{
				return $item;
			}
		}

		return array();
	}

	protected static function prepareMultiselectValue(Array $items = array(), Array $value = array(), $isStrict = false)
	{
		$result = array();
		$values = array_values($value);

		foreach ($items as $key => $item)
		{
			if (in_array($item["VALUE"], $values, $isStrict))
			{
				$result[] = $item;
			}
		}

		return $result;
	}

	protected static function prepareValue(Array $field, Array $presetFields = array(), $prefix)
	{
		$fieldValuesKeys = array_keys($field["VALUES"]);
		$fieldName = strpos($field["NAME"], $prefix) !== false ? str_replace($prefix, "", $field["NAME"]) : $field["NAME"];
		$result = array();

		foreach ($fieldValuesKeys as $key => $keyName)
		{
			$currentFieldName = $fieldName.$keyName;
			$result[$keyName] = "";

			if (array_key_exists($currentFieldName, $presetFields))
			{
				$result[$keyName] = $presetFields[$currentFieldName];
			}
		}

		return $result;
	}

	protected static function prepareSubtype(Array $field, Array $presetFields = array(), $prefix)
	{
		$subTypes = $field["SUB_TYPES"];
		$dateselName = strpos($field["NAME"], $prefix) === false ? $field["NAME"].$prefix : $field["NAME"];
		$result = $subTypes[0];

		if (array_key_exists($dateselName, $presetFields))
		{
			foreach ($subTypes as $key => $subType)
			{
				if ($subType["VALUE"] === $presetFields[$dateselName])
				{
					$result = $subType;
				}
			}
		}

		return $result;
	}

	protected static function prepareCustomEntityValue(Array $field, Array $presetFields = array())
	{
		$fieldName = $field["NAME"];
		$fieldNameLabel = $fieldName."_label";
		$fieldNameLabelAlias = $fieldName."_name";
		$fieldNameValue = $fieldName."_value";
		$result = array(
			"_label" => "",
			"_value" => ""
		);

		if (array_key_exists($fieldName, $presetFields))
		{
			$result["_value"] = $presetFields[$fieldName];

		}

		if (empty($result["_value"]) && array_key_exists($fieldNameValue, $presetFields))
		{
			$result["_value"] = $presetFields[$fieldNameValue];

		}

		if (array_key_exists($fieldNameLabel, $presetFields))
		{
			$result["_label"] = $presetFields[$fieldNameLabel];
		}

		if (empty($result["_label"]) && array_key_exists($fieldNameLabelAlias, $presetFields))
		{
			$result["_label"] = $presetFields[$fieldNameLabelAlias];
		}

		if (!empty($result["_value"]) && empty($result["_label"]))
		{
			$result["_label"] = "#".$result["_value"];
		}

		return $result;
	}

	protected static function prepareCustomValue(Array $field, Array $presetFields = array())
	{
		return array_key_exists($field["NAME"], $presetFields) ? $presetFields[$field["NAME"]] : "";
	}

	protected static function prepareDestSelectorValue(Array $field, Array $presetFields = array(), Array $params = array())
	{
		$fieldName = $field["NAME"];
		$fieldNameLabel = $fieldName."_label";
		$fieldNameLabelAlias = $fieldName."_name";
		$fieldNameValue = $fieldName."_value";
		$result = array(
			"_label" => "",
			"_value" => ""
		);

		if (array_key_exists($fieldName, $presetFields))
		{
			$result["_value"] = $presetFields[$fieldName];
		}

		if (empty($result["_value"]) && array_key_exists($fieldNameValue, $presetFields))
		{
			$result["_value"] = $presetFields[$fieldNameValue];
		}

		if (array_key_exists($fieldNameLabel, $presetFields))
		{
			$result["_label"] = $presetFields[$fieldNameLabel];
		}

		if (empty($result["_label"]) && array_key_exists($fieldNameLabelAlias, $presetFields))
		{
			$result["_label"] = $presetFields[$fieldNameLabelAlias];
		}

		if (!empty($result["_value"]) && empty($result["_label"]))
		{
			$value = (
				!empty($params[$field['NAME']])
				&& !empty($params[$field['NAME']]['params'])
				&& !empty($params[$field['NAME']]['params']['isNumeric'])
				&& $params[$field['NAME']]['params']['isNumeric'] == 'Y'
				&& !empty($params[$field['NAME']]['params']['prefix'])
					? $params[$field['NAME']]['params']['prefix'].$result["_value"]
					: $result["_value"]
			);

			$entityType = Bitrix\Main\UI\Selector\Entities::getEntityType(array(
				'itemCode' => $value
			));
			if (!empty($entityType))
			{
				if ($entityType == 'department')
				{
					$entityType = 'departments';
				}

				$provider = \Bitrix\Main\UI\Selector\Entities::getProviderByEntityType(strtoupper($entityType));
				if ($provider !== false)
				{
					$result["_label"] = $provider->getItemName($value);
				}
			}
		}

		if (!empty($result["_value"]) && empty($result["_label"]))
		{
			$result["_label"] = "#".$result["_value"];
		}

		return $result;
	}

	protected static function compatibleDateselValue($value = "")
	{
		$dateMap = array(
			"" => DateType::NONE,
			"today" => DateType::CURRENT_DAY,
			"yesterday" => DateType::YESTERDAY,
			"tomorrow" => DateType::TOMORROW,
			"week_ago" => DateType::LAST_WEEK,
			"month" => DateType::MONTH,
			"month_ago" => DateType::LAST_MONTH,
			"exact" => DateType::EXACT,
			"after" => DateType::RANGE,
			"before" => DateType::RANGE,
			"interval" => DateType::RANGE
		);

		return array_key_exists($value, $dateMap) ? $dateMap[$value] : $value;
	}

	protected function preparePresetFields($presetRows = array(), $presetFields = array())
	{
		$result = array();

		if (is_array($presetRows) && is_array($presetFields))
		{
			foreach ($presetRows as $rowKey => $rowName)
			{
				$field = $this->getField($rowName);

				if (empty($field))
				{
					continue;
				}

				$value = array_key_exists($rowName, $presetFields) ? $presetFields[$rowName] : "";

				switch ($field["TYPE"])
				{
					case Type::SELECT :
					{
						if (!empty($value) && is_array($value))
						{
							$values = array_values($value);
							$value = $values[0];
						}

						$field["VALUE"] = self::prepareSelectValue($field["ITEMS"], $value, $field["STRICT"]);
						break;
					}

					case Type::MULTI_SELECT :
					{
						if ($value !== "")
						{
							$value = is_array($value) ? $value : [$value];
							$field["VALUE"] = self::prepareMultiselectValue($field["ITEMS"], $value, $field['STRICT']);
						}
						break;
					}

					case Type::DATE :
					{
						$presetFields[$field["NAME"]."_datesel"] = self::compatibleDateselValue(
							$presetFields[$field["NAME"]."_datesel"]
						);
						$field["SUB_TYPE"] = self::prepareSubtype($field, $presetFields, "_datesel");
						$field["VALUES"] = self::prepareValue($field, $presetFields, "_datesel");

						if (is_array($field["YEARS_SWITCHER"]))
						{
							$field["YEARS_SWITCHER"]["VALUE"] = self::prepareSelectValue(
								$field["YEARS_SWITCHER"]["ITEMS"],
								$presetFields[$field["NAME"]."_allow_year"],
								$field["STRICT"]
							);
						}

						break;
					}

					case Type::CUSTOM_DATE :
					{
						$days = array();

						if (isset($presetFields[$field["NAME"]."_days"]) && is_array($presetFields[$field["NAME"]."_days"]))
						{
							$days = $presetFields[$field["NAME"]."_days"];
						}

						$months = array();

						if (isset($presetFields[$field["NAME"]."_months"]) && is_array($presetFields[$field["NAME"]."_months"]))
						{
							$months = $presetFields[$field["NAME"]."_months"];
						}

						$years = array();

						if (isset($presetFields[$field["NAME"]."_years"]) && is_array($presetFields[$field["NAME"]."_years"]))
						{
							$years = $presetFields[$field["NAME"]."_years"];
						}

						$field["VALUE"] = array(
							"days" => $days,
							"months" => $months,
							"years" => $years
						);
					}

					case Type::NUMBER :
					{
						$field["SUB_TYPE"] = self::prepareSubtype($field, $presetFields, "_numsel");
						$field["VALUES"] = self::prepareValue($field, $presetFields, "_numsel");
						break;
					}

					case Type::CUSTOM_ENTITY :
					{
						$field["VALUES"] = self::prepareCustomEntityValue($field, $presetFields);
						break;
					}

					case Type::CUSTOM :
					{
						$field["_VALUE"] = self::prepareCustomValue($field, $presetFields);
						break;
					}

					case Type::DEST_SELECTOR :
					{
						$field["VALUES"] = self::prepareDestSelectorValue($field, $presetFields, $this->arParams['FILTER']);
						break;
					}

					case Type::STRING :
					{
						$field["VALUE"] = $value;
						break;
					}

				}

				$result[] = $field;
			}
		}

		return $result;
	}

	protected function applyOptions()
	{
		$options = $this->getUserOptions();
		$arOptions = $options->getOptions();
		$optionsPresets = $arOptions["filters"];
		$defaultPresets = $this->preparePresets();
		$arFilter = $options->getFilter($this->arParams["FILTER"]);

		if (!empty($optionsPresets) && is_array($optionsPresets))
		{
			$index = 0;

			foreach ($optionsPresets as $presetId => $presetFields)
			{
				$rows = array();
				if (isset($presetFields["filter_rows"]))
				{
					$rows = explode(",", $presetFields["filter_rows"]);
				}
				elseif(isset($presetFields["fields"]) && is_array($presetFields["fields"]))
				{
					$rows = array_keys($presetFields["fields"]);
				}

				$fields = isset($presetFields["fields"]) && is_array($presetFields["fields"])
					? $presetFields["fields"] : array();

				if (isset($presetFields["for_all"]))
				{
					$forAll = $presetFields["for_all"];
				}
				else
				{
					$forAll = !$this->arParams["FILTER_PRESETS"][$presetId]["disallow_for_all"];
				}

				$preset = array(
					"ID" => $presetId,
					"SORT" => $presetFields["sort"] !== null ? $presetFields["sort"] : $index,
					"TITLE" => $presetFields["name"],
					"FIELDS" => $this->preparePresetFields($rows, $fields),
					"FOR_ALL" => $forAll,
					"IS_PINNED" => false
				);

				$additionalFields = $options->getAdditionalPresetFields($presetId);

				if (is_array($additionalFields))
				{
					$additionalRows = \Bitrix\Main\UI\Filter\Options::getRowsFromFields($additionalFields);
					$preset["ADDITIONAL"] = $this->preparePresetFields($additionalRows, $additionalFields);
				}

				if ($arOptions["default"] === $presetId)
				{
					$preset["IS_PINNED"] = true;
				}

				if ($preset["ID"] === "default_filter")
				{
					$preset["FIELDS_COUNT"] = $this->prepareFieldsCount();
				}

				if ($preset["ID"] === "tmp_filter")
				{
					$preset["FIELDS_COUNT"] = $this->prepareFieldsCount();
				}

				$isReplace = array_key_exists($presetId, $this->arParams["FILTER_PRESETS"]);
				if ($isReplace || $preset["ID"] === "default_filter")
				{
					foreach ($defaultPresets as $defKey => $defaultPreset)
					{
						if ($defaultPreset["ID"] === $preset["ID"])
						{
							if (!isset($presetFields["fields"]) && !isset($presetFields["filter_rows"]))
							{
								$preset["FIELDS"] = $this->arResult["PRESETS"][$defKey]["FIELDS"];
							}

							$this->arResult["PRESETS"][$defKey] = $preset;
						}
					}
				}
				else
				{
					$this->arResult["PRESETS"][] = $preset;
				}

				$index++;
			}

			if (isset($arFilter["PRESET_ID"]))
			{
				foreach ($this->arResult["PRESETS"] as $key => $preset)
				{
					if ($arFilter["PRESET_ID"] === $preset["ID"])
					{
						$this->arResult["CURRENT_PRESET"] = $preset;
						$this->arResult["CURRENT_PRESET"]["FIND"] = $arFilter["FIND"];
					}
				}
			}
		}

		\Bitrix\Main\Type\Collection::sortByColumn(
			$this->arResult["PRESETS"],
			array("SORT" => array(SORT_NUMERIC, SORT_ASC)),
			'',
			1000
		);
	}

	protected function prepareDefaultPreset()
	{
		global $USER;

		if (!is_array($this->arResult["CURRENT_PRESET"]) &&
			$USER->CanDoOperation("edit_other_settings"))
		{
			$this->arResult["CURRENT_PRESET"] = array(
				"ID" => "default_filter",
				"TITLE" => Loc::getMessage("MAIN_UI_FILTER__DEFAULT_FILTER_TITLE"),
				"FIELDS" => $this->prepareFilterRows(),
				"FIELDS_COUNT" => $this->prepareFieldsCount(),
				"FOR_ALL" => true
			);
		}

		return $this->arResult["CURRENT_PRESET"];
	}

	protected function prepareFieldsCount()
	{
		$options = $this->getUserOptions();
		$filter = $options->getFilter($this->arParams["FILTER"]);
		$arOptions = $options->getOptions();
		$count = 0;

		if (!empty($filter) && array_key_exists($filter["PRESET_ID"], $arOptions["filters"]))
		{
			$preset = $arOptions["filters"][$filter["PRESET_ID"]];
			$fields = $preset["fields"];
			$rows = explode(",", $preset["filter_rows"]);

			foreach ($rows as $key => $row)
			{
				if (array_key_exists($row, $fields) && !empty($fields[$row]))
				{
					$count++;
				}
				else
				{
					$dataRow = $row."_datesel";
					$numRow = $row."_numsel";
					$from = $row."_from";
					$to = $row."_to";
					$days = $row."_days";

					if ((array_key_exists($dataRow, $fields) || array_key_exists($numRow, $fields)) && (
							(array_key_exists($from, $fields) && !empty($fields[$from])) ||
							(array_key_exists($to, $fields) && !empty($fields[$to])) ||
							(array_key_exists($days, $fields) && !empty($fields[$days]))
						)
					)
					{
						$count++;
					}
				}
			}
		}

		return $count;
	}

	protected function prepareFilterRows()
	{
		if (!is_array($this->arResult["FILTER_ROWS"]))
		{
			$this->arResult["FILTER_ROWS"] = array();

			if (isset($this->arParams["FILTER_ROWS"]) &&
				!empty($this->arParams["FILTER_ROWS"]) &&
				is_array($this->arParams["FILTER_ROWS"]))
			{
				foreach ($this->arParams["FILTER_ROWS"] as $rowId => $isEnabled)
				{
					if ($isEnabled)
					{
						$field = $this->getField($rowId);
						$this->arResult["FILTER_ROWS"][] = $field;
					}
				}
			}
		}

		return $this->arResult["FILTER_ROWS"];
	}

	protected function getViewId()
	{
		$viewId = "";

		if (isset($this->arParams["RENDER_FILTER_INTO_VIEW"]) &&
			!empty($this->arParams["RENDER_FILTER_INTO_VIEW"]) &&
			is_string($this->arParams["RENDER_FILTER_INTO_VIEW"]))
		{
			$viewId = $this->arParams["RENDER_FILTER_INTO_VIEW"];
		}

		return $viewId;
	}

	protected function getViewSort()
	{
		$viewSort = $this->defaultViewSort;

		if (isset($this->arParams["RENDER_FILTER_INTO_VIEW_SORT"]) &&
			!empty($this->arParams["RENDER_FILTER_INTO_VIEW_SORT"]))
		{
			$viewSort = (int) $this->arParams["RENDER_FILTER_INTO_VIEW_SORT"];
		}

		return $viewSort;
	}

	protected function prepareSourcePresets()
	{
		$sourcePresets = $this->arParams["FILTER_PRESETS"];
		$presets = array();
		$sort = 0;

		if (!empty($sourcePresets) && is_array($sourcePresets))
		{
			$preset = array();

			foreach ($sourcePresets as $presetId => $presetFields)
			{
				if ($presetId !== "default_filter")
				{
					$rows = is_array($presetFields["fields"]) ? array_keys($presetFields["fields"]) : array();
					$preset["ID"] = $presetId;
					$preset["TITLE"] = $presetFields["name"];
					$preset["SORT"] = $sort;
					$preset["FIELDS"] = $this->preparePresetFields($rows, $presetFields["fields"]);
					$preset["IS_DEFAULT"] = true;
					$preset["FOR_ALL"] = !$presetFields["disallow_for_all"];
					$preset["PINNED"] = $presetFields["default"] == true;

					$presets[] = $preset;
					$sort++;
				}
			}
		}

		global $USER;
		if (!$USER->CanDoOperation("edit_other_settings"))
		{
			$commonOptions = $this->getCommonOptions();

			if (!empty($commonOptions) &&
				is_array($commonOptions) &&
				isset($commonOptions["filters"]) &&
				is_array($commonOptions["filters"]) &&
				isset($commonOptions["filters"]["default_filter"]))
			{
				$rows = explode(",", $commonOptions["filters"]["default_filter"]["filter_rows"]);
			}
			else
			{
				$rows = array_keys($this->prepareFilterRowsParam());
			}
		}
		else
		{
			$rows = array_keys($this->prepareFilterRowsParam());
		}

		$sort++;

		$presets[] = array(
			"ID" => "default_filter",
			"TITLE" => Loc::getMessage("MAIN_UI_FILTER__DEFAULT_FILTER_TITLE"),
			"SORT" => $sort,
			"FIELDS" => $this->preparePresetFields($rows, $rows),
			"IS_DEFAULT" => true,
			"FOR_ALL" => true
		);

		return $presets;
	}

	protected function preparePresets()
	{
		if (!is_array($this->arResult["PRESETS"]))
		{
			$this->arResult["PRESETS"] = $this->prepareSourcePresets();
		}

		return $this->arResult["PRESETS"];
	}

	protected function getField($fieldId)
	{
		$fields = $this->prepareFields();
		$resultField = array();

		if (!empty($fields) && is_array($fields))
		{
			foreach ($fields as $fieldKey => $fieldFields)
			{
				if ($fieldFields["NAME"] === $fieldId ||
					$fieldFields["NAME"]."_datesel" === $fieldId ||
					$fieldFields["NAME"]."_numsel" === $fieldId)
				{
					$resultField = $fieldFields;
				}
			}
		}

		return $resultField;
	}

	protected function prepareFields()
	{
		if (!is_array($this->arResult["FIELDS"]))
		{
			$this->arResult["FIELDS"] = array();
			$sourceFields = $this->arParams["FILTER"];

			if (is_array($sourceFields) && !empty($sourceFields))
			{
				foreach ($sourceFields as $sourceFieldKey => $sourceField)
				{
					$this->arResult["FIELDS"][] = array_merge(
						FieldAdapter::adapt($sourceField),
						array("STRICT" => $sourceField["strict"] === true)
					);
				}
			}
		}

		return $this->arResult["FIELDS"];
	}

	protected function prepareSettingsUrl()
	{
		$path = $this->getPath();
		return join("/", array($path, "settings.ajax.php"));
	}

	protected function checkRequiredParams()
	{
		$errors = new \Bitrix\Main\ErrorCollection();

		if (!isset($this->arParams["FILTER_ID"]) ||
			empty($this->arParams["FILTER_ID"]) ||
			!is_string($this->arParams["FILTER_ID"]))
		{
			$errors->add(array(new \Bitrix\Main\Error(Loc::getMessage("MAIN_UI_FILTER__FILTER_ID_NOT_SET"))));
		}

		foreach ($errors->toArray() as $key => $error)
		{
			ShowError($error);
		}

		return $errors->count() === 0;
	}

	protected function getJsFolder()
	{
		return $this->jsFolder;
	}

	protected function getBlocksFolder()
	{
		return $this->blocksFolder;
	}

	protected function getCssFolder()
	{
		return $this->cssFolder;
	}

	protected function includeScripts($folder)
	{
		$tmpl = $this->getTemplate();
		$absPath = $_SERVER["DOCUMENT_ROOT"].$tmpl->GetFolder().$folder;
		$relPath = $tmpl->GetFolder().$folder;

		if (is_dir($absPath))
		{
			$dir = opendir($absPath);

			if($dir)
			{
				while(($file = readdir($dir)) !== false)
				{
					$ext = getFileExtension($file);

					if ($ext === 'js' && !(strpos($file, 'map.js') !== false || strpos($file, 'min.js') !== false))
					{
						$tmpl->addExternalJs($relPath.$file);
					}
				}

				closedir($dir);
			}
		}
	}

	protected function includeStyles($folder)
	{
		$tmpl = $this->getTemplate();
		$absPath = $_SERVER["DOCUMENT_ROOT"].$tmpl->GetFolder().$folder;
		$relPath = $tmpl->GetFolder().$folder;

		if (is_dir($absPath))
		{
			$dir = opendir($absPath);

			if($dir)
			{
				while(($file = readdir($dir)) !== false)
				{
					$ext = getFileExtension($file);

					if ($ext === 'css' && !(strpos($file, 'map.css') !== false || strpos($file, 'min.css') !== false))
					{
						$tmpl->addExternalCss($relPath.$file);
					}
				}

				closedir($dir);
			}
		}
	}

	protected function includeComponentBlocks()
	{
		$blocksFolder = $this->getBlocksFolder();
		$this->includeScripts($blocksFolder);
	}

	protected function includeComponentScripts()
	{
		$scriptsFolder = $this->getJsFolder();
		$this->includeScripts($scriptsFolder);
	}

	protected function saveOptions()
	{
		$request = $this->request;

		if ($request->getPost("apply_filter") === "Y")
		{
			$options = $this->getUserOptions();
			$options->setFilterSettings($request->get("filter_id"), $request->toArray());
			$options->save();
		}
	}

	protected function prepareDefaultPresets()
	{
		$this->arResult["DEFAULT_PRESETS"] = $this->prepareSourcePresets();
	}

	protected function getCommonOptions()
	{
		if (!$this->commonOptions)
		{
			$this->commonOptions = \CUserOptions::getOption("main.ui.filter.common", $this->arParams["FILTER_ID"], array());
		}

		return $this->commonOptions;
	}

	protected function initParams()
	{
		global $USER;
		if (!$USER->CanDoOperation("edit_other_settings"))
		{
			$commonOptions = $this->getCommonOptions();
			$filters = $commonOptions["filters"];

			if (!empty($filters) && is_array($filters))
			{
				unset($filters["tmp_filter"]);
				$this->arParams["FILTER_PRESETS"] = $filters;
			}
		}

		if (!isset($this->arParams["FILTER_PRESETS"]) || !is_array($this->arParams["FILTER_PRESETS"]))
		{
			$this->arParams["FILTER_PRESETS"] = array();
		}

		if (!is_array($this->arParams["CONFIG"]))
		{
			$this->arParams["CONFIG"] = array();
		}

		if ($this->arParams["VALUE_REQUIRED"] === true)
		{
			$allowValueRequiredParam = false;

			foreach ($this->arParams["FILTER"] as $key => $field)
			{
				if (!$allowValueRequiredParam && $field["required"] === true)
				{
					$allowValueRequiredParam = true;
				}
			}

			$this->arParams["VALUE_REQUIRED"] = $allowValueRequiredParam;
		}
	}

	protected function getTheme()
	{
		if (!$this->theme && isset($this->arParams["THEME"]) && !empty($this->arParams["THEME"]))
		{
			$availableThemes = Theme::getList();
			if (in_array($this->arParams["THEME"], $availableThemes))
			{
				$this->theme = $this->arParams["THEME"];
			}
		}

		if (!$this->theme)
		{
			$this->theme = Theme::DEFAULT_FILTER;
		}

		return $this->theme;
	}

	protected function includeTheme()
	{
		$theme = $this->getTheme();
		if ($theme !== Theme::DEFAULT_FILTER)
		{
			$themePath = strtolower($theme);
			$themePath = $this->themesFolder.$themePath."/";

			$this->includeStyles($themePath);
			$this->includeScripts($themePath);
		}
	}

	protected function getConfig($path)
	{
		$file = new \Bitrix\Main\IO\File($path);
		$config = array();

		if ($file->isExists())
		{
			$jsonConfig = $file->getContents();

			if (!empty($jsonConfig))
			{
				$config = \Bitrix\Main\Web\Json::decode($jsonConfig);
			}
		}

		return $config;
	}

	protected function getAbsoluteThemesPath()
	{
		$templatePath = $this->getTemplate()->getFolder();
		$relativeThemesPath = $templatePath.$this->themesFolder;
		$absolutePath = \Bitrix\Main\IO\Path::convertRelativeToAbsolute($relativeThemesPath);
		return $absolutePath;
	}

	public function prepareConfig()
	{
		$themesPath = $this->getAbsoluteThemesPath();
		$themeId = $this->getTheme();
		$themeFolder = strtolower($themeId);
		$defaultConfigPath = $themesPath."/".$this->configName;
		$themeConfigPath = $themesPath."/".$themeFolder."/".$this->configName;
		$defaultConfig = $this->getConfig($defaultConfigPath);
		$themeConfig = $this->getConfig($themeConfigPath);
		$paramsConfig = $this->arParams["CONFIG"];
		return array_merge($defaultConfig, $themeConfig, $paramsConfig);
	}

	public function executeComponent()
	{
		if ($this->checkRequiredParams())
		{
			$this->initParams();
			$this->prepareDefaultPresets();
			$this->saveOptions();
			$this->prepareParams();
			$this->prepareResult();
			$this->applyOptions();
			$this->includeComponentTemplate();
			$this->includeComponentScripts();
			$this->includeComponentBlocks();
			$this->includeTheme();
		}
	}
}