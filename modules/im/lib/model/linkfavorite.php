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
 * Class FavoriteMessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> CHAT_ID int optional
 * <li> MESSAGE_AUTHOR_ID int optional
 * <li> SAVE_AUTHOR_ID int optional
 * <li> DATE_SAVE datetime mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkFavorite_Query query()
 * @method static EO_LinkFavorite_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkFavorite_Result getById($id)
 * @method static EO_LinkFavorite_Result getList(array $parameters = [])
 * @method static EO_LinkFavorite_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkFavorite createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkFavorite_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkFavorite wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkFavorite_Collection wakeUpCollection($rows)
 */

class LinkFavoriteTable extends DataManager
{
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_link_favorite';
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
				]
			),
			'AUTHOR_ID' => new IntegerField(
				'AUTHOR_ID',
				[
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