<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Localization\Loc;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

class MethodTime implements iMethod
{
	/** @var DateTime $dateTime Date. */
	protected $dateTime;

	/** @var Entity\Letter $letter Letter. */
	private $letter;

	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter)
	{
		$this->letter = $letter;
	}

	/**
	 * Apply method.
	 *
	 * @param DateTime $dateTime Date.
	 */
	public function setDateTime(DateTime $dateTime)
	{
		$this->dateTime = $dateTime;
	}

	/**
	 * Apply method.
	 *
	 * @return DateTime
	 */
	public function getDateTime()
	{
		return $this->dateTime;
	}

	/**
	 * Apply method.
	 *
	 * @return void
	 */
	public function apply()
	{
		$this->letter->plan($this->dateTime);
	}

	/**
	 * Revoke method.
	 *
	 * @return void
	 */
	public function revoke()
	{
		if (!$this->letter->getState()->isReady())
		{
			$this->letter->getState()->ready();
		}
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return Method::TIME;
	}
}