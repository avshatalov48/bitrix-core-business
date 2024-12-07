<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\MultiplyInsertTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class CounterOverflowTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> CHAT_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Im
 **/

class CounterOverflowTable extends DataManager
{
	use MultiplyInsertTrait;
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_counter_overflow';
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
		];
	}
}