<?php

namespace Bitrix\Location\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields;

class AreaTable extends DataManager
{
	/**
	 * @inheritDoc
	 */
	public static function getTableName()
	{
		return 'b_location_area';
	}

	/**
	 * @inheritDoc
	 */
	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),
			(new Fields\StringField('TYPE'))
				->configureRequired(true)
				->addValidator(new LengthValidator(1, 255)),
			(new Fields\StringField('CODE'))
				->addValidator(new LengthValidator(null, 255)),
			(new Fields\IntegerField('SORT'))
				->configureDefaultValue(100),
			(new Fields\StringField('GEOMETRY'))
				->configureRequired(true),
		];
	}
}
