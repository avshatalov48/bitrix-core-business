<?

namespace Bitrix\Main\UI\Filter;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;


Loc::loadMessages(__FILE__);

/**
 * Class Field
 * @package Bitrix\Main\UI\Filter
 */
class Field
{
	/**
	 * Prepares data of string field
	 * @param string $name
	 * @param string $defaultValue
	 * @param string $label
	 * @param string $placeholder
	 * @return array
	 */
	public static function string($name, $defaultValue = "", $label = "", $placeholder = "")
	{
		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::STRING,
			"NAME" => $name,
			"VALUE" => $defaultValue,
			"LABEL" => $label,
			"PLACEHOLDER" => $placeholder
		);

		return $field;
	}


	/**
	 * Prepares data of custom field
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param string $placeholder
	 * @param bool $style
	 * @return array
	 */
	public static function custom($name, $value, $label = "", $placeholder = "", $style = false)
	{
		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::CUSTOM,
			"NAME" => $name,
			"VALUE" => HtmlFilter::encode($value),
			"PLACEHOLDER" => $placeholder,
			"LABEL" => $label,
			"ENABLE_STYLE" => $style
		);

		return $field;
	}


	/**
	 * Prepares data of custom_entity field
	 * @param string $name
	 * @param string $label
	 * @param string $placeholder
	 * @param bool $multiple
	 * @return array
	 */
	public static function customEntity($name, $label = "", $placeholder = "", $multiple = false)
	{
		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::CUSTOM_ENTITY,
			"NAME" => $name,
			"LABEL" => $label,
			"VALUES" => array(
				"_label" => "",
				"_value" => ""
			),
			"MULTIPLE" => $multiple,
			"PLACEHOLDER" => $placeholder
		);

		return $field;
	}


	/**
	 * Prepares data of date field
	 * @param string $name
	 * @param string $type
	 * @param array $values
	 * @param string $label
	 * @param string $placeholder
	 * @param bool $enableTime
	 * @param array $exclude
	 * @param array $include
	 * @param boolean $allowYearsSwithcer
	 * @param array $messages
	 * @return array
	 */
	public static function date(
		$name,
		$type = DateType::NONE,
		$values = [],
		$label = "",
		$placeholder = "",
		$enableTime = false,
		$exclude = [],
		$include = [],
		$allowYearsSwithcer = false,
		$messages = []
	)
	{
		if (!is_bool($enableTime))
		{
			$enableTime = false;
		}

		if (!is_array($exclude))
		{
			$exclude = array();
		}

		$selectParams = array("isMulti" => false);

		if (empty($values))
		{
			$values = array(
				"_from" => "",
				"_to" => "",
				"_days" => "",
				"_month" => "",
				"_quarter" => "",
				"_year" => ""
			);
		}

		$sourceMonths = range(1, 12);
		$date = new Date();
		$currentMonthNumber = $date->format("n");
		$months = array();
		$currentMonthType = array();

		foreach($sourceMonths as $key => $month)
		{
			$months[] = array(
				"VALUE" => $month,
				"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_MONTH_".$month)
			);

			if ($currentMonthNumber == $month)
			{
				$currentMonthType = array(
					"VALUE" => $month,
					"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_MONTH_".$month)
				);
			}
		}


		$sourceQuarters = range(1, 4);
		$quarters = array();
		$quarterNumber = Quarter::getCurrent();
		$currentQuarterType = array();

		foreach($sourceQuarters as $key => $quarter)
		{
			$quarters[] = array(
				"VALUE" => $quarter,
				"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_QUARTER_".$quarter)
			);

			if ($quarterNumber == $quarter)
			{
				$currentQuarterType = array(
					"VALUE" => $quarter,
					"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_QUARTER_".$quarter)
				);
			}
		}

		$sourceSubtypes = DateType::getList();
		$subtypes = array();
		$subtypeType = array();

		foreach ($sourceSubtypes as $key => $subtype)
		{
			if (!in_array($subtype, $exclude))
			{
				$subtypes[] = array(
					"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_SUBTYPE_".$subtype),
					"VALUE" => $subtype
				);

				if ($subtype == $type)
				{
					$subtypeType = array(
						"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_SUBTYPE_".$subtype),
						"VALUE" => $subtype
					);
				}
			}
		}

		if (is_array($include))
		{
			foreach ($include as $key => $item)
			{
				if ($item === AdditionalDateType::CUSTOM_DATE)
				{
					$subtypes[] = array(
						"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_SUBTYPE_".$item),
						"VALUE" => AdditionalDateType::CUSTOM_DATE,
						"DECL" => static::customDate(array("id" => $name, "name" => $label))
					);
				}

				if ($item === AdditionalDateType::NEXT_DAY ||
					$item === AdditionalDateType::PREV_DAY ||
					$item === AdditionalDateType::MORE_THAN_DAYS_AGO ||
					$item === AdditionalDateType::AFTER_DAYS)
				{
					$subtypes[] = array(
						"NAME" => static::getMessage($messages, "MAIN_UI_FILTER_FIELD_SUBTYPE_".$item),
						"VALUE" => $item
					);
				}
			}
		}

		$currentYear = (int) $date->format("Y");
		$sourceYears = range($currentYear+5, $currentYear-20);
		$years = array();
		$currentYearType = array();

		foreach ($sourceYears as $key => $year)
		{
			$years[] = array(
				"NAME" => $year,
				"VALUE" => $year
			);

			if ($year == $currentYear)
			{
				$currentYearType = array(
					"NAME" => $year,
					"VALUE" => $year
				);
			}
		}

		$yearsSwitcher = static::select(
			$name."_allow_year",
			array(
				array(
					"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_YEARS_SWITCHER_YES"),
					"VALUE" => 1
				),
				array(
					"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_YEARS_SWITCHER_NO"),
					"VALUE" => 0
				)
			),
			array()
		);

		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::DATE,
			"NAME" => $name,
			"SUB_TYPE" => $subtypeType,
			"SUB_TYPES" => $subtypes,
			"MONTH" => $currentMonthType,
			"MONTHS" => $months,
			"QUARTER" => $currentQuarterType,
			"QUARTERS" => $quarters,
			"YEAR" => $currentYearType,
			"YEARS" => $years,
			"VALUES" => $values,
			"PLACEHOLDER" => $placeholder,
			"LABEL" => $label,
			"ENABLE_TIME" => $enableTime,
			"SELECT_PARAMS" => $selectParams,
			"YEARS_SWITCHER" => $allowYearsSwithcer ? $yearsSwitcher : null
		);

		return $field;
	}


	/**
	 * Prepares data of number field
	 * @param string $name
	 * @param string $type
	 * @param array $values
	 * @param string $label
	 * @param string $placeholder
	 * @return array
	 */
	public static function number($name, $type = NumberType::SINGLE, $values = array(), $label = "", $placeholder = "")
	{
		$selectParams = array("isMulti" => false);

		if (empty($values))
		{
			$values = array(
				"_from" => "",
				"_to" => ""
			);
		}

		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::NUMBER,
			"NAME" => $name,
			"SUB_TYPE" => $type,
			"VALUES" => $values,
			"LABEL" => $label,
			"PLACEHOLDER" => $placeholder,
			"SELECT_PARAMS" => $selectParams
		);

		return $field;
	}


	/**
	 * Prepares data of select field
	 * @param string $name
	 * @param array $items
	 * @param array $defaultValue
	 * @param string $label
	 * @param string $placeholder
	 * @return array
	 */
	public static function select($name, $items, Array $defaultValue = array(), $label = "", $placeholder = "")
	{
		if (empty($defaultValue) && count($items))
		{
			$defaultValue["NAME"] = $items[0]["NAME"];
			$defaultValue["VALUE"] = $items[0]["VALUE"];
		}

		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::SELECT,
			"NAME" => $name,
			"VALUE" => $defaultValue,
			"PLACEHOLDER" => $placeholder,
			"LABEL" => $label,
			"ITEMS" => $items,
			"PARAMS" => array("isMulti" => false)
		);

		return $field;
	}


	/**
	 * Prepares data of multiselect field
	 * @param string $name
	 * @param array $items
	 * @param array $defaultValues
	 * @param string $label
	 * @param string $placeholder
	 * @return array
	 */
	public static function multiSelect($name, $items, $defaultValues = array(), $label = "", $placeholder = "")
	{
		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::MULTI_SELECT,
			"NAME" => $name,
			"VALUE" => $defaultValues,
			"PLACEHOLDER" => $placeholder,
			"LABEL" => $label,
			"ITEMS" => $items,
			"PARAMS" => array("isMulti" => true)
		);

		return $field;
	}


	public static function customDate($options = array())
	{
		$defaultValues = array(
			"days" => array(),
			"months" => array(),
			"years" => array()
		);

		$days = static::getDaysList();
		$daysDate = new Date();
		$today = (int) $daysDate->format("d");
		$yesterday = (int) $daysDate->add("-1 days")->format("d");
		$tomorrow = (int) $daysDate->add("2 days")->format("d");
		$additionalDays = array(
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_TODAY"),
				"VALUE" => $today
			),
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_YESTERDAY"),
				"VALUE" => $yesterday
			),
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_TOMORROW"),
				"VALUE" => $tomorrow
			),
			array(
				"SEPARATOR" => true
			)
		);
		$days = array_merge($additionalDays, $days);

		$months = static::getMonthsList();
		$monthsDate = new Date();
		$currentMonth = (int) $monthsDate->format("n");
		$lastMonth = (int) $monthsDate->add("-1 month")->format("n");
		$nextMonth = (int) $monthsDate->add("2 month")->format("n");
		$additionalMonths = array(
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_CURRENT_MONTH"),
				"VALUE" => $currentMonth
			),
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_LAST_MONTH"),
				"VALUE" => $lastMonth
			),
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_NEXT_MONTH"),
				"VALUE" => $nextMonth
			),
			array(
				"SEPARATOR" => true
			)
		);
		$months = array_merge($additionalMonths, $months);

		$years = static::getYearsList();
		$yearsDate = new Date();
		$currentYear = (int) $yearsDate->format("Y");
		$lastYear = (int) $yearsDate->add("-1 year")->format("Y");
		$nextYear = (int) $yearsDate->add("2 year")->format("Y");
		$additionalYears = array(
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_CURRENT_YEAR"),
				"VALUE" => $currentYear
			),
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_LAST_YEAR"),
				"VALUE" => $lastYear
			),
			array(
				"NAME" => Loc::getMessage("MAIN_UI_FILTER_FIELD_SUBTYPE_CUSTOM_DATE_NEXT_YEAR"),
				"VALUE" => $nextYear
			),
			array(
				"SEPARATOR" => true
			)
		);
		$years = array_merge($additionalYears, $years);

		return array(
			"ID" => "field_".$options["id"],
			"TYPE" => Type::CUSTOM_DATE,
			"NAME" => $options["id"],
			"VALUE" => $defaultValues,
			"LABEL" => $options["name"],
			"DAYS" => $days,
			"MONTHS" => $months,
			"YEARS" => $years,
			"DAYS_PLACEHOLDER" => Loc::getMessage("MAIN_UI_FILTER_FIELD_DAYS"),
			"MONTHS_PLACEHOLDER" => Loc::getMessage("MAIN_UI_FILTER_FIELD_MONTHS"),
			"YEARS_PLACEHOLDER" => Loc::getMessage("MAIN_UI_FILTER_FIELD_YEARS")
		);
	}


	/**
	 * Gets months list
	 * @return array
	 */
	protected static function getMonthsList()
	{
		$months = array();

		foreach(range(1, 12) as $key => $month)
		{
			$months[] = array(
				"VALUE" => $month,
				"NAME" => (string) Loc::getMessage("MAIN_UI_FILTER_FIELD_MONTH_".$month)
			);
		}

		return $months;
	}


	/**
	 * Gets years list
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function getYearsList()
	{
		$date = new Date();
		$currentYear = (int) $date->format("Y");
		$sourceYears = range(($currentYear+5), ($currentYear-95));
		$years = array();

		foreach ($sourceYears as $key => $year)
		{
			$years[] = array(
				"NAME" => (string) $year,
				"VALUE" => $year
			);
		}

		return $years;
	}


	/**
	 * Gets days list
	 * @return array
	 */
	protected static function getDaysList()
	{
		$days = array();

		foreach(range(1, 31) as $key => $day)
		{
			$days[] = array(
				"VALUE" => $day,
				"NAME" => (string) $day
			);
		}

		return $days;
	}

	/**
	 * Prepares data of user field
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param string $placeholder
	 * @return array
	 */
	public static function destSelector($name, $label = "", $placeholder = "", $multiple = false, $params = array(), $lightweight = false)
	{
		\CJSCore::init(array('socnetlogdest'));

		global $APPLICATION;

		$field = array(
			"ID" => "field_".$name,
			"TYPE" => Type::DEST_SELECTOR,
			"NAME" => $name,
			"LABEL" => $label,
			"VALUES" => array(
				"_label" => "",
				"_value" => ""
			),
			"MULTIPLE" => $multiple,
			"PLACEHOLDER" => $placeholder
		);

		if (!$lightweight)
		{
			ob_start();
			$optionsList = array(
				'multiple' => ($multiple ? 'Y' : 'N'),
				'eventInit' => 'BX.Filter.DestinationSelector:openInit',
				'eventOpen' => 'BX.Filter.DestinationSelector:open',
				'context' => (isset($params['context']) ? $params['context'] : 'FILTER_'.$name),
				'useSearch' => 'N',
				'userNameTemplate' => \CUtil::jSEscape(\CSite::getNameFormat()),
				'useClientDatabase' => 'Y',
				'allowEmailInvitation' => (isset($params['allowEmailInvitation']) && $params['allowEmailInvitation'] == 'Y' ? 'Y' : 'N'),
				'enableLast' => 'Y',
				'enableUsers' => (!isset($params['enableUsers']) || $params['enableUsers'] != 'N' ? 'Y' : 'N'),
				'enableDepartments' => (!isset($params['enableDepartments']) || $params['enableDepartments'] != 'N' ? 'Y' : 'N'),
				'enableSonetgroups' => (isset($params['enableSonetgroups']) && $params['enableSonetgroups'] == 'Y' ? 'Y' : 'N'),
				'departmentSelectDisable' => (isset($params['departmentSelectDisable']) && $params['departmentSelectDisable'] == 'Y' ? 'Y' : 'N'),
				'allowAddUser' => 'N',
				'allowAddCrmContact' => 'N',
				'allowAddSocNetGroup' => 'N',
				'allowSearchEmailUsers' => (isset($params['allowSearchEmailUsers']) && $params['allowSearchEmailUsers'] == 'Y' ? 'Y' : 'N'),
				'allowSearchCrmEmailUsers' => 'N',
				'allowSearchNetworkUsers' => 'N',
				'allowSonetGroupsAjaxSearchFeatures' => 'N',
				'useNewCallback' => 'Y',
				'enableAll' => (isset($params['enableAll']) && $params['enableAll'] == 'Y' ? 'Y' : 'N'),
				'enableEmpty' => (isset($params['enableEmpty']) && $params['enableEmpty'] == 'Y' ? 'Y' : 'N'),
				'enableProjects' => (isset($params['enableProjects']) && $params['enableProjects'] == 'Y' ? 'Y' : 'N'),
				'focusInputOnSelectItem' => 'N',
				'focusInputOnSwitchTab' => 'N'
			);

			if (!empty($params['contextCode']))
			{
				$optionsList['contextCode'] = $params['contextCode'];
			}

			$APPLICATION->includeComponent(
				"bitrix:main.ui.selector",
				".default",
				array(
					'API_VERSION' => (!empty($params['apiVersion']) && intval($params['apiVersion']) >= 2 ? intval($params['apiVersion']) : 2),
					'ID' => $name,
					'ITEMS_SELECTED' => array(),
					'CALLBACK' => array(
						'select' => 'BX.Filter.DestinationSelectorManager.onSelect.bind(null, \''.(isset($params['isNumeric']) && $params['isNumeric'] == 'Y' ? 'Y' : 'N').'\', \''.(isset($params['prefix']) ? $params['prefix'] : '').'\')',
						'unSelect' => '',
						'openDialog' => 'BX.Filter.DestinationSelectorManager.onDialogOpen',
						'closeDialog' => 'BX.Filter.DestinationSelectorManager.onDialogClose',
						'openSearch' => ''
					),
					'OPTIONS' => $optionsList
				),
				false,
				array("HIDE_ICONS" => "Y")
			);

			$field["HTML"] = ob_get_clean();
		}

		return $field;
	}

	protected static function getMessage($messages, $messageId)
	{
		if (is_array($messages) && array_key_exists($messageId, $messages))
		{
			return $messages[$messageId];
		}

		return Loc::getMessage($messageId);
	}
}