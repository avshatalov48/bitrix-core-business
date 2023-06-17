<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\UserTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

/**
 * Class ChatPinnedMessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHAT_ID int optional
 * <li> MESSAGE_ID int optional
 * <li> PIN_AUTHOR_ID int optional
 * <li> DATE_CREATE datetime mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ChatPinnedMessage_Query query()
 * @method static EO_ChatPinnedMessage_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ChatPinnedMessage_Result getById($id)
 * @method static EO_ChatPinnedMessage_Result getList(array $parameters = [])
 * @method static EO_ChatPinnedMessage_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_ChatPinnedMessage createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_ChatPinnedMessage_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_ChatPinnedMessage wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_ChatPinnedMessage_Collection wakeUpCollection($rows)
 */

class ChatPinnedMessageTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_chat_pinned_message';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			'CHAT_ID' => new IntegerField(
				'CHAT_ID',
				[]
			),
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[]
			),
			'PIN_AUTHOR_ID' => new IntegerField(
				'PIN_AUTHOR_ID',
				[]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
					'default_value' => static function() {
						return new DateTime();
					}
				]
			),
			'MESSAGE' => (new Reference(
				'MESSAGE',
				MessageTable::class,
				Join::on('this.MESSAGE_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
			'CHAT' => (new Reference(
				'CHAT',
				ChatTable::class,
				Join::on('this.CHAT_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
			'PIN_AUTHOR' => (new Reference(
				'PIN_AUTHOR',
				UserTable::class,
				Join::on('this.PIN_AUTHOR_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_LEFT)
		];
	}
}