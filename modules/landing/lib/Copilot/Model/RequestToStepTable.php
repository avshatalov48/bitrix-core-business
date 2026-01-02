<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;

/**
 * Class RequestToStepTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RequestToStep_Query query()
 * @method static EO_RequestToStep_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RequestToStep_Result getById($id)
 * @method static EO_RequestToStep_Result getList(array $parameters = [])
 * @method static EO_RequestToStep_Entity getEntity()
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToStep createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToStep_Collection createCollection()
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToStep wakeUpObject($row)
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToStep_Collection wakeUpCollection($rows)
 */
class RequestToStepTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName(): string
	{
		return 'b_landing_copilot_request_to_step';
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
			(new Fields\IntegerField('REQUEST_ID'))
				->configureRequired()
			,
			(new Fields\IntegerField('GENERATION_ID'))
				->configureRequired()
			,
			(new Fields\IntegerField('STEP'))
				->configureRequired()
			,
			(new Fields\BooleanField('APPLIED'))
				->configureRequired()
				->configureValues(0, 1)
				->configureDefaultValue(0)
			,
		];
	}
}
