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

/**
 * Class Method
 * @package Bitrix\Sender\Dispatch
 */
class Method implements iMethod
{
	const DEFERED = 'defered';
	const TIME = 'time';
	const SCHEDULE = 'schedule';

	/** @var Entity\Letter $letter Letter. */
	private $letter;

	/** @var iMethod $method Method. */
	private $method;

	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter)
	{
		$this->letter = $letter;

		if ($letter->isReiterate())
		{
			$this->set(new MethodSchedule($this->letter));
		}
		elseif($this->letter->get('AUTO_SEND_TIME'))
		{
			$this->time($this->letter->get('AUTO_SEND_TIME'));
		}
		else
		{
			$this->defer();
		}

	}

	/**
	 * Check change possibility.
	 *
	 * @return bool
	 */
	public function canChange()
	{
		return (
			(
				$this->letter->isReiterate()
				||
				!$this->letter->getState()->wasStartedSending()
			)
			&&
			!$this->letter->getState()->isFinished()
		);
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->method->getCode();
	}

	/**
	 * Set defer method.
	 *
	 * @return void
	 */
	public function defer()
	{
		$this->set(new MethodDefered($this->letter));
	}

	/**
	 * Set time method.
	 *
	 * @param DateTime $dateTime Date.
	 * @return void
	 */
	public function time(DateTime $dateTime)
	{
		$method = new MethodTime($this->letter);
		$method->setDateTime($dateTime);
		$this->set($method);
	}

	/**
	 * Set time method with current time.
	 *
	 * @return void
	 */
	public function now()
	{
		$this->time(new DateTime);
	}

	/**
	 * Set method.
	 *
	 * @param iMethod $method Method.
	 */
	public function set(iMethod $method)
	{
		if ($this->method)
		{
			$this->revoke();
		}
		$this->method = $method;
	}

	/**
	 * Get method.
	 *
	 * @return iMethod
	 */
	public function get()
	{
		return $this->method;
	}

	/**
	 * Apply method.
	 *
	 * @return void
	 */
	public function apply()
	{
		$this->method->apply();
	}

	/**
	 * Revoke method.
	 *
	 * @return void
	 */
	public function revoke()
	{
		$this->method->revoke();
	}
}