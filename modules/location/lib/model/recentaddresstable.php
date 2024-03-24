<?php

namespace Bitrix\Location\Model;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields;

class RecentAddressTable extends Main\ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_location_recent_address';
	}

	public static function getMap()
	{
		return array(
			(new Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			new Fields\IntegerField('USER_ID'),
			new Fields\TextField('ADDRESS'),
			new Fields\DatetimeField('USED_AT', [
				'default_value' => static fn() => new Main\Type\DateTime(),
			]),
		);
	}
}
