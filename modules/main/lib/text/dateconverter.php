<?php
namespace Bitrix\Main\Text;

use Bitrix\Main\Localization\Loc as Loc;

class DateConverter
{
	/**
	 * Creates Date object from Text (return array of result object)
	 * 
	 * Examples: "end of next week", "tomorrow morning", "friday 25.10"
	 *
	 * @param string $text
	 * @param integer $limit
	 * @return \Bitrix\Main\Text\DateConverterResult[]
	 */
	public static function decode($text, $limit = 0)
	{
		$result = Array();
		if (strlen($text) <= 0)
		{
			return $result;
		}

		$metrics = Array();
		$date = new \Bitrix\Main\Type\DateTime();
		
		if ($limit > 0 && strlen($text) > $limit)
		{
			$text = substr($text, 0, $limit);
		}
		
		$originalText = $text;
		$text = ToLower($text);
		
		$workTimeStart = explode('.', \Bitrix\Main\Config\Option::get('calendar', 'work_time_start', '9'));
		$timeOfStartDate = str_pad(intval($workTimeStart[0]), 2, "0", STR_PAD_LEFT).':'.str_pad(intval($workTimeStart[1]), 2, "0", STR_PAD_LEFT);
		
		$workTimeEnd = explode('.', \Bitrix\Main\Config\Option::get('calendar', 'work_time_end', '18'));
		$timeOfEndDate = str_pad(intval($workTimeEnd[0]), 2, "0", STR_PAD_LEFT).':'.str_pad(intval($workTimeEnd[1]), 2, "0", STR_PAD_LEFT);

		$defaultPregExceptionSearch =  Array('.', '/', '-', ':');
		$defaultPregExceptionReplace = Array('\.', '\/', '\-', '\:');

		// metric 1: near date
		$pattern = Array();
		for ($i = 1; $i <= 11; $i++)
		{
			$pattern[$i] = ToLower(Loc::getMessage("MAIN_TDC_METRIC_1_".$i));
			$pattern[$i] = strlen($pattern[$i]) > 0? $pattern[$i]: 'bxt2dmetricskip';
			$pattern[$i] = str_replace($defaultPregExceptionSearch, $defaultPregExceptionReplace, $pattern[$i]);
		}
		if (preg_match_all("/(".implode('|', $pattern).")/", $text, $match, PREG_OFFSET_CAPTURE))
		{
			foreach ($match[0] as $matchPattern)
			{
				$matchType = '';
				switch($matchPattern[0])
				{
					case $pattern[1]:
						$matchType = 'DAYAFTERTOMORROW';
					break;
					case $pattern[2]:
						$matchType = 'TOMORROW';
					break;
					case $pattern[3]:
						$matchType = 'TODAY';
					break;
					case $pattern[4]:
						$matchType = 'YESTERDAY';
					break;
					case $pattern[5]:
						$matchType = 'DAYBEFOREYESTERDAY';
					break;
					case $pattern[6]:
					case $pattern[7]:
					case $pattern[8]:
						$matchType = '';
					break;
					case $pattern[9]:
						$matchType = 'WEEK';
					break;
					case $pattern[10]:
						$matchType = 'WEEKEND';
					break;
					case $pattern[11]:
						$matchType = 'MONTH';
					break;
				}
				if (strlen($matchType) > 0)
				{
					$position = defined("BX_UTF")? strlen(\Bitrix\Main\Text\BinaryString::getSubstring($text, 0, $matchPattern[1])): $matchPattern[1];
					$matchWord = self::getMatchWord($originalText, $position);
					
					$metrics[1][] = Array(
						'TYPE' => $matchType,
						'COUNT' => strlen($matchWord),
						'POSITION' => $position,
						'MATCH' => $matchWord
					);
				}
			}
		}

		// metric 2: day of weeks
		$pattern = Array();
		for ($i = 1; $i <= 10; $i++)
		{
			$pattern[$i] = ToLower(Loc::getMessage("MAIN_TDC_METRIC_2_".$i));
			$pattern[$i] = strlen($pattern[$i]) > 0? $pattern[$i]: 'bxt2dmetricskip';
			$pattern[$i] = str_replace($defaultPregExceptionSearch, $defaultPregExceptionReplace, $pattern[$i]);
		}
		if (preg_match_all("/(".implode('|', $pattern).")/", $text, $match, PREG_OFFSET_CAPTURE))
		{
			foreach ($match[0] as $matchPattern)
			{
				$matchType = '';
				switch($matchPattern[0])
				{
					case $pattern[1]:
						$matchType = 'MONDAY';
						if (is_array($metrics[1]))
						{
							foreach ($metrics[1] as $key => $metric)
							{
								if ($metric['TYPE'] == 'WEEK')
								{
									$position = defined("BX_UTF") ? strlen(\Bitrix\Main\Text\BinaryString::getSubstring($text, 0, $matchPattern[1])) : $matchPattern[1];
									$matchWord = self::getMatchWord($originalText, $position);

									if ($matchPattern[1] < $metric['POSITION'] && $matchPattern[1] + strlen($matchWord) >= $metric['POSITION'] + $metric['COUNT'])
									{
										unset($metrics[1][$key]);
										if (count($metrics[1]) == 0)
										{
											unset($metrics[1]);
										}
									}
								}
							}
						}
					break;
					case $pattern[2]:
						$matchType = 'TUESDAY';
					break;
					case $pattern[3]:
					case $pattern[4]:
					case $pattern[5]:
						$matchType = 'WEDNESDAY';
					break;
					case $pattern[6]:
						$matchType = 'THURSDAY';
					break;
					case $pattern[7]:
						$matchType = 'FRIDAY';
					break;
					case $pattern[8]:
						$matchType = 'SATURDAY';
					break;
					case $pattern[9]:
					case $pattern[10]:
						$matchType = 'SUNDAY';
					break;
				}

				if (strlen($matchType) > 0)
				{
					$position = defined("BX_UTF")? strlen(\Bitrix\Main\Text\BinaryString::getSubstring($text, 0, $matchPattern[1])): $matchPattern[1];
					$matchWord = self::getMatchWord($originalText, $position);
					
					$metrics[2][] = Array(
						'TYPE' => $matchType,
						'COUNT' => strlen($matchWord),
						'POSITION' => $position,
						'MATCH' => $matchWord
					);
				}
			}
		}

		// metric 3: modificators before/after and time
		$pattern = Array();
		for ($i = 1; $i <= 9; $i++)
		{
			$pattern[$i] = ToLower(Loc::getMessage("MAIN_TDC_METRIC_3_".$i));
			$pattern[$i] = strlen($pattern[$i]) > 0? $pattern[$i]: 'bxt2dmetricskip';
			$pattern[$i] = str_replace($defaultPregExceptionSearch, $defaultPregExceptionReplace, $pattern[$i]);
		}
		if (preg_match_all("/(".implode('|', $pattern).")/", $text, $match, PREG_OFFSET_CAPTURE))
		{
			foreach ($match[0] as $matchPattern)
			{
				$matchType = '';
				switch($matchPattern[0])
				{
					case $pattern[1]:
						$matchType = 'BEFORE';
					break;
					case $pattern[2]:
						$matchType = 'AFTER';
					break;
					case $pattern[3]:
						$matchType = 'NEXT';
					break;
					case $pattern[4]:
						$matchType = 'PREVIOUS';
					break;
					case $pattern[5]:
					case $pattern[6]:
						$matchType = 'ENDOF';
					break;
					case $pattern[7]:
						$matchType = 'MIDDLEOF';
					break;
					case $pattern[8]:
						$matchType = 'STARTOF';
					break;
					case $pattern[9]:
					case $pattern[10]:
						$matchType = 'THIS';
					break;
				}
				
				if (in_array($matchType, Array('BEFORE', 'AFTER')) && isset($metrics[1]))
				{
					foreach ($metrics[1] as $key => $metric)
					{
						if (in_array($metric['TYPE'], Array('DAYAFTERTOMORROW', 'DAYBEFOREYESTERDAY')))
						{
							if ($metric['POSITION'] == $matchPattern[1])
							{
								$matchType = '';
							}
						}
						else if (in_array($metric['TYPE'], Array('TOMORROW', 'YESTERDAY')))
						{
							if ($metric['POSITION'] == $matchPattern[1]+strlen($matchPattern[0])+1)
							{
								if ($metric['TYPE'] == 'TOMORROW')
								{
									$metrics[1][$key]['TYPE'] = $matchType == 'AFTER'? 'DAYAFTERTOMORROW': 'TODAY';
								}
								else
								{
									$metrics[1][$key]['TYPE'] = $matchType == 'AFTER'? 'TODAY': 'DAYBEFOREYESTERDAY';
								}
								$matchType = '';
							}
						}
					}
				}

				if (strlen($matchType) > 0)
				{
					$position = defined("BX_UTF")? strlen(\Bitrix\Main\Text\BinaryString::getSubstring($text, 0, $matchPattern[1])): $matchPattern[1];
					$matchWord = self::getMatchWord($originalText, $position);
					
					$metrics[3][] = Array(
						'TYPE' => $matchType,
						'COUNT' => strlen($matchWord),
						'POSITION' => $position,
						'MATCH' => $matchWord
					);
				}
			}
		}
		if (preg_match_all("/([0-2][0-9]:[0-5][0-9])/", $text, $match, PREG_OFFSET_CAPTURE))
		{
			foreach ($match[0] as $matchPattern)
			{
				$position = defined("BX_UTF")? strlen(\Bitrix\Main\Text\BinaryString::getSubstring($text, 0, $matchPattern[1])): $matchPattern[1];
				$matchWord = self::getMatchWord($originalText, $position);	
				
				$metrics[3][] = Array(
					'TYPE' => 'HHMM',
					'VALUE' => $matchPattern[0],
					'COUNT' => strlen($matchPattern[0]),
					'POSITION' => $position,
					'MATCH' => $matchWord
				);
			}
		}

		// metric 4: date
		$pattern = Array();
		$patternOriginal = Array();
		$patternLength = Array();
		for ($i = 1; $i <= 3; $i++)
		{
			$patternOriginal[$i] = ToLower(Loc::getMessage("MAIN_TDC_METRIC_4_".$i));
			$patternOriginal[$i] = strlen($patternOriginal[$i]) > 0? $patternOriginal[$i]: 'bxt2dmetricskip';
			$patternLength[$i] = strlen($patternOriginal[$i]);
			if ($patternOriginal[$i] != 'bxt2dmetricskip')
			{
				$pattern[$i] = str_replace($defaultPregExceptionSearch, $defaultPregExceptionReplace, $patternOriginal[$i]);
				$pattern[$i] = str_replace(
					Array('yyyy', 'yy', 'mm', 'dd'),
					Array('[1-2][0-9][0-9][0-9]', '[0-9][0-9]', '[0-1][0-9]', '[0-3][0-9]'),
					$pattern[$i]
				);
				$pattern[$i] = '^(?>[\s|\t]?)('.$pattern[$i].')|[\s|^|\t]('.$pattern[$i].')(?=[\s|$|\r|\t])';
			}
		}
		if (preg_match_all("/".implode('|', $pattern)."/", $text, $match, PREG_OFFSET_CAPTURE))
		{
			$count = count($match)-1;
			for ($i = 1; $i <= $count; $i++)
			{
				foreach ($match[$i] as $matchPattern)
				{
					if (!$matchPattern[0])
					{
						continue;
					}
					
					$matchType = '';
					$matchLength = '';
					switch(strlen($matchPattern[0]))
					{
						case $patternLength[1]:
							$matchType = 'DDMMYYYY';
							$matchYear = substr($matchPattern[0], strpos($patternOriginal[1], 'yyyy'), 4);
							$matchMonth = substr($matchPattern[0], strpos($patternOriginal[1], 'mm'), 2);
							$matchDay = substr($matchPattern[0], strpos($patternOriginal[1], 'dd'), 2);
							$matchPattern[0] = $matchYear.'-'.$matchMonth.'-'.$matchDay;
							$matchLength = $patternLength[1];
						break;
						case $patternLength[2]:
							$matchType = 'DDMMYY';
							$matchYear = substr($matchPattern[0], strpos($patternOriginal[2], 'yy'), 2);
							$matchMonth = substr($matchPattern[0], strpos($patternOriginal[2], 'mm'), 2);
							$matchDay = substr($matchPattern[0], strpos($patternOriginal[2], 'dd'), 2);
							$matchPattern[0] = '20'.$matchYear.'-'.$matchMonth.'-'.$matchDay;
							$matchLength = $patternLength[2];
						break;
						case $patternLength[3]:
							$matchType = 'DDMM';
							$matchMonth = substr($matchPattern[0], strpos($patternOriginal[3], 'mm'), 2);
							$matchDay = substr($matchPattern[0], strpos($patternOriginal[3], 'dd'), 2);
							$matchPattern[0] = $date->format('Y').'-'.$matchMonth.'-'.$matchDay;
							$matchLength = $patternLength[3];
						break;
					}
	
					$position = defined("BX_UTF")? strlen(\Bitrix\Main\Text\BinaryString::getSubstring($text, 0, $matchPattern[1])): $matchPattern[1];
					$matchWord = substr($originalText, $position, $matchLength);
					
					$metrics[4][] = Array(
						'TYPE' => $matchType,
						'VALUE' => $matchPattern[0],
						'COUNT' => $matchLength,
						'POSITION' => $position,
						'MATCH' => $matchWord
					);
				}
			}
		}

		// metric 5: modificators of time
		$pattern = Array();
		for ($i = 1; $i <= 5; $i++)
		{
			$pattern[$i] = ToLower(Loc::getMessage("MAIN_TDC_METRIC_5_".$i));
			$pattern[$i] = strlen($pattern[$i]) > 0? $pattern[$i]: 'bxt2dmetricskip';
			$pattern[$i] = str_replace($defaultPregExceptionSearch, $defaultPregExceptionReplace, $pattern[$i]);
		}
		if (preg_match_all("/(".implode('|', $pattern).")/", $text, $match, PREG_OFFSET_CAPTURE))
		{
			foreach ($match[0] as $matchPattern)
			{
				$matchType = '';
				$matchValue = '';
				switch($matchPattern[0])
				{
					case $pattern[1]:
						$matchType = 'MORNING';
						$matchValue = $timeOfStartDate;
					break;
					case $pattern[2]:
						$matchType = 'LUNCH';
						$matchValue = '14:00';
					break;
					case $pattern[3]:
					break;
					case $pattern[4]:
						$matchType = 'WORKDAY';
						$matchValue = $timeOfEndDate;

						if (isset($metrics[1]))
						{
							foreach ($metrics[1] as $key => $metric)
							{
								if ($metric['TYPE'] == 'TODAY')
								{
									if ($metric['POSITION'] == $matchPattern[1]+strlen($matchPattern[3]))
									{
										$matchType = '';
									}
								}
							}
						}
					break;
					case $pattern[5]:
						$matchType = 'EVENING';
						$matchValue = '20:00';
					break;
				}
				if (strlen($matchType) > 0)
				{
					$position = defined("BX_UTF")? strlen(\Bitrix\Main\Text\BinaryString::getSubstring($text, 0, $matchPattern[1])): $matchPattern[1];
					$matchWord = self::getMatchWord($originalText, $position);
					
					$metrics[5][] = Array(
						'TYPE' => $matchType,
						'VALUE' => $matchValue,
						'COUNT' => strlen($matchWord),
						'POSITION' => $position,
						'MATCH' => $matchWord
					);
				}
			}
		}
		
		$countOfMetrics = 0;
		foreach ($metrics as $values)
		{
			if (count($values) > 0)
			{
				$countOfMetrics++;
			}
		}

		$useDefault = false;
		if ($countOfMetrics == 1 && (isset($metrics[3]) || isset($metrics[5])))
		{}
		else if ($countOfMetrics == 1)
		{
			$useDefault = true;
		}
		else if ($countOfMetrics == 2 && isset($metrics[3]))
		{
			$useDefault = !isset($metrics[5]);
		}
		else if ($countOfMetrics == 2 && isset($metrics[5]) && (isset($metrics[1]) || isset($metrics[2]) || isset($metrics[4])))
		{
			if (isset($metrics[1]))
			{
				foreach ($metrics[1] as $metric)
				{
					$progressDate = self::createDateUsingMetrics(1, $metric, $metrics[5]);
					$matchParams = self::getTextForReplace($originalText, $metric, $metrics[5]);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_RELATIVE, $metric, $metrics[5]);
				}
			}
			else if (isset($metrics[2]))
			{
				foreach ($metrics[2] as $metric)
				{
					$progressDate = self::createDateUsingMetrics(2, $metric, $metrics[5]);
					$matchParams = self::getTextForReplace($originalText, $metric, $metrics[5]);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_DAYOFWEEK, $metric, $metrics[5]);
				}
			}
			else if (isset($metrics[4]))
			{
				foreach ($metrics[4] as $metric)
				{
					$progressDate = self::createDateUsingMetrics(4, $metric, $metrics[5]);
					$matchParams = self::getTextForReplace($originalText, $metric, $metrics[5]);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_CALENDAR, $metric, $metrics[5]);
				}
			}
		}
		else if ($countOfMetrics == 2 && isset($metrics[1]) && (isset($metrics[2]) || isset($metrics[4])))
		{
			if (isset($metrics[2]))
			{
				foreach ($metrics[2] as $metric)
				{
					$array = array();
					$progressDate = self::createDateUsingMetrics(2, $metric, $array);
					$matchParams = self::getTextForReplace($originalText, $metric, array());
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_DAYOFWEEK, $metric);
				}
			}
			elseif (isset($metrics[4]))
			{
				foreach ($metrics[4] as $metric)
				{
					$array = array();
					$progressDate = self::createDateUsingMetrics(4, $metric, $array);
					$matchParams = self::getTextForReplace($originalText, $metric, array());
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_CALENDAR, $metric);
				}
			}
		}
		else
		{
			$useDefault = true;
		}
		
		if ($useDefault)
		{
			$modificators = isset($metrics[3])? $metrics[3]: array();
			if (isset($metrics[4]) && count($metrics[4]))
			{
				foreach ($metrics[4] as $metric)
				{
					$progressDate = self::createDateUsingMetrics(4, $metric, $modificators);
					$matchParams = self::getTextForReplace($originalText, $metric, $modificators);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_CALENDAR, $metric, $modificators);
				}
			}
			else if (isset($metrics[2]) && count($metrics[2]))
			{
				foreach ($metrics[2] as $metric)
				{
					$progressDate = self::createDateUsingMetrics(2, $metric, $modificators);
					$matchParams = self::getTextForReplace($originalText, $metric, $modificators);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_DAYOFWEEK, $metric, $modificators);
				}
			}
			else if (isset($metrics[1]) && count($metrics[1]))
			{
				foreach ($metrics[1] as $metric)
				{
					$progressDate = self::createDateUsingMetrics(1, $metric, $modificators);
					$matchParams = self::getTextForReplace($originalText, $metric, $modificators);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_RELATIVE, $metric, $modificators);
				}
			}
			else if (isset($metrics[5]) && count($metrics[5]))
			{
				foreach ($metrics[5] as $metric)
				{
					$progressDate = self::createDateUsingMetrics(5, $metric, $modificators);
					$matchParams = self::getTextForReplace($originalText, $metric, $modificators);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_PARTOFDAY, $metric, $modificators);
				}
			}
			else if (isset($metrics[3]) && count($metrics[3]))
			{
				foreach ($metrics[3] as $metric)
				{
					$array = array();
					$progressDate = self::createDateUsingMetrics(3, $metric, $array);
					$matchParams = self::getTextForReplace($originalText, $metric, $modificators);
					$result[] = new DateConverterResult($progressDate, $matchParams, DateConverterResult::TYPE_MODIFIER, $metric);
				}
			}

		}

		return $result;
	}

	/**
	 * Creates Date object from metrics (private method for self::decode)
	 *
	 * @param int $type Type of metric
	 * @param array $metric Params of metric
	 * @param array $metricModificator List of metric modificators
	 * @param \Bitrix\Main\Type\DateTime $date If not specified, the modified date is got from the current date.
	 * @return \Bitrix\Main\Type\DateTime
	 *
	 */
	private static function createDateUsingMetrics($type, $metric, &$metricModificator = array(), \Bitrix\Main\Type\DateTime $date = null)
	{
		$defaultDate = $date? $date: new \Bitrix\Main\Type\DateTime();
		$metricModificator = is_array($metricModificator)? $metricModificator: array();

		$workTimeStart = explode('.', \Bitrix\Main\Config\Option::get('calendar', 'work_time_start', '9'));
		$timeOfStartDate = str_pad(intval($workTimeStart[0]), 2, "0", STR_PAD_LEFT).':'.str_pad(intval($workTimeStart[1]), 2, "0", STR_PAD_LEFT);
		
		$workTimeEnd = explode('.', \Bitrix\Main\Config\Option::get('calendar', 'work_time_end', '18'));
		$timeOfEndDate = str_pad(intval($workTimeEnd[0]), 2, "0", STR_PAD_LEFT).':'.str_pad(intval($workTimeEnd[1]), 2, "0", STR_PAD_LEFT);
		
		$result = null;
		switch($type)
		{
			case 1:
				$metricDate = $defaultDate;
				switch($metric['TYPE'])
				{
					case 'DAYAFTERTOMORROW':
						$metricDate->add('TOMORROW')->add('TOMORROW');
					break;
					case 'TOMORROW':
						$metricDate->add('TOMORROW');
					break;
					case 'YESTERDAY':
						$metricDate->add('YESTERDAY');
					break;
					case 'DAYBEFOREYESTERDAY':
						$metricDate->add('YESTERDAY')->add('YESTERDAY');
					break;
				}

				$modificator = '';
				$metricTime = $timeOfEndDate;
				
				$metricModificator = self::checkModifierPosition($metric, $metricModificator);
				
				foreach ($metricModificator as $currentModificator)
				{
					if ($metric['TYPE'] == 'WEEK')
					{
						if (in_array($currentModificator['TYPE'], Array('BEFORE', 'PREVIOUS')))
						{
							$metricDate->add('-1 WEEK');
						}
						else if (in_array($currentModificator['TYPE'], Array('AFTER', 'NEXT')))
						{
							$metricDate->add('1 WEEK');
						}
						else if ($currentModificator['TYPE'] == 'MIDDLEOF')
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('WEDNESDAY', $metricDate);
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'STARTOF')
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('MONDAY', $metricDate);
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'ENDOF')
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('FRIDAY', $metricDate);
							$modificator = true;
						}
					}
					else if ($metric['TYPE'] == 'WEEKEND')
					{
						if (in_array($currentModificator['TYPE'], Array('BEFORE')))
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('SATURDAY', $metricDate);
							$metricDate->add('-1 DAY');
							$modificator = true;
						}
						else if (in_array($currentModificator['TYPE'], Array('PREVIOUS')))
						{
							$metricDate->add('-1 WEEK');
							$metricDate = self::getDateOfDayOfCurrentWeek('SATURDAY', $metricDate);
							$modificator = true;
						}
						else if (in_array($currentModificator['TYPE'], Array('NEXT')))
						{
							$metricDate->add('1 WEEK');
							$metricDate = self::getDateOfDayOfCurrentWeek('SATURDAY', $metricDate);
							$modificator = true;
						}
						else if (in_array($currentModificator['TYPE'], Array('AFTER')))
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('SUNDAY', $metricDate);
							$metricDate->add('1 DAY');
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'MIDDLEOF')
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('SATURDAY', $metricDate);
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'STARTOF')
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('SATURDAY', $metricDate);
							$metricTime = $timeOfStartDate;
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'ENDOF')
						{
							$metricDate = self::getDateOfDayOfCurrentWeek('SUNDAY', $metricDate);
							$modificator = true;
						}
					}
					else if ($metric['TYPE'] == 'MONTH')
					{
						if (in_array($currentModificator['TYPE'], Array('BEFORE', 'PREVIOUS')))
						{
							$metricDate->add('-1 MONTH');
							$numberOfMonth = $metricDate->format('t');
							$metricDate = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m').'-'.$numberOfMonth.' '.$timeOfEndDate.':00', 'Y-m-d H:i:s');
							$modificator = true;
						}
						else if (in_array($currentModificator['TYPE'], Array('AFTER', 'NEXT')))
						{
							$metricDate->add('1 MONTH');
							$numberOfMonth = $metricDate->format('t');
							$metricDate = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m').'-'.$numberOfMonth.' '.$timeOfEndDate.':00', 'Y-m-d H:i:s');
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'MIDDLEOF')
						{
							$numberOfHalfMonth = ceil($metricDate->format('t')/2);
							$metricDate = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m').'-'.$numberOfHalfMonth.' '.$timeOfEndDate.':00', 'Y-m-d H:i:s');
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'STARTOF')
						{
							$metricDate = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m').'-01 '.$timeOfStartDate.':00', 'Y-m-d H:i:s');
							$modificator = true;
						}
						else if ($currentModificator['TYPE'] == 'ENDOF')
						{
							$numberOfMonth = $metricDate->format('t');
							$metricDate = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m').'-'.$numberOfMonth.' '.$timeOfEndDate.':00', 'Y-m-d H:i:s');
							$modificator = true;
						}
					}
					else
					{
						if (in_array($currentModificator['TYPE'], Array('BEFORE', 'PREVIOUS')))
						{
							$metricDate->add('-1 DAY');
						}
						else if (in_array($currentModificator['TYPE'], Array('AFTER', 'NEXT')))
						{
							$metricDate->add('1 DAY');
						}
						else if ($currentModificator['TYPE'] == 'MIDDLEOF')
						{
							$metricTime = '14:00';
						}
						else if ($currentModificator['TYPE'] == 'STARTOF')
						{
							$metricTime = $timeOfStartDate;
						}
						$modificator = true;
					}
					
					if (in_array($currentModificator['TYPE'], Array('MORNING', 'LUNCH', 'EVENING', 'HHMM')))
					{
						$metricTime = $currentModificator['VALUE'];
						$modificator = true;
					}
				}
				if (!$modificator)
				{
					if ($metric['TYPE'] == 'WEEK')
					{
						if ($metricDate->format('N') == 3)
						{
							$metricDate->add('FRIDAY');
						}
						else if ($metricDate->format('N') < 3)
						{
							$metricDate->add('THURSDAY');
						}
						else if ($metricDate->format('N') != 7)
						{
							$metricDate->add('SUNDAY');
						}
					}
					else if ($metric['TYPE'] == 'WEEKEND')
					{
						if (ToLower($metricDate->format('l')) == 'saturday')
							$metricDate->add('SUNDAY');
						else if (ToLower($metricDate->format('l')) != 'sunday')
							$metricDate->add('SATURDAY');
					}
					else if ($metric['TYPE'] == 'MONTH')
					{
						$numberOfMonth = $metricDate->format('j');
						if ($numberOfMonth > 15)
						{
							$plus = $metricDate->format('t')-$numberOfMonth;
							if ($plus >= 1)
							{
								$metricDate->add($plus.' DAY');
							}
						}
						else if ($numberOfMonth >= 10 && $numberOfMonth <= 15)
						{
							$plus = 20-$numberOfMonth;
							$metricDate->add($plus.' DAY');
						}
						else
						{
							$plus = 15-$numberOfMonth;
							$metricDate->add($plus.' DAY');
						}
					}
				}

				$result = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m-d').' '.$metricTime.':00', 'Y-m-d H:i:s');

			break;
			case 2:

				$metricDate = $defaultDate;
				$modificator = '';
				$metricTime = $timeOfEndDate;
				
				$metricModificator = self::checkModifierPosition($metric, $metricModificator);
				
				foreach ($metricModificator as $currentModificator)
				{
					$metricDate = self::getDateOfDayOfCurrentWeek($metric['TYPE'], $metricDate);
					if ($currentModificator['TYPE'] == 'BEFORE')
					{
						$metricDate->add('-1 DAY');
					}
					else if ($currentModificator['TYPE'] == 'AFTER')
					{
						$metricDate->add('1 DAY');
					}
					else if ($currentModificator['TYPE'] == 'THIS')
					{
						$metricDate->add('LAST '.$metric['TYPE']);
					}
					else if (in_array($currentModificator['TYPE'], Array('NEXT', 'PREVIOUS')))
					{
						if ($currentModificator['TYPE'] == 'NEXT')
						{
							$metricDate->add('NEXT '.$metric['TYPE']);
						}
						else
						{
							$metricDate->add('PREVIOUS '.$metric['TYPE']);
						}
					}
					else if ($currentModificator['TYPE'] == 'MIDDLEOF')
					{
						$metricTime = '14:00';
					}
					else if ($currentModificator['TYPE'] == 'STARTOF')
					{
						$metricTime = $timeOfStartDate;
					}
					else if (in_array($currentModificator['TYPE'], Array('MORNING', 'LUNCH', 'EVENING', 'HHMM')))
					{
						$metricTime = $currentModificator['VALUE'];
					}
					$modificator = true;
				}
				if (!$modificator)
				{
					$modificator = ToLower($metric['TYPE']) == ToLower($metricDate->format('l'))? 'NEXT ': '';
					$metricDate->add($modificator.$metric['TYPE']);
				}

				$result = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m-d').' '.$metricTime.':00', 'Y-m-d H:i:s');

			break;
			case 3:
				$metricDate = $defaultDate;
				$metricTime = $timeOfEndDate;
				switch($metric['TYPE'])
				{
					case 'BEFORE':
						$metricDate->add('TOMORROW')->add('TOMORROW');
					break;
					case 'AFTER':
						$metricDate->add('TOMORROW');
					break;
					case 'NEXT':
						$metricDate->add('YESTERDAY');
					break;
					case 'PREVIOUS':
						$metricDate->add('YESTERDAY')->add('YESTERDAY');
					break;
					case 'MIDDLEOF':
						$metricTime = '14:00';
					break;
					case 'STARTOF':
						$metricTime = $timeOfStartDate;
					break;
					case 'HHMM':
						$metricTime = $metric['VALUE'];
					break;
				}
				$result = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m-d').' '.$metricTime.':00', 'Y-m-d H:i:s');

			break;
			case 4:
				$modificator = '';
				$metricTime = $timeOfEndDate;
				
				$metricModificator = self::checkModifierPosition($metric, $metricModificator);
				
				foreach ($metricModificator as $currentModificator)
				{
					if (in_array($currentModificator['TYPE'], Array('MORNING', 'LUNCH', 'EVENING', 'HHMM')))
					{
						$metricTime = $currentModificator['VALUE'];
					}
				}

				$result = new \Bitrix\Main\Type\DateTime($metric['VALUE'].' '.$metricTime.':00', 'Y-m-d H:i:s');

			break;
			case 5:

				$metricDate = $defaultDate;
				$modificator = '';
				$metricTime = $metric['VALUE'];
				
				$metricModificator = self::checkModifierPosition($metric, $metricModificator);
				
				foreach ($metricModificator as $currentModificator)
				{
					if ($currentModificator['TYPE'] == 'BEFORE')
					{
						$metricDate->add('-1 DAY');
					}
					else if ($currentModificator['TYPE'] == 'AFTER')
					{
						$metricDate->add('1 DAY');
					}
					else if (in_array($currentModificator['TYPE'], Array('NEXT', 'PREVIOUS')))
					{
						if ($currentModificator['TYPE'] == 'NEXT')
						{
							$metricDate->add('NEXT '.$metric['TYPE']);
						}
						else
						{
							$metricDate->add('PREVIOUS '.$metric['TYPE']);
						}
					}
					else if ($currentModificator['TYPE'] == 'MIDDLEOF')
					{
						$metricTime = '14:00';
					}
					else if ($currentModificator['TYPE'] == 'STARTOF')
					{
						$metricTime = $timeOfStartDate;
					}
					else if (in_array($currentModificator['TYPE'], Array('MORNING', 'LUNCH', 'EVENING', 'HHMM')))
					{
						$metricTime = $currentModificator['VALUE'];
					}
					$modificator = true;
				
				}

				if (!$modificator)
				{
					$modificator = ToLower($metric['TYPE']) == ToLower($metricDate->format('l'))? 'NEXT ': '';
					$metricDate->add($modificator.$metric['TYPE']);
				}

				$result = new \Bitrix\Main\Type\DateTime($metricDate->format('Y-m-d').' '.$metricTime.':00', 'Y-m-d H:i:s');
			break;
		}

		return $result;
	}
	
	/**
	 * Creates Date object of the day of the method week (private function for self::createDateUsingMetrics)
	 *
	 * @param string $nameOfday name of the day (monday, tuesday, etc)
	 * @param \Bitrix\Main\Type\DateTime $date If not specified, the modified date is got from the current date.
	 * @return \Bitrix\Main\Type\DateTime
	 *
	 */
	private static function getDateOfDayOfCurrentWeek($nameOfday, \Bitrix\Main\Type\DateTime $date = null)
	{
		$date = $date? $date: new \Bitrix\Main\Type\DateTime();

		$nameOfday = strtoupper($nameOfday);
		if (!in_array($nameOfday, Array('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY')))
			return false;

		$relation = '';
		$day = $date->format('N');
		if (
			$nameOfday == 'MONDAY' && $day == 1 ||
			$nameOfday == 'TUESDAY' && $day <= 2 ||
			$nameOfday == 'WEDNESDAY' && $day <= 3 ||
			$nameOfday == 'THURSDAY' && $day <= 4 ||
			$nameOfday == 'FRIDAY' && $day <= 5 ||
			$nameOfday == 'SATURDAY' && $day <= 6 ||
			$nameOfday == 'SUNDAY' && $day <= 7
		)
		{
			$relation = 'THIS';
		}
		$date->add($relation.' '.$nameOfday);

		return $date;
	}

	/**
	 * @param $text
	 * @param $position
	 * @return string
	 */
	private static function getMatchWord($text, $position)
	{
		$letters = array_merge(Array(" ", "\n", "\t", "\r"), str_split('"\'-.,?!#$%^&*();:<>\|{}-=^@[]`'));
		$spaceFound = self::findFirstOccurrence($text, $letters, $position);
		if ($spaceFound !== false)
		{
			$result = substr($text, $position, $spaceFound-$position);
		}
		else
		{
			$result = substr($text, $position);
		}
		return $result;
	}
	
	private static function getTextForReplace($text, $metrics, $metricModifier)
	{
		$found = false;
		
		$minStartPosition = null;
		$maxEndPosition = null;
		
		if (!empty($metrics))
		{
			$minStartPosition = $metrics['POSITION'];
			$maxEndPosition = $metrics['POSITION'] + $metrics['COUNT'];
			$found = true;
		}
		
		if (is_array($metricModifier))
		{
			foreach ($metricModifier as $metrics)
			{
				if (!$found)
				{
					$minStartPosition = $metrics['POSITION'];
					$maxEndPosition = $metrics['POSITION'] + $metrics['COUNT'];
					$found = true;
				}
				else
				{
					if ($minStartPosition > $metrics['POSITION'])
					{
						$minStartPosition = $metrics['POSITION'];
					}
					if ($maxEndPosition < $metrics['POSITION'] + $metrics['COUNT'])
					{
						$maxEndPosition = $metrics['POSITION'] + $metrics['COUNT'];
					}
				}
			}
		}
		
		if (is_null($minStartPosition) || is_null($maxEndPosition))
		{
			$result = false;
		}
		else
		{
			$result = substr($text, $minStartPosition, $maxEndPosition - $minStartPosition);
			$result = Array('TEXT' => $result, 'POSITION' => $minStartPosition, 'LENGTH' => strlen($result));
		}
		
		return $result;
	}
	
	private static function checkModifierPosition($metrics, $metricModifier)
	{
		$newMetrics = $metrics;
		
		$stackMetrics = Array();
		
		$while = true;
		$maxWhile = 100;
		while ($while && $maxWhile > 0)
		{
			$while = false;
			foreach ($metricModifier as $key => $currentModificator)
			{
				$diffResult = abs($newMetrics['POSITION'] - ($currentModificator['POSITION'] + $currentModificator['COUNT']));
				if ($diffResult < 5)
				{
					$while = true;
					$newMetrics = $currentModificator;
					$stackMetrics[] = $currentModificator;
					unset($metricModifier[$key]);
				}
				else
				{
					$diffResult = abs($newMetrics['POSITION']+$newMetrics['COUNT'] - ($currentModificator['POSITION']));
					if ($diffResult < 5)
					{
						$while = true;
						$newMetrics = $currentModificator;
						$stackMetrics[] = $currentModificator;
						unset($metricModifier[$key]);
					}
				}
			}
			$maxWhile--;
		}
		
		return $stackMetrics;
	}
	
	private static function findFirstOccurrence($haystack, $needle, $offset=0)
	{
		$haystack = substr($haystack, 0, 25+$offset);
		if(!is_array($needle))
		{
			$needle = array($needle);
		}
		
		$positions = array();
		foreach($needle as $query) 
		{
			$result = strpos($haystack, $query, $offset);
			if ($result !== false)
			{
				$positions[] = $result;
			}
		}
		
		return empty($positions)? false: min($positions);
	}
}
