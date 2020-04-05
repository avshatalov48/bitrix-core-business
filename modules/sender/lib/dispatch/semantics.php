<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class State
 * @package Bitrix\Sender\Dispatch
 */
class Semantics
{
	const FINISHED = 1;
	const READY = 2;
	const WORKING = 3;

	/**
	 * Get finish states.
	 *
	 * @return array
	 */
	public static function getFinishStates()
	{
		return self::getStates(self::FINISHED);
	}

	/**
	 * Get ready states.
	 *
	 * @return array
	 */
	public static function getReadyStates()
	{
		return self::getStates(self::READY);
	}

	/**
	 * Get work states.
	 *
	 * @return array
	 */
	public static function getWorkStates()
	{
		return self::getStates(self::WORKING);
	}

	/**
	 * Get states by semantic ID.
	 *
	 * @param integer $semanticId Semantic ID.
	 * @return array
	 */
	public static function getStates($semanticId)
	{
		switch ($semanticId)
		{
			case self::FINISHED:
				return array(
					State::SENT,
					State::STOPPED
				);

			case self::WORKING:
				return array(
					State::SENDING,
					State::PAUSED,
					State::WAITING,
					State::PLANNED,
					State::HALTED,
				);

			case self::READY:
			default:
				return array(
					State::READY,
					State::NEWISH,
					State::INIT
				);
		}
	}
}