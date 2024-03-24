<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\SystemException,
	Bitrix\Main\ORM\Data;



/**
 * Class OptionUserTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> GROUP_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Im
 **/

class OptionUserTable extends DataManager
{
	use Data\Internal\MergeTrait;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_im_option_user';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws SystemException
	 */
	public static function getMap(): array
	{
		return [
			'USER_ID' => (new IntegerField('USER_ID', [
				'primary' => true,
			])),
			'NOTIFY_GROUP_ID' => (new IntegerField('NOTIFY_GROUP_ID', [])),
			'GENERAL_GROUP_ID' => (new IntegerField('GENERAL_GROUP_ID', [])),
		];
	}
}