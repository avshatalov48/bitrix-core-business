<?

namespace Bitrix\Main\UI\Filter;


use Bitrix\Main\Type as MainType;


class DateTime
{
	/** @var MainType\Date */
	protected $date;

	/** @var MainType\DateTime */
	protected $dateTime;

	/** @var ?number */
	protected $timestamp;


	/**
	 * DateTime constructor.
	 * @param string $timestamp
	 */
	public function __construct($timestamp = "")
	{
		$this->timestamp = $timestamp;

		if (empty($this->timestamp))
		{
			$this->date = new MainType\Date();
			$this->timestamp = $this->date->getTimestamp();
		}

		$this->dateTime = MainType\DateTime::createFromTimestamp($this->timestamp);
		static::adjustTime($this->dateTime);
	}


	/**
	 * Adjusts time relative current timezone offset
	 * @param MainType\DateTime $dateTime
	 * @return int timestamp
	 */
	public static function adjustTime(MainType\DateTime $dateTime)
	{
		if(\CTimeZone::Enabled())
		{
			static $diff = null;
			if($diff === null)
			{
				$diff = \CTimeZone::GetOffset();
			}
			if($diff <> 0)
			{
				$dateTime->add(($diff > 0? "-":"")."PT".abs($diff)."S");
			}
		}
	}



	/**
	 * Gets month from date
	 * @return string
	 */
	public function month()
	{
		$date = new MainType\Date($this->toString());
		return $date->format("n");
	}


	/**
	 * Gets year
	 * @return string
	 */
	public function year()
	{
		$date = new MainType\Date($this->toString());
		return $date->format("Y");
	}


	/**
	 * Gets quarter number
	 * @return int
	 */
	public function quarter()
	{
		$date = new MainType\Date($this->toString());
		return Quarter::get($date);
	}


	/**
	 * Gets quarter start datetime
	 * @return string
	 */
	public function quarterStart()
	{
		$startDate = Quarter::getStartDate($this->quarter(), $this->year());
		$dateTime = MainType\DateTime::createFromTimestamp(MakeTimeStamp($startDate));
		static::adjustTime($dateTime);
		return $dateTime->toString();
	}


	/**
	 * Gets quarter end dateTime
	 * @return string
	 */
	public function quarterEnd()
	{
		$endDate = Quarter::getEndDate($this->quarter(), $this->year());
		$dateTime = MainType\DateTime::createFromUserTime($endDate);
		$dateTime->add("- 1 second");
		return $dateTime->toString();
	}


	/**
	 * Gets datetime string with offset.
	 * @param string $offset
	 * @return string
	 */
	public function offset($offset)
	{
		$date = MainType\DateTime::createFromTimestamp($this->getTimestamp());
		$date->add($offset);
		static::adjustTime($date);
		return $date->toString();
	}


	/**
	 * Gets datetime string
	 * @return string
	 */
	public function toString()
	{
		return $this->dateTime->toString();
	}


	/**
	 * Gets timestamp
	 * @return number
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}
}