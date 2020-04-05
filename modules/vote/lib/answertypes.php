<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class AnswerTypes
{
	const RADIO = 0;
	const CHECKBOX = 1;
	const DROPDOWN = 2;
	const MULTISELECT = 3;
	const TEXT = 4;
	const TEXTAREA = 5;

	/**
	 * Gets types list
	 * @return array
	 */
	public static function getFullList()
	{
		$res = (new \ReflectionClass(__CLASS__))->getConstants();
		$result = array();
		foreach ($res as $code => $id)
		{
			$result[] = array("ID" => $id, "CODE" => $code, "TITLE" => Loc::getMessage("VOTE_ANSWER_TYPE_".$code));
		}
		return $result;
	}

	/**
	 * Returns array of types
	 * @return array
	 */
	public static function getTitledList()
	{
		$res = (new \ReflectionClass(__CLASS__))->getConstants();
		$result = array();
		foreach ($res as $code => $id)
		{
			$result[$id] = Loc::getMessage("VOTE_ANSWER_TYPE_".$code);
		}
		return $result;
	}

	/**
	 * Returns type title.
	 * @param (int|string) $id Field type like (0 - radio).
	 * @return string
	 */
	public static function getTitleById($id)
	{
		$res = array_flip((new \ReflectionClass(__CLASS__))->getConstants());
		$val = $id;
		if (array_key_exists($id, $res))
		{
			$val = Loc::getMessage("VOTE_ANSWER_TYPE_".$res[$id]);
		}
		return $val;
	}
}