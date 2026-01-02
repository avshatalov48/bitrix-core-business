<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Model;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Psr\Log\LogLevel;

/**
 * Class CalendarLogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CalendarLog_Query query()
 * @method static EO_CalendarLog_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CalendarLog_Result getById($id)
 * @method static EO_CalendarLog_Result getList(array $parameters = [])
 * @method static EO_CalendarLog_Entity getEntity()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_CalendarLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_CalendarLog_Collection createCollection()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_CalendarLog wakeUpObject($row)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_CalendarLog_Collection wakeUpCollection($rows)
 */
class CalendarLogTable extends DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName(): string
	{
		return 'b_calendar_log';
	}

	/**
	 * @return array
	 */
	public static function getMap(): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return [
			(new IntegerField('ID'))
				->configureTitle(Loc::getMessage('LOG_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true)
			,
			(new DatetimeField('TIMESTAMP_X'))
				->configureTitle(Loc::getMessage('LOG_ENTITY_TIMESTAMP_X_FIELD'))
			,
			(new StringField('LEVEL'))
				->configureRequired()
				->addValidator(new LengthValidator(4, 9))
				->configureDefaultValue(LogLevel::DEBUG)
			,
			(new TextField('MESSAGE'))
				->configureTitle(Loc::getMessage('LOG_ENTITY_MESSAGE_FIELD'))
			,
			(new StringField('TYPE'))
				->configureTitle(Loc::getMessage('LOG_ENTITY_TYPE_FIELD'))
			,
			(new StringField('UUID'))
				->configureTitle(Loc::getMessage('LOG_ENTITY_UUID_FIELD'))
			,
			(new IntegerField('USER_ID'))
				->configureTitle(Loc::getMessage('LOG_ENTITY_USER_ID_FIELD'))
			,
			(new TextField('CONTEXT')),
		];
	}
}
