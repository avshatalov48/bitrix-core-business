<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class LastMessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> DATE_CREATE datetime mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LastMessage_Query query()
 * @method static EO_LastMessage_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LastMessage_Result getById($id)
 * @method static EO_LastMessage_Result getList(array $parameters = [])
 * @method static EO_LastMessage_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LastMessage createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LastMessage_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LastMessage wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LastMessage_Collection wakeUpCollection($rows)
 */

class LastMessageTable extends DataManager
{
	use MergeTrait;
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_last_message';
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
			'USER_ID' => new IntegerField(
				'USER_ID',
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
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[
					'required' => true,
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
				]
			),
		];
	}
}