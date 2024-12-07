<?php
namespace Bitrix\Im\Update;

use Bitrix\Im\V2\Integration\HumanResources\Department\Department;
use Bitrix\Main\Loader;

class Bot
{
	public static function removeDepartmentLinkAgent()
	{
		if (
			!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
			|| !Loader::includeModule('iblock')
		)
		{
			return "";
		}

		$result = \Bitrix\Main\UserTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=EXTERNAL_AUTH_ID' => \Bitrix\Im\Bot::EXTERNAL_AUTH_ID,
				'!UF_DEPARTMENT' => false
			]
		]);

		$user = new \CUser;
		while($row = $result->fetch())
		{
			$user->Update($row['ID'], ['UF_DEPARTMENT' => []]);
		}

		$botDepartments = Department::getInstance()->getListByXml('im_bot');

		foreach ($botDepartments as $department)
		{
			if ($department->id !== null)
			{
				\CIBlockSection::Delete($department->id);
			}
		}

		return "";
	}
}
