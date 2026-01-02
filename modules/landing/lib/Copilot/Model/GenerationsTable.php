<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Model;

use Bitrix\Landing\Copilot\Model\Helper\EmojiDataProcessor;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Type\DateTime;

/**
 * Class GenerationsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Generations_Query query()
 * @method static EO_Generations_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Generations_Result getById($id)
 * @method static EO_Generations_Result getList(array $parameters = [])
 * @method static EO_Generations_Entity getEntity()
 * @method static \Bitrix\Landing\Copilot\Model\EO_Generations createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Copilot\Model\EO_Generations_Collection createCollection()
 * @method static \Bitrix\Landing\Copilot\Model\EO_Generations wakeUpObject($row)
 * @method static \Bitrix\Landing\Copilot\Model\EO_Generations_Collection wakeUpCollection($rows)
 */
class GenerationsTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName(): string
	{
		return 'b_landing_copilot_generations';
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
			(new Fields\StringField('SCENARIO'))
				->configureRequired()
			,
			(new Fields\IntegerField('STEP')),
			(new Fields\IntegerField('CHAT_ID'))
				->configureNullable()
			,
			(new Fields\IntegerField('SITE_ID'))
				->configureNullable()
			,
			(new Fields\IntegerField('BLOCK_ID'))
				->configureNullable()
			,
			(new Fields\ArrayField('SITE_DATA'))
				->configureSerializationPhp()
				->addSaveDataModifier([EmojiDataProcessor::class, 'encode'])
				->addFetchDataModifier([EmojiDataProcessor::class, 'decode'])
			,
			(new Fields\ArrayField('DATA'))
				->configureSerializationPhp()
				->configureNullable()
				->configureDefaultValue(null)
			,
			(new Fields\IntegerField('CREATED_BY_ID'))
				->configureRequired()
			,
			(new Fields\DatetimeField('DATE_CREATE'))
				->configureRequired()
				->configureDefaultValue(function ()
				{
					return new DateTime();
				})
			,
			(new Fields\DatetimeField('DATE_FINISHED'))
				->configureNullable()
			,
		];
	}
}
