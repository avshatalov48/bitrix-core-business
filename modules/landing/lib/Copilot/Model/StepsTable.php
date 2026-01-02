<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Type\DateTime;

 /**
 * Class StepsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Steps_Query query()
 * @method static EO_Steps_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Steps_Result getById($id)
 * @method static EO_Steps_Result getList(array $parameters = [])
 * @method static EO_Steps_Entity getEntity()
 * @method static \Bitrix\Landing\Copilot\Model\EO_Steps createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Copilot\Model\EO_Steps_Collection createCollection()
 * @method static \Bitrix\Landing\Copilot\Model\EO_Steps wakeUpObject($row)
 * @method static \Bitrix\Landing\Copilot\Model\EO_Steps_Collection wakeUpCollection($rows)
 */
class StepsTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName(): string
	{
		return 'b_landing_copilot_steps';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap(): array
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new Fields\IntegerField('GENERATION_ID'))
				->configureTitle('ID or relation generation')
				->configureRequired()
			,
			(new Fields\IntegerField('STEP_ID'))
				->configureTitle('Status of step executing')
				->configureRequired()
			,
			(new Fields\StringField('CLASS'))
				->configureTitle('Class of current step')
				->configureRequired()
			,
			(new Fields\IntegerField('STATUS'))
				->configureTitle('Status of step executing')
				->configureRequired()
			,
		];
	}
}
