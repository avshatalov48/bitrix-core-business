<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Model;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class PushTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Push_Query query()
 * @method static EO_Push_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Push_Result getById($id)
 * @method static EO_Push_Result getList(array $parameters = [])
 * @method static EO_Push_Entity getEntity()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Push createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Push_Collection createCollection()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Push wakeUpObject($row)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Push_Collection wakeUpCollection($rows)
 */
class PushTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_calendar_push';
	}

	public static function getMap(): array
	{
		return [
			(new StringField(
				'ENTITY_TYPE',
				[
					'validation' => [__CLASS__, 'validateEntityType']
				]
			))
				->configureTitle(Loc::getMessage('PUSH_ENTITY_ENTITY_TYPE_FIELD'))
				->configurePrimary(true)
			,
			(new IntegerField('ENTITY_ID'))
				->configureTitle(Loc::getMessage('PUSH_ENTITY_ENTITY_ID_FIELD'))
				->configurePrimary(true)
			,
			(new StringField(
				'CHANNEL_ID',
				[
					'validation' => [__CLASS__, 'validateChannelId']
				]
			))
				->configureTitle(Loc::getMessage('PUSH_ENTITY_CHANNEL_ID_FIELD'))
				->configureRequired(true)
			,
			(new StringField(
				'RESOURCE_ID',
				[
					'validation' => [__CLASS__, 'validateResourceId']
				]
			))
				->configureTitle(Loc::getMessage('PUSH_ENTITY_RESOURCE_ID_FIELD'))
				->configureRequired(true)
			,
			(new DatetimeField('EXPIRES'))
				->configureTitle(Loc::getMessage('PUSH_ENTITY_EXPIRES_FIELD'))
				->configureRequired(true)
			,
			(new EnumField('NOT_PROCESSED'))
				->configureTitle(Loc::getMessage('PUSH_ENTITY_NOT_PROCESSED_FIELD'))
				->configureValues(['N', 'Y', 'B', 'U'])
				->configureDefaultValue('N')
			,
			(new DatetimeField('FIRST_PUSH_DATE'))
				->configureTitle(Loc::getMessage('PUSH_ENTITY_FIRST_PUSH_DATE_FIELD'))
			,
		];
	}

	/**
	 * Returns validators for ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEntityType(): array
	{
		return [
			new LengthValidator(null, 24),
		];
	}

	/**
	 * Returns validators for CHANNEL_ID field.
	 *
	 * @return array
	 */
	public static function validateChannelId(): array
	{
		return [
			new LengthValidator(null, 128),
		];
	}

	/**
	 * Returns validators for RESOURCE_ID field.
	 *
	 * @return array
	 */
	public static function validateResourceId(): array
	{
		return [
			new LengthValidator(null, 128),
		];
	}
}
