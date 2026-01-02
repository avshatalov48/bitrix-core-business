<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Storage\Db\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;

/**
 * Class MessageStorageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessengerMessage_Query query()
 * @method static EO_MessengerMessage_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_MessengerMessage_Result getById($id)
 * @method static EO_MessengerMessage_Result getList(array $parameters = [])
 * @method static EO_MessengerMessage_Entity getEntity()
 * @method static \Bitrix\Main\Messenger\Internals\Storage\Db\Model\EO_MessengerMessage createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Messenger\Internals\Storage\Db\Model\EO_MessengerMessage_Collection createCollection()
 * @method static \Bitrix\Main\Messenger\Internals\Storage\Db\Model\EO_MessengerMessage wakeUpObject($row)
 * @method static \Bitrix\Main\Messenger\Internals\Storage\Db\Model\EO_MessengerMessage_Collection wakeUpCollection($rows)
 */
class MessengerMessageTable extends DataManager
{
	public static function onBeforeUpdate(Event $event): EventResult
	{
		$result = new EventResult();

		$result->modifyFields(['UPDATED_AT' => new DateTime()]);

		return $result;
	}

	public static function getTableName(): string
	{
		return 'b_main_messenger_message';
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new StringField('QUEUE_ID'))
				->configureRequired()
				->configureSize(255),

			(new StringField('ITEM_ID'))
				->configureNullable()
				->configureSize(64),

			(new StringField('CLASS'))
				->configureRequired(),

			(new TextField('PAYLOAD'))
				->configureRequired(),

			(new DatetimeField('CREATED_AT'))
				->configureRequired()
				->configureDefaultValueNow(),

			(new DatetimeField('UPDATED_AT'))
				->configureRequired()
				->configureDefaultValueNow(),

			(new IntegerField('TTL'))
				->configureRequired(),

			(new DatetimeField('AVAILABLE_AT'))
				->configureRequired()
				->configureDefaultValue(fn() => new DateTime()),

			(new StringField('STATUS'))
				->configureRequired()
				->configureDefaultValue(MessageStatus::New->value)
				->configureSize(10),
		];
	}
}
