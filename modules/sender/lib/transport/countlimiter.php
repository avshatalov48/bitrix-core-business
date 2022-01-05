<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Transport;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CountLimiter
 * @package Bitrix\Sender\Transport
 */
class CountLimiter implements iLimiter
{
	/** @var string $name Name. */
	private $name;

	/** @var string $caption Caption. */
	private $caption;

	/** @var array $parameters Parameters. */
	private $parameters = array();

	/** @var integer $limit Limit.  */
	private $limit;

	/** @var callable $current Current. */
	private $current;

	/** @var integer $interval Interval. */
	private $interval;

	/** @var string $unit Unit. */
	private $unit;

	/** @var string $unit Unit. */
	private $unitName;

	private $hidden = false;

	/**
	 * Create instance.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	private static function parseUnit($unit)
	{
		$unit = explode(' ', $unit);
		$num = (int) $unit[0];
		$unit = $unit[1];

		return array(
			'num' => $num,
			'unit' => $unit
		);
	}

	/**
	 * Create unit interval.
	 *
	 * @param string $unit Unit.
	 * @return integer
	 */
	public static function getUnitInterval($unit)
	{
		$parsed = self::parseUnit($unit);

		switch ($parsed['unit'])
		{
			case iLimiter::MINUTES:
				$interval = 60;
				break;

			case iLimiter::HOURS:
				$interval = 3600;
				break;

			case iLimiter::MONTHS:
				$interval = 86400 * 31;
				break;

			case iLimiter::DAYS:
			default:
				$interval = 86400;
				break;
		}

		return $interval * $parsed['num'];
	}

	/**
	 * Limitation constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * With name.
	 *
	 * @param string $name Name.
	 * @return $this
	 */
	public function withName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * With caption.
	 *
	 * @param string $caption Caption.
	 * @return $this
	 */
	public function withCaption($caption)
	{
		$this->caption = $caption;
		return $this;
	}

	/**
	 * With limit.
	 *
	 * @param integer $limit Limit.
	 * @return $this
	 */
	public function withLimit($limit)
	{
		$this->limit = (int) $limit;
		return $this;
	}

	/**
	 * With time interval in seconds.
	 *
	 * @param integer $interval Time interval.
	 * @return $this
	 */
	public function withInterval($interval)
	{
		$this->interval = (int) $interval;
		return $this;
	}

	/**
	 * With unit.
	 *
	 * @param string $unit Unit.
	 * @return $this
	 */
	public function withUnit($unit)
	{
		$this->unit = $unit;
		$this->interval = self::getUnitInterval($unit);
		return $this;
	}

	/**
	 * With unit name.
	 *
	 * @param string $unitName Unit.
	 * @return $this
	 */
	public function withUnitName($unitName)
	{
		$this->unitName = $unitName;
		return $this;
	}

	/**
	 * Set current.
	 *
	 * @param callable $callable Callable.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function withCurrent($callable)
	{
		if (!is_callable($callable))
		{
			throw new ArgumentException('Wrong type of parameter `callable`.');
		}

		$this->current = $callable;
		return $this;
	}

	/**
	 * Get initial limit.
	 *
	 * @return integer
	 */
	public function getInitialLimit()
	{
		return $this->limit;
	}

	/**
	 * Get limit rate.
	 *
	 * @return integer
	 */
	public function getLimit()
	{
		$limit = (int) $this->limit;
		$percentage = (int) $this->getParameter('percentage');
		if ($percentage > 0 && $percentage <= 100)
		{
			$limit *= $percentage / 100;
		}

		return $limit;
	}

	/**
	 * Get unit.
	 *
	 * @return string
	 */
	public function getUnit()
	{
		return $this->unit;
	}

	/**
	 * Get unit name.
	 *
	 * @return string
	 */
	public function getUnitName()
	{
		if ($this->unitName)
		{
			return $this->unitName;
		}

		$parsed = self::parseUnit($this->unit);
		switch ($parsed['unit'])
		{
			case iLimiter::MINUTES:
				$format = 'idiff';
				break;

			case iLimiter::HOURS:
				$format = 'Hdiff';
				break;

			case iLimiter::MONTHS:
				$format = 'mdiff';
				break;

			case iLimiter::DAYS:
			default:
				$format = 'ddiff';
				break;
		}


		$formatted = \FormatDate($format, $this->getCurrentTimestamp() - $this->interval);
		if (mb_substr($formatted, 0, 2) == '1 ')
		{
			$formatted = mb_substr($formatted, 2);
		}

		return Loc::getMessage('SENDER_TRANSPORT_COUNT_LIMIT_UNIT_DATE_AT') . ' ' . $formatted;
	}

	/**
	 * Get caption.
	 *
	 * @return string
	 */
	public function getCaption()
	{
		return $this->caption;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get text.
	 *
	 * @return string
	 */
	public function getText()
	{
		return Loc::getMessage(
			'SENDER_TRANSPORT_COUNT_LIMIT_TEXT_MONTHLY',
			[
				'%current%' => number_format($this->getAvailable(), 0, '.', ' '),
				'%limit%' => number_format($this->getLimit(), 0, '.', ' '),
				'%setupUri%' => $this->getParameter('setupUri'),
				'%setupCaption%' => $this->getParameter('setupCaption'),
			]
		);
	}

	/**
	 * Get parameter.
	 *
	 * @param string $name Name.
	 * @return mixed|null
	 */
	public function getParameter($name)
	{
		return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
	}

	/**
	 * Set parameter.
	 *
	 * @param string $name Name.
	 * @param mixed $value Value.
	 * @return $this
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
		return $this;
	}

	/**
	 * Inc current limit rate.
	 *
	 * @param integer $amount Amount.
	 * @return $this
	 * @throws SystemException
	 */
	public function inc($amount = 1)
	{
		if ($this->getTimestamp())
		{
			$isNewPeriod = ($this->getCurrentTimestamp() - $this->getTimestamp()) >= $this->interval;
		}
		else
		{
			$isNewPeriod = true;
		}


		if ($isNewPeriod)
		{
			$this->setCurrent(0);
			$this->setTimestamp($this->getCurrentTimestamp());
		}

		$current = $this->getCurrent() + $amount;
		if ($current >= $this->getLimit())
		{
			throw new SystemException(Loc::getMessage(
				'SENDER_TRANSPORT_COUNT_LIMIT_EXCEEDED',
				array('%limit%' => $this->getLimit(), '%unit%' => $this->getUnitName())
			));
		}

		if (!$isNewPeriod)
		{
			$this->setCurrent($current);
		}

		return $this;
	}

	/**
	 * Get current limit rate.
	 *
	 * @return integer
	 */
	public function getAvailable()
	{
		$available = $this->getLimit() - $this->getCurrent();
		return (!$available || $available < 0) ? 0 : $available;
	}

	/**
	 * Get current limit rate.
	 *
	 * @return integer
	 */
	public function getCurrent()
	{
		if (is_callable($this->current))
		{
			return call_user_func($this->current);
		}

		return (int) Option::get('main', $this->getCounterOptionName(), 0);
	}

	/**
	 * Set current limit rate.
	 *
	 * @param integer $value Value.
	 * @return $this
	 */
	private function setCurrent($value)
	{
		Option::set('main', $this->getCounterOptionName(), $value);
		return $this;
	}

	/**
	 * Set timestamp of current period.
	 *
	 * @param integer $value Value.
	 * @return $this
	 */
	private function setTimestamp($value)
	{
		Option::set('main', $this->getDateOptionName(), $value);
		return $this;
	}

	/**
	 * Get timestamp of current period.
	 *
	 * @return int
	 */
	public function getTimestamp()
	{
		return (int) Option::get('main', $this->getDateOptionName(), 0);
	}

	private function getCurrentTimestamp()
	{
		$dateTime = new DateTime();
		return $dateTime->getTimestamp();
	}

	private function getCounterOptionName()
	{
		return "~sender_limit_count_" . $this->name;
	}

	private function getDateOptionName()
	{
		return "~sender_limit_date_" . $this->name;
	}

	/**
	 * Set limiter hidden.
	 * @param bool $hidden
	 * @return $this
	 */
	public function setHidden(bool $hidden): CountLimiter
	{
		$this->hidden = $hidden;
		return $this;
	}
	/**
	 * @inheritDoc
	 */
	public function isHidden()
	{
		return $this->hidden;
	}
}