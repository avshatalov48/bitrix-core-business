<?php

namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Report\VisualConstructor\Helper\Filter;


/**
 * Class TimePeriod
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class TimePeriod extends BaseValuable
{
	const JS_EVENT_ON_SELECT = 'onSelect';

	const FILTER_PERIOD_TIME = 'FILTER';
	const QUARTER_PERIOD_TIME = 'QUARTER';
	const YEAR_PERIOD_TIME = 'YEAR';
	const MONTH_PERIOD_TIME = 'MONTH';
	const THIS_YEAR_PERIOD_TIME = 'THIS_YEAR';
	const THIS_QUARTER_PERIOD_TIME = 'THIS_QUARTER';
	const THIS_MONTH_PERIOD_TIME = 'THIS_MONTH';
	const THIS_DAY_PERIOD_TIME = 'THIS_DAY';
	const LAST_90_DAYS_PERIOD_TIME = 'LAST_90_DAYS';
	const LAST_60_DAYS_PERIOD_TIME = 'LAST_60_DAYS';
	const LAST_30_DAYS_PERIOD_TIME = 'LAST_30_DAYS';
	const LAST_7_DAYS_PERIOD_TIME = 'LAST_7_DAYS';


	const DEFAULT_TIME_PERIOD_TYPE = self::FILTER_PERIOD_TIME;


	const DEFAULT_YEAR_LIST_START = 2017;


	private $filterId;

	/**
	 * Time period field constructor.
	 *
	 * @param string $key Unique Id.
	 * @param null $filterId Filter id which value will apply as time period FILTER value.
	 */
	public function __construct($key, $filterId = null)
	{
		parent::__construct($key);
		$this->setLabel('Time Period: ');
		$this->setDefaultValue($this->getDefaultConfigValue());
		$this->setFilterId($filterId);
	}


	/**
	 * Load field component with label or timeperiod template.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$this->includeFieldComponent('timeperiod');
	}


	/**
	 * @return DateTime[]
	 */
	public function getDefaultConfigValue()
	{
		return array(
			'type' => static::DEFAULT_TIME_PERIOD_TYPE,
			'month' => $this->getCurrentMonth(),
			'quarter' => $this->getCurrentQuarter(),
			'year' => $this->getCurrentYear(),
		);
	}

	/**
	 * @return string
	 */
	public function getFilterId()
	{
		return $this->filterId;
	}

	/**
	 * Fiter id setter.
	 *
	 * @param string $id Filter id.
	 * @return void
	 */
	public function setFilterId($id)
	{
		$this->filterId = $id;
	}

	/**
	 * Conver value to array which will save in db.
	 *
	 * @param array $config Configuration which must normalise.
	 * @return array
	 */
	protected function normalise($config)
	{
		if (isset($config['type']))
		{
			return array(
				'type' => $config['type'],
				'month' => !empty($config['month']) ? $config['month'] : $this->getCurrentMonth(),
				'quarter' => !empty($config['quarter']) ? $config['quarter'] : $this->getCurrentQuarter(),
				'year' => !empty($config['year']) ? $config['year'] : $this->getCurrentYear(),
			);
		}
		else
		{
			return $this->getDefaultConfigValue();
		}
	}

	/**
	 * Convert field value to array with keys start, end.
	 *
	 * @return array
	 */
	public function getValueAsPeriod()
	{
		$value = $this->getValue();
		$defaultStartDate = strtotime("-1 year", time());
		$defaultStartDate = date('Y-m-d', $defaultStartDate);
		$result = array(
			'start' => new DateTime($defaultStartDate, 'Y-m-d'),
			'end' => new DateTime(),
		);
		switch ($value['type'])
		{
			case self::FILTER_PERIOD_TIME:
				$forFilter = $this->prepareTimePeriodForFilter();
				if ($forFilter)
				{
					$result = $forFilter;
				}
				break;
			case self::QUARTER_PERIOD_TIME:
				$result = $this->prepareTimePeriodForQuarter($value);
				break;
			case self::YEAR_PERIOD_TIME:
				$result = $this->prepareTimePeriodForYear($value);
				break;
			case self::MONTH_PERIOD_TIME:
				$result = $this->prepareTimePeriodForMonth($value);
				break;
			case self::THIS_YEAR_PERIOD_TIME:
				$result = $this->prepareTimePeriodForThisYear();
				break;
			case self::THIS_QUARTER_PERIOD_TIME:
				$result = $this->prepareTimePeriodForThisQuarter();
				break;
			case self::THIS_MONTH_PERIOD_TIME:
				$result = $this->prepareTimePeriodForThisMonth();
				break;
			case self::THIS_DAY_PERIOD_TIME:
				$result = $this->prepareTimePeriodForLastDays();
				break;
			case self::LAST_90_DAYS_PERIOD_TIME:
				$result = $this->prepareTimePeriodForLastDays(90);
				break;
			case self::LAST_60_DAYS_PERIOD_TIME:
				$result = $this->prepareTimePeriodForLastDays(60);
				break;
			case self::LAST_30_DAYS_PERIOD_TIME:
				$result = $this->prepareTimePeriodForLastDays(30);
				break;
			case self::LAST_7_DAYS_PERIOD_TIME:
				$result = $this->prepareTimePeriodForLastDays(7);
				break;

		}
		$result['type'] = $value['type'];
		return $result;
	}

	/**
	 * Conver Filter value to time period  field value.
	 *
	 * @return array
	 */
	private function prepareTimePeriodForFilter()
	{
		$result = array();
		$filter = $this->getFilterOptions()->getFilter(Filter::getFieldsList());
		if ($filter)
		{
			$result = array(
				'start' => new DateTime($filter['TIME_PERIOD_from']),
				'end' => new DateTime($filter['TIME_PERIOD_to'])
			);
		}
		return $result;
	}

	/**
	 * Prepare time period value for selected quaerter.
	 *
	 * @param array $value Value of this field.
	 * @return array
	 */
	private function prepareTimePeriodForQuarter($value)
	{
		$year = $value['year'];
		$startYear = $endYear = $year;
		$quarter = $value['quarter'];
		$quarterStartMonth = 1 + ($quarter - 1) * 3;
		if ($quarterStartMonth < 10)
		{
			$quarterStartMonth = '0' . $quarterStartMonth;
		}

		$quarterEndMonth = 1 + $quarter * 3;
		if ($quarterEndMonth < 10)
		{
			$quarterEndMonth = '0' . $quarterEndMonth;
		}
		elseif ($quarterEndMonth === 13)
		{
			$endYear++;
			$quarterEndMonth = '01';
		}

		$quarterStartStr = $startYear . '-' . $quarterStartMonth . '-01 00:00';
		$quarterEndStr = $endYear . '-' . $quarterEndMonth . '-01 00:00';
		$result = array(
			'start' => new DateTime($quarterStartStr, 'Y-m-d H:i'),
			'end' => new DateTime($quarterEndStr, 'Y-m-d H:i'),
		);
		return $result;
	}

	/**
	 * Prepare time period value for selected year.
	 *
	 * @param array $value Value of this field.
	 * @return array
	 */
	private function prepareTimePeriodForYear($value)
	{
		$year = $value['year'];
		$yearStartStr = $year . '01-01 00:00';
		$yearEndStr = $year + 1 . '01-01 00:00';
		$result = array(
			'start' => new DateTime($yearStartStr, 'Y-m-d H:i'),
			'end' => new DateTime($yearEndStr, 'Y-m-d H:i'),
		);
		return $result;
	}

	/**
	 * Prepare time period value for selected month.
	 *
	 * @param array $value Value of this field.
	 * @return array
	 */
	private function prepareTimePeriodForMonth($value)
	{
		$year = $value['year'];
		$month = $value['month'];
		$startYear = $endYear = $year;
		$startMonth = $month;
		$endMonth = $month + 1;

		if ($startMonth < 10)
		{
			$startMonth = '0' . $startMonth;
		}
		if ($endMonth < 10)
		{
			$endMonth = '0' . $endMonth;
		}
		if ($endMonth == 13)
		{
			$endYear++;
			$endMonth = '01';
		}
		$monthStartStr = $startYear . '-' . $startMonth . '-01 00:00';
		$monthEndStr = $endYear . '-' . $endMonth . '-01 00:00';
		$result = array(
			'start' => new DateTime($monthStartStr, 'Y-m-d H:i'),
			'end' => new DateTime($monthEndStr, 'Y-m-d H:i'),
		);
		return $result;
	}

	/**
	 * Return this year diapason.
	 *
	 * @return array
	 */
	private function prepareTimePeriodForThisYear()
	{
		$year = new DateTime();
		$thisYear = (int)$year->format('Y');
		$value['year'] = $thisYear;
		return $this->prepareTimePeriodForYear($value);
	}

	/**
	 * Return this month diapason.
	 *
	 * @return array
	 */
	private function prepareTimePeriodForThisMonth()
	{
		$year = new DateTime();
		$thisYear = (int)$year->format('Y');
		$thisMonth = (int)$year->format('m');
		$value['year'] = $thisYear;
		$value['month'] = $thisMonth;
		return $this->prepareTimePeriodForMonth($value);
	}

	/**
	 * Return this quarter diapason.
	 *
	 * @return array
	 */
	private function prepareTimePeriodForThisQuarter()
	{
		$year = new DateTime();
		$thisYear = (int)$year->format('Y');
		$thisMonth = (int)$year->format('m');
		$value['year'] = $thisYear;
		$value['quarter'] = $thisMonth / 3;
		return $this->prepareTimePeriodForQuarter($value);
	}

	/**
	 * Return date time diapason where exis last $dayCount days.
	 *
	 * @param int $dayCount Days count existed in returned time diapason.
	 * @return array
	 */
	private function prepareTimePeriodForLastDays($dayCount = 0)
	{
		$nextDay = strtotime("+1 day", time());
		$nextDay = date('Y-m-d', $nextDay);
		$agoDays = strtotime("-" . $dayCount . " day", time());
		$agoDays = date('Y-m-d', $agoDays);

		return array(
			'start' => new DateTime($agoDays, 'Y-m-d H:i'),
			'end' => new DateTime($nextDay, 'Y-m-d H:i'),
		);
	}

	/**
	 * @return Options
	 */
	private function getFilterOptions()
	{
		$options = new Options($this->getFilterId(), Filter::getPresetsList());
		return $options;
	}
	/**
	 * @return string[]
	 */
	public function getTypeList()
	{
		$typeList = array();
		if ($this->getFilterId())
		{
			$typeList += array(
				self::FILTER_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_FILTER'),
			);
		}
		$typeList += array(
			self::YEAR_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_YEAR'),
			self::QUARTER_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_QUARTER'),
			self::MONTH_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_MONTH'),
			self::THIS_YEAR_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_THIS_YEAR'),
			self::THIS_QUARTER_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_THIS_QUARTER'),
			self::THIS_MONTH_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_THIS_MONTH'),
			self::THIS_DAY_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_THIS_DAY'),
			self::LAST_90_DAYS_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_LAST_90_DAYS'),
			self::LAST_60_DAYS_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_LAST_60_DAYS'),
			self::LAST_30_DAYS_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_LAST_30_DAYS'),
			self::LAST_7_DAYS_PERIOD_TIME => Loc::getMessage('REPORT_TIME_PERIOD_FIELD_TYPE_LAST_7_DAYS'),
		);
		return $typeList;
	}
	/**
	 * @return string[] of month with translates.
	 */
	public function getMonthList()
	{
		$monthList = array(
			1 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_JANUARY'),
			2 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_FEBRUARY'),
			3 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_MARCH'),
			4 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_APRIL'),
			5 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_MAY'),
			6 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_JUNE'),
			7 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_JULY'),
			8 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_AUGUST'),
			9 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_SEPTEMBER'),
			10 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_OCTOBER'),
			11 => Loc::getMessage('REPORT_TIME_PERIOD_MONTH_NOVEMBER'),
			12 =>Loc::getMessage('REPORT_TIME_PERIOD_MONTH_DECEMBER'),
		);
		return $monthList;
	}

	/**
	 * @return string[] of quarter.
	 */
	public function getQuarterList()
	{
		$quarterList = array(
			1 => 'I',
			2 => 'II',
			3 => 'III',
			4 => 'IV',
		);
		return $quarterList;
	}

	/**
	 * @return string[] of years in time period.
	 */
	public function getYearList()
	{
		$yearList = array();
		for ($year = static::DEFAULT_YEAR_LIST_START; $year < $this->getCurrentYear() + 5; $year++)
		{
			$yearList[$year] = $year;
		}
		return $yearList;
	}

	/**
	 * @return string
	 */
	public function getValueForHuman()
	{
		$value = $this->getValue();
		$typeList = $this->getTypeList();
		$result = $typeList[$value['type']];
		switch ($value['type'])
		{
			case self::YEAR_PERIOD_TIME:
				$result .= ': ' . $value['year'];
				break;
			case self::MONTH_PERIOD_TIME:
				$monthList = $this->getMonthList();
				$monthName = $monthList[$value['month']];
				$result .= ': ' . $monthName . ' ' . $value['year'];
				break;
			case self::QUARTER_PERIOD_TIME:
				$quarterList = $this->getQuarterList();
				$quarterName = $quarterList[$value['quarter']];
				$result .= ': ' . $quarterName . ' ' . $value['year'];
		}
		return $result;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateTime()
	{
		return new \DateTime();
	}

	/**
	 * @return int
	 */
	public function getCurrentYear()
	{
		return (int)$this->getDateTime()->format('Y');
	}

	/**
	 * @return int
	 */
	public function getCurrentMonth()
	{
		return (int)$this->getDateTime()->format('m');
	}

	/**
	 * @return int
	 */
	public function getCurrentQuarter()
	{
		return (int)ceil($this->getCurrentMonth() / 3);
	}
}