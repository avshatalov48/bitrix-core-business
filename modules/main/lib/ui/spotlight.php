<?

namespace Bitrix\Main\UI;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\UserTable;

class Spotlight
{
	const USER_TYPE_OLD = "OLD";
	const USER_TYPE_NEW = "NEW";
	const USER_TYPE_ALL = "ALL";

	const CATEGORY_NAME = "spotlight";

	private $id;
	private $userType;
	private $userTimeSpan;
	private $lifetime;
	private $startDate = null;
	private $endDate = null;

	/**
	 * Spotlight constructor.
	 *
	 * @param string $id Unique identifier
	 *
	 * @throws ArgumentTypeException
	 */
	public function __construct($id)
	{
		if (!is_string($id))
		{
			throw new ArgumentTypeException("id", "string");
		}

		$this->id = $id;

		$this->userTimeSpan = 3600 * 24 * 30;
		$this->userType = static::USER_TYPE_OLD;
		$this->lifetime = 3600 * 24 * 30;
	}

	/**
	 * @param bool $userId
	 *
	 * @return bool
	 */
	public function isAvailable($userId = false)
	{
		$now = time();

		if (
			($this->getStartDate() && $now < $this->getStartDate()) ||
			($this->getEndDate() && $now > $this->getEndDate())
		)
		{
			return false;
		}

		$userId = $this->getUserId($userId);
		if ($userId < 1)
		{
			return false;
		}

		if ($this->isViewed($userId))
		{
			return false;
		}

		$activationDate = $this->getActivationDate();
		if ($this->getLifetime() && $activationDate + $this->getLifetime() < $now)
		{
			return false;
		}

		$userType = $this->getUserType();
		if ($userType !== static::USER_TYPE_ALL)
		{
			$registerDate = $this->getRegisterDate($userId);
			if ($registerDate === false)
			{
				return false;
			}

			$newUserDate = $activationDate - $this->getUserTimeSpan();
			if ($userType === static::USER_TYPE_OLD && $registerDate > $newUserDate)
			{
				return false;
			}

			if ($userType === static::USER_TYPE_NEW && $registerDate < $newUserDate)
			{
				return false;
			}
		}

		return true;
	}

	public function getActivationDate()
	{
		$activationDate = intval(Option::get(static::CATEGORY_NAME, $this->getActDateOptionName(), 0));
		if ($activationDate === 0)
		{
			$activationDate = $this->activate();
		}

		return $activationDate;
	}

	public function activate($activationDate = false)
	{
		$activationDate = is_int($activationDate) ? $activationDate : time();
		Option::set(static::CATEGORY_NAME, $this->getActDateOptionName(), $activationDate);

		return $activationDate;
	}

	public function deactivate()
	{
		Option::delete(static::CATEGORY_NAME, array("name" => $this->getActDateOptionName()));
	}

	public function isViewed($userId)
	{
		return $this->getViewDate($userId) > 0;
	}

	public function getViewDate($userId)
	{
		$userId = intval($userId);

		return intval(\CUserOptions::getOption(static::CATEGORY_NAME, $this->getViewDateOptionName(), 0, $userId));
	}

	public function setViewDate($userId, $date = false)
	{
		$userId = intval($userId);
		$date = is_int($date) ? $date : time();
		\CUserOptions::setOption(static::CATEGORY_NAME, $this->getViewDateOptionName(), $date, false, $userId);

		return $date;
	}

	public function unsetViewDate($userId)
	{
		$userId = intval($userId);
		\CUserOptions::deleteOption(static::CATEGORY_NAME, $this->getViewDateOptionName(), false, $userId);
	}

	private function getViewDateOptionName()
	{
		return "view_date_".$this->getId();
	}

	private function getActDateOptionName()
	{
		return "activation_date_".$this->getId();
	}

	private function getRegisterDate($userId)
	{
		$user = $this->getUser($userId);
		return $user ? $user["DATE_REGISTER"]->getTimestamp() : false;
	}

	private function getUserId($userId = false)
	{
		return $userId === false && is_object($GLOBALS["USER"]) ? $GLOBALS["USER"]->getID() : intval($userId);
	}

	private function getUser($userId)
	{
		return UserTable::getRow(
			array(
				"select" => array("ID", "DATE_REGISTER"),
				"filter" => array(
					"=ID" => $userId
				),
			)
		);
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getUserType()
	{
		return $this->userType;
	}

	/**
	 * @param string $userType
	 */
	public function setUserType($userType)
	{
		if (in_array($userType, static::getUserTypes()))
		{
			$this->userType = $userType;
		}
	}

	public static function getUserTypes()
	{
		static $types = null;
		if ($types !== null)
		{
			return $types;
		}

		$types = array();
		$refClass = new \ReflectionClass(__CLASS__);
		foreach ($refClass->getConstants() as $name => $value)
		{
			if (mb_substr($name, 0, 9) === "USER_TYPE")
			{
				$types[] = $value;
			}
		}

		return $types;
	}

	/**
	 * @return int
	 */
	public function getUserTimeSpan()
	{
		return $this->userTimeSpan;
	}

	/**
	 * @param int $userTimeSpan
	 */
	public function setUserTimeSpan($userTimeSpan)
	{
		if (is_int($userTimeSpan) && $userTimeSpan >= 0)
		{
			$this->userTimeSpan = $userTimeSpan;
		}
	}

	/**
	 * @return int
	 */
	public function getLifetime()
	{
		return $this->lifetime;
	}

	/**
	 * @param int $lifetime
	 */
	public function setLifetime($lifetime)
	{
		if (is_int($lifetime) && $lifetime >= 0)
		{
			$this->lifetime = $lifetime;
		}
	}

	/**
	 * @return int|null
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * @param int|null $startDate
	 */
	public function setStartDate($startDate)
	{
		if (is_int($startDate) || $startDate === null)
		{
			$this->startDate = $startDate;
		}
	}

	/**
	 * @return int|null
	 */
	public function getEndDate()
	{
		return $this->endDate;
	}

	/**
	 * @param int|null $endDate
	 */
	public function setEndDate($endDate)
	{
		if (is_int($endDate) || $endDate)
		{
			$this->endDate = $endDate;
		}
	}
}