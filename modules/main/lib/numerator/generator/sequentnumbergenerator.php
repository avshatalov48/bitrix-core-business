<?
namespace Bitrix\Main\Numerator\Generator;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Model\NumeratorSequenceTable;
use Bitrix\Main\Result;
use Bitrix\Main\Numerator\Generator\Contract\Sequenceable;
use Bitrix\Main\Numerator\Generator\Contract\UserConfigurable;

Loc::loadMessages(__FILE__);

/**
 * Class SequentNumberGenerator
 * @package Bitrix\Main\Numerator\Generator
 */
class SequentNumberGenerator extends NumberGenerator implements Sequenceable, UserConfigurable
{
	const DAY = 'day';
	const MONTH = 'month';
	const YEAR = 'year';
	const TEMPLATE_WORD_NUMBER = 'NUMBER';

	const ERROR_SEQUENCE_NOT_SET = 'ERROR_SEQUENCE_NOT_SET';

	protected $start;
	protected $step;
	protected $length = 0;
	protected $padString = '0';
	protected $periodicBy;
	protected $timezone;
	protected $isDirectNumeration;

	protected $nowTime;

	/** value stored in database */
	protected $nextNumber;

	/** calculated value that used for template parsing */
	protected $currentNumber;

	protected $lastInvocationTime;
	protected $numeratorId;
	protected $numberHash;

	/** @inheritdoc */
	public function setConfig($config)
	{
		$this->setFromArrayOrDefault('timezone', $config);
		$this->setFromArrayOrDefault('start', $config, 1, 'int');
		$this->setFromArrayOrDefault('step', $config, 1, 'int');
		$this->setFromArrayOrDefault('length', $config, 0, 'int');
		$this->setFromArrayOrDefault('padString', $config, '0', 'string');
		$this->setFromArrayOrDefault('isDirectNumeration', $config, false, 'bool');
		$this->setFromArrayOrDefault('periodicBy', $config);
		$this->setFromArrayOrDefault('nowTime', $config, time());
		if (isset($config['numeratorId']))
		{
			$this->lastInvocationTime = $config['lastInvocationTime'];
			$this->numeratorId = $config['numeratorId'];
			if ($this->isDirectNumeration)
			{
				$this->numberHash = $this->numeratorId;
			}
		}
		else
		{
			$this->nextNumber = $this->start;
		}
	}

	/** @inheritdoc */
	public static function getSettingsFields()
	{
		$timezonesSettings = static::getTimezoneSettings();
		foreach ($timezonesSettings as $index => $timezonesSetting)
		{
			$timezonesSettings[$index]['settingName'] = $timezonesSetting['name'];
			unset($timezonesSettings[$index]['name']);
		}
		return [
			[
				'settingName' => 'length', 'type' => 'int', 'default' => 0,
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_LENGTH'),
			],
			[
				'settingName' => 'padString', 'type' => 'string', 'default' => '0',
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_PAD_STRING'),
			],
			[
				'settingName' => 'start', 'type' => 'int', 'default' => 1,
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_START'),
			],
			[
				'settingName' => 'step', 'type' => 'int', 'default' => 1,
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_STEP'),
			],
			[
				'settingName' => 'periodicBy', 'type' => 'array',
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_PERIODICBY'),
				'values' => [
					[
						'settingName' => 'default', 'value' => '',
						'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_PERIODICBY_DEFAULT'),
					],
					[
						'settingName' => self::DAY, 'value' => self::DAY,
						'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_PERIODICBY_DAY'),
					],
					[
						'settingName' => self::MONTH, 'value' => self::MONTH,
						'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_PERIODICBY_MONTH'),
					],
					[
						'settingName' => self::YEAR, 'value' => self::YEAR,
						'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_PERIODICBY_YEAR'),
					],
				],
			],
			[
				'settingName' => 'timezone', 'type' => 'array', 'values' => $timezonesSettings,
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_TIMEZONE'),
			],
			[
				'settingName' => 'isDirectNumeration', 'type' => 'boolean',
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_ISDIRECTNUMERATION'),
			],
		];
	}

	/** @inheritdoc */
	public static function getTemplateWordsSettings()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_NUMBER) =>
				Loc::getMessage('BITRIX_MAIN_NUMERATOR_GENERATOR_SEQUENTNUMBERGENERATOR_WORD_NUMBER'),
		];
	}

	/** @inheritdoc */
	public function getConfig()
	{
		return [
			'start' => $this->start,
			'step' => $this->step,
			'length' => $this->length,
			'padString' => $this->padString,
			'periodicBy' => $this->periodicBy,
			'timezone' => $this->timezone,
			'isDirectNumeration' => (bool)$this->isDirectNumeration,
		];
	}

	/**
	 * @param null $numeratorId
	 * @param bool $createIfEmpty
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getSettings($numeratorId = null, $createIfEmpty = true)
	{
		if ($numeratorId === null)
		{
			$numeratorId = $this->numeratorId;
		}
		$nextNumberSettings = NumeratorSequenceTable::getSettings($numeratorId, $this->getNumberHash());
		if (!$nextNumberSettings && $createIfEmpty)
		{
			$nextNumberSettings = NumeratorSequenceTable::setSettings($numeratorId, $this->getNumberHash(), $this->start, $this->nowTime);
		}
		return $nextNumberSettings;
	}

	/**
	 * @return mixed
	 */
	private function getNumberHash()
	{
		if ($this->numberHash === null)
		{
			$this->setNumberHash($this->numeratorId);
		}
		return $this->numberHash;
	}

	/** @inheritdoc */
	public function parseTemplate($template)
	{
		for ($tryouts = 0; $tryouts < 50; $tryouts++)
		{
			$this->nextNumber = null;
			$this->currentNumber = null;
			$nextNumberSettings = $this->getSettings();
			if (!$nextNumberSettings)
			{
				continue;
			}
			$this->lastInvocationTime = $nextNumberSettings['LAST_INVOCATION_TIME'];
			$this->calculateNextAndCurrentNumber($nextNumberSettings['NEXT_NUMBER']);
			$this->lastInvocationTime = $this->nowTime;
			$affectedRows = $this->saveNumeratorSequenceSettings(
				$this->numeratorId,
				$this->getNumberHash(),
				[
					'NEXT_NUMBER' => $this->nextNumber,
					'LAST_INVOCATION_TIME' => $this->lastInvocationTime,
				],
				$nextNumberSettings['NEXT_NUMBER']
			);
			if ($affectedRows == 1)
			{
				break;
			}
		}

		return $this->replaceNumberInPattern($template);
	}

	protected function saveNumeratorSequenceSettings($numeratorId, $numberHash, $fields, $whereNextNumber = null)
	{
		return NumeratorSequenceTable::updateSettings($numeratorId, $numberHash, $fields, $whereNextNumber);
	}

	/** @inheritdoc */
	public static function getTemplateWordsForParse()
	{
		return [static::getPatternFor(static::TEMPLATE_WORD_NUMBER)];
	}

	/** @inheritdoc */
	public function parseTemplateForPreview($template)
	{
		$nextNumberSettings = $this->getSettings($this->numeratorId, false);
		$this->lastInvocationTime = isset($nextNumberSettings['LAST_INVOCATION_TIME']) ? $nextNumberSettings['LAST_INVOCATION_TIME'] : $this->nowTime;
		$this->calculateNextAndCurrentNumber(isset($nextNumberSettings['NEXT_NUMBER']) ? $nextNumberSettings['NEXT_NUMBER'] : $this->start);
		return $this->replaceNumberInPattern($template);
	}

	private function replaceNumberInPattern($template)
	{
		$resultNumber = $this->currentNumber;
		if ($this->length > 0)
		{
			$resultNumber = str_pad($resultNumber, $this->length, $this->padString, STR_PAD_LEFT);
		}
		return str_replace(static::getPatternFor(static::TEMPLATE_WORD_NUMBER), $resultNumber, $template);
	}

	/** @inheritdoc */
	public function getNextNumber($numeratorId)
	{
		if (!$numeratorId)
		{
			return null;
		}
		$this->numeratorId = $numeratorId;
		$nextNumberSettings = $this->getSettings($numeratorId, false);
		if ($nextNumberSettings)
		{
			return $nextNumberSettings['NEXT_NUMBER'];
		}
		else
		{
			return $this->start;
		}
	}

	/**
	 * @return string
	 */
	public static function getAvailableForType()
	{
		return 'DEFAULT';
	}

	/*** @inheritdoc */
	public function setNextNumber($numeratorId, $newNumber, $whereNumber)
	{
		$this->nextNumber = $newNumber;
		$sequence = $this->getSettings($numeratorId, false);
		if (!$sequence)
		{
			return (new Result())->addError(new Error(Loc::getMessage('NUMERATOR_UPDATE_SEQUENT_IS_NOT_SET_YET')));
		}
		$affectedRows = $this->saveNumeratorSequenceSettings(
			$numeratorId,
			$this->getNumberHash(),
			[
				'NEXT_NUMBER' => $this->nextNumber,
			],
			$whereNumber
		);
		if ($affectedRows == 1)
		{
			return new Result();
		}
		return (new Result())->addError(new Error(Loc::getMessage('NUMERATOR_SEQUENT_DEFAULT_INTERNAL_ERROR')));
	}

	/**
	 * set current number to its start position if generator is periodic and period has been just changed
	 */
	private function resetCurrentNumberIfNeeded()
	{
		if ($this->periodicBy)
		{
			if ($this->periodicBy == static::YEAR && $this->isHasChanged(static::YEAR))
			{
				$this->currentNumber = $this->start;
			}
			if ($this->periodicBy == static::MONTH)
			{
				if ($this->isHasChanged(static::MONTH) || $this->isSameMonthButDifferentYear())
				{
					$this->currentNumber = $this->start;
				}
			}
			if ($this->periodicBy == static::DAY)
			{
				if ($this->isHasChanged(static::DAY)
					|| $this->isSameDayButDifferent(static::MONTH)
					|| $this->isSameDayButDifferent(static::YEAR))
				{
					$this->currentNumber = $this->start;
				}
			}
		}
	}

	/**
	 * @return bool
	 */
	private function isSameMonthButDifferentYear()
	{
		return $this->getLastInvocationUserTime()->format('m') === $this->getNowUserTime()->format('m') && $this->isHasChanged(static::YEAR);
	}

	/**
	 * @param $interval
	 * @return bool
	 */
	private function isSameDayButDifferent($interval)
	{
		$isSameDay = $this->getLastInvocationUserTime()->format('d') === $this->getNowUserTime()->format('d');
		if ($interval == static::MONTH)
		{
			return $isSameDay && $this->isHasChanged(static::MONTH);
		}
		if ($interval == static::YEAR)
		{
			return $isSameDay && $this->isHasChanged(static::YEAR);
		}
		return false;
	}

	/**
	 * @param $interval
	 * @return bool
	 */
	private function isHasChanged($interval)
	{
		if ($interval == static::MONTH)
		{
			return $this->getLastInvocationUserTime()->format('m') !== $this->getNowUserTime()->format('m');
		}
		if ($interval == static::DAY)
		{
			return $this->getLastInvocationUserTime()->format('d') !== $this->getNowUserTime()->format('d');
		}
		if ($interval == static::YEAR)
		{
			return $this->getLastInvocationUserTime()->format('Y') !== $this->getNowUserTime()->format('Y');
		}
		return false;
	}

	/**
	 * @return array
	 */
	private static function getTimezoneSettings()
	{
		$timezones = \CTimeZone::GetZones();
		$settings = [];
		foreach ($timezones as $timezoneValue => $timezoneName)
		{
			$settings[] = ['name' => $timezoneName, 'value' => $timezoneValue,];
		}
		return $settings;
	}

	private function getNowUserTime()
	{
		return $this->createDateTimeInCurrentTimezone($this->nowTime);
	}

	private function getLastInvocationUserTime()
	{
		return $this->createDateTimeInCurrentTimezone($this->lastInvocationTime);
	}

	private function createDateTimeInCurrentTimezone($timestamp)
	{
		$dateTime = \DateTime::createFromFormat('U', $timestamp);
		if ($this->timezone)
		{
			$result = $dateTime->setTimezone(new \DateTimeZone($this->timezone));
			if ($result === false)
			{
				$dateTime = \DateTime::createFromFormat('U', $timestamp);
			}
		}
		return $dateTime;
	}

	/** @inheritdoc */
	public function validateConfig($config)
	{
		$result = new Result();
		return $result;
	}

	/** @inheritdoc */
	public function setNumberHash($numberHash)
	{
		if (!is_string($numberHash) && !is_int($numberHash))
		{
			return;
		}
		if ($this->numberHash === null)
		{
			$this->numberHash = (string)$numberHash;
		}
	}

	private function calculateNextAndCurrentNumber($initNumber)
	{
		$this->currentNumber = $initNumber;
		$this->resetCurrentNumberIfNeeded();
		$this->nextNumber = $this->currentNumber + $this->step;
	}
}