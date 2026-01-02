<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Model;

use Bitrix\Landing\Copilot\Model\Helper\EmojiDataProcessor;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Type\DateTime;

/**
 * Class RequestsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Requests_Query query()
 * @method static EO_Requests_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Requests_Result getById($id)
 * @method static EO_Requests_Result getList(array $parameters = [])
 * @method static EO_Requests_Entity getEntity()
 * @method static \Bitrix\Landing\Copilot\Model\EO_Requests createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Copilot\Model\EO_Requests_Collection createCollection()
 * @method static \Bitrix\Landing\Copilot\Model\EO_Requests wakeUpObject($row)
 * @method static \Bitrix\Landing\Copilot\Model\EO_Requests_Collection wakeUpCollection($rows)
 */
class RequestsTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName(): string
	{
		return 'b_landing_copilot_requests';
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
			(new Fields\StringField('HASH'))
				->configureTitle('Hash of AI-request')
			,
			(new Fields\ArrayField('RESULT'))
				->configureTitle('Result of AI-request')
				->configureNullable()
				->configureSerializationPhp()
				->addSaveDataModifier([EmojiDataProcessor::class, 'encode'])
				->addFetchDataModifier([EmojiDataProcessor::class, 'decode'])
			,
			(new Fields\ArrayField('ERROR'))
				->configureTitle('Error of AI-request')
				->configureNullable()
				->configureSerializationPhp()
			,
			(new Fields\BooleanField('DELETED'))
				->configureTitle('If request was deleted')
				->configureRequired()
				->configureStorageValues('N', 'Y')
				->configureDefaultValue(false)
			,
			(new Fields\DatetimeField('DATE_CREATE'))
				->configureRequired()
				->configureDefaultValue(function () {
					return new DateTime();
				})
			,
			(new Fields\DatetimeField('DATE_RECEIVE'))
				->configureTitle('When the response was received')
				->configureNullable()
			,
			(new Fields\Relations\Reference(
				'STEP_REF',
				RequestToStepTable::class,
				Query\Join::on('this.ID', 'ref.REQUEST_ID')
			)),
		];
	}
}
