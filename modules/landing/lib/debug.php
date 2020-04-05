<?php
namespace Bitrix\Landing;

class Debug
{
	/**
	 * Gets last query in ORM.
	 * @return string
	 */
	public static function q()
	{
		return \Bitrix\Main\Entity\Query::getLastQuery();
	}
}