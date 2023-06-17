<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

/**
 * Class LinkPinTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> DATE_CREATE int mandatory
 * <li> AUTHOR_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkPin_Query query()
 * @method static EO_LinkPin_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkPin_Result getById($id)
 * @method static EO_LinkPin_Result getList(array $parameters = [])
 * @method static EO_LinkPin_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkPin createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkPin_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkPin wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkPin_Collection wakeUpCollection($rows)
 */

class LinkPinTable extends DataManager
{
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_link_pin';
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
			'AUTHOR_ID' => new IntegerField(
				'AUTHOR_ID',
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
			'AUTHOR' => (new Reference(
				'AUTHOR',
				UserTable::class,
				Join::on('this.AUTHOR_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		];
	}
}