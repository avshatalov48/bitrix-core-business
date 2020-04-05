<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote\Vote;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Anonymity
{
	const UNDEFINED = 0;
	const PUBLICLY = 1;
	const ANONYMOUSLY = 2;

	/**
	 * Gets types list
	 * @return array
	 */
	public static function getList()
	{
		return (new \ReflectionClass(__CLASS__))->getConstants();
	}
	/**
	 * Gets types list
	 * @return array
	 */
	public static function getTitledList()
	{
		$res = (new \ReflectionClass(__CLASS__))->getConstants();
		$result = array();
		foreach ($res as $code => $id)
		{
			$result[$id] = Loc::getMessage("VOTE_ANONYMITY_TYPE_".$code);
		}
		return $result;
	}

	/**
	 * @return array
	 */
	public static function getValues()
	{
		return array_values(self::getList());
	}
	/**
	 * Returns visibility user voting result for others.
	 * @param bool $userValue
	 * @param $voteValue
	 * @return bool
	 */
	public static function isUserVoteVisible(bool $userValue, int $voteValue)
	{
		if ($voteValue === self::ANONYMOUSLY)
			return false;
		else if ($voteValue === self::PUBLICLY)
			return true;
		return $userValue !== false;
	}
	public static function getTitle()
	{
		return Loc::getMessage("VOTE_ANONYMITY_TITLE");
	}
	public static function validate($value)
	{

	}
}