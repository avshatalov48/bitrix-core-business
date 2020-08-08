<?php
namespace Bitrix\Im\Integration\Crm;

use \Bitrix\Main\Loader;

class Common
{
	/**
	 * @param $type
	 * @param null $id
	 * @return bool|mixed|string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getLink($type, $id = null)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		$defaultValue = false;
		if (is_null($id))
		{
			$defaultValue = true;
			$id = 0;
		}

		$result = \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::ResolveID($type), $id, false);

		if ($defaultValue)
		{
			$result = str_replace($id, '#ID#', $result);
		}

		return $result;
	}
}