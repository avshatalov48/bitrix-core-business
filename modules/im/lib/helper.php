<?php
namespace Bitrix\Im;

class Helper
{
	public static function getOnlineIntranetUsers()
	{
		$users = \Bitrix\Main\UserTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=IS_REAL_USER' => 'Y',
                '=IS_ONLINE' => 'Y',
              	'!=UF_DEPARTMENT' => false
            ]
        ])->fetchAll();

		return array_map(function($item) { return $item['ID']; }, $users);
	}
}


