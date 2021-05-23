<?php
namespace Bitrix\Im\Update;

class Bot
{
	public static function removeDepartmentLinkAgent()
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
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

		$departmentId = \Bitrix\Im\Bot\Department::getId(true);
		if ($departmentId && \CModule::IncludeModule('iblock'))
		{
			\CIBlockSection::Delete($departmentId);
		}

		return "";
	}
}