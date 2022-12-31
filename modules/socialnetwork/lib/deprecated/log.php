<?php

namespace Bitrix\Socialnetwork\Deprecated;

class Log
{
	/**
	 * @param $userId
	 * @return string
	 * @deprecated
	 */
	public static function __InitUserTmp($userId)
	{
		$title = '';

		$res = \CUser::getById($userId);
		if ($userFields = $res->GetNext())
		{
			$title .= \CSocNetUser::FormatName($userFields["NAME"], $userFields["LAST_NAME"], $userFields["LOGIN"]);
		}

		return $title;
	}

	/**
	 * @param $message
	 * @param $titleTemplate1
	 * @param $titleTemplate2
	 * @return string
	 * @deprecated
	 */
	public static function __InitUsersTmp($message, $titleTemplate1, $titleTemplate2)
	{
		$usersIdList = explode(',', $message);

		$title = '';

		$first = true;
		$count = 0;
		foreach ($usersIdList as $userId)
		{
			$titleTmp = self::__InitUserTmp($userId);

			if ($titleTmp !== '')
			{
				if (!$first)
				{
					$title .= ", ";
				}

				$title .= $titleTmp;
				$count++;
			}

			$first = false;
		}

		return str_replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1));
	}

	/**
	 * @param $groupId
	 * @return string
	 * @deprecated
	 */
	public static function __InitGroupTmp($groupId)
	{
		$title = '';

		$groupFields = \CSocNetGroup::GetByID($groupId);
		if ($groupFields)
		{
			$title .= $groupFields["NAME"];
		}

		return $title;
	}

	/**
	 * @param $message
	 * @param $titleTemplate1
	 * @param $titleTemplate2
	 * @return string
	 * @deprecated
	 */
	public static function __InitGroupsTmp($message, $titleTemplate1, $titleTemplate2)
	{
		$groupsIdList = explode(',', $message);

		$title = "";

		$bFirst = true;
		$count = 0;
		foreach ($groupsIdList as $groupId)
		{
			$titleTmp = self::__InitGroupTmp($groupId);

			if ($titleTmp !== '')
			{
				if (!$bFirst)
				{
					$title .= ", ";
				}

				$title .= $titleTmp;
				$count++;
			}

			$bFirst = false;
		}

		return str_replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1));
	}
}
