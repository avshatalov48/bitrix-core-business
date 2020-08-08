<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\PrettyDate;

Loc::loadMessages(__FILE__);

class Holiday
{
	/** @var  string $code */
	private $code;
	/** @var  Date $date */
	private $date;
	/** @var  Date $dateFrom */
	private $dateFrom;
	/** @var  Date $dateTo */
	private $dateTo;

	/** @var array $list */
	private static $list = [
		'feb14' => [14, 2], // code => [day, month]
		'feb23' => [23, 2],
		'mar8' => [8, 3],
		'halloween' => [31, 10],
		'thanks' => [22, 11],
		'christmas' => [25, 12],
		'new_year' => [
			[1, 12],
			[10, 1],
		],
	];

	/** @var int $defaultYear */
	private static $defaultYear = 2049;

	/**
	 * Get list by language.
	 *
	 * @param string|mixed $languageId Language ID.
	 * @return self[]
	 */
	public static function getList($languageId = LANGUAGE_ID)
	{
		switch ($languageId)
		{
			case 'ru':
				$listLocal = ['feb14', 'feb23', 'mar8', 'halloween', 'new_year'];
				break;

			case 'ua':
				$listLocal = ['feb14', 'day_mar8', 'halloween', 'new_year'];
				break;

			default:
				$listLocal = ['feb14', 'halloween', 'thanks', 'christmas'];
		}

		$list = [];
		foreach ($listLocal as $code)
		{
			if (!isset(self::$list[$code]))
			{
				continue;
			}

			$period = self::$list[$code];
			if (is_array($period[0]))
			{
				$date = null;
				$dateFrom = self::createDate($period[0][0], $period[0][1]);
				$dateTo = self::createDate($period[1][0], $period[1][1]);
			}
			else
			{
				$date = self::createDate($period[0], $period[1]);
				$dateFrom = self::createDate($period[0], $period[1])->add('-5 days');
				$dateTo = self::createDate($period[0], $period[1])->add('+3 days');
			}

			$list[] = new self($code, $date, $dateFrom, $dateTo);
		}

		return $list;
	}

	/**
	 * Holiday constructor.
	 *
	 * @param string $code Code.
	 * @param Date|null $date Date.
	 * @param Date $dateFrom Date from.
	 * @param Date $dateTo Date to.
	 */
	public function __construct($code, Date $date = null, Date $dateFrom, Date $dateTo)
	{
		$this->code = $code;
		$this->date = $date;
		$this->dateFrom = $dateFrom;
		$this->dateTo = $dateTo;
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Get upper code.
	 *
	 * @return string
	 */
	public function getCodeUpper()
	{
		return mb_strtoupper($this->code);
	}

	/**
	 * Get name.
	 *
	 * @param string $template Template of name.
	 * @param string $placeholder Placeholder of name in template.
	 * @return string
	 */
	public function getName($template = null, $placeholder = '%name%')
	{
		$name = Loc::getMessage('SENDER_INTEGRATION_HOLIDAY_' . $this->getCodeUpper());
		if ($template)
		{
			return str_replace($placeholder, $name, $template);
		}

		return $name;
	}

	/**
	 * Get formatted date.
	 *
	 * @return string
	 */
	public function formatDate()
	{
		return $this->date ? PrettyDate::formatDate($this->date) : $this->getName();
	}

	/**
	 * Get date.
	 *
	 * @return Date
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * Get day.
	 *
	 * @return int
	 */
	public function getDay()
	{
		return $this->dateFrom->format('j');
	}

	/**
	 * Get day.
	 *
	 * @return int
	 */
	public function getMonth()
	{
		return $this->dateFrom->format('n');
	}

	/**
	 * Get date from.
	 *
	 * @return Date
	 */
	public function getDateFrom()
	{
		return $this->dateFrom;
	}

	/**
	 * Get date to.
	 *
	 * @return Date
	 */
	public function getDateTo()
	{
		return $this->dateTo;
	}

	private static function createDate($day, $month, $year = null)
	{
		return Date::createFromTimestamp(mktime(0, 0, 0, $month, $day, $year ?: self::$defaultYear));
	}
}