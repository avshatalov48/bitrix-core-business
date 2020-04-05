<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class MethodDefered
 * @package Bitrix\Sender\Dispatch
 */
class MethodDefered implements iMethod
{
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
	 * @return void
	 */
	public function apply()
	{
		$this->letter->set('REITERATE', 'N');
		$this->letter->set('AUTO_SEND_TIME', null);
		$this->letter->save();
		if (!$this->letter->getState()->isReady())
		{
			$this->letter->getState()->ready();
		}
	}

	/**
	 * Revoke method.
	 *
	 * @return void
	 */
	public function revoke()
	{
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return Method::DEFERED;
	}
}