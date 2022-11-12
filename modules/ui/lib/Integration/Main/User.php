<?php
namespace Bitrix\UI\Integration\Main;

use \Bitrix\UI\Avatar\Mask;

class User
{
	public static function onDelete($userId): void
	{
		(new Mask\Owner\User($userId))->delete();
	}
}