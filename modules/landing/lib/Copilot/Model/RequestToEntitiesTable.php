<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query;

/**
 * Class RequestToEntitiesTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RequestToEntities_Query query()
 * @method static EO_RequestToEntities_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RequestToEntities_Result getById($id)
 * @method static EO_RequestToEntities_Result getList(array $parameters = [])
 * @method static EO_RequestToEntities_Entity getEntity()
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToEntities createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToEntities_Collection createCollection()
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToEntities wakeUpObject($row)
 * @method static \Bitrix\Landing\Copilot\Model\EO_RequestToEntities_Collection wakeUpCollection($rows)
 */
class RequestToEntitiesTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName(): string
	{
		return 'b_landing_copilot_request_to_entities';
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
			(new Fields\StringField('ENTITY_TYPE'))
				->configureRequired()
			,
			(new Fields\IntegerField('LANDING_ID')),
			(new Fields\IntegerField('BLOCK_ID')),
			(new Fields\StringField('NODE_CODE')),
			(new Fields\IntegerField('POSITION'))
				->configureDefaultValue(0)
			,
			(new Fields\Relations\Reference(
				'STEP_REF',
				RequestToStepTable::class,
				Query\Join::on('this.REQUEST_ID', 'ref.REQUEST_ID')
			)),
			(new Fields\Relations\Reference(
				'REQUEST_REF',
				RequestsTable::class,
				Query\Join::on('this.REQUEST_ID', 'ref.ID')
			)),
		];
	}
}
