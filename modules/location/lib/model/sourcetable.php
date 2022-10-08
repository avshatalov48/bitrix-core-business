<?php

namespace Bitrix\Location\Model;

use Bitrix\Main;
use	Bitrix\Main\ORM\Fields;

/**
 * Class SourceTable
 * @package Bitrix\Location\Model
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Source_Query query()
 * @method static EO_Source_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Source_Result getById($id)
 * @method static EO_Source_Result getList(array $parameters = array())
 * @method static EO_Source_Entity getEntity()
 * @method static \Bitrix\Location\Model\EO_Source createObject($setDefaultValues = true)
 * @method static \Bitrix\Location\Model\EO_Source_Collection createCollection()
 * @method static \Bitrix\Location\Model\EO_Source wakeUpObject($row)
 * @method static \Bitrix\Location\Model\EO_Source_Collection wakeUpCollection($rows)
 */
class SourceTable extends Main\ORM\Data\DataManager
{
	/**
	 * @inheritDoc
	 */
	public static function getTableName()
	{
		return 'b_location_source';
	}

	/**
	 * @inheritDoc
	 */
	public static function getMap()
	{
		return [
			(new Fields\StringField('CODE'))
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(1, 15))
				->configurePrimary(true),
			(new Fields\StringField('NAME'))
				->configureRequired(true)
				->addValidator(new Main\ORM\Fields\Validators\LengthValidator(1, 255)),
			new Fields\TextField('CONFIG'),
		];
	}
}
