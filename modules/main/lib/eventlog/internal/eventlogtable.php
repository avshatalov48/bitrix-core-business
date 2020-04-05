<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Main\EventLog\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

class EventLogTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_event_log';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("ID"))
				->configurePrimary(true)
				->configureAutocomplete(true),
			(new Fields\DatetimeField("TIMESTAMP_X")),
			(new Fields\StringField("SEVERITY")),
			(new Fields\StringField("AUDIT_TYPE_ID")),
			(new Fields\StringField("MODULE_ID")),
			(new Fields\StringField("ITEM_ID")),
			(new Fields\StringField("REMOTE_ADDR")),
			(new Fields\TextField("USER_AGENT")),
			(new Fields\TextField("REQUEST_URI")),
			(new Fields\StringField("SITE_ID")),
			(new Fields\IntegerField("USER_ID")),
			(new Fields\IntegerField("GUEST_ID")),
			(new Fields\TextField("DESCRIPTION")),
		];
	}
}
