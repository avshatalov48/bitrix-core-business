<?php
namespace Bitrix\Forum;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class ForumSort
{
	private const LAST_POST_DATE = "P";
	private const TITLE = "T";
	private const POSTS = "N";
	private const VIEWS = "V";
	private const START_DATE = "D";
	private const USER_START_NAME = "A";

	/**
	 * Gets types list
	 * @return array
	 */
	public static function getList()
	{
		$res = (new \ReflectionClass(__CLASS__))->getConstants();
		$result = array();
		foreach ($res as $code => $id)
		{
			$result[$id] = Loc::getMessage("FORUM_SORT_".$code);
		}
		return $result;
	}
}