<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Model;

use Bitrix\Main\Messenger\Internals\Storage\Db\Model\MessengerMessageTable;

/**
 * Class MessengerTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Messenger_Query query()
 * @method static EO_Messenger_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Messenger_Result getById($id)
 * @method static EO_Messenger_Result getList(array $parameters = [])
 * @method static EO_Messenger_Entity getEntity()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Messenger createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Messenger_Collection createCollection()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Messenger wakeUpObject($row)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_Messenger_Collection wakeUpCollection($rows)
 */
class MessengerTable extends MessengerMessageTable
{
	public static function getTableName(): string
	{
		return 'b_calendar_messenger_message';
	}
}
