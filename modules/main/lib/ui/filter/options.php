<?

namespace Bitrix\Main\UI\Filter;


use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UI\Filter;


/**
 * Class Options of main.ui.filter
 * @package Bitrix\Main\UI\Filter
 */
class Options
{
	protected $id;
	protected $options;
	protected $commonPresets;
	protected $useCommonPresets;
	protected $commonPresetsId;
	protected $request;
	protected ?string $currentFilterPresetId = null;

	const DEFAULT_FILTER = "default_filter";
	const TMP_FILTER = "tmp_filter";


	/**
	 * Options constructor.
	 * @param string $filterId $arParams["FILTER_ID"]
	 * @param array $filterPresets $arParams["FILTER_PRESETS"]
	 * @param string $commonPresetsId $arParams["COMMON_PRESETS_ID"] Set if you want to use common presets
	 */
	public function __construct($filterId, $filterPresets = array(), $commonPresetsId = null)
	{
		$this->id = $filterId;
		$this->options = $this->fetchOptions($this->id);
		$this->useCommonPresets = false;

		if (!empty($commonPresetsId) && is_string($commonPresetsId))
		{
			$this->commonPresets = static::fetchCommonPresets($commonPresetsId);
			$this->useCommonPresets = true;
			$this->commonPresetsId = $commonPresetsId;
			$this->options["filters"] = $this->commonPresets["filters"] ?? null;
			$this->options["deleted_presets"] = $this->commonPresets["deleted_presets"] ?? null;
		}

		if (!isset($this->options["use_pin_preset"]))
		{
			$this->options["use_pin_preset"] = true;
		}

		if (!isset($this->options["deleted_presets"]) || !is_array($this->options["deleted_presets"]))
		{
			$this->options["deleted_presets"] = array();
		}

		if (!empty($filterPresets) && is_array($filterPresets))
		{
			$this->options["default_presets"] = $filterPresets;
		}
		else
		{
			$this->options["default_presets"] = array();
		}

		if (!isset($this->options["default"]) || empty($this->options["default"]) ||
			($this->options["default"] === self::DEFAULT_FILTER && $this->options["use_pin_preset"]))
		{
			$this->options["default"] = self::findDefaultPresetId($this->options["default_presets"]);
		}

		if (!isset($this->options["filter"]) || empty($this->options["filter"]) || !is_string($this->options["filter"]))
		{
			$this->options["filter"] = $this->options["default"];
		}

		if (!isset($this->options["filters"]) || !is_array($this->options["filters"]))
		{
			$this->options["filters"] = $this->options["default_presets"];
		}

		// Move additional fields from options to session
		if (is_array($this->options["filters"]))
		{
			foreach ($this->options["filters"] as $presetId => $options)
			{
				if (isset($options["additional"]) && is_array($options["additional"]))
				{
					$this->setAdditionalPresetFields($presetId, $options["additional"]);
					unset($this->options["filters"][$presetId]["additional"]);
				}
			}
		}

		if (isset($this->options["update_default_presets"]) &&
			$this->options["update_default_presets"] == true &&
			!empty($filterPresets) &&
			is_array($filterPresets))
		{
			$this->options["update_default_presets"] = false;
			$sort = 0;

			foreach ($filterPresets as $key => $defaultPreset)
			{
				$this->options["filters"][$key] = $defaultPreset;
				$this->options["filters"][$key]["sort"] = $sort;
				$sort++;
			}

			foreach ($this->options["filters"] as $key => $preset)
			{
				if (!array_key_exists($key, $filterPresets))
				{
					$this->options["filters"][$key]["sort"] = $sort;
					$sort++;
				}
			}
		}
	}


	/**
	 * @return string
	 */
	public function getCommonPresetsId()
	{
		return $this->commonPresetsId;
	}


	/**
	 * @return bool
	 */
	public function isUseCommonPresets()
	{
		return $this->useCommonPresets;
	}


	/**
	 * Gets filter id
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Sets filter presets
	 * @param array $presets
	 */
	public function setPresets($presets = array())
	{
		$this->options["filters"] = $presets;
	}


	/**
	 * Sets current preset id
	 * @param string $presetId
	 */
	public function setCurrentPreset($presetId = "default_filter")
	{
		$this->options["filter"] = $presetId;
	}


	/**
	 * Gets default presets from filter options
	 * @return array|null
	 */
	public function getDefaultPresets()
	{
		return $this->options["default_presets"];
	}


	/**
	 * Gets presets
	 * @return array|null
	 */
	public function getPresets()
	{
		return $this->options["filters"];
	}


	/**
	 * Sets default preset id
	 * @param string $presetId
	 */
	public function setDefaultPreset($presetId = "default_filter")
	{
		$this->options["default"] = $presetId;
	}


	/**
	 * Checks is need use pinned preset
	 * @return bool
	 */
	public function isUsePinPreset()
	{
		return $this->options["use_pin_preset"];
	}


	/**
	 * Sets default presets
	 * @param array $presets
	 */
	public function setDefaultPresets($presets = array())
	{
		$this->options["default_presets"] = $presets;
	}


	/**
	 * Sets deleted presets array
	 * @param array $deletedPresets
	 */
	public function setDeletedPresets($deletedPresets = array())
	{
		$this->options["deleted_presets"] = $deletedPresets;
	}


	/**
	 * Sets use_pin_preset values
	 * @param boolean $value
	 */
	public function setUsePinPreset($value = true)
	{
		$this->options["use_pin_preset"] = $value;
	}


	/**
	 * Gets common presets from database
	 * @param string $id Common presets id $arParams["COMMON_PRESETS_ID"]
	 * @return array|bool
	 */
	public static function fetchCommonPresets($id)
	{
		global $USER;

		if ($USER->isAuthorized() ||
			(!$USER->isAuthorized() && !isset(Application::getInstance()->getSession()["main.ui.filter.presets"][$id])))
		{
			$options = \CUserOptions::getOption("main.ui.filter.presets", $id, array(), self::getUserId());
		}
		else
		{
			$options = Application::getInstance()->getSession()["main.ui.filter.presets"][$id];
		}

		return $options;

	}


	/**
	 * Gets filter options from database
	 * @param string $id Filter id $arParams["FILTER_ID"]
	 * @return array|bool
	 */
	public function fetchOptions($id)
	{
		global $USER;

		if ($USER->isAuthorized() ||
			(!$USER->isAuthorized() && !isset(Application::getInstance()->getSession()["main.ui.filter"][$this->getId()]["options"])))
		{
			$options = \CUserOptions::getOption("main.ui.filter", $id, array(), self::getUserId());

			if (empty($options))
			{
				$options = \CUserOptions::getOption("main.ui.filter.common", $id, array(), 0);
			}
		}
		else
		{
			$options = Application::getInstance()->getSession()["main.ui.filter"][$this->getId()]["options"];
		}

		return $options;
	}


	protected static function getUserId()
	{
		global $USER;
		$userId = 0;

		if ($USER->isAuthorized())
		{
			$userId = $USER->getID();
		}

		return $userId;
	}


	protected function getRequest()
	{
		return Context::getCurrent()->getRequest();
	}


	/**
	 * Finds default preset in presets array
	 * @param array $presets
	 * @return string Default preset id
	 */
	public static function findDefaultPresetId($presets = array())
	{
		$result = "default_filter";

		if (!empty($presets) && is_array($presets))
		{
			foreach ($presets as $presetId => $preset)
			{
				if (isset($preset["default"]) && $preset["default"])
				{
					$result = $presetId;
				}
			}
		}

		return $result;
	}


	/**
	 * Gets filter options
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * Makes preset as default
	 * @param string $presetId Preset id
	 */
	public function pinPreset($presetId = "default_filter")
	{
		if ($presetId === "default_filter")
		{
			$this->options["use_pin_preset"] = false;
		}
		else
		{
			$this->options["use_pin_preset"] = true;
		}

		$this->options["default"] = $presetId;
	}


	/**
	 * Checks is need whether to set fields from query
	 * @param HttpRequest $request
	 *
	 * @return bool
	 */
	public static function isSetFromRequest(HttpRequest $request)
	{
		$applyFilter = $request->get("apply_filter");
		$isAjaxRequest = $request->get("ajax_request");

		return $applyFilter !== null && $isAjaxRequest === null && !$request->isAjaxRequest();
	}


	/**
	 * Fetches field values from request
	 * @param array $fields
	 * @param HttpRequest $request
	 *
	 * @return array|null
	 */
	public static function fetchSettingsFromQuery($fields = array(), HttpRequest $request)
	{
		$result = array("fields" => array(), "rows" => array());

		foreach ($fields as $key => $field)
		{
			$id = $field["id"];
			$fromId = $id."_from";
			$toId = $id."_to";
			$quarterId = $id."_quarter";
			$yearId = $id."_year";
			$monthId = $id."_month";
			$daysId = $id."_days";
			$nameId = $id."_name";
			$labelId = $id."_label";
			$valueId = $id."_value";
			$dateselId = $id."_datesel";
			$numselId = $id."_numsel";
			$type = $field["type"] ?? null;
			$isEmpty = $id."_isEmpty";
			$hasAnyValue = $id."_hasAnyValue";

			if ($type == "date")
			{
				if ($request[$dateselId] !== null && ($request[$fromId] !== null ||
						$request[$toId] !== null ||
						$request[$quarterId] !== null ||
						$request[$yearId] !== null ||
						$request[$daysId] !== null ||
						$request[$monthId] !== null))
				{
					$result["fields"][$dateselId] = $request[$dateselId];
					$result["fields"][$fromId] = $request[$fromId] !== null ? $request[$fromId] : "";
					$result["fields"][$toId] = $request[$toId] !== null ? $request[$toId] : "";
					$result["fields"][$yearId] = $request[$yearId] !== null ? $request[$yearId] : "";
					$result["fields"][$monthId] = $request[$monthId] !== null ? $request[$monthId] : "";
					$result["fields"][$daysId] = $request[$daysId] !== null ? $request[$daysId] : "";
					$result["rows"][] = $id;
				}
			}
			else if ($type == "number")
			{
				if ($request[$numselId] !== null && ($request[$fromId] !== null || $request[$toId]))
				{
					$result["fields"][$numselId] = $request[$numselId];
					$result["fields"][$fromId] = $request[$fromId] !== null ? $request[$fromId] : "";
					$result["fields"][$toId] = $request[$toId] !== null ? $request[$toId] : "";
					$result["rows"][] = $id;
				}
			}
			else if ($type == "custom_entity")
			{
				if ($request[$id] !== null)
				{
					if ($request[$id] !== null || $request[$labelId] !== null)
					{
						$result["fields"][$labelId] = ($request[$nameId] !== null ?
							$request[$nameId] : $request[$labelId]);
					}
					$result["fields"][$id] = $request[$id];
					$result["rows"][] = $id;
				}
			}
			else if ($type == "dest_selector" || $type == "entity_selector")
			{
				if ($request[$id] !== null)
				{
					$result["fields"][$id] = $request[$id];
					$result["rows"][] = $id;
				}
			}
			else
			{
				if ($request[$id] !== null)
				{
					$result["fields"][$id] = $request[$id];
					$result["rows"][] = $id;
				}
			}

			if (isset($request[$isEmpty]))
			{
				$result['fields'][$isEmpty] = $request[$isEmpty];
				$result["rows"][] = $id;
			}

			if (isset($request[$hasAnyValue]))
			{
				$result['fields'][$hasAnyValue] = $request[$hasAnyValue];
				$result["rows"][] = $id;
			}
		}

		if ($request["FIND"] !== null)
		{
			$result["fields"]["FIND"] = $request["FIND"];
		}

		if (empty($result["fields"]) && empty($result["rows"]))
		{
			$result = null;
		}

		return $result;
	}


	/**
	 * Gets session filter
	 * @return mixed
	 */
	public function getSessionFilterId()
	{
		$session = Application::getInstance()->getSession();

		return $session["main.ui.filter"][$this->getId()]["filter"] ?? null;
	}

	public function isSetOutside(): bool
	{
		$session = Application::getInstance()->getSession();

		return filter_var(
			$session["main.ui.filter"][$this->getId()]["isSetOutside"] ?? false,
			FILTER_VALIDATE_BOOLEAN
		);
	}


	/**
	 * Gets additional preset fields
	 * @param string $presetId
	 * @return array
	 */
	public function getAdditionalPresetFields($presetId)
	{
		$session = Application::getInstance()->getSession();
		$additional = $session["main.ui.filter"][$this->getId()]["filters"][$presetId]["additional"] ?? [];

		return is_array($additional) ? $additional : [];
	}


	/**
	 * Sets additional fields
	 * @param string $presetId
	 * @param array $additional
	 */
	public function setAdditionalPresetFields($presetId, $additional = array())
	{
		Application::getInstance()->getSession()["main.ui.filter"][$this->getId()]["filters"][$presetId]["additional"] = $additional;
	}


	/**
	 * Gets default filter
	 * @return mixed
	 */
	public function getDefaultFilterId()
	{
		return $this->options["default"];
	}


	/**
	 * Gets current applied filter id
	 * @return mixed
	 */
	public function getCurrentFilterId()
	{
		$sessionFilterId = ($this->getCurrentFilterPresetId() ?? $this->getSessionFilterId());
		$defaultFilterId = $this->getDefaultFilterId();
		return !empty($sessionFilterId) ? $sessionFilterId : $defaultFilterId;
	}


	protected function trySetFilterFromRequest($fields = array())
	{
		$request = $this->getRequest();

		if (self::isSetFromRequest($request))
		{
			$settings = self::fetchSettingsFromQuery($fields, $this->getRequest());
			$clear = mb_strtoupper($request->get("clear_filter")) == "Y";

			if ($settings !== null || $clear)
			{
				$presetId = $clear ? self::DEFAULT_FILTER : self::TMP_FILTER;

				$this->setFilterSettings($presetId, $settings);
				$this->save();
			}
		}
	}


	/**
	 * Gets filter settings by preset id
	 * @param $presetId
	 * @return array|null
	 */
	public function getFilterSettings($presetId)
	{
		return $this->options["filters"][$presetId] ?? null;
	}


	/**
	 * Fetches filter fields from filter settings
	 * @param array $filterSettings
	 * @param array $additionalFields
	 * @return array
	 */
	protected static function fetchFieldsFromFilterSettings($filterSettings = array(), $additionalFields = array())
	{
		$filterFields = array();

		if (is_array($filterSettings))
		{
			if (is_array($filterSettings["fields"]))
			{
				$filterFields = $filterSettings["fields"];
			}

			if (is_array($additionalFields))
			{
				$filterFields = array_merge($filterFields, $additionalFields);
			}
		}

		return $filterFields;
	}


	/**
	 * @param string $key
	 * @return bool
	 */
	public static function isDateField($key = "")
	{
		return is_string($key) && mb_substr($key, -8) === "_datesel";
	}


	/**
	 * Fetches date field values
	 * @param string $key
	 * @param array $filterFields
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function fetchDateFieldValue($key = "", $filterFields = array())
	{
		$date = array();
		$date[$key] = $filterFields[$key];

		$cleanKey = str_replace("_datesel", "", $key);

		self::calcDates($cleanKey, $filterFields, $date);

		if (!isset($date[$cleanKey . "_from"]) && !isset($date[$cleanKey . "_to"]))
		{
			unset($date[$cleanKey . "_datesel"]);
			unset($date[$cleanKey . "_to"]);
			unset($date[$cleanKey . "_from"]);
		}

		return $date;
	}


	/**
	 * Fetches number field values
	 * @param string $key
	 * @param array $filterFields
	 *
	 * @return array
	 */
	public static function fetchNumberFieldValue($key = "", $filterFields = array())
	{
		$number = array();
		$number[$key] = $filterFields[$key];
		$cleanKey = str_replace("_numsel", "", $key);

		if (array_key_exists($cleanKey . "_from", $filterFields))
		{
			$number[$cleanKey . "_from"] = $filterFields[$cleanKey . "_from"];
		}

		if (array_key_exists($cleanKey . "_to", $filterFields))
		{
			$number[$cleanKey . "_to"] = $filterFields[$cleanKey . "_to"];
		}

		if ($number[$cleanKey . "_from"] === "" && $number[$cleanKey . "_to"] === "")
		{
			unset($number[$cleanKey . "_from"]);
			unset($number[$cleanKey . "_to"]);
			unset($number[$cleanKey . "_numsel"]);
		}

		return $number;
	}


	public static function isNumberField($key = "")
	{
		return is_string($key) && mb_substr($key, -7) === "_numsel";
	}


	public static function fetchFieldValuesFromFilterSettings($filterSettings = array(), $additionalFields = array(), $sourceFields = array())
	{
		$filterFields = self::fetchFieldsFromFilterSettings($filterSettings, $additionalFields);
		$resultFields = array();
		foreach ($filterFields as $key => $field)
		{
			$isStrictField = false;

			foreach ($sourceFields as $sourceKey => $sourceField)
			{
				if (isset($sourceField["id"]) && $key === $sourceField["id"] && isset($sourceField["strict"]))
				{
					$isStrictField = true;
				}
			}

			if (($field !== "" && mb_strpos($key, -6) !== "_label") || $isStrictField)
			{
				if (self::isDateField($key))
				{
					$date = self::fetchDateFieldValue($key, $filterFields);
					$resultFields = array_merge($resultFields, $date);
				}

				elseif (self::isNumberField($key))
				{
					$number = self::fetchNumberFieldValue($key, $filterFields);
					$resultFields = array_merge($resultFields, $number);
				}

				elseif (mb_substr($key, -5) !== "_from" && mb_substr($key, -3) !== "_to")
				{
					if  (mb_substr($key, -8) === "_isEmpty")
					{
						$resultFields[substr($key, 0, -8)] = false;
					}
					elseif  (mb_substr($key, -12) === "_hasAnyValue")
					{

						$resultFields['!'.substr($key, 0, -12)] = false;
					}
					else
					{
						$resultFields[$key] = $field;
					}
				}
			}
		}

		return $resultFields;
	}


	/**
	 * @param string $presetId
	 * @return bool
	 */
	public static function isDefaultFilter($presetId = "")
	{
		return $presetId === self::DEFAULT_FILTER;
	}

	/**
	 * Gets current filter values
	 * @param array $sourceFields Filter fields $arParams["FILTER"]
	 * @return array
	 */
	public function getFilter($sourceFields = array())
	{
		$result = array();
		$this->trySetFilterFromRequest($sourceFields);
		$currentPresetId = $this->getCurrentFilterId();

		if (!self::isDefaultFilter($currentPresetId))
		{
			$filterSettings = $this->getFilterSettings($currentPresetId);
			$additionalFields = $this->getAdditionalPresetFields($currentPresetId);
			$fieldsValues = self::fetchFieldValuesFromFilterSettings($filterSettings, $additionalFields, $sourceFields);

			$result = $fieldsValues;
			$searchString = $this->getSearchString();

			if (!empty($result) || $searchString !== "")
			{
				$result["PRESET_ID"] = $currentPresetId;
				$result["FILTER_ID"] = $currentPresetId;
				$result["FILTER_APPLIED"] = true;
				$result["FIND"] = $this->getSearchString();
			}
		}
		return $result;
	}

	/**
	 * Gets current filter values that available for DB seach
	 * @param array $sourceFields Filter fields $arParams["FILTER"]
	 * @return array
	 */
	public function getFilterLogic($sourceFields = array())
	{
		$filter = $this->getFilter($sourceFields);
		$applied = ($filter["FILTER_APPLIED"] ?? false);
		if ($applied === true)
		{
			return Type::getLogicFilter($filter, $sourceFields);
		}
		return [];
	}

	/**
	 * Gets filter search string
	 * @return string
	 */
	public function getSearchString()
	{
		$session = Application::getInstance()->getSession();
		$search = $session["main.ui.filter"][$this->id]["filter_search"] ?? '';

		return is_string($search) ? $search : "";
	}


	/**
	 * Saves filter optionsGet
	 */
	public function save()
	{
		global $USER;

		if ($this->isUseCommonPresets())
		{
			$presets = array(
				"filters" => $this->options["filters"],
				"deleted_presets" => $this->options["deleted_presets"]
			);

			if ($USER->isAuthorized())
			{
				\CUserOptions::setOption("main.ui.filter.presets", $this->getCommonPresetsId(), $presets);
			}
			else
			{
				Application::getInstance()->getSession()["main.ui.filter.presets"][$this->getCommonPresetsId()] = $presets;
			}
		}


		if ($USER->isAuthorized())
		{
			\CUserOptions::setOption("main.ui.filter", $this->getId(), $this->options);
		}
		else
		{
			Application::getInstance()->getSession()["main.ui.filter"][$this->getId()]["options"] = $this->options;
		}
	}


	/** @noinspection PhpUndefinedClassInspection */

	/**
	 * Gets filter options for all users
	 * @return bool|\CDBResult
	 */
	public function getAllUserOptions()
	{
		return \CUserOptions::getList(null, array("CATEGORY" => "main.ui.filter", "NAME" => $this->getId()));
	}


	/**
	 * @return bool
	 */
	public static function isCurrentUserEditOtherSettings()
	{
		global $USER;
		return $USER->CanDoOperation("edit_other_settings");
	}


	/**
	 * Saves filter options for all users
	 */
	public function saveForAll()
	{
		global $USER;

		if (self::isCurrentUserEditOtherSettings())
		{
			$allUserOptions = $this->getAllUserOptions();

			if ($allUserOptions)
			{
				$currentOptions = $this->options;

				$forAllPresets = array();

				foreach ($currentOptions["filters"] as $key => $preset)
				{
					if ($preset["for_all"])
					{
						$forAllPresets[$key] = $preset;
					}
				}

				while ($userOptions = $allUserOptions->fetch())
				{
					$currentUserOptions = unserialize($userOptions["VALUE"], ['allowed_classes' => false]);

					if (is_array($currentUserOptions))
					{
						if (!self::isCommon($userOptions))
						{
							$currentUserOptions["deleted_presets"] = $currentOptions["deleted_presets"];
							$currentUserOptions["filters"] = $forAllPresets;

							if (!$USER->CanDoOperation("edit_other_settings", $userOptions["USER_ID"]))
							{
								$currentUserOptions["default_presets"] = $forAllPresets;
							}
						}

						$this->saveOptionsForUser($currentUserOptions, $userOptions["USER_ID"]);
					}
				}

				$this->saveCommon();
			}

		}
	}


	/**
	 * Checks whether the parameters is common
	 * @param $options
	 * @return bool
	 */
	public static function isCommon($options)
	{
		return isset($options["USER_ID"]) && $options["USER_ID"] == 0;
	}


	/**
	 * Saves options for user with $userId
	 * @param array $options
	 * @param $userId
	 */
	public function saveOptionsForUser($options = array(), $userId)
	{
		if ($this->isUseCommonPresets())
		{
			$presets = array(
				"filters" => $options["filters"],
				"deleted_presets" => $options["deleted_presets"]
			);

			\CUserOptions::SetOption("main.ui.filter.presets", $this->getCommonPresetsId(), $presets, null, $userId);
		}

		$userOptions = \CUserOptions::GetOption("main.ui.filter", $this->getId(), array("filters" => array(), "default_presets" => array()), $userId);

		if (is_array($options["deleted_presets"]))
		{
			foreach ($options["deleted_presets"] as $key => $isDeleted)
			{
				if (array_key_exists($key, $userOptions["filters"]))
				{
					unset($userOptions["filters"][$key]);
				}
			}
		}

		$options["filters"] = array_merge($userOptions["filters"], $options["filters"]);
		\CUserOptions::SetOption("main.ui.filter", $this->getId(), $options, null, $userId);
	}


	/**
	 * Saves current options as common
	 */
	public function saveCommon()
	{
		$presets = array();
		$options = $this->getOptions();

		foreach ($options["filters"] as $key => $preset)
		{
			if ($preset["for_all"])
			{
				$presets[$key] = $preset;
			}
		}

		$options["filters"] = $presets;

		\CUserOptions::setOption("main.ui.filter.common", $this->id, $options, true);
	}


	/**
	 * Sets filter preset rows
	 * @param string $presetId
	 * @param $rows
	 */
	public function setFilterRows($presetId = "", $rows)
	{
		$aColsTmp = explode(",", $rows);
		$aCols = array();
		foreach($aColsTmp as $col)
			if(($col = trim($col)) <> "")
				$aCols[] = $col;
		if($presetId <> '')
			$this->options["filters"][$presetId]["filter_rows"] = implode(",", $aCols);
		else
			$this->options["filter_rows"] = implode(",", $aCols);
	}

	public function removeRowFromPreset(string $presetId, string $rowName): bool
	{
		$rowsString = $this->options["filters"][$presetId]["filter_rows"] ?? '';
		if ($rowsString === '')
		{
			return false;
		}
		$rows = explode(",", $rowsString);
		$pos = array_search($rowName, $rows,true);
		if ($pos !== false)
		{
			unset($rows[$pos]);
			$this->options["filters"][$presetId]["filter_rows"] = implode(",", $rows);

			return true;
		}

		return false;
	}

	/**
	 * Restores filter options to default
	 * @param array $settings
	 */
	public function restore($settings = array())
	{
		if (!empty($settings))
		{
			foreach ($settings as $key => $preset)
			{
				$this->setFilterSettings($key, $preset, false);

				if (array_key_exists($key, $this->options["deleted_presets"]))
				{
					unset($this->options["deleted_presets"][$key]);
				}
			}

			$this->options["default"] = self::findDefaultPresetId($this->options["default_presets"]);
			$this->options["use_pin_preset"] = true;
			$this->options["filter"] = $this->options["default"];
			unset(Application::getInstance()->getSession()["main.ui.filter"][$this->id]["filter"]);
		}
	}


	/**
	 * @param array $settings
	 */
	public function setFilterSettingsArray($settings = array())
	{
		if (!empty($settings))
		{
			foreach ($settings as $key => $preset)
			{
				if ($key !== "current_preset" && $key !== "common_presets_id")
				{
					$this->setFilterSettings($key, $preset, false);
				}
			}

			$this->options["filter"] = $settings["current_preset"];
			$request = $this->getRequest();

			if (
				isset($request["params"]["forAll"])
				&& (
					$request["params"]["forAll"] === "true"
					|| $request["params"]["forAll"] === true
				)
			)
			{
				$this->saveForAll();
			}
		}
	}


	/**
	 * @param string $presetId
	 * @param $settings
	 * @param bool $currentPreset
	 * @param bool $useRequest
	 */
	public function setFilterSettings($presetId, $settings, $currentPreset = true, $useRequest = true)
	{
		if (!empty($presetId))
		{
			if ($currentPreset)
			{
				$request = $this->getRequest();
				$params = $request->getPost('params');
				$params = is_array($params) ? $params : [];

				$isApplyFilter = (
					(mb_strtoupper($request->get("apply_filter")) == "Y") ||
					(isset($params["apply_filter"]) && mb_strtoupper($params["apply_filter"]) == "Y")
				);
				$isClearFilter = (
					(mb_strtoupper($request->get("clear_filter")) == "Y") ||
					(isset($params["clear_filter"]) && mb_strtoupper($params["clear_filter"]) == "Y")
				);
				$isWithPreset = (
					(mb_strtoupper($request->get("with_preset")) == "Y") ||
					(isset($params["with_preset"]) && mb_strtoupper($params["with_preset"]) == "Y")
				);
				$currentPresetId = $this->getCurrentFilterId();

				if (
					($useRequest
						&& ($isApplyFilter || $isClearFilter)
						&& (!$isWithPreset || $currentPresetId === static::DEFAULT_FILTER)
					)
					|| $useRequest === false
				)
				{
					Application::getInstance()->getSession()["main.ui.filter"][$this->id]["filter"] = $presetId;
					Application::getInstance()->getSession()["main.ui.filter"][$this->id]["isSetOutside"] = $params["isSetOutside"] ?? false;
				}

			}

			if (!isset($this->options["filters"][$presetId]) || !is_array($this->options["filters"][$presetId]))
			{
				$this->options["filters"][$presetId] = array();
			}

			if (isset($settings["name"]) && !empty($settings["name"]))
			{
				$this->options["filters"][$presetId]["name"] = $settings["name"];
			}

			if (isset($settings["for_all"]))
			{
				$this->options["filters"][$presetId]["for_all"] = $settings["for_all"] === "true";
			}

			if (isset($settings["sort"]) && is_numeric($settings["sort"]))
			{
				$this->options["filters"][$presetId]["sort"] = $settings["sort"];
			}

			if (isset($settings["fields"]) && is_array($settings["fields"]))
			{
				if (array_key_exists("FIND", $settings["fields"]))
				{
					Application::getInstance()->getSession()["main.ui.filter"][$this->id]["filter_search"] = $settings["fields"]["FIND"];
					unset($settings["fields"]["FIND"]);
				}

				if ($presetId == "default_filter")
				{
					$this->options["filters"][$presetId]["fields"] = array();
				}
				else
				{
					$this->options["filters"][$presetId]["fields"] = $settings["fields"];

					$additionalFields = isset($settings["additional"]) && is_array($settings["additional"]) ? $settings["additional"] : array();
					$this->setAdditionalPresetFields($presetId, $additionalFields);
				}
			}

			if (!isset($settings["fields"]) && isset($settings["clear_filter"]) && $settings["clear_filter"] === 'Y')
			{
				$this->options["filters"][$presetId]["fields"] = array();
			}

			if (isset($settings["name"]) && !empty($settings["name"]))
			{
				$this->options["filters"][$presetId]["name"] = $settings["name"];
			}

			if (isset($settings["rows"]))
			{
				$rows = $settings["rows"];
				if (is_array($rows))
				{
					$result = array();
					foreach($rows as $id)
					{
						$id = trim($id);
						if($id !== "")
						{
							$result[] = $id;
						}
					}
					$this->options["filters"][$presetId]["filter_rows"] = implode(",", $result);
				}
				elseif (is_string($settings["rows"]))
				{
					$this->options["filters"][$presetId]["filter_rows"] = $settings["rows"];
				}
			}
		}
	}


	/**
	 * Deletes preset by preset id
	 * @param string $presetId
	 * @param bool $isDefault
	 */
	public function deleteFilter($presetId, $isDefault = false)
	{
		if ($isDefault)
		{
			$this->options["deleted_presets"][$presetId] = true;
		}

		unset($this->options["filters"][$presetId]);
	}


	/**
	 * Checks preset is deleted
	 * @param string $presetId
	 * @return bool
	 */
	public function isDeletedPreset($presetId)
	{
		return array_key_exists($presetId, $this->options["deleted_presets"]);
	}

	/**
	 * Setup Default Filter Settings
	 * @param array $fields Default Filter Fields.
	 * @param array $rows Default Filter Rows.
	 */
	public function setupDefaultFilter(array $fields, array $rows)
	{
		$this->setFilterSettings("tmp_filter", array("fields" => $fields, "rows" => $rows), true, false);
		$this->save();
	}

	/**
	 * Calculate date value
	 *
	 * @param string $fieldId
	 * @param array $source Source values
	 * @param array $result Result values
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function calcDates($fieldId, $source, &$result)
	{
		switch($source[$fieldId."_datesel"])
		{
			case DateType::YESTERDAY :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::YESTERDAY;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_from"] = $dateTime->offset("- 1 days");
				$result[$fieldId."_to"] = $dateTime->offset("- 1 second");
				break;
			}

			case DateType::CURRENT_DAY :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::CURRENT_DAY;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_from"] = $dateTime->toString();
				$result[$fieldId."_to"] = $dateTime->offset("+ 1 days - 1 second");
				break;
			}

			case DateType::TOMORROW :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::TOMORROW;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_from"] = $dateTime->offset("+ 1 days");
				$result[$fieldId."_to"] = $dateTime->offset("+ 2 days - 1 second");
				break;
			}

			case DateType::CURRENT_WEEK :
			{
				$dateTime = Filter\DateTimeFactory::createCurrentWeekMonday();

				$result[$fieldId."_datesel"] = DateType::CURRENT_WEEK;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_from"] = $dateTime->toString();
				$result[$fieldId."_to"] = $dateTime->offset("7 days - 1 second");
				break;
			}

			case DateType::NEXT_WEEK :
			{
				$dateTime = Filter\DateTimeFactory::createNextWeekMonday();

				$result[$fieldId."_datesel"] = DateType::NEXT_WEEK;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_from"] = $dateTime->toString();
				$result[$fieldId."_to"] = $dateTime->offset("7 days - 1 second");
				break;
			}

			case DateType::CURRENT_MONTH :
			{
				$dateTime = Filter\DateTimeFactory::createFirstDayOfCurrentMonth();

				$result[$fieldId."_datesel"] = DateType::CURRENT_MONTH;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_from"] = $dateTime->toString();
				$result[$fieldId."_to"] = $dateTime->offset("1 month - 1 second");
				break;
			}

			case DateType::NEXT_MONTH :
			{
				$dateTime = Filter\DateTimeFactory::createFirstDayOfNextMonth();

				$result[$fieldId."_datesel"] = DateType::NEXT_MONTH;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_from"] = $dateTime->toString();
				$result[$fieldId."_to"] = $dateTime->offset("1 month - 1 second");
				break;
			}

			case DateType::CURRENT_QUARTER :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::QUARTER;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_from"] = $dateTime->quarterStart();
				$result[$fieldId."_to"] = $dateTime->quarterEnd();
				break;
			}

			case DateType::LAST_7_DAYS :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::LAST_7_DAYS;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_from"] = $dateTime->offset("- 7 days");
				$result[$fieldId."_to"] = $dateTime->offset("1 days - 1 second");
				break;
			}

			case DateType::LAST_30_DAYS :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::LAST_30_DAYS;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_from"] = $dateTime->offset("- 30 days");
				$result[$fieldId."_to"] = $dateTime->offset("1 days - 1 second");
				break;
			}

			case DateType::LAST_60_DAYS :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::LAST_60_DAYS;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_from"] = $dateTime->offset("- 60 days");
				$result[$fieldId."_to"] = $dateTime->offset("1 days - 1 second");
				break;
			}

			case DateType::LAST_90_DAYS :
			{
				$dateTime = Filter\DateTimeFactory::createToday();

				$result[$fieldId."_datesel"] = DateType::LAST_90_DAYS;
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_quarter"] = $dateTime->quarter();
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_from"] = $dateTime->offset("- 90 days");
				$result[$fieldId."_to"] = $dateTime->offset("1 days - 1 second");
				break;
			}

			case DateType::MONTH :
			{
				$month = $source[$fieldId."_month"];
				$year = $source[$fieldId."_year"];

				if (!empty($month) && !empty($year))
				{
					$dateTime = new Filter\DateTime(mktime(0, 0, 0, $month, 1, $year));

					$result[$fieldId."_datesel"] = DateType::MONTH;
					$result[$fieldId."_month"] = $dateTime->month();
					$result[$fieldId."_quarter"] = $dateTime->quarter();
					$result[$fieldId."_year"] = $dateTime->year();
					$result[$fieldId."_from"] = $dateTime->toString();
					$result[$fieldId."_to"] = $dateTime->offset("1 month - 1 second");
				}

				break;
			}

			case DateType::NEXT_DAYS :
			{
				if (is_numeric($source[$fieldId."_days"]))
				{
					$dateTime = Filter\DateTimeFactory::createToday();
					$days = (int) $source[$fieldId."_days"];
					$days = $days > 0 ? ($days + 1) : $days;

					$result[$fieldId."_datesel"] = DateType::NEXT_DAYS;
					$result[$fieldId."_month"] = $dateTime->month();
					$result[$fieldId."_quarter"] = $dateTime->quarter();
					$result[$fieldId."_days"] = $source[$fieldId."_days"];
					$result[$fieldId."_year"] = $dateTime->year();
					$result[$fieldId."_from"] = $dateTime->offset("1 days");
					$result[$fieldId."_to"] = $dateTime->offset($days." days - 1 second");
				}

				break;
			}

			case DateType::PREV_DAYS :
			{
				if (is_numeric($source[$fieldId."_days"]))
				{
					$dateTime = Filter\DateTimeFactory::createToday();
					$days = (int) $source[$fieldId."_days"];
					$days = max($days, 0);

					$result[$fieldId."_datesel"] = DateType::PREV_DAYS;
					$result[$fieldId."_month"] = $dateTime->month();
					$result[$fieldId."_quarter"] = $dateTime->quarter();
					$result[$fieldId."_days"] = $source[$fieldId."_days"];
					$result[$fieldId."_year"] = $dateTime->year();
					$result[$fieldId."_from"] = $dateTime->offset("- ".$days." days");
					$result[$fieldId."_to"] = $dateTime->offset("1 days -1 second");
				}

				break;
			}

			case AdditionalDateType::PREV_DAY :
			{
				if (is_numeric($source[$fieldId."_days"]))
				{
					$dateTime = Filter\DateTimeFactory::createToday();
					$days = (int) $source[$fieldId."_days"];

					$result[$fieldId."_days"] = $source[$fieldId."_days"];
					$result[$fieldId."_from"] = $dateTime->offset(-$days." days");
					$result[$fieldId."_to"] = $dateTime->offset(-($days-1)." days -1 second");
				}

				break;
			}

			case AdditionalDateType::NEXT_DAY :
			{
				if (is_numeric($source[$fieldId."_days"]))
				{
					$dateTime = Filter\DateTimeFactory::createToday();
					$days = (int) $source[$fieldId."_days"];

					$result[$fieldId."_days"] = $source[$fieldId."_days"];
					$result[$fieldId."_from"] = $dateTime->offset($days." days");
					$result[$fieldId."_to"] = $dateTime->offset(($days+1)." days -1 second");
				}

				break;
			}

			case AdditionalDateType::MORE_THAN_DAYS_AGO :
			{
				if (is_numeric($source[$fieldId."_days"]))
				{
					$dateTime = Filter\DateTimeFactory::createToday();
					$days = (int) $source[$fieldId."_days"];

					$result[$fieldId."_days"] = $source[$fieldId."_days"];
					$result[$fieldId."_from"] = $dateTime->offset(-($days+1)." days");
					$result[$fieldId."_to"] = $dateTime->offset(-$days." days -1 second");
				}

				break;
			}

			case AdditionalDateType::AFTER_DAYS :
				{
					if (is_numeric($source[$fieldId."_days"]))
					{
						$dateTime = Filter\DateTimeFactory::createToday();
						$days = (int) $source[$fieldId."_days"];

						$result[$fieldId."_days"] = $source[$fieldId."_days"];
						$result[$fieldId."_from"] = $dateTime->offset($days." days");
						$result[$fieldId."_to"] = $dateTime->offset(($days+1)." days -1 second");
					}

					break;
				}


			case DateType::QUARTER :
			{
				$quarter = $source[$fieldId."_quarter"];
				$year = $source[$fieldId."_year"];

				if (!empty($quarter) && !empty($year))
				{
					$dateTime = new Filter\DateTime(MakeTimeStamp(Quarter::getStartDate($quarter, $year)));

					$result[$fieldId."_datesel"] = DateType::QUARTER;
					$result[$fieldId."_quarter"] = $dateTime->quarter();
					$result[$fieldId."_year"] = $dateTime->year();
					$result[$fieldId."_month"] = $dateTime->month();
					$result[$fieldId."_from"] = $dateTime->quarterStart();
					$result[$fieldId."_to"] = $dateTime->quarterEnd();
				}

				break;
			}

			case DateType::YEAR :
			{
				$year = $source[$fieldId."_year"];

				if (!empty($year))
				{
					$dateTime = new Filter\DateTime(mktime(0, 0, 0, 1, 1, $year));

					$result[$fieldId."_datesel"] = DateType::YEAR;
					$result[$fieldId."_year"] = $dateTime->year();
					$result[$fieldId."_from"] = $dateTime->toString();
					$result[$fieldId."_to"] = $dateTime->offset("1 year - 1 second");
				}

				break;
			}

			case DateType::EXACT :
			{
				$sourceDate = $source[$fieldId."_from"];

				if (!empty($sourceDate))
				{
					$date = new Date($sourceDate);
					$dateTime = new Filter\DateTime(MakeTimeStamp($sourceDate));

					$result[$fieldId."_datesel"] = DateType::EXACT;

					if ($dateTime->getTimestamp() > $date->getTimestamp())
					{
						$result[$fieldId."_from"] = $dateTime->toString();
						$result[$fieldId."_to"] = $dateTime->toString();
					}
					else
					{
						$result[$fieldId."_from"] = $dateTime->toString();
						$result[$fieldId."_to"] = $dateTime->offset("1 days - 1 second");
					}
				}

				break;
			}

			case DateType::LAST_WEEK :
			{
				$dateTime = Filter\DateTimeFactory::createLastWeekMonday();

				$result[$fieldId."_datesel"] = DateType::LAST_WEEK;
				$result[$fieldId."_from"] = $dateTime->toString();
				$result[$fieldId."_to"] = $dateTime->offset("7 days - 1 second");
				break;
			}

			case DateType::LAST_MONTH :
			{
				$dateTime = Filter\DateTimeFactory::createFirstDayOfLastMonth();

				$result[$fieldId."_datesel"] = DateType::LAST_MONTH;
				$result[$fieldId."_year"] = $dateTime->year();
				$result[$fieldId."_month"] = $dateTime->month();
				$result[$fieldId."_from"] = $dateTime->toString();
				$result[$fieldId."_to"] = $dateTime->offset("1 month - 1 second");
				break;
			}

			case DateType::RANGE :
			{
				$startSourceDate = $source[$fieldId."_from"];
				$endSourceDate = $source[$fieldId."_to"];

				$result[$fieldId."_from"] = "";
				$result[$fieldId."_to"] = "";

				if (!empty($startSourceDate))
				{
					$startDateTime = new Filter\DateTime(MakeTimeStamp($startSourceDate));

					$result[$fieldId."_datesel"] = DateType::RANGE;
					$result[$fieldId."_from"] = $startDateTime->toString();
				}

				if (!empty($endSourceDate))
				{
					$endDate = Date::createFromTimestamp(MakeTimeStamp($endSourceDate));
					$endDateTime = new Filter\DateTime(MakeTimeStamp($endSourceDate));

					$result[$fieldId."_datesel"] = DateType::RANGE;

					if ($endDateTime->getTimestamp() > $endDate->getTimestamp())
					{
						$result[$fieldId."_to"] = $endDateTime->toString();
					}
					else
					{
						$result[$fieldId."_to"] = $endDateTime->offset("1 days - 1 second");
					}
				}

				break;
			}
		}
	}


	/**
	 * Resets current applied filter
	 */
	public function reset()
	{
		Application::getInstance()->getSession()["main.ui.filter"][$this->id] = null;
	}


	/**
	 * Destroys this filter options
	 */
	public function destroy()
	{
		static::destroyById($this->getId());
	}


	/**
	 * Destroys filter options by filter id
	 * @param $filterId
	 */
	public static function destroyById($filterId)
	{
		\CUserOptions::deleteOption("main.ui.filter", $filterId);
		\CUserOptions::deleteOption("main.ui.filter.presets", $filterId);
		unset(Application::getInstance()->getSession()["main.ui.filter"][$filterId]);
		unset(Application::getInstance()->getSession()["main.ui.filter.presets"][$filterId]);
	}


	public static function getRowsFromFields($fields = array())
	{
		$rows = array();

		foreach ($fields as $key => $field)
		{
			$rows[] = str_replace(
				array(
					"_datesel",
					"_numsel",
					"_from",
					"_to",
					"_days",
					"_month",
					"_quarter",
					"_id",
					"_year",
					"_name",
					"_label",
					"_value",
					"_days",
					"_months",
					"_years",
					"_isEmpty",
					"_hasAnyValue",
				),
				"",
				$key
			);
		}

		return array_unique($rows);
	}


	/**
	 * Fetches preset fields list
	 * @param array $preset
	 * @return array
	 */
	public static function fetchPresetFields($preset)
	{
		if (isset($preset["filter_rows"]) && is_string($preset["filter_rows"]))
		{
			$fields = explode(",", $preset["filter_rows"]);
			return array_unique($fields);
		}

		return static::getRowsFromFields($preset["fields"]);
	}


	/**
	 * Gets used fields
	 * @return array
	 */
	public function getUsedFields()
	{
		$fields = array();

		// Fetch fields from user presets
		foreach ($this->getPresets() as $key => $preset)
		{
			$presetFields = static::fetchPresetFields($preset);
			$fields = array_merge($fields, $presetFields);
		}

		$defaultPresetFieldsOrder = [];
		// Fetch fields from default presets
		foreach ($this->getDefaultPresets() as $key => $preset)
		{
			$presetFields = static::fetchPresetFields($preset);
			$fields = array_merge($fields, $presetFields);
			if (isset($preset['default']))
			{
				$defaultPresetFieldsOrder = $presetFields;
			}
		}

		$fields = array_unique($fields);

		if (!empty($defaultPresetFieldsOrder))
		{
			// fields order should be defined by default filter preset
			$fields = array_unique(array_merge($defaultPresetFieldsOrder, $fields));
		}

		return $fields;
	}

	/**
	 * @return string|null
	 */
	public function getCurrentFilterPresetId(): ?string
	{
		return $this->currentFilterPresetId;
	}

	/**
	 * @param string|null $presetId
	 * @return Options
	 */
	public function setCurrentFilterPresetId(?string $presetId): Options
	{
		$this->currentFilterPresetId = $presetId;
		return $this;
	}
}