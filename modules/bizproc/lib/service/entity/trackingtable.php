<?php

namespace Bitrix\Bizproc\Service\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class TrackingTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Tracking_Query query()
 * @method static EO_Tracking_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Tracking_Result getById($id)
 * @method static EO_Tracking_Result getList(array $parameters = [])
 * @method static EO_Tracking_Entity getEntity()
 * @method static \Bitrix\Bizproc\Service\Entity\EO_Tracking createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Service\Entity\EO_Tracking_Collection createCollection()
 * @method static \Bitrix\Bizproc\Service\Entity\EO_Tracking wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Service\Entity\EO_Tracking_Collection wakeUpCollection($rows)
 */
class TrackingTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_bp_tracking';
	}

	public static function getMap()
	{
		return [
			(new Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new Entity\StringField('WORKFLOW_ID'))
				->addValidator(new LengthValidator(1, 32)),

			new Entity\IntegerField('TYPE'),

			new Entity\DatetimeField('MODIFIED'),

			(new Entity\StringField('ACTION_NAME'))
				->addValidator(new LengthValidator(0, 128)),

			(new Entity\StringField('ACTION_TITLE'))
				->addValidator(new LengthValidator(0, 255)),

			(new Entity\IntegerField('EXECUTION_STATUS'))
				->configureDefaultValue(0),

			(new Entity\IntegerField('EXECUTION_RESULT'))
				->configureDefaultValue(0),

			(new Entity\TextField('ACTION_NOTE'))
				->configureNullable(),

			(new Entity\IntegerField('MODIFIED_BY'))
				->configureNullable(),

			(new Entity\StringField('COMPLETED'))
				->configureDefaultValue('N')
				->addValidator(new LengthValidator(0, 1)),
		];
	}
}