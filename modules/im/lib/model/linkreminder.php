<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

/**
 * Class LinkReminderTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> DATE_CREATE datetime mandatory
 * <li> AUTHOR_ID int mandatory
 * <li> DATE_REMIND datetime optional
 * <li> IS_REMINDED bool ('N', 'Y') optional default 'N'
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkReminder_Query query()
 * @method static EO_LinkReminder_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkReminder_Result getById($id)
 * @method static EO_LinkReminder_Result getList(array $parameters = [])
 * @method static EO_LinkReminder_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkReminder createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkReminder_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkReminder wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkReminder_Collection wakeUpCollection($rows)
 */

class LinkReminderTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_link_reminder';
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
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[
					'required' => true,
				]
			),
			'CHAT_ID' => new IntegerField(
				'CHAT_ID',
				[
					'required' => true,
				]
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
			'AUTHOR_ID' => new IntegerField(
				'AUTHOR_ID',
				[
					'required' => true,
				]
			),
			'DATE_REMIND' => new DatetimeField(
				'DATE_REMIND',
				[
					'required' => true,
				]
			),
			'IS_REMINDED' => new BooleanField(
				'IS_REMINDED',
				[
					'required' => true,
					'values' => array('N', 'Y'),
					'default' => 'N',
					'default_value' => false,
				]
			),
			'MESSAGE' => (new Reference(
				'MESSAGE',
				MessageTable::class,
				Join::on('this.MESSAGE_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		];
	}
}