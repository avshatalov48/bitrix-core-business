<?php
namespace Bitrix\Im\Update;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;


final class ChatOptionMigration extends Stepper
{
	protected static $moduleId = 'im';

	function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		Option::delete(self::$moduleId, ['name' => 'unconverted_settings_users']);
		Option::delete(self::$moduleId, ['name' => 'migration_to_new_settings']);
		Option::delete(self::$moduleId, ['name' => 'last_converted_user']);
		Option::delete(self::$moduleId, ['name' => 'im_options_migration_chat']);

		return false;
	}

}