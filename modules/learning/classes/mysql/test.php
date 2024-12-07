<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/test.php");

class CTest extends CAllTest
{
	public static function GetRandFunction()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		return $connection->getSqlHelper()->getRandomFunction();
	}
}
