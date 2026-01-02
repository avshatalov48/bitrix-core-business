<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2025 Bitrix
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/classes/general/channel.php");

class CVoteChannel extends CAllVoteChannel
{
	public static function GetDropDownList()
	{
		global $DB;

		$sqlHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				" . $sqlHelper->getConcatFunction("'['", "ID", "']'", "TITLE" ) . " as REFERENCE
			FROM b_vote_channel
			ORDER BY C_SORT
			";
		$res = $DB->Query($strSql);
		return $res;
	}
}
