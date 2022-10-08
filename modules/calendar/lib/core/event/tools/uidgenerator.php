<?php

namespace Bitrix\Calendar\Core\Event\Tools;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Util;

class UidGenerator
{
	public const DATE_PART_FORMAT = 'Ymd\THis\Z';
	public const MAX_UID_LENGTH = 255;
	private const CORRECT_LENGTH = 2;

	/**
	 * @var Date
	 */
	private Date $date;
	/**
	 * @var string
	 */
	private string $portalName;
	/**
	 * @var int
	 */
	private int $userId;

	/**
	 * @return UidGenerator
	 */
	public static function createInstance(): UidGenerator
	{
		return new self();
	}

	/**
	 * @param Date $date
	 * @param string $portalName
	 * @param Date|null $originalDate
	 * @return string
	 */
	public function getUidWithDate(): string
	{
		$portalName = $this->portalName ?? '';

		$datePart = $this->date
			->setTimezone(Util::prepareTimezone())
			->format(self::DATE_PART_FORMAT)
		;

		$postfix = md5((string)time(). $this->userId);

		$datePartLength = mb_strlen($datePart);
		$portalNameLength = mb_strlen($portalName);
		$hashLength = mb_strlen($postfix);

		if (($datePartLength + $portalNameLength + $hashLength) > self::MAX_UID_LENGTH)
		{
			$allowableLength = self::MAX_UID_LENGTH - $datePartLength - $hashLength - self::CORRECT_LENGTH;
			$portalName = substr($this->portalName, 0, $allowableLength);
		}

		return $datePart . '-' . $postfix . "@" . $portalName;
	}

	/**
	 * @param Date $date
	 * @return $this
	 */
	public function setDate(Date $date): UidGenerator
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * @param string $portalName
	 * @return $this
	 */
	public function setPortalName(string $portalName): UidGenerator
	{
		$this->portalName = $portalName;

		return $this;
	}

	/**
	 * @param int $userId
	 * @return $this
	 */
	public function setUserId(int $userId): UidGenerator
	{
		$this->userId = $userId;

		return $this;
	}
}
