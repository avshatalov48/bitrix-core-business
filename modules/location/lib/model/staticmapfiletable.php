<?php

namespace Bitrix\Location\Model;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields;

class StaticMapFileTable extends Main\ORM\Data\DataManager
{
	use Main\ORM\Data\Internal\MergeTrait;

	public static function getTableName()
	{
		return 'b_location_static_map_file';
	}

	public static function getMap()
	{
		return array(
			(new Fields\StringField('HASH'))
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(1, 40))
				->configurePrimary(),
			new Fields\IntegerField('FILE_ID'),
		);
	}
}
