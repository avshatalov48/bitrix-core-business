<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\InsertSelectTrait;
use Bitrix\Im\V2\Common\MultiplyInsertTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

/**
 * Class MessageReadTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> DATE_CREATE datetime optional default current datetime
 * <li> REACTION string(50) optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageViewed_Query query()
 * @method static EO_MessageViewed_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_MessageViewed_Result getById($id)
 * @method static EO_MessageViewed_Result getList(array $parameters = [])
 * @method static EO_MessageViewed_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_MessageViewed createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_MessageViewed_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_MessageViewed wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_MessageViewed_Collection wakeUpCollection($rows)
 */

class MessageViewedTable extends DataManager
{
	use MultiplyInsertTrait;
	use MergeTrait;
	use InsertSelectTrait;
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_message_viewed';
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
					'default' => function()
					{
						return new DateTime();
					},
				]
			),
		];
	}

	/**
	 * Returns validators for REACTION field.
	 *
	 * @return array
	 */
	public static function validateReaction(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}